<?php

namespace addventure;

/**
 * @Entity
 * @Table(
 *     indexes={@Index(name="notificationUserIndex", columns={"user_id"}), @Index(name="notificationEpisodeIndex", columns={"episode_id"})}
 * )
 */
class Notification implements IAddventure
{
    /**
     * @Id
     * @ManyToOne(targetEntity="addventure\User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @var User
     */
    private $user;

    /**
     * @Id
     * @ManyToOne(targetEntity="addventure\Episode")
     * @JoinColumn(name="episode_id", referencedColumnName="id", nullable=false)
     * @var Episode
     */
    private $episode;

    public function getUser()
    {
        return $this->user;
    }

    public function getEpisode()
    {
        return $this->episode;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function setEpisode(Episode $episode)
    {
        $this->episode = $episode;
        return $this;
    }

    public function toAtom(\SimpleXMLElement &$parent)
    {
        
    }

    public function toJson()
    {
        
    }

    public function toRss(\SimpleXMLElement &$parent)
    {
        
    }

    public function toSmarty()
    {
        return array(
            'episode' => $this->episode->toSmarty(),
            'user' => $this->user->toSmarty()
        );
    }

}
