<?php

require_once '../doctrine-bootstrap.php';

define('BASEPATH', ''); // HACK to make the following require_once work
require_once '../application/helpers/xss_clean_helper.php';

class Util {

    public static function extractParent($html) {
        if(preg_match('|<a href="\.\./[0-9]{3,4}/([0-9]+)\.html">Go back</a> - Go to the parent episode\.<p>|iS', $html, $matches)) {
            return (int) $matches[1];
        }
        else {
            return null;
        }
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
    private $importBase;

    public function __construct($importBase, $firstEpisode = 0) {
        $this->importBase = $importBase;
        $this->queued[] = $firstEpisode;
    }

    /**
     * Clean up HTML by using Tidy.
     * @param string $html CP1252-encoded dirty HTML
     * @return null|string
     */
    private function cleanupHtml($html) {
        $tidy = new tidy;
        $tidy->parseString(xss_clean(mb_convert_encoding($html, 'UTF-8', 'CP1252')), array('output-html' => true, 'wrap' => 200), 'utf8');
        if(!$tidy->cleanRepair()) {
            return null;
        }
        return (string) $tidy;
    }

    private function createLegacyEpisode($id, $cleanedHtml) {
        $legacy = new addventure\LegacyEpisode();
        $legacy->setId($id);
        $legacy->setRawContent($cleanedHtml);
        return $legacy;
    }

    private function extractChildren($html, $skipUnwritten) {
        $result = array();
        foreach(explode('<li>', $html) as $liChunk) {
            if(!preg_match('|(.)<a href="\.\./[0-9]+/([0-9]+)\.html">.*?</a>|iS', $liChunk, $liLink)) {
                continue;
            }
            if($skipUnwritten && $liLink[1] == ' ') {
                continue;
            }
            $result[] = (int) $liLink[2];
        }
        return $result;
    }

    private function isPlaceholder($text) {
        if(!$text) {
            return true;
        }
        elseif(strpos($text, '<meta name="Description" content="Place-holder page for extension">')) {
            return true;
        }
        elseif(preg_match('|<a href="\.\./[0-9]+/[0-9]+\.html">Cancel</a> - Do <b>not</b> create the episode.|iS', $text)) {
            return true;
        }
        return false;
    }

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
            echo "#$current already downloaded --";
            $this->imported[] = $current;
            $raw = $legacy->getRawContent();
            $parent = Util::extractParent($raw);
            if($parent !== null) {
                echo " parent=$parent";
                $this->queued[] = (int) $parent;
            }

            $children = $this->extractChildren($raw, false);
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

        $raw = @file_get_contents(sprintf('%s/%03d/%d.html', $this->importBase, (int) ($current / 1000), $current));
        if(!$raw) {
            echo "#$current -- Import failed!\n";
            return true;
        }
        elseif($this->isPlaceholder($raw)) {
            return true;
        }

        $clean = $this->cleanupHtml($raw);
        if($clean === null) {
            echo "#$current -- Failed to clean up the HTML!\n";
            return true;
        }

        echo "#$current --";
        $parent = Util::extractParent($raw);
        if($parent !== null) {
            echo " parent=$parent";
            $this->queued[] = (int) $parent;
        }

        $children = $this->extractChildren($raw, true);
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

        echo "#$current -- Success.\n";
        echo '[', count($this->imported), ' imported, ', count($this->queued), ' queued]', "\n";

        return true;
    }

    public function importAll() {
        echo "Downloading raw episodes... go get some coffee!\n";
        while($this->importNext()) {
            // run run run...
        }
        echo "Downloaded all episodes.\n";
    }

    public function getImported() {
        return $this->imported;
    }

}

class Transformer {

    /**
     * @var int[]
     */
    private $queue;

    public function __construct(array $queue) {
        $this->queue = $queue;
        sort($queue);
    }

    private function extractTitle($html) {
        if(preg_match('|<h1>\s*(.*?)\s*</h1>|iS', $html, $matches)) {
            return strip_tags($matches[1]);
        }
        else {
            return '';
        }
    }

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

    private function extractCreationDate($html) {
        if(preg_match('|</address><p>[ \r\n\t]*([^<]+)|iS', $html, $matches)) {
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

    private function extractChildren($text) {
        $result = array();
        $links = explode('<li>', $text);
        foreach($links as $link) {
            if(!preg_match('|(.)<a href="../[0-9]+/([0-9]+)\.html">\s*(.*?)\s*</a>|iS', $link, $matches)) {
                continue;
            }
            $result[] = array((int) $matches[2] => array(
                    'title' => simplifyWhitespace(strip_tags($matches[3]), 9999, true),
                    'isBacklink' => ($matches[1] === '>')
            ));
        }
        return $result;
    }

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
        $nAuthor->setUser(new User());
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

    public function transformNext() {
        if(empty($this->queue)) {
            return false;
        }
        $current = array_pop($this->queue);

        $entityManager = initDoctrineConnection();
        $legacy = $entityManager->find('addventure\LegacyEpisode', $current);
        if($legacy->getEpisode() !== null && $legacy->getEpisode()->getText() !== null) {
            // already parsed
            $entityManager->clear();
            return true;
        }

        $text = $legacy->getRawContent();

        echo "Parsing $current ... ";
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
        if(preg_match('|<address>\s*(.+?)\s*</address>|iS', $text, $matches)) {
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
        foreach($this->extractChildren($text) as $targetEpisodeId => $linkInfo) {
            if($targetEpisodeId == $parent) {
                continue;
            }
            $linkTitle = $linkInfo['title'];

            echo $targetEpisodeId;
            $legacyTarget = $entityManager->find('addventure\LegacyEpisode', $targetEpisodeId);
            if($legacyTarget === null) {
                throw new \RuntimeException("Data consistency error; legacy target episode $targetEpisodeId missing");
            }

            $link = new addventure\Link();
            $link->setFromEp($transformed);
            $link->setToEp($legacyTarget->getEpisode() ? $legacyTarget->getEpisode() : new addventure\Episode());
            $entityManager->persist($link->getToEp());

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

        $entityManager->persist($transformed);
        $entityManager->flush();
        $entityManager->clear();

        echo "\n";
        return TRUE;
    }

    public function transformAll() {
        echo "Transforming raw episodes... go get some coffee!\n";
        while($this->transformNext()) {
            // run run run...
        }
        echo "Transformed all episodes.\n";
    }

}

$importer = new Importer('/path/to/the/addventure');
$importer->importAll();

$transformer = new Transformer($importer->getImported());
$transformer->transformAll();
