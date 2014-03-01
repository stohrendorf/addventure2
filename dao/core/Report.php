<?php

namespace addventure;

/**
 * @Entity
 */
class Report {

    const ILLEGAL = 0;
    const WRONG_TOP_NOTES = 1;
    const WRONG_BOTTOM_NOTES = 2;
    const FORMATTING = 3;

    /**
     * @Id
     * @ManyToOne(targetEntity="addventure\Episode", fetch="LAZY")
     * @JoinColumn(name="episode", referencedColumnName="id", nullable=false)
     * @var Episode
     */
    private $episode = null;

    /**
     * @Id
     * @Column(type="integer", nullable=false)
     * @var integer
     */
    private $type = self::ILLEGAL;

    public function getEpisode() {
        return $this->episode;
    }

    public function getType() {
        return $this->type;
    }

    public function setEpisode(Episode $episode) {
        $this->episode = $episode;
        return $this;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

}
