<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Welcome extends CI_Controller
{

    public function index()
    {
        $this->load->helper('smarty');
        $this->load->library('em');
        
        $query = $this->em->getEntityManager()->createQuery('SELECT e FROM addventure\Episode e WHERE e.parent IS NULL ORDER BY e.id');
        $query->setQueryCacheLifetime(24*60*60); // a day should be enough
        $roots = array();
        foreach($query->iterate() as $row) {
            $ep = $row[0];
            $roots[] = array(
                'id' => $ep->getId(),
                'title' => $ep->getAutoTitle()
            );
        }
        $smarty = createSmarty();
        $smarty->assign('roots', $roots);
        $smarty->display('main.tpl');
    }

}
