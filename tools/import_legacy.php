<?php

/******************************************************************************
 * Dear user, please be kind.  Do NOT scrape an existing Addventure without
 * permission from the admin.  If you ARE the admin however, use the
 * URLRetriever at the end of this file and supply a LOCAL FILE PATH (i.e.,
 * not an actual URL) to it and DO NOT run this script on the web server,
 * because it does some heavy file operations which may slow its operation or
 * even may take it down.  If you already have migrated to a database, use
 * either the MySQLRetriever or write your own retriever.
 ******************************************************************************/

/**
 * Name of rooms. Make sure to escape PCRE chars and '#'.
 */
define('ROOMWORD', 'episode');

/**
 * Migration script for legacy Addventures.
 * 
 * This works roughly as follows:
 *   1. Retrieve all legacy episodes.
 *   2. Run through the imported episodes and transform them to the new layout.
 */

require_once '../doctrine-bootstrap.php';

define('BASEPATH', ''); // HACK to make the following require_once work
require_once '../application/helpers/xss_clean_helper.php';

/**
 * Basic utility functions for the import.
 */
class Util {

    /**
     * Tries to extract the parent episode ID from legacy HTML.
     * @param string $html Raw, legacy HTML.
     * @return null|int
     */
    public static function extractParent($html) {
        if(preg_match('|\<a href\="\.\.\/[0-9]+\/([0-9]+)\.html"\>Go back\<\/a\> - Go to the parent ' . ROOMWORD . '\.|isuS', $html, $matches)) {
            return (int) $matches[1];
        }
        else {
            return null;
        }
    }

}

/**
 * Interface for retrieving an episode.
 */
interface IRetriever {

    /**
     * Retrieves an episode.
     * @param int $episodeId The episode id
     * @return string|null Either the episode's contents, or NULL on an error.
     */
    function retrieve($episodeId);
}

/**
 * File retriever.
 */
class URLRetriever implements IRetriever {

    /**
     * @var string Base URL or File Path
     */
    private $base;

    /**
     * Constructor
     * @param string $base Base URL or File Path
     */
    public function __construct($base) {
        $this->base = $base;
    }

    public function retrieve($episodeId) {
        $raw = @file_get_contents(sprintf('%s/%03d/%d.html', $this->base, (int) ($episodeId / 1000), $episodeId));
        if($raw === null || $raw === false) {
            return null;
        }
        else {
            return $raw;
        }
    }

}

/**
 * MySQL database retriever.
 */
class MySQLRetriever implements IRetriever
{
    /**
     * @var mysql Database connection
     */
    private $sql;
    
    /**
     * @var string Table name
     */
    private $table;
    /**
     * @var string Legacy Episode ID column in the table
     */
    private $idColumn;
    
    /**
     * @var string Legacy HTML column in the table
     */
    private $textColumn;
    
    /**
     * Constructor
     * @param string $host DB host
     * @param string $user DB user name
     * @param string $password DB password
     * @param string $database DB Database with the episodes
     * @param string $table Table within the database with the episodes
     * @param string $idColumn Legacy Episode ID column in the table
     * @param string $textColumn Legacy HTML column in the table
     */
    public function __construct($host, $user, $password, $database, $table, $idColumn, $textColumn) {
        $this->sql = new mysqli($host, $user, $password, $database);
        $this->sql->set_charset('utf8');
        $this->sql->autocommit(FALSE);
        $this->table = $table;
        $this->idColumn = $idColumn;
        $this->textColumn = $textColumn;
    }
    
    public function retrieve($episodeId) {
        $q = $this->sql->query("SELECT $this->textColumn FROM $this->table WHERE $this->idColumn=$episodeId");
        if(!$q) {
            return null;
        }
        $txt = $q->fetch_assoc();
        if(!$txt) {
            return null;
        }
        return $txt[$this->textColumn];
    }

}


/**
 * Imports legacy episodes.
 */
class Importer {

    /**
     * @var int[] List of successfully imported episodes
     */
    private $imported = array();

    /**
     * @var int[] List of queued episodes to be imported
     */
    private $queued = array();

