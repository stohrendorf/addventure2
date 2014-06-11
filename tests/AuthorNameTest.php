<?php
namespace addventure;

require 'DoctrineTestCase.php';

class AuthorNameTest extends DoctrineTestCase
{
    /**
     * @var AuthorName
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new AuthorName;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers addventure\AuthorName::getEpisodes
     * @todo   Implement testGetEpisodes().
     */
    public function testGetEpisodes()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers addventure\AuthorName::getName
     * @covers addventure\AuthorName::setName
     */
    public function testGetAndSetName()
    {
        // test emptyness
        try {
            // ä * 201 is not OK
            $this->object->setName('');
            $this->fail('Empty name');
        } catch (\InvalidArgumentException $ex) {
        }
        
        // test trimming
        $this->object->setName('   a test ' . "\n\r");
        $this->assertEquals($this->object->getName(), 'a test');
        
        // test trim before checking the length
        $this->object->setName('a test' . str_repeat(' ', 500));
        $this->assertEquals($this->object->getName(), 'a test');
        
        // test UTF-8 length
        try {
            // ä * 200 is OK
            $this->object->setName(str_repeat("\xC3\xA4",200));
        } catch (\InvalidArgumentException $ex) {
            $this->fail('UTF-8 encoding length (1)');
        }
        try {
            // ä * 201 is not OK
            $this->object->setName(str_repeat("\xC3\xA4",201));
            $this->fail('UTF-8 encoding length (2)');
        } catch (\InvalidArgumentException $ex) {
        }
    }

    /**
     * @covers addventure\AuthorName::getUser
     * @covers addventure\AuthorName::setUser
     */
    public function testGetUser()
    {
        $user = new \addventure\User();
        $this->object->setUser($user);
        $this->assertSame($user, $this->object->getUser());
    }

    /**
     * @covers addventure\AuthorName::setId
     * @covers addventure\AuthorName::getId
     */
    public function testGetAndSetId()
    {
        $this->object->setId(123);
        $this->assertEquals($this->object->getId(), 123);
    }

    /**
     * @covers addventure\AuthorName::setUser
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetUser()
    {
        $this->object->setUser(null);
    }

    public function testaddEpisode1()
    {
        /*
         * Episode:
         *   id = 123
         *   author:
         *     id = AUTO
         *     name = 'Johnny Doe'
         *     user:
         *       username = 'A. U. Thor'
         */
        $user = new User();
        $user->setUsername('A. U. Thor');
        $this->getEm()->persist($user);

        $this->object->setUser($user);
        $this->object->setName('Johnny Doe');
        $this->getEm()->persist($this->object);
        $this->getEm()->flush();

        $ep = new Episode();
        $ep->setAuthor($this->object);
        $ep->setOldId(123);
        $this->getEm()->persist($ep);
        $this->getEm()->flush();
        
        $this->object->getEpisodes()->add($ep);
        $this->getEm()->persist($this->object);
        $this->getEm()->flush();
        
        $thisId = $this->object->getId();
        
        $this->object = $this->getEm()->find('addventure\AuthorName', $thisId);
        $this->assertEquals(1, count($this->object->getEpisodes()));
    }
}
