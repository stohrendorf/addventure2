<?php

namespace addventure;

/**
 * @Entity
 * @Table(indexes={@Index(name="userIndex", columns={"user_id"})})
 */
class AuthorName {

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
   * @ManyToOne(targetEntity="addventure\User", inversedBy="authorName", fetch="LAZY")
   * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
   * @var User
   */
  private $user;

  /**
   * @OneToMany(targetEntity="addventure\Episode", mappedBy="author", fetch="EXTRA_LAZY")
   * @var array
   */
  private $episode;

  public function getEpisode() {
    return $this->episode;
  }

  public function getId() {
    return $this->id;
  }

  public function getName() {
    return $this->name;
  }

  public function getUser() {
    return $this->user;
  }

  public function setId($id) {
    $this->id = $id;
    return $this;
  }

  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  public function setUser(User $user) {
    $this->user = $user;
    return $this;
  }

  public function setEpisode($episode) {
    $this->episode = $episode;
    return $this;
  }

}
