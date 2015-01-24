<?php

class Feed extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('em');
        $this->load->helper('url');
    }

    public function rss($userid = null) {
        header('Content-Type: application/rss+xml', true);
        if($userid !== null) {
            $userid = filter_var($userid, FILTER_SANITIZE_NUMBER_INT);
        }
        $eps = $this->_getRecentEpisodes($userid);
        $res = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><rss version="2.0"></rss>');
        $ch = $res->addChild('channel');
        $ch->addChild('title', _('Addventure2 feed'));
        $ch->addChild('description', _('Recent episodes'));
        $ch->addChild('language', 'en-US');
        $ch->addChild('copyright', _('The Addventure Authors'));
        $ch->addChild('pubDate', (new \DateTime())->format(DateTime::RSS));

        foreach($eps as $ep) {
            $ep->toRss($ch);
        }

        echo $res->asXML();
    }

    public function atom($userid = null) {
        header('Content-Type: application/atom+xml', true);
        if($userid !== null) {
            $userid = filter_var($userid, FILTER_SANITIZE_NUMBER_INT);
        }
        $eps = $this->_getRecentEpisodes($userid);
        $res = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>');
        $res->addChild('title', _('Addventure2 feed'));
        $res->addChild('id', current_url());
        $res->addChild('updated', (new \DateTime())->format(DateTime::ATOM));
        $res->addChild('author', _('The Addventure Authors'));
        $l = $res->addChild('link');
        $l->addAttribute('href', current_url());
        $l->addAttribute('rel', 'alternate');
        $res->addChild('subTitle', _('Recent episodes'));

        foreach($eps as $ep) {
            $ep->toAtom($res);
        }

        echo $res->asXML();
    }

    /**
     * Find the recent episodes, either globally or for one specific user.
     * @param int|null $user User-ID
     * @return \addventure\Episode[]
     */
    private function _getRecentEpisodes($user) {
        try {
            if($user !== null && $user !== false) {
                $eps = $this->em->getEpisodeRepository()->getRecentEpisodesByUser(ADDVENTURE_FEED_SIZE, $user);
            }
            else {
                $eps = $this->em->getEpisodeRepository()->getRecentEpisodes(ADDVENTURE_FEED_SIZE);
            }
            if(!$eps) {
                http_response_code(400);
                die('');
            }
            return $eps;
        }
        catch(\InvalidArgumentException $ex) {
            http_response_code(400);
            die('');
        }
    }

}
