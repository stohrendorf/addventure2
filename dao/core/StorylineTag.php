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
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if(empty($name)) {
            throw new \InvalidArgumentException("Tag title must not be empty");
        }
        elseif(mb_strlen($name) > 200) {
            throw new \InvalidArgumentException("Tag title too long: " . mb_strlen($name));
        }
        $this->name = $name;
        return $this;
    }

    public function setEpisodes($episodes) {
        $this->episodes = $episodes;
        return $this;
    }

}