    /**
     * @var IRetriever
     */
    private $retriever;
    
    /**
     * @var boolean Whether to extract unwritten children IDs on initial import
     */
    private $followUnwritten;

    /**
     * Constructor
     * @param IRetriever $retriever Episode retriever
     * @param int $firstEpisode First episode to import, i.e. the root
     * @param boolean $followUnwritten Whether to initially try to import unwritten episodes
     */
    public function __construct(IRetriever $retriever, $firstEpisode = 0, $followUnwritten = false) {
        $this->retriever = $retriever;
        $this->queued[] = $firstEpisode;
        $this->followUnwritten = $followUnwritten;
    }

    /**
     * Clean up HTML by using Tidy.
     * @param string $html Dirty HTML
     * @param boolean $reencode Whether to convert from CP1252 to UTF8
     * @return null|string
     */
    private function cleanupHtml($html, $reencode = false) {
        $tidy = new tidy;
        if($reencode) {
            $html = mb_convert_encoding($html, 'UTF-8', 'CP1252');
        }
        $tidy->parseString(xss_clean2($html), array('show-body-only' => true, 'output-html' => true, 'wrap' => 200), 'utf8');
        if(!$tidy->cleanRepair()) {
            return null;
        }
        return (string) $tidy;
    }

    /**
     * Helper for creating legacy episode instances
     * @param int $id Legacy Episode ID
     * @param string $cleanedHtml Cleaned up HTML
     * @return \addventure\LegacyEpisode
     */
    private function createLegacyEpisode($id, $cleanedHtml) {
        $legacy = new addventure\LegacyEpisode();
        $legacy->setId($id);
        $legacy->setRawContent($cleanedHtml);
        return $legacy;
    }

    /**
     * Extract a legacy episode's children
     * @param string $html Legacy HTML
     * @param bool $skipUnwritten Whether to extract yet unwritten children IDs
     * @return int[] Array of children IDs
     */
    private function extractChildren($html, $skipUnwritten) {
        $result = array();
        if(preg_match_all('#(&gt;|\<li\>|\*)\<a href\="\.\.\/[0-9]+\/([0-9]+)\.html"\>.*?\<\/a\>\<\/li\>#isuS', '<li>' . $html, $liLinks, PREG_SET_ORDER)) {
            foreach($liLinks as $link) {
                $result[] = (int) $link[2];
            }
        }
        return $result;
    }

    /**
     * Determine if a legacy episode's HTML is only a placeholder
     * @param string $text
     * @return boolean
     */
    private function isPlaceholder($text) {
        if(!$text) {
            return true;
        }
        elseif(strpos($text, '<meta name="Description" content="Place-holder page for extension">')) {
            return true;
        }
        elseif(preg_match('|\<a href\="\.\.\/[0-9]+\/[0-9]+\.html"\>Cancel\<\/a\> - Do \<b\>not\<\/b\> create the ' . ROOMWORD . '\.|isuS', $text)) {
            return true;
        }
        return false;
    }

