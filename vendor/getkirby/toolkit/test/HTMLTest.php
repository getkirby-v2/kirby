<?php

require_once('lib/bootstrap.php');

class HTMLTest extends PHPUnit_Framework_TestCase {
  public function testHTML() {
    $expected = '<img src="myimage.jpg" width="100" height="200">';
    $this->assertEquals($expected, html::tag('img', null, array('src' => 'myimage.jpg', 'width' => 100, 'height' => 200)));
    
    $expected = '<a href="http://google.com" title="Google">Google</a>';
    $this->assertEquals($expected, html::tag('a', 'Google', array('href' => 'http://google.com', 'title' => 'Google')));
    
    $expected = '<p>Nice Paragraph</p>';
    $this->assertEquals($expected, html::tag('p', 'Nice Paragraph'));
    
    $expected = '<br>';
    $this->assertEquals($expected, html::tag('br'));
    
    $expected = '<a href="http://google.com" title="Google">Google</a>';
    $this->assertEquals($expected, html::a('http://google.com', 'Google', array('title' => 'Google')));
    
    $expected = '<img src="myimage.jpg" alt="myimage" width="100" height="200">';
    $this->assertEquals($expected, html::img('myimage.jpg', array('width' => 100, 'height' => 200)));
                                            
    $expected  = '<!--[if lt IE 9]>' . PHP_EOL;
    $expected .= '<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>' . PHP_EOL;
    $expected .= '<![endif]-->' . PHP_EOL;
    
    $this->assertEquals($expected, html::shiv());
        
  }
}