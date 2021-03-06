<?php

namespace addventure;

/**
 * @Entity
 * @Table(
 *     indexes={@Index(name="fromIndex", columns={"fromEp"}), @Index(name="toIndex", columns={"toEp"})}
 * )
 * @HasLifecycleCallbacks
 */
class Link implements IAddventure {

    /**
     * @Column(type="boolean", nullable=false)
     * @var boolean
     */
    private $isBacklink = false;

    /**
     * @Id
     * @ManyToOne(targetEntity="addventure\Episode", fetch="EXTRA_LAZY")
     * @JoinColumn(name="fromEp", referencedColumnName="id", nullable=false)
     * @var Episode
     */
    private $fromEp;

    /**
     * @Id
     * @ManyToOne(targetEntity="addventure\Episode", fetch="EXTRA_LAZY")
     * @JoinColumn(name="toEp", referencedColumnName="id", nullable=false)
     * @var Episode
     */
    private $toEp;

    /**
     * @Column(type="string", nullable=false)
     * @var string
     */
    private $title;

    /**
     * @PrePersist
     * @PreUpdate
     */
    public function checkInvariants() {
        if($this->fromEp == $this->toEp) {
            throw new \InvalidArgumentException("Self-links are not allowed.");
        }
    }

    public function getIsBacklink() {
        return $this->isBacklink;
    }

    public function getFromEp() {
        return $this->fromEp;
    }

    public function getToEp() {
        return $this->toEp;
    }

    public function setIsBacklink($isBacklink) {
        if(!is_bool($isBacklink)) {
            throw new \InvalidArgumentException('isBacklink must be boolean');
        }
        $this->isBacklink = $isBacklink;
        return $this;
    }

    public function setFromEp(Episode $from) {
        $this->fromEp = $from;
        return $this;
    }

    public function setToEp(Episode $to) {
        $this->toEp = $to;
        return $this;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = simplifyWhitespace($title, 255, false);
        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function toJson() {
        return array(
            'isBacklink' => $this->getIsBacklink(),
            'fromEp' => $this->getFromEp()->getId(),
            'toEp' => $this->getToEp()->getId(),
            'title' => $this->getTitle()
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function toSmarty() {
        return array(
            'isBacklink' => $this->getIsBacklink(),
            'fromEp' => $this->getFromEp()->getId(),
            'toEp' => $this->getToEp()->getId(),
            'title' => $this->getTitle(),
            'isWritten' => ($this->getToEp()->getText() != NULL)
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
    public function toAtom(\SimpleXMLElement &$parent) {
        
    }

}
