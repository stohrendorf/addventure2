<?php

namespace addventure;

/**
 * @Entity
 */
class LegacyEpisode {

    /**
     * @Id
     * @Column(type="integer", nullable="false")
     * @var int
     */
    private $id;

    /**
     * @Column(type="addventure\Episode", unique=true, nullable=true)
     * @var \addventure\Episode
     */
    private $episode = null;

    /**
     * @Column(type="text", nullable=false)
     * @var string
     */
    private $rawContent;

    public function getId() {
        return $this->id;
    }

    public function getEpisode() {
        return $this->episode;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setEpisode(\addventure\Episode $episode) {
        $this->episode = $episode;
        return $this;
    }

    public function getRawContent() {
        return $this->rawContent;
    }

    public function setRawContent($rawContent) {
        $this->rawContent = $rawContent;
        return $this;
    }

}
