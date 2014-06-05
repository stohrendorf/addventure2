<?php

namespace addventure;

class LinkTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Link
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Link;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @covers addventure\Link::checkInvariants
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Self-links are not allowed.
     */
    public function testCheckInvariants() {
        $ep = new \addventure\Episode();
        $ep->setId(1);
        $this->object->setFromEp($ep);
        $this->object->setToEp($ep);
        $this->object->checkInvariants();
    }

    /**
     * @covers addventure\Link::checkInvariants
          */
    public function testCheckInvariants2() {
        $ep1 = new \addventure\Episode();
        $ep1->setId(1);
        $ep2 = new \addventure\Episode();
        $ep2->setId(2);
        $this->object->setFromEp($ep1);
        $this->object->setToEp($ep2);
        $this->object->checkInvariants();
    }

    /**
     * @covers addventure\Link::getFromEp
     * @covers addventure\Link::setFromEp
     */
    public function testGetFromEp() {
        $ep = new \addventure\Episode();
        $this->object->setFromEp($ep);
        $this->assertSame($this->object->getFromEp(), $ep);
    }

    /**
     * @covers addventure\Link::getToEp
     * @covers addventure\Link::setToEp
     */
    public function testGetToEp() {
        $ep = new \addventure\Episode();
        $this->object->setToEp($ep);
        $this->assertSame($this->object->getToEp(), $ep);
    }

    /**
     * @covers addventure\Link::setIsBacklink
     * @covers addventure\Link::getIsBacklink
     */
    public function testSetIsBacklink() {
        try {
            $this->object->setIsBacklink(0);
            $this->fail('0 is not boolean');
        }
        catch(\InvalidArgumentException $ex) {
            // OK, this is expected
        }
        
        try {
            $this->object->setIsBacklink('false');
            $this->fail("'false' is not boolean");
        }
        catch(\InvalidArgumentException $ex) {
            // OK, this is expected
        }
        
        try {
            $this->object->setIsBacklink('FALSE');
            $this->fail("'FALSE' is not boolean");
        }
        catch(\InvalidArgumentException $ex) {
            // OK, this is expected
        }
        
        try {
            $this->object->setIsBacklink(false);
        }
        catch(\InvalidArgumentException $ex) {
            $this->fail("Literal 'false' is a boolean");
        }
        $this->assertEquals($this->object->getIsBacklink(), false);
        
        try {
            $this->object->setIsBacklink(true);
        }
        catch(\InvalidArgumentException $ex) {
            $this->fail("Literal 'true' is a boolean");
        }
        $this->assertEquals($this->object->getIsBacklink(), true);
    }

    /**
     * @covers addventure\Link::setFromEp
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetFromEp() {
        $this->object->setFromEp(null);
    }

    /**
     * @covers addventure\Link::setToEp
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetToEp() {
        $this->object->setToEp(null);
    }

    /**
     * @covers addventure\Link::getTitle
     * @covers addventure\Link::setTitle
     */
    public function testGetAndSetTitle() {
        // test trimming
        $this->object->setTitle('   a test ' . "\n\r");
        $this->assertEquals($this->object->getTitle(), 'a test');
        
        // test strip
        $this->object->setTitle("a\n\r    test  case");
        $this->assertEquals($this->object->getTitle(), 'a test case');
        
        // test both
        $this->object->setTitle("   a\n\r    test  case\r\n  ");
        $this->assertEquals($this->object->getTitle(), 'a test case');
        
        // test strip and trim before checking the length
        $this->object->setTitle('a test' . str_repeat(' ', 500));
        $this->assertEquals($this->object->getTitle(), 'a test');
        
        $this->object->setTitle('a' . str_repeat("\n", 500) . 'test');
        $this->assertEquals($this->object->getTitle(), 'a test');
        
        // test UTF-8 length
        try {
            // ä * 255 is OK
            $this->object->setTitle(str_repeat("\xC3\xA4",255));
        } catch (\InvalidArgumentException $ex) {
            $this->fail('UTF-8 encoding length (1)');
        }
        try {
            // ä * 256 is not OK
            $this->object->setTitle(str_repeat("\xC3\xA4",256));
            $this->fail('UTF-8 encoding length (2)');
        } catch (\InvalidArgumentException $ex) {
        }
    }

}
