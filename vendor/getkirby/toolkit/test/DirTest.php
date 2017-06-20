<?php

require_once('lib/bootstrap.php');

class DirTest extends PHPUnit_Framework_TestCase {

  protected $tmpDir;
  protected $movedDir;
  
  protected function setUp() {
    $this->tmpDir = TEST_ROOT_TMP . DS . 'test';
    $this->movedDir = TEST_ROOT_TMP . DS . 'moved';
  }

  public function testMake() {
    $this->assertTrue(dir::make($this->tmpDir));
  }  

  public function testRead() {
    $files = dir::read(TEST_ROOT_ETC);
    $this->assertEquals(4, count($files));
  } 

  public function testMove() {
    $this->assertTrue(dir::move($this->tmpDir, $this->movedDir));
  }

  public function testRemove() {
    $this->assertTrue(dir::remove($this->movedDir));
  }

  public function testClean() {

    dir::make($this->tmpDir);
    f::write($this->tmpDir . DS . 'testfile.txt', '');

    $this->assertTrue(dir::clean($this->tmpDir));

    $files = dir::read($this->tmpDir);
    $this->assertEquals(0, count($files));

    dir::remove($this->tmpDir);

  }

  public function testSize() {

    dir::make($this->tmpDir);
    
    f::write($this->tmpDir . DS . 'testfile-1.txt', str::random(5));
    f::write($this->tmpDir . DS . 'testfile-2.txt', str::random(5));
    f::write($this->tmpDir . DS . 'testfile-3.txt', str::random(5));

    $this->assertEquals(15, dir::size($this->tmpDir));
    $this->assertEquals('15 B', dir::niceSize($this->tmpDir));

    dir::remove($this->tmpDir);

  }

  public function testModified() {
    $this->assertTrue(is_int(dir::modified(TEST_ROOT)));
  }

  public function testWritable() {
    $this->assertEquals(is_writable(TEST_ROOT), dir::writable(TEST_ROOT));
  }

  public function testReadable() {
    $this->assertEquals(is_readable(TEST_ROOT), dir::readable(TEST_ROOT));    
  }

}
