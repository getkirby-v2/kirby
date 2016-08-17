<?php

require_once('lib/bootstrap.php');

class EmbedTest extends PHPUnit_Framework_TestCase {
  public function testEmbed() {
          
    $expected = '<iframe src="//youtube.com/embed/_9tHtxOCvy4" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" width="100%" height="100%"></iframe>';
    $this->assertEquals($expected, embed::youtube('http://www.youtube.com/watch?feature=player_embedded&v=_9tHtxOCvy4'));
    
    $expected = '<iframe src="//player.vimeo.com/video/52345557" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" width="100%" height="100%"></iframe>';
    $this->assertEquals($expected, embed::vimeo('http://vimeo.com/52345557'));
    
    $expected = '<script src="https://gist.github.com/2924148.js"></script>';
    $this->assertEquals($expected, embed::gist('https://gist.github.com/2924148'));

  }
}