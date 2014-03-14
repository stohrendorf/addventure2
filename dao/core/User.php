<?php

namespace addventure;

/**
 * @Entity
 */
class User {

    // This is ordered by the amount of rights each role has.
    const Anonymous = 0;
    const AwaitApproval = 1;
    const Registered = 2;
    const Moderator = 3;
    const Administrator = 4;

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @Column(type="string", length=100, unique=true, nullable=true)
     * @var string
     */
    private $email;
    
    /**
     * @Column(type="string", unique=false, nullable=true)
     * @var string
     */
    private $password = null;

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
    
    /**
     * @Column(type="boolean", nullable=false)
     * @var bool
     */
    private $blocked = false;

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
    
    public function getPassword() {
        return $this->password;
    }
    
    public function getBlocked() {
        return $this->blocked;
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
        if($role<0 || $role>self::Administrator) {
            throw new \InvalidArgumentException("Invalid user role specified");
        }
        $this->role = $role;
        return $this;
    }

    public function setAuthorNames($authorNames) {
        $this->authorName = $authorNames;
        return $this;
    }

    public function setPassword($pw) {
        $this->password = $pw;
    }
    
    public function setBlocked($b) {
        $this->blocked = $b;
    }
}