    /**
     * Import the next episode
     * @return boolean Are there more episodes to be imported?
     */
    public function importNext() {
        while(!empty($this->queued)) {
            $current = array_pop($this->queued);
            if(!in_array($current, $this->imported)) {
                break;
            }
        }
        if(!isset($current)) {
            return false;
        }

        $entityManager = initDoctrineConnection();
        if(($legacy = $entityManager->find('addventure\LegacyEpisode', $current))) {
            // already imported, so just use it passively...
            echo '[', count($this->imported), '+', count($this->queued), '] ';
            echo "#$current already retrieved --";
            $this->imported[] = $current;
            $raw = $legacy->getRawContent();
            $parent = Util::extractParent($raw);
            if($parent !== null) {
                echo " parent=$parent";
                $this->queued[] = $parent;
            }

            $children = $this->extractChildren($raw, !$this->followUnwritten);
            if(!empty($children)) {
                echo " children=", implode(' ', $children);
                foreach($children as $child) {
                    $this->queued[] = $child;
                }
            }
            echo "\n";
            $entityManager->clear();
            return true;
        }

        $raw = $this->retriever->retrieve($current);
        if($raw === null) {
            echo '[', count($this->imported), '+', count($this->queued), '] ';
            echo "#$current -- Retrieval failed!\n";
            initLogger()->error("#$current -- Retrieval failed!");
            return true;
        }
        elseif($this->isPlaceholder($raw)) {
            echo '[', count($this->imported), '+', count($this->queued), '] ';
            echo "#$current -- Placeholder.\n";
            return true;
        }

        $clean = $this->cleanupHtml($raw);
        if($clean === null) {
            echo '[', count($this->imported), '+', count($this->queued), '] ';
            echo "#$current -- Failed to clean up the HTML!\n";
            return true;
        }

        echo '[', count($this->imported), '+', count($this->queued), '] ';
        echo "#$current --";
        $parent = Util::extractParent($clean);
        if($parent !== null) {
            echo " parent=$parent";
            $this->queued[] = $parent;
        }

        $children = $this->extractChildren($clean, !$this->followUnwritten);
        if(!empty($children)) {
            echo " children=", implode(' ', $children);
            foreach($children as $child) {
                $this->queued[] = $child;
            }
        }
        echo "\n";

        $legacy = $this->createLegacyEpisode($current, $clean);
        $entityManager->persist($legacy);
        $entityManager->flush();
        $entityManager->clear();

        $this->imported[] = $current;

        echo '[', count($this->imported), '+', count($this->queued), '] ';
        echo "#$current -- Success.\n";

        return true;
    }

    /**
     * Import ALL episodes.
     */
    public function importAll() {
        echo "Retrieving raw episodes... go get some coffee!\n";
        while($this->importNext()) {
            // run run run...
        }
        echo "Retrieved all episodes.\n";
    }

    /**
     * Get all successfully imported episode IDs.
     * @return int[]
     */
    public function getImported() {
        return $this->imported;
    }

}


/**
 * Transform all legacy episodes to the new infrastructure.
 */
class Transformer {

    /**
     * @var int[] Queue of episode IDs to be transformed.
     */
    private $queue;
    
    /**
     * @var int Initial count of queued episode IDs.
     */
    private $totalEpisodes;

    /**
     * Constructor
     * @param int[] $queue Episode IDs to be imported
     */
    public function __construct(array $queue) {
        $this->queue = $queue;
        $this->totalEpisodes = count($queue);
        sort($queue);
    }

    /**
     * Extract the tag-stripped title from the legacy HTML
     * @param string $html
     * @return string
     */
    private function extractTitle($html) {
        if(preg_match('|\<h1\>\s*(.*?)\s*\<\/h1\>|isuS', $html, $matches)) {
            return strip_tags($matches[1]);
        }
        else {
            return '';
        }
    }

    /**
     * Tidy up and XSS clean legacy HTML
     * @param string $text Legacy HTML
     * @return string|null
     */
    private function cleanupHtml($text) {
        $tidy = new tidy;
        $tidy->parseString($text, array('show-body-only' => true, 'output-html' => true, 'wrap' => 200), 'utf8');
        if(!$tidy->cleanRepair()) {
            return null;
        }
        $clean = (string) $tidy;
        if(empty($clean)) {
            return '<div class="alert alert-warning">No text in this episode.</div>';
        }
        else {
            return $clean;
        }
    }

    /**
     * Extract the episode's content
     * @param string $html
     * @return string
     */
    private function extractText($html) {
        $titleLess = explode('</h2>', $html, 2);
        if(!isset($titleLess[1])) {
            return '<div class="alert alert-warning">No text in this episode.</div>';
        }
        else {
            $titleLess = $titleLess[1];
        }

        $content = explode('<ol>', $titleLess);
        array_pop($content);
        $content = trim(implode('<ol>', $content));

        if(empty($content)) {
            return '<div class="alert alert-warning">No text in this episode.</div>';
        }
        else {
            return $this->cleanupHtml($content);
        }
    }

