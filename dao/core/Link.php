<?php

namespace addventure;

/**
 * @Entity
 * @Table(
 *     indexes={@Index(name="fromIndex", columns={"from"}), @Index(name="toIndex", columns={"to"})}
 * )
 * @HasLifecycleCallbacks
 */
class Link {

  /**
   * @Column(type="boolean", nullable=false)
   * @var boolean
   */
  private $isBacklink = false;

  /**
   * @Id
   * @ManyToOne(targetEntity="addventure\Episode")
   * @JoinColumn(name="from", referencedColumnName="id", nullable=false)
   * @var Episode
   */
  private $from;

  /**
   * @Id
   * @ManyToOne(targetEntity="addventure\Episode")
   * @JoinColumn(name="to", referencedColumnName="id", nullable=false)
   * @var Episode
   */
  private $to;

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
    if ($this->from == $this->to) {
      throw new \InvalidArgumentException("Self-links are not allowed.");
    }
  }

  public function getIsBacklink() {
    return $this->isBacklink;
  }

  public function getFrom() {
    return $this->from;
  }

  public function getTo() {
    return $this->to;
  }

  public function setIsBacklink($isBacklink) {
    $this->isBacklink = $isBacklink;
    return $this;
  }

  public function setFrom(Episode $from) {
    $this->from = $from;
    return $this;
  }

  public function setTo(Episode $to) {
    $this->to = $to;
    return $this;
  }

  public function getTitle() {
    return $this->title;
  }

  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }

}
