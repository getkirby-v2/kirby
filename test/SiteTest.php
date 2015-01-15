<?php

require_once('lib/bootstrap.php');

class SiteTest extends KirbyTestCase {

  public function testConstruction() {

    $kirby = $this->kirbyInstance();    
    $site  = $this->siteInstance($kirby);

    $this->assertInstanceOf('Kirby', $site->kirby());
    $this->assertEquals($kirby, $site->kirby());
    $this->assertEquals(null, $site->page);
    $this->assertEquals($kirby->urls()->index(), $site->url());
    $this->assertEquals(0, $site->depth());
    $this->assertEquals('', $site->uri());
    $this->assertEquals($site, $site->site());
    $this->assertEquals($kirby->roots()->content(), $site->root());
    $this->assertEquals(basename($kirby->roots()->content()), $site->dirname());
    $this->assertEquals('', $site->diruri());
    $this->assertTrue($site->isSite());
    $this->assertEquals('site', $site->template());
    $this->assertEquals('site', $site->intendedTemplate());
    $this->assertFalse($site->templateFile());
    $this->assertFalse($site->intendedTemplateFile());
    $this->assertFalse($site->hasTemplate());
    $this->assertEquals($kirby->option('locale'), $site->locale());
    $this->assertFalse($site->multilang());
    $this->assertEquals(null, $site->languages());
    $this->assertEquals(null, $site->language());
    $this->assertEquals(null, $site->defaultLanguage());
    $this->assertEquals(null, $site->detectedLanguage());

  }

}