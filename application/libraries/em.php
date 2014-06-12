<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class EM {

    /**
     * @global \Doctrine\ORM\EntityManager $entityManager
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        global $entityManager;
        return $entityManager;
    }
 
    /**
     * Try to find a user by his E-mail address.
     * @param string $mail User E-Mail
     * @return \addventure\User|null
     */
    public function findUserByMail($mail) {
        $em = $this->getEntityManager();
        $user = $em->getRepository('addventure\User')
                ->createQueryBuilder('u')
                ->where('u.email = ?1')
                ->setParameter(1, $mail)
                ->getQuery()
                ->getOneOrNullResult();
        return $user;
    }
    
    /**
     * Try to find a user by his username address.
     * @param string $name Username
     * @return \addventure\User|null
     */
    public function findUserByName($name) {
        $em = $this->getEntityManager();
        $user = $em->getRepository('addventure\User')
                ->createQueryBuilder('u')
                ->where('u.username = ?1')
                ->setParameter(1, $name)
                ->getQuery()
                ->getOneOrNullResult();
        return $user;
    }
    
    public function persistAndFlush($object) {
        $em = $this->getEntityManager();
        $em->persist($object);
        $em->flush();
    }
    
    /**
     * @param int $id Episode ID
     * @return null|\addventure\Episode
     */
    public function findEpisode($id) {
        $em = $this->getEntityManager();
        return $em->find('addventure\Episode', $id);
    }
    
    /**
     * @param int $id User ID
     * @return null|\addventure\User
     */
    public function findUser($id) {
        $em = $this->getEntityManager();
        return $em->find('addventure\User', $id);
    }
    
    /**
     * Find a link between two episodes
     * @param int $from Source episode
     * @param int $to Destination episode
     * @return null|\addventure\Link
     */
    public function findLink($from, $to) {
        $em = $this->getEntityManager();
        return $em->find('addventure\Link', array('fromEp' => $from, 'toEp' => $to));
    }
    
    /**
     * Get all reported issues.
     * @return \addventure\Report[]
     */
    public function getAllReports() {
        $em = $this->getEntityManager();
        return $em->createQuery('SELECT r FROM addventure\Report r')->getResult();
    }
    
    /**
     * Get the episode repository
     * @return \addventure\EpisodeRepository
     */
    public function getEpisodeRepository() {
        $em = $this->getEntityManager();
        return $em->getRepository('addventure\Episode');
    }
}
