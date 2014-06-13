<?php

namespace addventure;

class UserRoleTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var UserRole
     */
    protected $object;

    protected function setUp() {
        $this->object = new UserRole;
    }

    protected function tearDown() {
        
    }

    /**
     * @covers addventure\UserRole::__construct
     */
    public function testConstructor() {
        $this->assertEquals(UserRole::Anonymous, $this->object->get());

        $this->object = new UserRole(UserRole::Anonymous);
        $this->assertEquals(UserRole::Anonymous, $this->object->get());
        $this->object = new UserRole('Anonymous');
        $this->assertEquals(UserRole::Anonymous, $this->object->get());
        $this->object = new UserRole(0);
        $this->assertEquals(UserRole::Anonymous, $this->object->get());

        $this->object = new UserRole(UserRole::AwaitApproval);
        $this->assertEquals(UserRole::AwaitApproval, $this->object->get());
        $this->object = new UserRole('AwaitApproval');
        $this->assertEquals(UserRole::AwaitApproval, $this->object->get());
        $this->object = new UserRole(1);
        $this->assertEquals(UserRole::AwaitApproval, $this->object->get());

        $this->object = new UserRole(UserRole::Registered);
        $this->assertEquals(UserRole::Registered, $this->object->get());
        $this->object = new UserRole('Registered');
        $this->assertEquals(UserRole::Registered, $this->object->get());
        $this->object = new UserRole(2);
        $this->assertEquals(UserRole::Registered, $this->object->get());

        $this->object = new UserRole(UserRole::Moderator);
        $this->assertEquals(UserRole::Moderator, $this->object->get());
        $this->object = new UserRole('Moderator');
        $this->assertEquals(UserRole::Moderator, $this->object->get());
        $this->object = new UserRole(3);
        $this->assertEquals(UserRole::Moderator, $this->object->get());

        $this->object = new UserRole(UserRole::Administrator);
        $this->assertEquals(UserRole::Administrator, $this->object->get());
        $this->object = new UserRole('Administrator');
        $this->assertEquals(UserRole::Administrator, $this->object->get());
        $this->object = new UserRole(4);
        $this->assertEquals(UserRole::Administrator, $this->object->get());
    }

    /**
     * @covers addventure\UserRole::set
     * @covers addventure\UserRole::get
     */
    public function testSetAndGet() {
        $this->assertEquals(UserRole::Anonymous, $this->object->get());

        $this->object->set(UserRole::Anonymous);
        $this->assertEquals(UserRole::Anonymous, $this->object->get());
        $this->object->set('Anonymous');
        $this->assertEquals(UserRole::Anonymous, $this->object->get());
        $this->object->set(0);
        $this->assertEquals(UserRole::Anonymous, $this->object->get());

        $this->object->set(UserRole::AwaitApproval);
        $this->assertEquals(UserRole::AwaitApproval, $this->object->get());
        $this->object->set('AwaitApproval');
        $this->assertEquals(UserRole::AwaitApproval, $this->object->get());
        $this->object->set(1);
        $this->assertEquals(UserRole::AwaitApproval, $this->object->get());

        $this->object->set(UserRole::Registered);
        $this->assertEquals(UserRole::Registered, $this->object->get());
        $this->object->set('Registered');
        $this->assertEquals(UserRole::Registered, $this->object->get());
        $this->object->set(2);
        $this->assertEquals(UserRole::Registered, $this->object->get());

        $this->object->set(UserRole::Moderator);
        $this->assertEquals(UserRole::Moderator, $this->object->get());
        $this->object->set('Moderator');
        $this->assertEquals(UserRole::Moderator, $this->object->get());
        $this->object->set(3);
        $this->assertEquals(UserRole::Moderator, $this->object->get());

        $this->object->set(UserRole::Administrator);
        $this->assertEquals(UserRole::Administrator, $this->object->get());
        $this->object->set('Administrator');
        $this->assertEquals(UserRole::Administrator, $this->object->get());
        $this->object->set(4);
        $this->assertEquals(UserRole::Administrator, $this->object->get());

        try {
            $this->object->set(UserRole::Anonymous - 1);
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }
        try {
            $this->object->set(UserRole::Administrator + 1);
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }
        try {
            $this->object->set('unknown');
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }
        try {
            $this->object->set(null);
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }
    }

}
