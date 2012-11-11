<?php

class FileMock
{
	protected $return = true;

	public function __construct($return)
	{
		$this->return = $return;
	}

	public $file = array(
		'_id' => 'some_id',
	);

	public function write()
	{
		return $this->return;
	}
}

class FilesystemTests extends PHPUnit_Framework_TestCase
{
	public function testStore()
	{
		$id = new MongoId('09ecu0q9fh2h3');

		$mock = $this->getMockBuilder('MongoGridFS')
			->disableOriginalConstructor()
			->setMethods(array('put'))
			->getMock();

		$mock->expects($this->once())
			->method('put')
			->with('filename.json', array('downloads' => 0))
			->will($this->returnValue($id));

		$fs = new Monga\Filesystem($mock);

		$result = $fs->store('filename.json', array('downloads' => 0));
		$this->assertEquals($id, $result);
	}

	public function testStoreBytes()
	{
		$id = new MongoId('09ecu0q9fh2h3');

		$mock = $this->getMockBuilder('MongoGridFS')
			->disableOriginalConstructor()
			->setMethods(array('storeBytes'))
			->getMock();

		$mock->expects($this->once())
			->method('storeBytes')
			->with('filename.json', array('downloads' => 0), array())
			->will($this->returnValue($id));

		$fs = new Monga\Filesystem($mock);

		$result = $fs->storeBytes('filename.json', array('downloads' => 0));
		$this->assertEquals($id, $result);
	}

	public function testStoreUpload()
	{
		$id = new MongoId('09ecu0q9fh2h3');

		$mock = $this->getMockBuilder('MongoGridFS')
			->disableOriginalConstructor()
			->setMethods(array('storeUpload'))
			->getMock();

		$mock->expects($this->once())
			->method('storeUpload')
			->with('filename.json', array('downloads' => 0))
			->will($this->returnValue($id));

		$fs = new Monga\Filesystem($mock);

		$result = $fs->storeUpload('filename.json', array('downloads' => 0));
		$this->assertEquals($id, $result);
	}

	public function testExtractFailing()
	{
		$mock = $this->getMockBuilder('MongoGridFS')
			->disableOriginalConstructor()
			->setMethods(array('extract', 'findOne', 'remove'))
			->getMock();

		$fileMock = $this->getMockBuilder('MongoGridFile')
			->disableOriginalConstructor()
			->setMethods(array('write'))
			->getMock();

		$mock->expects($this->once())
			->method('findOne')
			->with('filename.json')
			->will($this->returnValue(new FileMock(false)));


		$fs = new Monga\Filesystem($mock);

		$this->assertFalse($fs->extract('filename.json'));
	}

	public function testExtract()
	{
		$mock = $this->getMockBuilder('MongoGridFS')
			->disableOriginalConstructor()
			->setMethods(array('extract', 'findOne', 'remove'))
			->getMock();

		$fileMock = $this->getMockBuilder('MongoGridFile')
			->disableOriginalConstructor()
			->setMethods(array('write'))
			->getMock();

		$mock->expects($this->once())
			->method('findOne')
			->with('filename.json')
			->will($this->returnValue(new FileMock(true)));

		$mock->expects($this->once())
			->method('remove')
			->with(array('_id' => 'some_id'))
			->will($this->returnValue(true));

		$fs = new Monga\Filesystem($mock);

		$this->assertTrue($fs->extract('filename.json'));
	}

	public function testStoreFile()
	{
		$id = new MongoId('09ecu0q9fh2h3');

		$mock = $this->getMockBuilder('MongoGridFS')
			->disableOriginalConstructor()
			->setMethods(array('storeFile'))
			->getMock();

		$mock->expects($this->once())
			->method('storeFile')
			->with('filename.json', array('downloads' => 0), array())
			->will($this->returnValue($id));

		$fs = new Monga\Filesystem($mock);

		$result = $fs->storeFile('filename.json', array('downloads' => 0));
		$this->assertEquals($id, $result);
	}

	public function testFindOne()
	{
		$mock = $this->getMockBuilder('MongoGridFS')
			->disableOriginalConstructor()
			->setMethods(array('findOne'))
			->getMock();

		$mock->expects($this->once())
			->method('findOne')
			->with(array('key' => 'value'))
			->will($this->returnValue('_dummy_'));

		$fs = new Monga\Filesystem($mock);

		$result = $fs->findOne(array('key' => 'value'));
		$this->assertEquals('_dummy_', $result);
	}
}