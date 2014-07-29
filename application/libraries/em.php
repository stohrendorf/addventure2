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
        $user = $this->getEntityManager()
                ->getRepository('addventure\User')
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
        $user = $this->getEntityManager()
                ->getRepository('addventure\User')
                ->createQueryBuilder('u')
                ->where('u.username = ?1')
                ->setParameter(1, $name)
                ->getQuery()
                ->getOneOrNullResult();
        return $user;
    }
    
    public function persistAndFlush($object) {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($object);
        $entityManager->flush();
    }
    
    /**
     * @param int $id Episode ID
     * @return null|\addventure\Episode
     */
    public function findEpisode($id) {
        return $this->getEntityManager()->find('addventure\Episode', $id);
    }
    
    /**
     * @param int $id User ID
     * @return null|\addventure\User
     */
    public function findUser($id) {
        return $this->getEntityManager()->find('addventure\User', $id);
    }
    
    /**
     * Find a link between two episodes
     * @param int $from Source episode
     * @param int $to Destination episode
     * @return null|\addventure\Link
     */
    public function findLink($from, $to) {
        return $this->getEntityManager()->find('addventure\Link', array('fromEp' => $from, 'toEp' => $to));
    }
    
    /**
     * Get all reported issues.
     * @return \addventure\Report[]
     */
    public function getAllReports() {
        return $this->getEntityManager()->createQuery('SELECT r FROM addventure\Report r')->getResult();
    }
    
    /**
     * Get the episode repository
     * @return \addventure\EpisodeRepository
     */
    public function getEpisodeRepository() {
        return $this->getEntityManager()->getRepository('addventure\Episode');
    }
    
    /**
     * Get all registered notifications for an episode.
     * @param int $doc Document ID
     * @return \addventure\Notification[]
     */
    public function getNotificationsForDoc($doc) {
        return $this->getEntityManager()
                ->getRepository('addventure\Notification')
                ->createQueryBuilder('n')
                ->where('n.episode = ?1')
                ->setParameter(1, $doc)
                ->getQuery()
                ->getResult();
    }
}
