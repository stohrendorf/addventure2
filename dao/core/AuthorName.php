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
    private $episodes;

    public function __construct() {
        $this->episodes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
        $this->name = simplifyWhitespace($name, 200, false);
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
