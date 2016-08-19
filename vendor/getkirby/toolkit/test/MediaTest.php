<?php

require_once('lib/bootstrap.php');

class MediaTest extends PHPUnit_Framework_TestCase {

  public function __construct() {

    $this->file  = TEST_ROOT_ETC . DS . 'images' . DS . 'favicon.png';
    $this->url   = 'http://superdomain.com/favicon.png';
    $this->media = new Media($this->file, $this->url);
  }

  public function testURL() {
    $this->assertEquals($this->url, $this->media->url());
  }

  public function testRoot() {
    $this->assertEquals($this->file, $this->media->root());
  }
 
  public function testFilename() {
    $this->assertEquals('favicon.png', $this->media->filename());    
  }

  public function testDir() {
    $this->assertEquals(dirname($this->file), $this->media->dir());    
  }

  public function testName() {
    $this->assertEquals('favicon', $this->media->name());    
  }

  public function testExtension() {
    $this->assertEquals('png', $this->media->extension());    
  }

  public function testType() {
    $this->assertEquals('image', $this->media->type());    
  }

  public function testIs() {
    $this->assertTrue($this->media->is('png'));    
    $this->assertTrue($this->media->is('image/png'));    
    $this->assertFalse($this->media->is('jpg'));    
  }

  public function testModified() {
    $this->assertEquals(filemtime($this->file), $this->media->modified());        
  }

  public function testExists() {
    $this->assertTrue($this->media->exists());        
  }

  public function testIsReadable() {
    $this->assertEquals(is_readable($this->file), $this->media->isReadable());        
  }

  public function testIsWritable() {
    $this->assertEquals(is_writable($this->file), $this->media->isWritable());        
  }

  public function testSize() {
    $this->assertEquals(428, $this->media->size());        
  }

  public function testNiceSize() {
    $this->assertEquals('428 B', $this->media->niceSize());        
  }

  public function testMime() {
    $this->assertEquals('image/png', $this->media->mime());        
  }

  public function testExif() {
    $this->assertInstanceOf('Exif', $this->media->exif());        
  }

  public function testImagesize() {
    $this->assertEquals(getimagesize($this->file), $this->media->imagesize());        
  }

  public function testDimensions() {
    $this->assertInstanceOf('Dimensions', $this->media->dimensions());        
  }

  public function testWidth() {
    $this->assertEquals(32, $this->media->width());        
  }

  public function testHeight() {
    $this->assertEquals(32, $this->media->height());        
  }

  public function testRatio() {
    $this->assertEquals(1, $this->media->ratio());        
  }

  public function testHeader() {
    $this->assertEquals('Content-type: image/png', $this->media->header($send = false));        
  }

  public function testToString() {
    $this->assertEquals($this->media->root(), (string)$this->media);
  }

}