<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Maintenance extends CI_Controller {

    private function report($docId, $description, $type) {
        $this->load->library('log');
        global $entityManager;
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === null || $docId === false) {
            $this->log->warning('Maintenance/' . $description . ' - invalid DocID');
            show_404();
        }
        $ep = $entityManager->find('addventure\Episode', $docId);
        if(!$ep) {
            $this->log->warning('Maintenance/' . $description . ' - Document not found: ' . $docId);
            show_404();
            return;
        }
        $this->log->debug('Maintenance/' . $description . ': ' . $docId);
        $report = new addventure\Report();
        $report->setEpisode($ep);
        $report->setType($type);
        try {
            $entityManager->persist($report);
            $entityManager->flush();
        }
        catch(PDOException $e) {
            $this->log->debug('Maintenance/' . $description . ': Duplicate ' . $docId);
        }
    }

    public function illegal($docId) {
        $this->report($docId, 'Illegal', addventure\Report::ILLEGAL);
    }

    public function reportTitle($docId) {
        $this->report($docId, 'TopNotes', addventure\Report::WRONG_TOP_NOTES);
    }

    public function reportNotes($docId) {
        $this->report($docId, 'BottomNotes', addventure\Report::WRONG_BOTTOM_NOTES);
    }

    public function reportFormatting($docId) {
        $this->report($docId, 'Formatting', addventure\Report::FORMATTING);
    }

    public function log() {
        echo '<pre>';
        echo getFullLogData();
        echo '</pre>';
    }

    public function reports() {
        echo '<ul>';
        global $entityManager;
        foreach($entityManager->createQuery('SELECT r FROM addventure\Report r')->getResult() as $r) {
            echo '<li>' . $r->getEpisode()->getId() . ' as ' . $r->getType() . '</li>';
        }
        echo '</ul>';
    }

}
