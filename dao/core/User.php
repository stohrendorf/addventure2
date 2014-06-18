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
            return;
        }
        elseif(is_numeric($value)) {
            if($value < self::Anonymous || $value > self::Administrator) {
                throw new \InvalidArgumentException("Unknown user role '$value'");
            }
            $this->value = $value;
            return;
        }
        throw new \InvalidArgumentException("Unknown user role '$value'");
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
     * @var AuthorName[]|\Doctrine\Common\Collections\ArrayCollection
     */
    private $authorNames;

    /**
     * @Column(type="boolean", nullable=false)
     * @var bool
     */
    private $blocked = false;

    /**
     * @Column(type="DateTime", nullable=true)
     * @var \DateTime
     */
    private $registeredSince = null;

    /**
     * @Column(type="smallint", nullable=false)
     */
    private $failedLogins = 0;

    public function __construct() {
        $this->role = UserRole::Anonymous;
        $this->authorNames = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @PrePersist
     * @PreUpdate
     * 
     * Checks the invariants.
     * 
     * These are:
     *    1. An {@see UserRole::Anonymous} user must not have a password set.
     *    2. The other ones are required to have a password.
     *    3. The username may not be empty.
     */
    public function checkInvariants() {
        if(empty($this->username)) {
            throw new \InvalidArgumentException("All users must have a non-empty username.");
        }
        if($this->role !== UserRole::Anonymous) {
            if(empty($this->password)) {
                throw new \InvalidArgumentException("Non-anonymous users must have set a password.");
            }
            return;
        }

        if(!empty($this->password)) {
            throw new \InvalidArgumentException("Anonymous users must not have set a password.");
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
        if(is_array($authorNames)) {
            $this->authorNames = new \Doctrine\Common\Collections\ArrayCollection($authorNames);
            return $this;
        }
        if(!($authorNames instanceof \Doctrine\Common\Collections\ArrayCollection)) {
            throw new \InvalidArgumentException("Unexpected type");
        }
        $this->authorNames = $authorNames;
        return $this;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setBlocked($b) {
        if(!is_bool($b)) {
            throw new \InvalidArgumentException("Expected a boolean");
        }
        $this->blocked = $b;
    }

    public function isLockedOut() {
        return $this->failedLogins >= ADDVENTURE_MAX_FAILED_LOGINS;    
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
        return !$this->isLockedOut() && !$this->blocked && $this->role >= UserRole::Registered;
    }

    public function canCreateComment() {
        return !$this->isLockedOut() && !$this->blocked && $this->role >= UserRole::Registered;
    }

    public function canSubscribe() {
        return !$this->isLockedOut() && !$this->blocked && $this->role >= UserRole::Registered;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        if($username === null) {
            $this->username = null;
            return $this;
        }
        $this->username = simplifyWhitespace($username, 100, false);
        return $this;
    }

    public function getRegisteredSince() {
        return $this->registeredSince;
    }

    public function setRegisteredSince(\DateTime $registeredSince) {
        $this->registeredSince = $registeredSince;
        return $this;
    }

    public function getFailedLogins() {
        return $this->failedLogins;
    }

    public function setFailedLogins($failedLogins) {
        $this->failedLogins = ($failedLogins > ADDVENTURE_MAX_FAILED_LOGINS ? ADDVENTURE_MAX_FAILED_LOGINS : $failedLogins);
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
            'canSubscribe' => $this->canSubscribe(),
            'registeredSince' => ($this->registeredSince === null ? '' : $this->registeredSince->format("l, d M Y H:i"))
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
            'canSubscribe' => false,
            'registeredSince' => ''
        );
    }

}
