<?php

namespace addventure;

/**
 * @Entity
 * @Table(name="ci_sessions", indexes={@Index(name="last_activity_idx", columns={"last_activity"})})
 */
class Session {

    /**
     * @Id
     * @Column(type="string", length=40, nullable=false)
     * @var string
     */
    private $session_id = '0';

    /**
     * @Column(type="string", length=45, nullable=false)
     * @var string
     */
    private $ip_address = '0';

    /**
     * @Column(type="string", length=120, nullable=false)
     * @var string
     */
    private $user_agent;

    /**
     * @Column(type="integer", length=10, nullable=false)
     * @var int
     */
    private $last_activity = 0;

    /**
     * @Column(type="text", nullable=false)
     * @var string
     */
    private $user_data;

    public function getSession_id() {
        return $this->session_id;
    }

    public function getIp_address() {
        return $this->ip_address;
    }

    public function getUser_agent() {
        return $this->user_agent;
    }

    public function getLast_activity() {
        return $this->last_activity;
    }

    public function getUser_data() {
        return $this->user_data;
    }

    public function setSession_id($session_id) {
        $this->session_id = $session_id;
        return $this;
    }

    public function setIp_address($ip_address) {
        $this->ip_address = $ip_address;
        return $this;
    }

    public function setUser_agent($user_agent) {
        $this->user_agent = $user_agent;
        return $this;
    }

    public function setLast_activity($last_activity) {
        $this->last_activity = $last_activity;
        return $this;
    }

    public function setUser_data($user_data) {
        $this->user_data = $user_data;
        return $this;
    }

}
