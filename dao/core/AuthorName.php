<?php

namespace addventure;

/**
 * @Entity
 * @Table(indexes={@Index(name="userIndex", columns={"user_id"})})
 */
class AuthorName implements IAddventure {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id = null;

    /**
     * @Column(type="string", unique=true, length=200, nullable=false)
     * @var string
     */
    private $name = null;

    /**
     * @ManyToOne(targetEntity="addventure\User", inversedBy="authorNames", fetch="LAZY", cascade={"PERSIST"})
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @var User
     */
    private $user = null;

    /**
     * @OneToMany(targetEntity="addventure\Episode", mappedBy="author", fetch="EXTRA_LAZY")
     * @var Episode[]
     */
    private $episodes = null;

    public function getEpisodes() {
        return $this->episodes;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getUser() {
        return $this->user;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setName($name) {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if(empty($name)) {
            throw new \InvalidArgumentException("Name must not be empty");
        }
        elseif(mb_strlen($name) > 200) {
            throw new \InvalidArgumentException("Name too long: " . mb_strlen($name));
        }
        $this->name = $name;
        return $this;
    }

    public function setUser(User $user) {
        $this->user = $user;
        return $this;
    }

    public function setEpisodes($episodes) {
        $this->episodes = $episodes;
        return $this;
    }

    public function addEpisode(Episode $e) {
        if($this->episodes == null) {
            $this->episodes = new \Doctrine\Common\Collections\ArrayCollection();
        }
        $this->episodes[] = $e;
    }

    /**
     * @codeCoverageIgnore
     */
    public function toJson() {
        return array(
            'id' => $this->getId(),
            'user' => ($this->getUser() != null ? $this->getUser()->getId() : null),
            'name' => $this->getName()
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function toSmarty() {
        return array(
            'id' => $this->getId(),
            'user' => ($this->getUser() != null ? $this->getUser()->getId() : null),
            'name' => $this->getName()
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function toRss(\SimpleXMLElement &$parent) {
        
    }

    /**
     * @codeCoverageIgnore
     */
    public function toAtom(\SimpleXMLElement &$entry) {
        $a = $entry->addChild('author');
        $a->addChild('name', htmlspecialchars($this->getName()));
    }

}
