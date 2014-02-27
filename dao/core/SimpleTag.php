<?php

namespace addventure;

/**
 * @Entity
 */
class SimpleTag {

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
   * @ManyToMany(targetEntity="addventure\Episode", inversedBy="simpleTags", fetch="EXTRA_LAZY")
   * @JoinTable(
   *     name="EpisodeToSimpleTag", 
   *     joinColumns={@JoinColumn(name="simple_tag_id", referencedColumnName="id", nullable=false)}, 
   *     inverseJoinColumns={@JoinColumn(name="episode_id", referencedColumnName="id", nullable=false)}
   * )
   * @var Episode[]
   */
  private $episodes;

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
    $this->title = $title;
    return $this;
  }

  public function setEpisodes($episodes) {
    $this->episodes = $episodes;
    return $this;
  }

}
