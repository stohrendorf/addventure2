<?php

/**
 * Name of rooms. Make sure to escape PCRE chars and '#'.
 */
define('ROOMWORD', 'room');

require 'Importer.lib.php';

$stat = new StatAddv('/data/addventure/stat.addv');
$retriever = new URLRetriever('/data/addventure/docs');
$importer = new Importer($retriever, $stat->getAllEpisodes());
$importer->importAll();

$backlink = new BacklinkAddv('/data/addventure/backlink.addv');
$transformer = new Transformer($importer->getImported(), $stat, $backlink);
$transformer->transformAll();
