<?php

require_once('lib/bootstrap.php');

class FTest extends PHPUnit_Framework_TestCase {
  
  public function __construct() {
    $this->contentFile = TEST_ROOT_ETC . DS . 'content.php';
    $this->tmpFile = TEST_ROOT_TMP . DS . 'testfile.txt';
  }

  public function testExists() {
    $this->assertTrue(f::exists($this->contentFile));
  }

  public function testWrite() {
    $this->assertTrue(f::write($this->tmpFile, 'my content'));    
  }

  public function testAppend() {
    $this->assertTrue(f::append($this->tmpFile, ' is awesome'));        
  }

  public function testRead() {
    $this->assertEquals('my content is awesome', f::read($this->tmpFile));    
  }

  public function testURI() {

    $expected = 'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAIdQTFRF/z08/wQD/2pp/42N/+3t/0lI/9XV/xAP//n5/w0M/2Rj/66u/yUk/0A//+fn/8/P/5aW/1ta//b2/3l4/4SE/21s/729/9LS/8bG/0NC/0xL/+rq/4GB//Dw/39+/zEw/xwb/wcG/1JR/xMS/6io/9jY/zQz/1hX/+Hh/7S0/8zM/wEA////1oK62gAAALtJREFUeNqsk9cSgjAQRUPvICD23t3N/3+fUREZ2ABxPC+ZZM7DzRbGe2Cqgj3pFExdQ8uWCq7hoEDTTVoIQixxDLctJBHWCIOGEPvYYG7XhGzsYYsyykuwkMSpBEYL+HfhYBzf7/tiRQopX2/EbzT/xkcSgfNlkZ7FQQu706d6s4sk5Pb+vMFCEpIBQCZuU4CcFK7fxtIZLKjIVQslaZbX3W70486BiRK1kRswtAPGvn9xBqzeL9v9EGAAi9+gVs3Ccg4AAAAASUVORK5CYII=';
    $this->assertEquals($expected, f::base64(TEST_ROOT_ETC . DS . 'images' . DS . 'favicon.png'));

    $expected = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAIdQTFRF/z08/wQD/2pp/42N/+3t/0lI/9XV/xAP//n5/w0M/2Rj/66u/yUk/0A//+fn/8/P/5aW/1ta//b2/3l4/4SE/21s/729/9LS/8bG/0NC/0xL/+rq/4GB//Dw/39+/zEw/xwb/wcG/1JR/xMS/6io/9jY/zQz/1hX/+Hh/7S0/8zM/wEA////1oK62gAAALtJREFUeNqsk9cSgjAQRUPvICD23t3N/3+fUREZ2ABxPC+ZZM7DzRbGe2Cqgj3pFExdQ8uWCq7hoEDTTVoIQixxDLctJBHWCIOGEPvYYG7XhGzsYYsyykuwkMSpBEYL+HfhYBzf7/tiRQopX2/EbzT/xkcSgfNlkZ7FQQu706d6s4sk5Pb+vMFCEpIBQCZuU4CcFK7fxtIZLKjIVQslaZbX3W70486BiRK1kRswtAPGvn9xBqzeL9v9EGAAi9+gVs3Ccg4AAAAASUVORK5CYII=';
    $this->assertEquals($expected, f::uri(TEST_ROOT_ETC . DS . 'images' . DS . 'favicon.png'));

  }

  public function testMove() {
    $this->assertTrue(f::move($this->tmpFile, TEST_ROOT_TMP . DS . 'moved.txt'));
  }

  public function testCopy() {
    $this->assertTrue(f::copy(TEST_ROOT_TMP . DS . 'moved.txt', TEST_ROOT_TMP . DS . 'copied.txt'));
  }

  public function testRemove() {
    $this->assertTrue(f::remove(TEST_ROOT_TMP . DS . 'moved.txt'));
    $this->assertTrue(f::remove(TEST_ROOT_TMP . DS . 'copied.txt'));
  }

  public function testExtension() {
    $this->assertEquals('php', f::extension($this->contentFile));
    $this->assertEquals('content.txt', f::extension($this->contentFile, 'txt'));
  }

  public function testFilename() {
    $this->assertEquals('content.php', f::filename($this->contentFile));
  }

  public function testName() {
    $this->assertEquals('content', f::name($this->contentFile));
  }

  public function testDirname() {
    $this->assertEquals(dirname($this->contentFile), f::dirname($this->contentFile));
  }

  public function testSize() {
    $this->assertEquals(37, f::size($this->contentFile));
  }

  public function testNiceSize() {
    $this->assertEquals('37 B', f::niceSize($this->contentFile));
    $this->assertEquals('37 B', f::niceSize(37));
  }

  public function testModified() {
    $this->assertEquals(filemtime($this->contentFile), f::modified($this->contentFile));
  }

  public function testMime() {
    $this->assertEquals('text/plain', f::mime($this->contentFile));
  }

  public function testType() {

    $this->assertEquals('image', f::type('jpg'));
    $this->assertEquals('document', f::type('pdf'));
    $this->assertEquals('archive', f::type('zip'));
    $this->assertEquals('code', f::type('css'));
    $this->assertEquals('code', f::type('content.php'));
    $this->assertEquals('code', f::type('py'));
    $this->assertEquals('code', f::type('java'));

  }

  public function testIs() {

    $file = TEST_ROOT_ETC . DS . 'content.php';

    $this->assertTrue(f::is($file, 'php'));
    $this->assertTrue(f::is($file, 'text/plain'));

  }

  public function testSafeName() {
    $name     = 'Süper -invølid_fileßnamé!!!@2x.jpg';
    $expected = 'sueper-involid_filessname-@2x.jpg';

    $this->assertEquals($expected, f::safeName($name));
  } 

  public function testWritable() {
    $this->assertEquals(is_writable($this->contentFile), f::isWritable($this->contentFile));
  }

  public function testReadable() {
    $this->assertEquals(is_readable($this->contentFile), f::isReadable($this->contentFile));
  }

}