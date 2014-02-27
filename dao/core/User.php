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
   * @var AuthorName[]
   */
  private $authorNames;

  public function getId() {
    return $this->id;
  }

  public function getEmail() {
    return $this->email;
  }

  public function getRole() {
    return $this->role;
  }

  public function getAuthorNames() {
    return $this->authorNames;
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

  public function setAuthorNames($authorNames) {
    $this->authorName = $authorNames;
    return $this;
  }

}
