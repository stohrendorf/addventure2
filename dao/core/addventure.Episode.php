<?php

namespace addventure;

/**
 * @Entity
 * @Table(
 *     indexes={
 *         @Index(name="parentIndex", columns={"parent_id"}),
 *         @Index(name="authorIndex", columns={"author_id"}),
 *         @Index(name="storylineIndex", columns={"storyline_tag_id"})
 *     }
 * )
 */
class Episode {

  /**
   * @Id
   * @Column(type="integer")
   * @GeneratedValue(strategy="AUTO")
   * @var int
   */
  private $id;

  /**
   * @Column(type="string", nullable=true)
   * @var string
   */
  private $title;

  /**
   * @Column(type="datetime", nullable=true)
   * @var \DateTime
   */
  private $created;

  /**
   * @Column(type="text", nullable=true)
   * @var string
   */
  private $notes;

  /**
   * @Column(type="text", nullable=true)
   * @var string
   */
  private $text;

  /**
   * @Column(type="integer", nullable=true)
   * @var int
   */
  private $hitCount;

  /**
   * @Column(type="integer", nullable=true)
   * @var int
   */
  private $likes;

  /**
   * @Column(type="integer", nullable=true)
   * @var int
   */
  private $dislikes;

  /**
   * @ManyToOne(targetEntity="addventure\Episode", fetch="LAZY")
   * @var Episode
   */
  private $parent;

  /**
   * @ManyToOne(targetEntity="addventure\AuthorName")
   * @JoinColumn(name="author_id", referencedColumnName="id")
   * @var AuthorName
   */
  private $author;

  /**
   * @ManyToOne(targetEntity="addventure\StorylineTag", inversedBy="episode")
   * @JoinColumn(name="storyline_tag_id", referencedColumnName="id")
   * @var StorylineTag
   */
  private $storylineTag;

  /**
   * @ManyToMany(targetEntity="addventure\SimpleTag", mappedBy="episode")
   * @var array
   */
  private $simpleTag;

  public function getId() {
    return $this->id;
  }

  public function getTitle() {
    return $this->title;
  }

  public function getCreated() {
    return $this->created;
  }

  public function getNotes() {
    return $this->notes;
  }

  public function getText() {
    return $this->text;
  }

  public function getHitCount() {
    return $this->hitCount;
  }

  public function getLikes() {
    return $this->likes;
  }

  public function getDislikes() {
    return $this->dislikes;
  }

  public function getParent() {
    return $this->parent;
  }

  public function getAuthor() {
    return $this->author;
  }

  public function getStorylineTag() {
    return $this->storylineTag;
  }

  public function getSimpleTag() {
    return $this->simpleTag;
  }

  public function setId($id) {
    $this->id = $id;
    return $this;
  }

  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }

  public function setCreated(\DateTime $created) {
    $this->created = $created;
    return $this;
  }

  public function setNotes($notes) {
    $this->notes = $notes;
    return $this;
  }

  public function setText($text) {
    $this->text = $text;
    return $this;
  }

  public function setHitCount($hitCount) {
    $this->hitCount = $hitCount;
    return $this;
  }

  public function setLikes($likes) {
    $this->likes = $likes;
    return $this;
  }

  public function setDislikes($dislikes) {
    $this->dislikes = $dislikes;
    return $this;
  }

  public function setParent(Episode $parent) {
    $this->parent = $parent;
    return $this;
  }

  public function setAuthor(AuthorName $author) {
    $this->author = $author;
    return $this;
  }

  public function setStorylineTag(StorylineTag $storylineTag) {
    $this->storylineTag = $storylineTag;
    return $this;
  }

  public function setSimpleTag($simpleTag) {
    $this->simpleTag = $simpleTag;
    return $this;
  }

}
