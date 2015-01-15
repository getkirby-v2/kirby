<?php 

class KirbyTestCase extends PHPUnit_Framework_TestCase {

  public function kirbyInstance($options = array()) {

    c::$data = array();

    $kirby = new Kirby($options);    
    $kirby->roots->content = TEST_ROOT_ETC . DS . 'content';
    return $kirby;

  }

  public function siteInstance($kirby = null, $options = array()) {

    $kirby = !is_null($kirby) ? $kirby : $this->kirbyInstance($options);
    $site  = new Site($kirby);

    return $site;

  }

}