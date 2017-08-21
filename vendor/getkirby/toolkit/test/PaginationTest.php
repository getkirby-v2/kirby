<?php

require_once('lib/bootstrap.php');

class PaginationTest extends PHPUnit_Framework_TestCase {

  protected $data;
  protected $url;
  protected $pages;
  protected $pagination;

  protected function setUp() {
    
    $this->data = new Collection(); 

    foreach(range(0,99) as $key => $item) $this->data->set($key, str::random());
    
    $this->url        = 'http://getkirby.com/';
    $this->pages      = $this->data->paginate(10, array('url' => $this->url));
    $this->pagination = $this->pages->pagination();

  }
  
  public function testMethods() {

    $this->assertInstanceOf('Pagination', $this->pagination);
    $this->assertEquals(100, $this->pagination->countItems());
    $this->assertEquals(10, $this->pagination->limit());
    $this->assertEquals(10, $this->pagination->countPages());
    $this->assertTrue($this->pagination->hasPages());
    $this->assertEquals(1, $this->pagination->page());
    $this->assertEquals(0, $this->pagination->offset());
    $this->assertEquals(1, $this->pagination->firstPage());
    $this->assertEquals(10, $this->pagination->lastPage());
    $this->assertTrue($this->pagination->isFirstPage());
    $this->assertFalse($this->pagination->isLastPage());
    $this->assertEquals(1, $this->pagination->prevPage());
    $this->assertFalse($this->pagination->hasPrevPage());
    $this->assertEquals(2, $this->pagination->nextPage());
    $this->assertTrue($this->pagination->hasNextPage());
    $this->assertEquals(1, $this->pagination->numStart());
    $this->assertEquals(10, $this->pagination->numEnd());

    $this->assertEquals($this->url, $this->pagination->firstPageURL());
    $this->assertEquals($this->url, $this->pagination->prevPageURL());
    $this->assertEquals($this->url . 'page:3', $this->pagination->pageURL(3));
    $this->assertEquals($this->url . 'page:5', $this->pagination->pageURL(5));
    $this->assertEquals($this->url . 'page:10', $this->pagination->lastPageURL());
    $this->assertEquals($this->url . 'page:2', $this->pagination->nextPageURL());

    $pagination = new Pagination($this->data, 20, array(
      'url'      => $this->url,
      'variable' => 'seite', 
      'method'   => 'query'  
    ));
    
    $this->assertEquals($this->url . '?seite=3', $pagination->pageURL(3));
    $this->assertEquals($this->url . '?seite=5', $pagination->pageURL(5));
    $this->assertEquals($this->url, $pagination->firstPageURL());
    $this->assertEquals($this->url . '?seite=5', $pagination->lastPageURL());
    $this->assertEquals($this->url, $pagination->prevPageURL());
    $this->assertEquals($this->url . '?seite=2', $pagination->nextPageURL());

    // test the new page option
    $pagination = new Pagination(200, 20, array(
      'page' => 2
    ));

    $this->assertEquals(2, $pagination->page());

  }
}
