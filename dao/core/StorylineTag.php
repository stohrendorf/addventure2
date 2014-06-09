<?php

namespace addventure;

/**
 * @Entity
 */
class StorylineTag {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @Column(type="string", unique=true, length=200, nullable=false)
     * @var string
     */
    private $name;

    /**
     * @OneToMany(targetEntity="addventure\Episode", mappedBy="storylineTag", fetch="EXTRA_LAZY")
     * @var Episode[]
     */
    private $episodes;

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getEpisodes() {
        return $this->episodes;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setName($name) {
        $this->name = simplifyWhitespace($name, 200, false);
        return $this;
    }

    public function setEpisodes($episodes) {
        $this->episodes = $episodes;
        return $this;
    }

}
