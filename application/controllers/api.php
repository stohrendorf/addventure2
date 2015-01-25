<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * User account controller.
 * 
 * The workflow for a new user is as follows:
 *
 *   1. Enter the 'register' method.  This will first display the 'account_register'
 *      template for entering the needed information.  If the information is
 *      incomplete or faulty, the 'account_register_invalid' will be shown.  Else,
 *      a preliminary account with the role {@see \addventure\User::AwaitApproval} will be
 *      created an E-mail will be sent to the user which contains encrypted data
 *      for validation.
 * 
 *   2. The user receives his E-mail with the activation link, which points to
 *      the 'verify' method.  Here, the security token passed in the URL will
 *      be verified against the stored information, and if everything is OK,
 *      the account role will be set to {@see \addventure\User::Registered}.  But if
 *      something goes wrong, the 'account_register_invalid' will be shown.
 * 
 *   3. Now, the user has to login to create his session cookie and to store
 *      the session data in the database.  This could be done in the 'verify'
 *      step, but it ensures that the E-mail account isn't hijacked, because
 *      the user has to enter his password again.
 */
class Api extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->output->set_content_type('application/json');
    }

    public function backlinks($filter = '')
    {

        $this->load->library('em');

        $result = array();

        if(empty($filter)) {
            echo json_encode(array('entries' => $result));
            return;
        }

        if(preg_match('/^[0-9]+$/', $filter)) {
            $filter = filter_var($filter, FILTER_SANITIZE_NUMBER_INT);
            $qb = $this->em->getEntityManager()->createQueryBuilder();
            $qb->select('l')->from('addventure\Link', 'l')
                    ->where('l.isBacklink = TRUE')
                    ->andWhere('CONCAT(IDENTITY(l.toEp), \'\') LIKE :filter')
                    ->orderBy('l.toEp') // and then ordered by target
                    ->setMaxResults(50);
            $qb->setParameter('filter', '%' . addcslashes($filter, '%_') . '%', Doctrine\DBAL\Types\Type::STRING);
            foreach($qb->getQuery()->getResult() as $link) {
                $result[] = $link->toJson();
            }
        }
        else {
            $filter = filter_var($filter, FILTER_SANITIZE_STRING);
            $qb = $this->em->getEntityManager()->createQueryBuilder();
            $qb->select('l, LENGTH(l.title) AS HIDDEN len')->from('addventure\Link', 'l')
                    ->where('l.isBacklink = TRUE')
                    ->andWhere('UPPER(l.title) LIKE :filter')
                    ->orderBy('len') // the most-matching first
                    ->addOrderBy('l.title') // and then ordered by target
                    ->setMaxResults(50);
            $qb->setParameter('filter', '%' . addcslashes(mb_convert_case($filter, MB_CASE_UPPER), '%_') . '%', Doctrine\DBAL\Types\Type::STRING);
            foreach($qb->getQuery()->getResult() as $link) {
                $result[] = $link->toJson();
            }
        }

        echo json_encode(array('entries' => $result));
        return;
    }

}
