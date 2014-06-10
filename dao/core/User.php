<?php

/**
 * Classes handling user accounts.
 * @package DAO
 */

namespace addventure;

/**
 * Roles a user can have.
 * @package DAO
 */
class UserRole {

    private $value;

    // This is ordered by the amount of rights each role has.
    /**
     * An anonymous user, meant to be used for legacy users imported from old systems.
     */
    const Anonymous = 0;

    /**
     * The user has requested a registration, but hasn't activated his account yet.
     */
    const AwaitApproval = 1;

    /**
     * The user is registered an can write episodes and comments.
     */
    const Registered = 2;

    /**
     * The user is a moderator and allowed to edit/delete comments and episodes
     * and is able to block users.
     */
    const Moderator = 3;

    /**
     * The user has Moderator rights but can also change the roles of
     * users.
     */
    const Administrator = 4;

    /**
     * Construct a new role from a valid integer, or a valid string matching a
     * constant.
     * @param string|int $value Enum value
     * @throws \InvalidArgumentException if an invalid value has been passed
     */
    public function __construct($value = self::Anonymous) {
        $this->set($value);
    }

    public function set($value) {
        if(is_string($value)) {
            if(!defined("self::$value")) {
                throw new \InvalidArgumentException("Unknown user role '$value'");
            }
            $this->value = constant("self::$value");
        }
        elseif(is_numeric($value)) {
            if($value < self::Anonymous || $value > self::Administrator) {
                throw new \InvalidArgumentException("Unknown user role '$value'");
            }
            $this->value = $value;
        }
        else {
            throw new \InvalidArgumentException("Unknown user role '$value'");
        }
    }

    public function get() {
        return $this->value;
    }

}

/**
 * @Entity
 * @Table(name="AddventureUsers") because "User" is a reserved word.
 * @HasLifecycleCallbacks
 * @package DAO
 */
class User {

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
     * @Column(type="string", length=100, unique=true, nullable=true)
     * @var string
     */
    private $username;

    /**
     * @Column(type="string", unique=false, nullable=true)
     * @var string
     */
    private $password = null;

    /**
     * @Column(type="smallint", nullable=false)
     */
    private $role;

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

    public function __construct() {
        $this->role = UserRole::Anonymous;
    }

    /**
     * @PrePersist
     * @PreUpdate
     */
    public function checkInvariants() {
        if($this->role !== UserRole::Anonymous) {
            if(empty($this->username)) {
                throw new \InvalidArgumentException("Non-anonymous users must have a non-empty username.");
            }
            if(empty($this->password)) {
                throw new \InvalidArgumentException("Non-anonymous users must have set a password.");
            }
        }
        else {
            if(!empty($this->password)) {
                throw new \InvalidArgumentException("Anonymous users must not have set a password.");
            }
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }

    /**
     * @return UserRole
     */
    public function getRole() {
        return new UserRole($this->role);
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
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        if(!$email) {
            throw new \InvalidArgumentException('Invalid E-Mail supplied');
        }
        $this->email = $email;
        return $this;
    }

    public function setRole($role) {
        if(!($role instanceof UserRole)) {
            $role = new UserRole($role);
        }
        $this->role = $role->get();
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

    public function isAnonymous() {
        return $this->role === UserRole::Anonymous;
    }

    public function isAwaitingApproval() {
        return $this->role === UserRole::AwaitApproval;
    }

    public function isRegistered() {
        return $this->role === UserRole::Registered;
    }

    public function isModerator() {
        return $this->role === UserRole::Moderator;
    }

    public function isAdministrator() {
        return $this->role == UserRole::Administrator;
    }

    public function canCreateEpisode() {
        return !$this->blocked && $this->role >= UserRole::Registered;
    }

    public function canCreateComment() {
        return !$this->blocked && $this->role >= UserRole::Registered;
    }

    public function canSubscribe() {
        return !$this->blocked && $this->role >= UserRole::Registered;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        if($username === null) {
            $this->username = null;
        }
        else {
            $this->username = simplifyWhitespace($username, 100, false);
        }
        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function toSmarty() {
        return array(
            'blocked' => $this->getBlocked(),
            'userid' => $this->getId(),
            'username' => $this->getUsername(),
            'role' => $this->getRole(),
            'email' => $this->getEmail(),
            'canCreateEpisode' => $this->canCreateEpisode(),
            'canCreateComment' => $this->canCreateComment(),
            'canSubscribe' => $this->canSubscribe()
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public static function defaultSmarty() {
        return array(
            'blocked' => false,
            'userid' => -1,
            'username' => '',
            'role' => UserRole::Anonymous,
            'email' => '',
            'canCreateEpisode' => false,
            'canCreateComment' => false,
            'canSubscribe' => false
        );
    }

}