    /**
     * Try to extract the creation date from a legacy episode
     * @param string $html
     * @return null|DateTime
     */
    private function extractCreationDate($html) {
        if(preg_match('|\<\/address\>\<p\>[ \r\n\t]*([^<]+)|isuS', $html, $matches)) {
            $d = DateTime::createFromFormat('D M j G:i:s Y', $matches[1]);
            if(!$d) {
                $d = DateTime::createFromFormat('D M  j G:i:s Y', $matches[1]);
            }
            if(!$d) {
                $d = DateTime::createFromFormat('D M d G:i:s Y', $matches[1]);
            }
            if(!$d) {
                return null;
            }
            else {
                return $d;
            }
        }
        else {
            return null;
        }
    }

    /**
     * Extract the children with some meta-information from the legacy episode
     * @param string $text
     * @return array[]
     */
    private function extractChildren($text) {
        $result = array();
        if(preg_match_all('#(&gt;|\<li\>|\*)\<a href\="\.\.\/[0-9]+\/([0-9]+)\.html"\>(.*?)\<\/a\>\<\/li\>#isuS', '<li>' . $text, $matches, PREG_SET_ORDER)) {
            foreach($matches as $match) {
                $result[] = array(
                    'id' => (int) $match[2],
                    'title' => simplifyWhitespace(strip_tags($match[3]), 9999, true),
                    'isBacklink' => ($match[1] === '&gt;')
                );
            }
        }
        return $result;
    }

    /**
     * Associates an episode with an author name
     * @param string $author Legacy author name
     * @param addventure\Episode $ep The new episode
     */
    private function findOrCreateAuthor($author, addventure\Episode &$ep) {
        $author = simplifyWhitespace($author, 9999);
        if(empty($author)) {
            return;
        }
        $entityManager = initDoctrineConnection();
        $tmp = $entityManager->getRepository('addventure\AuthorName')->findOneBy(array('name' => $author));
        if($tmp) {
            $ep->setAuthor($tmp);
            $tmp->getEpisodes()->add($ep);
            $entityManager->persist($tmp);
            echo "author=``" . $tmp->getName() . "'' ";
            return;
        }

        $nAuthor = new addventure\AuthorName();
        $nAuthor->setUser(new addventure\User());
        try {
            $nAuthor->setName($author);
            $nAuthor->getUser()->setUsername($author);
            $nAuthor->getUser()->getAuthorNames()->add($nAuthor);
        }
        catch(\InvalidArgumentException $ex) {
            echo "author name too long ";
            return;
        }
        $ep->setAuthor($nAuthor);
        $nAuthor->getEpisodes()->add($ep);
        $entityManager->persist($nAuthor);
        $entityManager->persist($nAuthor->getUser());
        echo "author=``$author'' ";
    }

