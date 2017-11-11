<?php

use PHPUnit\Framework\TestCase;

class FileMock
{
    protected $return = true;

    public function __construct($return)
    {
        $this->return = $return;
    }

    public $file = [
        '_id' => 'some_id',
    ];

    public function write()
    {
        return $this->return;
    }
}

class FilesystemTests extends TestCase
{
    public function testStore()
    {
        $id = new MongoId('516ba5033b21c50005a93f76');

        $mock = $this->getMockBuilder('MongoGridFS')
            ->disableOriginalConstructor()
            ->setMethods(['put'])
            ->getMock();

        $mock->expects($this->once())
            ->method('put')
            ->with('filename.json', ['downloads' => 0])
            ->will($this->returnValue($id));

        $fs = new League\Monga\Filesystem($mock);

        $result = $fs->store('filename.json', ['downloads' => 0]);
        $this->assertEquals($id, $result);
    }

    public function testStoreBytes()
    {
        $id = new MongoId('516ba5033b21c50005a93f76');

        $mock = $this->getMockBuilder('MongoGridFS')
            ->disableOriginalConstructor()
            ->setMethods(['storeBytes'])
            ->getMock();

        $mock->expects($this->once())
            ->method('storeBytes')
            ->with('filename.json', ['downloads' => 0], [])
            ->will($this->returnValue($id));

        $fs = new League\Monga\Filesystem($mock);

        $result = $fs->storeBytes('filename.json', ['downloads' => 0]);
        $this->assertEquals($id, $result);
    }

    public function testStoreUpload()
    {
        $id = new MongoId('516ba5033b21c50005a93f76');

        $mock = $this->getMockBuilder('MongoGridFS')
            ->disableOriginalConstructor()
            ->setMethods(['storeUpload'])
            ->getMock();

        $mock->expects($this->once())
            ->method('storeUpload')
            ->with('filename.json', ['downloads' => 0])
            ->will($this->returnValue($id));

        $fs = new League\Monga\Filesystem($mock);

        $result = $fs->storeUpload('filename.json', ['downloads' => 0]);
        $this->assertEquals($id, $result);
    }

    public function testExtractFailing()
    {
        $mock = $this->getMockBuilder('MongoGridFS')
            ->disableOriginalConstructor()
            ->setMethods(['extract', 'findOne', 'remove'])
            ->getMock();

        $fileMock = $this->getMockBuilder('MongoGridFile')
            ->disableOriginalConstructor()
            ->setMethods(['write'])
            ->getMock();

        $mock->expects($this->once())
            ->method('findOne')
            ->with('filename.json')
            ->will($this->returnValue(new FileMock(false)));

        $fs = new League\Monga\Filesystem($mock);

        $this->assertFalse($fs->extract('filename.json'));
    }

    public function testExtract()
    {
        $mock = $this->getMockBuilder('MongoGridFS')
            ->disableOriginalConstructor()
            ->setMethods(['extract', 'findOne', 'remove'])
            ->getMock();

        $fileMock = $this->getMockBuilder('MongoGridFile')
            ->disableOriginalConstructor()
            ->setMethods(['write'])
            ->getMock();

        $mock->expects($this->once())
            ->method('findOne')
            ->with('filename.json')
            ->will($this->returnValue(new FileMock(true)));

        $mock->expects($this->once())
            ->method('remove')
            ->with(['_id' => 'some_id'])
            ->will($this->returnValue(true));

        $fs = new League\Monga\Filesystem($mock);

        $this->assertTrue($fs->extract('filename.json'));
    }

    public function testStoreFile()
    {
        $id = new MongoId('516ba5033b21c50005a93f76');

        $mock = $this->getMockBuilder('MongoGridFS')
            ->disableOriginalConstructor()
            ->setMethods(['storeFile'])
            ->getMock();

        $mock->expects($this->once())
            ->method('storeFile')
            ->with('filename.json', ['downloads' => 0], [])
            ->will($this->returnValue($id));

        $fs = new League\Monga\Filesystem($mock);

        $result = $fs->storeFile('filename.json', ['downloads' => 0]);
        $this->assertEquals($id, $result);
    }

    public function testFindOne()
    {
        $mock = $this->getMockBuilder('MongoGridFS')
            ->disableOriginalConstructor()
            ->setMethods(['findOne'])
            ->getMock();

        $mock->expects($this->once())
            ->method('findOne')
            ->with(['key' => 'value'])
            ->will($this->returnValue('_dummy_'));

        $fs = new League\Monga\Filesystem($mock);

        $result = $fs->findOne(['key' => 'value']);
        $this->assertEquals('_dummy_', $result);
    }
}
