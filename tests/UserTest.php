<?php

namespace addventure;

class UserTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var User
     */
    protected $object;

    protected function setUp() {
        $this->object = new User;
    }

    protected function tearDown() {
        
    }

    /**
     * @covers addventure\User::getId
     * @covers addventure\User::setId
     */
    public function testGetAndSetId() {
        $this->object->setId(123);
        $this->assertEquals(123, $this->object->getId());
        $this->object->setId(456);
        $this->assertEquals(456, $this->object->getId());
    }

    /**
     * @covers addventure\User::getEmail
     * @covers addventure\User::setEmail
     */
    public function testGetAndSetEmail() {
        $this->object->setEmail('user@example.com');
        $this->assertEquals('user@example.com', $this->object->getEmail());

        $this->object->setEmail(' user.space@example.com  ');
        $this->assertEquals('user.space@example.com', $this->object->getEmail());

        try {
            $this->object->setEmail('invalid@mail');
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }

        try {
            $this->object->setEmail(null);
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }
    }

    /**
     * @covers addventure\User::getUsername
     * @covers addventure\User::setUsername
     */
    public function testGetAndSetUsername() {
        $this->object->setUsername('John Doe');
        $this->assertEquals('John Doe', $this->object->getUsername());

        $this->object->setUsername('Johnny     Doe  ');
        $this->assertEquals('Johnny Doe', $this->object->getUsername());

        $this->object->setUsername(' Anna' . str_repeat(' ', 500));
        $this->assertEquals($this->object->getUsername(), 'Anna');

        try {
            // NULL is allowed
            $this->object->setUsername(null);
        }
        catch(\InvalidArgumentException $ex) {
            $this->fail();
        }

        try {
            // but an empty name is not allowed
            $this->object->setUsername('');
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }

        // test UTF-8 length
        try {
            // ä * 100 is OK
            $this->object->setUsername(str_repeat("\xC3\xA4", 100));
        }
        catch(\InvalidArgumentException $ex) {
            $this->fail('UTF-8 encoding length (1)');
        }
        try {
            // ä * 101 is not OK
            $this->object->setUsername(str_repeat("\xC3\xA4", 101));
            $this->fail('UTF-8 encoding length (2)');
        }
        catch(\InvalidArgumentException $ex) {
            
        }
    }

    /**
     * @covers addventure\User::getRole
     * @covers addventure\User::setRole
     */
    public function testGetAndSetRole() {
        $this->object->setRole(0);
        $this->assertEquals(UserRole::Anonymous, $this->object->getRole()->get());
        $this->object->setRole(1);
        $this->assertEquals(UserRole::AwaitApproval, $this->object->getRole()->get());
        $this->object->setRole('Registered');
        $this->assertEquals(UserRole::Registered, $this->object->getRole()->get());
        $this->object->setRole(UserRole::Moderator);
        $this->assertEquals(UserRole::Moderator, $this->object->getRole()->get());
    }

    /**
     * @covers addventure\User::getAuthorNames
     * @covers addventure\User::setAuthorNames
     */
    public function testGetAndSetAuthorNames() {
        try {
            $this->object->setAuthorNames(null);
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
        }
        
        try {
            $this->object->setAuthorNames(new \addventure\AuthorName());
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
        }
        
        $this->object->setAuthorNames(array());
        $this->assertEquals(0, $this->object->getAuthorNames()->count());
        $this->object->setAuthorNames(new \Doctrine\Common\Collections\ArrayCollection());
        $this->assertEquals(0, $this->object->getAuthorNames()->count());
        
        // must be an ArrayCollection
        $this->object->getAuthorNames()->add(new \addventure\AuthorName());
        $this->object->getAuthorNames()->add(new \addventure\AuthorName());
        $this->object->getAuthorNames()->add(new \addventure\AuthorName());
        $this->assertEquals(3, $this->object->getAuthorNames()->count());

        $this->object->setAuthorNames(array(new \addventure\AuthorName()));
        $this->assertEquals(1, $this->object->getAuthorNames()->count());
        $this->object->setAuthorNames(new \Doctrine\Common\Collections\ArrayCollection(array(new \addventure\AuthorName())));
        $this->assertEquals(1, $this->object->getAuthorNames()->count());
    }

    /**
     * @covers addventure\User::getPassword
     * @covers addventure\User::setPassword
     */
    public function testGetAndSetPassword() {
        $this->object->setUsername('John Doe');

        try {
            $this->object->setRole(UserRole::Anonymous);
            $this->object->setPassword('password');
            $this->object->checkInvariants();
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }

        try {
            $this->object->setRole(UserRole::Anonymous);
            $this->object->setPassword('');
            $this->object->checkInvariants();
        }
        catch(\InvalidArgumentException $ex) {
            $this->fail();
        }

        try {
            $this->object->setRole(UserRole::Anonymous);
            $this->object->setPassword(null);
            $this->object->checkInvariants();
        }
        catch(\InvalidArgumentException $ex) {
            $this->fail();
        }

        try {
            $this->object->setRole(UserRole::AwaitApproval);
            $this->object->setPassword('password');
            $this->object->checkInvariants();
        }
        catch(\InvalidArgumentException $ex) {
            $this->fail();
        }

        $this->object->setPassword(password_hash('some password', PASSWORD_DEFAULT));
        $this->assertTrue(password_verify('some password', $this->object->getPassword()));
    }

    /**
     * @covers addventure\User::getBlocked
     * @covers addventure\User::setBlocked
     */
    public function testGetAndSetBlocked() {
        $this->object->setBlocked(true);
        $this->assertTrue($this->object->getBlocked());

        $this->object->setBlocked(false);
        $this->assertFalse($this->object->getBlocked());

        try {
            $this->object->setBlocked('false');
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
        }
        
        try {
            $this->object->setBlocked(0);
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
        }
    }

    /**
     * @covers addventure\User::isAnonymous
     * @covers addventure\User::isAwaitingApproval
     * @covers addventure\User::isRegistered
     * @covers addventure\User::isModerator
     * @covers addventure\User::isAdministrator
     * @covers addventure\User::canCreateEpisode
     * @covers addventure\User::canCreateComment
     * @covers addventure\User::canSubscribe
     */
    public function testUserPermissions() {
        $this->object->setRole(UserRole::Anonymous);
        $this->assertTrue($this->object->isAnonymous());
        $this->assertFalse($this->object->isAwaitingApproval());
        $this->assertFalse($this->object->isRegistered());
        $this->assertFalse($this->object->isModerator());
        $this->assertFalse($this->object->isAdministrator());
        $this->assertFalse($this->object->canCreateEpisode());
        $this->assertFalse($this->object->canCreateComment());
        $this->assertFalse($this->object->canSubscribe());
        
        $this->object->setRole(UserRole::AwaitApproval);
        $this->assertFalse($this->object->isAnonymous());
        $this->assertTrue($this->object->isAwaitingApproval());
        $this->assertFalse($this->object->isRegistered());
        $this->assertFalse($this->object->isModerator());
        $this->assertFalse($this->object->isAdministrator());
        $this->assertFalse($this->object->canCreateEpisode());
        $this->assertFalse($this->object->canCreateComment());
        $this->assertFalse($this->object->canSubscribe());
        
        $this->object->setRole(UserRole::Registered);
        $this->assertFalse($this->object->isAnonymous());
        $this->assertFalse($this->object->isAwaitingApproval());
        $this->assertTrue($this->object->isRegistered());
        $this->assertFalse($this->object->isModerator());
        $this->assertFalse($this->object->isAdministrator());
        $this->assertTrue($this->object->canCreateEpisode());
        $this->assertTrue($this->object->canCreateComment());
        $this->assertTrue($this->object->canSubscribe());
        
        $this->object->setRole(UserRole::Moderator);
        $this->assertFalse($this->object->isAnonymous());
        $this->assertFalse($this->object->isAwaitingApproval());
        $this->assertFalse($this->object->isRegistered());
        $this->assertTrue($this->object->isModerator());
        $this->assertFalse($this->object->isAdministrator());
        $this->assertTrue($this->object->canCreateEpisode());
        $this->assertTrue($this->object->canCreateComment());
        $this->assertTrue($this->object->canSubscribe());
        
        $this->object->setRole(UserRole::Administrator);
        $this->assertFalse($this->object->isAnonymous());
        $this->assertFalse($this->object->isAwaitingApproval());
        $this->assertFalse($this->object->isRegistered());
        $this->assertFalse($this->object->isModerator());
        $this->assertTrue($this->object->isAdministrator());
        $this->assertTrue($this->object->canCreateEpisode());
        $this->assertTrue($this->object->canCreateComment());
        $this->assertTrue($this->object->canSubscribe());
    }

    /**
     * @covers addventure\User::checkInvariants
     */
    public function testCheckInvariants() {
        try {
            $this->object->setUsername(null);
            $this->object->setRole(UserRole::Anonymous);
            $this->object->checkInvariants();
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }

        try {
            $this->object->setUsername('John');
            $this->object->setRole(UserRole::Anonymous);
            $this->object->checkInvariants();
        }
        catch(\InvalidArgumentException $ex) {
            $this->fail();
        }

        try {
            $this->object->setUsername(null);
            $this->object->setRole(UserRole::AwaitApproval);
            $this->object->checkInvariants();
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }

        try {
            $this->object->setUsername('John Doe');
            $this->object->setRole(UserRole::Anonymous);
            $this->object->setPassword('some password');
            $this->object->checkInvariants();
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }

        try {
            $this->object->setUsername('John Doe');
            $this->object->setRole(UserRole::AwaitApproval);
            $this->object->setPassword('');
            $this->object->checkInvariants();
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }

        try {
            $this->object->setUsername('John Doe');
            $this->object->setRole(UserRole::AwaitApproval);
            $this->object->setPassword('some password');
            $this->object->checkInvariants();
        }
        catch(\InvalidArgumentException $ex) {
            $this->fail();
        }
    }

    /**
     * @covers addventure\User::__construct
     */
    public function testConstructor() {
        $this->assertInstanceOf( '\Doctrine\Common\Collections\ArrayCollection', $this->object->getAuthorNames() );
        $this->assertEquals(UserRole::Anonymous, $this->object->getRole()->get());
    }

    /**
     * @covers addventure\User::isLockedOut
     * @covers addventure\User::getFailedLogins
     * @covers addventure\User::setFailedLogins
     */
    public function testLockedOut() {
        $this->assertEquals( 0, $this->object->getFailedLogins() );
        for( $i=0; $i < ADDVENTURE_MAX_FAILED_LOGINS; ++$i ) {
            $this->object->setFailedLogins($i);
            $this->assertEquals( $i, $this->object->getFailedLogins() );
            $this->assertFalse( $this->object->isLockedOut() );
        }
        for( $i=ADDVENTURE_MAX_FAILED_LOGINS; $i < ADDVENTURE_MAX_FAILED_LOGINS+2; ++$i ) {
            $this->object->setFailedLogins($i);
            $this->assertEquals( ADDVENTURE_MAX_FAILED_LOGINS, $this->object->getFailedLogins() );
            $this->assertTrue( $this->object->isLockedOut() );
        }
    }

    /**
     * @covers addventure\User::getRegisteredSince
     * @covers addventure\User::setRegisteredSince
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testRegisteredSince() {
        $this->assertEquals( null, $this->object->getRegisteredSince() );
        $this->object->setRegisteredSince( new \DateTime() );
        $this->assertNotEquals( null, $this->object->getRegisteredSince() );
        $this->object->setRegisteredSince( null );
    }

}
