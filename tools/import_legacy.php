<?php

/******************************************************************************
 * Dear user, please be kind.  Do NOT scrape an existing Addventure without
 * permission from the admin.  If you ARE the admin however, use the
 * URLRetriever at the end of this file and supply a LOCAL FILE PATH (i.e.,
 * not an actual URL) to it and DO NOT run this script on the web server,
 * because it does some heavy file operations which may slow its operation or
 * even may take it down.  If you already have migrated to a database, use
 * either the MySQLRetriever or write your own retriever.
 * 
 * This script requires about 120 MB RAM per 100.000 written episodes, so be
 * sure to adjust your cli/php5.ini accordingly, which has a default maximum
 * of 128 MB.  To be safe, consider using 200--250 MB per 100.000 episodes for
 * memory calculation.  Experience is that you can estimate the number of
 * written episodes by dividing the total number of episodes by 4.
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
class MySQLRetriever implements IRetriever {

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
     * @var IRetriever
     */
    private $retriever;

    /**
     * @var int Episode index to be imported next.
     */
    private $current;

    /**
     * @var int Maximum found episode index;
     */
    private $maxEpisode = 0;

    /**
     * Constructor
     * @param IRetriever $retriever Episode retriever
     * @param int $maxEpisode Initial upper limit for episode IDs.
     * @param boolean $followUnwritten Whether to initially try to import unwritten episodes
     */
    public function __construct(IRetriever $retriever, $maxEpisode = 10) {
        $this->retriever = $retriever;
        $this->current = 0;
        $this->maxEpisode = $maxEpisode;
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
     * @return int[] Array of children IDs
     */
    private function extractMaxChild($html) {
        $result = null;
        if(preg_match_all('#(&gt;|\<li\>|\*)\<a href\="\.\.\/[0-9]+\/([0-9]+)\.html"\>.*?\<\/a\>\<\/li\>#isuS', $html, $liLinks, PREG_SET_ORDER)) {
            foreach($liLinks as $link) {
                if($result === null || (int) $link[2] > $result) {
                    $result = (int) $link[2];
                }
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
        if($this->current > $this->maxEpisode) {
            return false;
        }

        $entityManager = initDoctrineConnection();
        if(($legacy = $entityManager->find('addventure\LegacyEpisode', $this->current))) {
            // already imported, so just use it passively...
            echo '[', $this->current, '/', $this->maxEpisode, "] already retrieved\n";
            $this->imported[] = $this->current;
            ++$this->current;
            $raw = $legacy->getRawContent();
            $parent = Util::extractParent($raw);
            if($parent !== null && $parent > $this->maxEpisode) {
                $this->maxEpisode = $parent;
            }

            $maxChild = $this->extractMaxChild($raw);
            if($maxChild !== null && $maxChild > $this->maxEpisode) {
                $this->maxEpisode = $maxChild;
            }
            $entityManager->clear();
            return true;
        }

        $raw = $this->retriever->retrieve($this->current);
        if($raw === null) {
            echo '[', $this->current, '/', $this->maxEpisode, "] -- Retrieval failed!\n";
            initLogger()->error("#$this->current -- Retrieval failed!");
            ++$this->current;
            return true;
        }
        elseif($this->isPlaceholder($raw)) {
            echo '[', $this->current, '/', $this->maxEpisode, "] -- Placeholder.\n";
            ++$this->current;
            return true;
        }
        else {
            echo '[', $this->current, '/', $this->maxEpisode, "] -- Retrieved. Importing...\n";
        }

        $clean = $this->cleanupHtml($raw);
        if($clean === null) {
            echo '[', $this->current, '/', $this->maxEpisode, "] -- Failed to clean up the HTML!\n";
            ++$this->current;
            return true;
        }

        $parent = Util::extractParent($clean);
        if($parent !== null && $parent > $this->maxEpisode) {
            $this->maxEpisode = $parent;
        }

        $maxChild = $this->extractMaxChild($clean);
        if($maxChild !== null && $maxChild > $this->maxEpisode) {
            $this->maxEpisode = $maxChild;
        }

        $legacy = $this->createLegacyEpisode($this->current, $clean);
        $entityManager->persist($legacy);
        $entityManager->flush();
        $entityManager->clear();

        $this->imported[] = $this->current;
        ++$this->current;

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
     * @var string[][] Columns of stat.addv
     */
    private $stat = array();

    const COL_ID = 0;
    const COL_DATE = 2;
    const COL_AUTHOR = 4;
    const COL_TITLE = 6;

    /**
     * Constructor
     * @param int[] $queue Episode IDs to be imported
     */
    public function __construct(array $queue, $statFile) {
        $this->queue = $queue;
        $this->totalEpisodes = count($queue);
        sort($queue, SORT_NUMERIC);

        echo "Loading stat.addv ($statFile)...\n";
        $stat = preg_split('/$\R?^/m', file_get_contents($statFile));

        echo "Preparing stat.addv (", count($stat), " entries)...\n";
        $index = 0;
        foreach($stat as $statLine) {
            $line = explode("\t", mb_convert_encoding($statLine, 'UTF-8', 'CP1252'));
            if(!in_array((int) $line[self::COL_ID], $queue)) {
                continue;
            }
            ++$index;
            printf("\r[%3.2f%%] %s          ", $index * 100 / $this->totalEpisodes, $line[self::COL_ID]);
            $line[self::COL_AUTHOR] = trim($line[self::COL_AUTHOR]); // author
            $line[self::COL_DATE] = DateTime::createFromFormat('Ymd-His', $line[self::COL_DATE]); // created
            if(preg_match('/^(.*)\s*\[' . ROOMWORD . ' [0-9]+\]$/i', $line[self::COL_TITLE], $matches)) {
                // title
                $line[self::COL_TITLE] = strip_tags($matches[1]);
            }
            else {
                $line[self::COL_TITLE] = strip_tags($line[self::COL_TITLE]);
            }
            $this->stat[$line[self::COL_ID]] = $line;
        }
        echo "\n";
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
            echo '[', $this->totalEpisodes - count($this->queue), '/', $this->totalEpisodes, "] #$current already parsed\n";
            $entityManager->clear();
            return true;
        }

        $text = $legacy->getRawContent();

        echo '[', $this->totalEpisodes - count($this->queue), '/', $this->totalEpisodes, "] Parsing #$current ... ";
        if($legacy->getEpisode() !== null) {
            $transformed = $legacy->getEpisode();
        }
        else {
            $transformed = new addventure\Episode();
        }

        // >>> Title
        $title = $this->stat[(string) $current][self::COL_TITLE];
        try {
            echo " ``$title''";
            $transformed->setTitle($title);
        }
        catch(\InvalidArgumentException $ex) {
            $transformed->setPreNotes($title);
            $transformed->setTitle('');
        }

        // >>> Author
        $this->findOrCreateAuthor($this->stat[(string) $current][self::COL_AUTHOR], $transformed);

        // >>> Created
        $created = $this->stat[(string) $current][self::COL_DATE];
        if($created) {
            echo " created=", $created->format('c');
            $transformed->setCreated($created);
        }

        // >>> Parent
        $parent = Util::extractParent($text);
        if($parent !== null) {
            $parentEpisode = $entityManager->find('addventure\LegacyEpisode', $parent);
            if($parentEpisode !== null) {
                if(!$parentEpisode->getEpisode()) {
                    $parentEpisode->setEpisode(new addventure\Episode());
                    $entityManager->persist($parentEpisode->getEpisode());
                }
                $transformed->setParent($parentEpisode->getEpisode());
            }
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
            if($legacyTarget !== null && $legacyTarget->getEpisode() === null) {
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
            $link->setToEp($legacyTarget !== null ? $legacyTarget->getEpisode() : new addventure\Episode());
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

$transformer = new Transformer($importer->getImported(), '/path/to/stat.addv');
$transformer->transformAll();
