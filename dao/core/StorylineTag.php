<?php

namespace addventure;

/**
 * @Entity
 */
class StorylineTag implements IAddventure {

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
    private $title;

    /**
     * @OneToMany(targetEntity="addventure\Episode", mappedBy="storylineTag", fetch="EXTRA_LAZY")
     * @var Episode[]|\Doctrine\Common\Collections\ArrayCollection
     */
    private $episodes;
    
    public function __construct() {
        $this->episodes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getEpisodes() {
        return $this->episodes;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setTitle($title) {
        $this->title = simplifyWhitespace($title, 200, false);
        return $this;
    }

    public function setEpisodes($episodes) {
        if(is_array($episodes)) {
            $this->episodes = new \Doctrine\Common\Collections\ArrayCollection( $episodes );
            return $this;
        }
        if(!($episodes instanceof \Doctrine\Common\Collections\ArrayCollection)) {
            throw new \InvalidArgumentException("Unexpected type");
        }
        $this->episodes = $episodes;
        return $this;
    }

    public function toAtom(\SimpleXMLElement &$parent)
    {
        
    }

    public function toJson()
    {
        return array(
            'title' => $this->getTitle(),
            'id' => $this->getId()
        );
    }

    public function toRss(\SimpleXMLElement &$parent)
    {
        
    }

    public function toSmarty()
    {
        return array(
            'title' => $this->getTitle(),
            'id' => $this->getId()
        );
    }

}
