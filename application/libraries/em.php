<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class EM
{

    /**
     * @global \Doctrine\ORM\EntityManager $entityManager
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        global $entityManager;
        return $entityManager;
    }

    /**
     * Try to find a user by his E-mail address.
     * @param string $mail User E-Mail
     * @return \addventure\User|null
     */
    public function findUserByMail($mail)
    {
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
    public function findUserByName($name)
    {
        $user = $this->getEntityManager()
                ->getRepository('addventure\User')
                ->createQueryBuilder('u')
                ->where('u.username = ?1')
                ->setParameter(1, $name)
                ->getQuery()
                ->getOneOrNullResult();
        return $user;
    }

    public function persistAndFlush($object)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($object);
        $entityManager->flush();
    }

    public function removeAndFlush($object)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($object);
        $entityManager->flush();
    }

    /**
     * @param int $id Episode ID
     * @return null|\addventure\Episode
     */
    public function findEpisode($id)
    {
        return $this->getEntityManager()->find('addventure\Episode', $id);
    }

    /**
     * @param int $id User ID
     * @return null|\addventure\User
     */
    public function findUser($id)
    {
        return $this->getEntityManager()->find('addventure\User', $id);
    }

    /**
     * Find a link between two episodes
     * @param int|\addventure\Episode $from Source episode
     * @param int|\addventure\Episode $to Destination episode
     * @return null|\addventure\Link
     */
    public function findLink($from, $to)
    {
        return $this->getEntityManager()->find('addventure\Link', array('fromEp' => $from, 'toEp' => $to));
    }
    
    /**
     * @return null|\addventure\Comment
     */
    public function findComment($id) {
        return $this->getEntityManager()->find('addventure\Comment', $id);
    }

    /**
     * @return null|\addventure\StorylineTag
     */
    public function findStorylineTag($id) {
        return $this->getEntityManager()->find('addventure\StorylineTag', $id);
    }

    /**
     * Get all reported issues.
     * @return \addventure\Report[]
     */
    public function getAllReports()
    {
        return $this->getEntityManager()->createQuery('SELECT r FROM addventure\Report r')->getResult();
    }

    /**
     * Get the episode repository
     * @return \addventure\EpisodeRepository
     */
    public function getEpisodeRepository()
    {
        return $this->getEntityManager()->getRepository('addventure\Episode');
    }

    /**
     * Get all registered notifications for an episode.
     * @param int $doc Document ID
     * @return \addventure\Notification[]
     */
    public function getNotificationsForDoc($doc)
    {
        return $this->getEntityManager()
                        ->getRepository('addventure\Notification')
                        ->createQueryBuilder('n')
                        ->where('n.episode = ?1')
                        ->setParameter(1, $doc)
                        ->getQuery()
                        ->getResult();
    }

    public function findOrCreateAuthorForUser(addventure\User $user, $name, $persist = false)
    {
        $author = $this->getEntityManager()->createQueryBuilder()
                        ->select('a')->from('addventure\AuthorName', 'a')
                        ->where('a.name = :name')
                        ->setParameter('name', $name)
                        ->getQuery()->getOneOrNullResult();
        if($author) {
            if($author->getUser()->getId() != $user->getId()) {
                return $persist ? null : false;
            }
            else {
                return $author;
            }
        }
        
        if(!$persist) {
            return true;
        }

        $author = new addventure\AuthorName();
        $author->setName($name);
        $author->setUser($user);
        $user->getAuthorNames()->add($author);
        if($persist) {
            $this->getEntityManager()->persist($author);
            $this->getEntityManager()->persist($user);
        }
        return $author;
    }

    public function mergeUser(addventure\User &$destination, addventure\User &$source) {
        if(!$source->getRole()->get() === addventure\UserRole::Anonymous) {
            return false;
        }
        $names = $source->getAuthorNames();
        foreach($names as $name) {
            $destination->getAuthorNames()->add($name);
            $name->setUser($destination);
            $source->getAuthorNames()->removeElement($name);
            $this->getEntityManager()->persist($name);
            $this->getEntityManager()->persist($source);
            $this->getEntityManager()->persist($destination);
        }
        $this->getEntityManager()->remove($source);
        $this->getEntityManager()->flush();
        return true;
    }
}