    /**
     * Transforms a single episode
     * @return boolean Whether there are more episodes to be transformed
     * @throws \RuntimeException
     */
    public function transformNext() {
        if(empty($this->queue)) {
            return false;
        }
        $current = array_pop($this->queue);

        $entityManager = initDoctrineConnection();
        $legacy = $entityManager->find('addventure\LegacyEpisode', $current);
        if($legacy->getEpisode() !== null && $legacy->getEpisode()->getText() !== null) {
            echo '[', $this->totalEpisodes-count($this->queue), '/', $this->totalEpisodes, "] #$current already parsed\n";
            $entityManager->clear();
            return true;
        }

        $text = $legacy->getRawContent();

        echo '[', $this->totalEpisodes-count($this->queue), '/', $this->totalEpisodes, "] Parsing #$current ... ";
        if($legacy->getEpisode() !== null) {
            $transformed = $legacy->getEpisode();
        }
        else {
            $transformed = new addventure\Episode();
        }

        // >>> Title
        $title = $this->extractTitle($text);
        try {
            echo " ``$title''";
            $transformed->setTitle($title);
        }
        catch(\InvalidArgumentException $ex) {
            $transformed->setPreNotes($title);
            $transformed->setTitle('');
        }

        // >>> Author
        if(preg_match('|\<address\>\s*(.+?)\s*\<\/address\>|isuS', $text, $matches)) {
            // echo "author=``" . trim($matches[1]) . "'' ";
            $this->findOrCreateAuthor($matches[1], $transformed);
        }

        // >>> Created
        $created = $this->extractCreationDate($text);
        if($created !== null) {
            echo " created=", $created->format('c');
            $transformed->setCreated($created);
        }

        // >>> Parent
        $parent = Util::extractParent($text);
        if($parent !== null) {
            $parentEpisode = $entityManager->find('addventure\LegacyEpisode', $parent);
            if(!$parentEpisode->getEpisode()) {
                $parentEpisode->setEpisode(new addventure\Episode());
                $entityManager->persist($parentEpisode->getEpisode());
            }
            $transformed->setParent($parentEpisode->getEpisode());
        }

        // >>> Back-linkable
        $isLinkable = (strlen($text) - strpos($text, '<i>Linking Enabled</i><p>') < 50); // check if at end, possibly with comments link
        $transformed->setLinkable($isLinkable);
        if($isLinkable) {
            echo "[Linkable] ";
        }

        // >>> Text
        $plain = $this->extractText($text);
        if(empty($plain)) {
            echo "Failed to extract text!\n";
            return true;
        }
        $transformed->setText($plain);

        // >>> Children/Backlinks
        echo " children=";
        foreach($this->extractChildren($text) as $linkInfo) {
            $targetEpisodeId = $linkInfo['id'];
            if($targetEpisodeId == $parent) {
                continue;
            }
            $linkTitle = $linkInfo['title'];

            echo $targetEpisodeId;
            $legacyTarget = $entityManager->find('addventure\LegacyEpisode', $targetEpisodeId);
            if($legacyTarget !== null && $legacyTarget->getEpisode()===null) {
                // the target episode doesn't have a "real" episode yet
                $legacyTarget->setEpisode(new addventure\Episode());
                $entityManager->persist($legacyTarget->getEpisode());
                $entityManager->persist($legacyTarget);
            }
            /*
            if($legacyTarget === null) {
                initLogger()->error("Data consistency error; legacy target episode $targetEpisodeId missing (in $current)");
                echo "Data consistency error; legacy target episode $targetEpisodeId missing (in $current)\n";
            }
            */

            $link = new addventure\Link();
            $link->setFromEp($transformed);
            // maybe the target episode isn't written yet
            $link->setToEp($legacyTarget!==null ? $legacyTarget->getEpisode() : new addventure\Episode());
            $entityManager->persist($link->getToEp());
            $entityManager->persist($transformed);

            if($entityManager->getRepository('addventure\Link')->findOneBy(array('fromEp' => $link->getFromEp(), 'toEp' => $link->getToEp()))) {
                // Link already in database
                continue;
            }

            try {
                $link->setTitle($linkTitle);
            }
            catch(\InvalidArgumentException $ex) {
                // OK, truncate it...
                $charLen = mb_strlen($linkTitle);
                $tmp = explode(' ', $linkTitle);
                $charLen -= count($tmp) - 1; // remove spaces
                while(isset($tmp[1]) && $charLen + count($tmp) - 1 > 255 - 3) {
                    array_pop($tmp);
                }
                if(!isset($tmp[1])) {
                    // do a hard truncate
                    $tmp[0] = mb_substr($tmp[0], 0, 255 - 3);
                }
                $link->setTitle(implode(' ', $tmp) . '...');
                echo " *LINK TRUNCATED* ";
            }
            if($linkInfo['isBacklink']) {
                $link->setIsBacklink(true);
                echo "* ";
            }
            else {
                echo " ";
            }

            $entityManager->persist($link);
            $entityManager->flush();
        }

        $legacy->setEpisode($transformed);
        $entityManager->persist($legacy);
        $entityManager->persist($transformed);
        $entityManager->flush();
        $entityManager->clear();

        echo "\n";
        return TRUE;
    }

    /**
     * Transform all queued episodes
     */
    public function transformAll() {
        echo "Transforming raw episodes... go get some coffee!\n";
        while($this->transformNext()) {
            // run run run...
        }
        echo "Transformed all episodes.\n";
    }

}

$importer = new Importer(new URLRetriever('/path/to/the/addventure'));
$importer->importAll();

$transformer = new Transformer($importer->getImported());
$transformer->transformAll();
