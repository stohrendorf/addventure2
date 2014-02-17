<?php

namespace addventure;

/**
 * @Entity
 * @Table(
 *     indexes={
 *         @Index(name="episodeIndex", columns={"episode_id"}),
 *         @Index(name="authorIndex", columns={"author_name_id"})
 *     }
 * )
 */
class Comment {

  /**
   * @Id
   * @Column(type="integer")
   * @GeneratedValue(strategy="AUTO")
   * @var int
   */
  private $id;

  /**
   * @Column(type="datetime", nullable=false)
   * @var \DateTime
   */
  private $created;

  /**
   * @Column(type="text", nullable=false)
   * @var string
   */
  private $text;

  /**
   * @ManyToOne(targetEntity="addventure\Episode", fetch="LAZY")
   * @JoinColumn(name="episode_id", referencedColumnName="id", nullable=false)
   * @var Episode
   */
  private $episode;

  /**
   * @ManyToOne(targetEntity="addventure\AuthorName")
   * @JoinColumn(name="author_name_id", referencedColumnName="id")
   * @var string
   */
  private $authorName;

  public function getId() {
    return $this->id;
  }

  public function getCreated() {
    return $this->created;
  }

  public function getText() {
    return $this->text;
  }

  public function getEpisode() {
    return $this->episode;
  }

  public function getAuthorName() {
    return $this->authorName;
  }

  public function setId($id) {
    $this->id = $id;
    return $this;
  }

  public function setCreated(\DateTime $created) {
    $this->created = $created;
    return $this;
  }

  public function setText($text) {
    $this->text = $text;
    return $this;
  }

  public function setEpisode(Episode $episode) {
    $this->episode = $episode;
    return $this;
  }

  public function setAuthorName($authorName) {
    $this->authorName = $authorName;
    return $this;
  }

}
