<?php

namespace addventure;

/**
 * @Entity
 */
class User {

  const Anonymous = 0;
  const Registered = 1;
  const Moderator = 2;
  const Administrator = 3;

  /**
   * @Id
   * @Column(type="integer")
   * @GeneratedValue(strategy="AUTO")
   * @var int
   */
  private $id;

  /**
   * @Column(type="string", length=100, nullable=true)
   * @var string
   */
  private $email;

  /**
   * @Column(type="smallint", nullable=false)
   * @var int
   */
  private $role = self::Anonymous;

  /**
   * @OneToMany(targetEntity="addventure\AuthorName", mappedBy="user", fetch="LAZY")
   * @var array
   */
  private $authorName;

  public function getId() {
    return $this->id;
  }

  public function getEmail() {
    return $this->email;
  }

  public function getRole() {
    return $this->role;
  }

  public function getAuthorName() {
    return $this->authorName;
  }

  public function setId($id) {
    $this->id = $id;
    return $this;
  }

  public function setEmail($email) {
    $this->email = $email;
    return $this;
  }

  public function setRole($role) {
    $this->role = $role;
    return $this;
  }

  public function setAuthorName($authorName) {
    $this->authorName = $authorName;
    return $this;
  }

}
