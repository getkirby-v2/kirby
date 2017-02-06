<?php

require_once('lib/bootstrap.php');

class EmbedTest extends PHPUnit_Framework_TestCase {

  public function testYoutube() {

    // default url variants
    $urls = [
      'http://www.youtube.com/embed/d9NF2edxy-M',
      'http://www.youtube.com/watch?feature=player_embedded&v=d9NF2edxy-M#!',
      'http://youtu.be/d9NF2edxy-M',      
    ];

    foreach($urls as $url) {
      $expected = '<iframe src="//youtube.com/embed/d9NF2edxy-M" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" width="100%" height="100%"></iframe>';
      $this->assertEquals($expected, embed::youtube($url));
    }

    // nocookie domains
    $expected = '<iframe src="//www.youtube-nocookie.com/embed/d9NF2edxy-M" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" width="100%" height="100%"></iframe>';
    $this->assertEquals($expected, embed::youtube('http://www.youtube-nocookie.com/embed/d9NF2edxy-M'));

  }

  public function testVimeo() {
            
    $expected = '<iframe src="//player.vimeo.com/video/52345557" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" width="100%" height="100%"></iframe>';
    $this->assertEquals($expected, embed::vimeo('http://vimeo.com/52345557'));
  
  }

  public function testGist() {

    $expected = '<script src="https://gist.github.com/2924148.js"></script>';
    $this->assertEquals($expected, embed::gist('https://gist.github.com/2924148'));

  }

}