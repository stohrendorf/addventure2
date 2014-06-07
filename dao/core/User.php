<?php

namespace addventure;

/**
 * @Entity
 * @Table(name="AddventureUsers") because "User" is a reserved word.
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
        if($role < 0 || $role > self::Administrator) {
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

    public function isAnonymous() {
        return $this->role === self::Anonymous;
    }

    public function isAwaitingApproval() {
        return $this->role === self::AwaitApproval;
    }

    public function isRegistered() {
        return $this->role === self::Registered;
    }

    public function isModerator() {
        return $this->role === self::Moderator;
    }

    public function isAdministrator() {
        return $this->role == self::Administrator;
    }

    public function canCreateEpisode() {
        return !$this->blocked && $this->role >= self::Registered;
    }

    public function canCreateComment() {
        return !$this->blocked && $this->role >= self::Registered;
    }

    public function canSubscribe() {
        return !$this->blocked && $this->role >= self::Registered;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        if($username === null) {
            throw new \InvalidArgumentException('Username may not be null');
        }
        $username = preg_replace('/\s+/', ' ', trim($username));
        if(mb_strlen($username) > 100) {
            throw new \InvalidArgumentException('Username too long: ' . mb_strlen($username));
        }
        $this->username = $username;
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
            'role' => self::Anonymous,
            'email' => '',
            'canCreateEpisode' => false,
            'canCreateComment' => false,
            'canSubscribe' => false
        );
    }

}
