<?php

namespace addventure;

class StorylineTagTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var StorylineTag
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new StorylineTag;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @covers addventure\StorylineTag::getId
     * @covers addventure\StorylineTag::setId
     */
    public function testGetAndSetId() {
        $this->object->setId(123);
        $this->assertEquals(123, $this->object->getId());
        $this->object->setId(456);
        $this->assertEquals(456, $this->object->getId());
    }

    /**
     * @covers addventure\StorylineTag::getTitle
     * @covers addventure\StorylineTag::setTitle
     */
    public function testGetAndSetTitle() {
        // test emptyness
        try {
            // ä * 201 is not OK
            $this->object->setTitle('');
            $this->fail('Empty name');
        }
        catch(\InvalidArgumentException $ex) {
            
        }

        // test trimming
        $this->object->setTitle('   a test ' . "\n\r");
        $this->assertEquals($this->object->getTitle(), 'a test');

        // test trim before checking the length
        $this->object->setTitle('a test' . str_repeat(' ', 500));
        $this->assertEquals($this->object->getTitle(), 'a test');

        // test UTF-8 length
        try {
            // ä * 200 is OK
            $this->object->setTitle(str_repeat("\xC3\xA4", 200));
        }
        catch(\InvalidArgumentException $ex) {
            $this->fail('UTF-8 encoding length (1)');
        }
        try {
            // ä * 201 is not OK
            $this->object->setTitle(str_repeat("\xC3\xA4", 201));
            $this->fail('UTF-8 encoding length (2)');
        }
        catch(\InvalidArgumentException $ex) {
            
        }
    }

    /**
     * @covers addventure\StorylineTag::getEpisodes
     * @covers addventure\StorylineTag::setEpisodes
     */
    public function testGetAndSetEpisodes() {
        try {
            $this->object->setEpisodes(null);
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }

        try {
            $this->object->setEpisodes(new \addventure\Episode());
            $this->fail();
        }
        catch(\InvalidArgumentException $ex) {
            
        }

        $this->object->setEpisodes(array());
        $this->assertEquals(0, $this->object->getEpisodes()->count());
        $this->object->setEpisodes(new \Doctrine\Common\Collections\ArrayCollection());
        $this->assertEquals(0, $this->object->getEpisodes()->count());

        // must be an ArrayCollection
        $this->object->getEpisodes()->add(new \addventure\Episode());
        $this->object->getEpisodes()->add(new \addventure\Episode());
        $this->object->getEpisodes()->add(new \addventure\Episode());
        $this->assertEquals(3, $this->object->getEpisodes()->count());

        $this->object->setEpisodes(array(new \addventure\Episode()));
        $this->assertEquals(1, $this->object->getEpisodes()->count());
        $this->object->setEpisodes(new \Doctrine\Common\Collections\ArrayCollection(array(new \addventure\Episode())));
        $this->assertEquals(1, $this->object->getEpisodes()->count());
    }

    /**
     * @covers addventure\StorylineTag::__construct
     */
    public function testConstructor() {
        $this->assertInstanceOf( '\Doctrine\Common\Collections\ArrayCollection', $this->object->getEpisodes() );
    }

}
