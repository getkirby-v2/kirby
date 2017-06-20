<?php

require_once('lib/bootstrap.php');

/**
 * Test the "header" class.
 * All tests are performed by passing $send=false
 */
class HeaderTest extends PHPUnit_Framework_TestCase {

  protected $statusHeaders;

  protected function setUp() {
    // incomplete list compared to header::$codes, mostly for
    // testing header::success and other named methods
    $this->statusHeaders = [
      200 => 'HTTP/1.1 200 OK',
      201 => 'HTTP/1.1 201 Created',
      202 => 'HTTP/1.1 202 Accepted',
      301 => 'HTTP/1.1 301 Moved Permanently',
      302 => 'HTTP/1.1 302 Found',
      400 => 'HTTP/1.1 400 Bad Request',
      403 => 'HTTP/1.1 403 Forbidden',
      404 => 'HTTP/1.1 404 Not Found',
      410 => 'HTTP/1.1 410 Gone',
      451 => 'HTTP/1.1 451 Unavailable For Legal Reasons',
      500 => 'HTTP/1.1 500 Internal Server Error',
      503 => 'HTTP/1.1 503 Service Unavailable'
    ];
  }

  public function testNamedStatuses() {
    $h = $this->statusHeaders;
    $this->assertEquals($h[200], header::success(false), 'success status should be 200');
    $this->assertEquals($h[201], header::created(false), 'created status should be 201');
    $this->assertEquals($h[202], header::accepted(false), 'accepted status should be 202');
    $this->assertEquals($h[400], header::error(false), 'error status should be 400');
    $this->assertEquals($h[403], header::forbidden(false), 'forbidden status should be 403');
    $this->assertEquals($h[404], header::notfound(false), 'notfound status should be 404');
    $this->assertEquals($h[404], header::missing(false), 'missing status should be 404');
    $this->assertEquals($h[410], header::gone(false), 'gone status should be 410');
    $this->assertEquals($h[500], header::panic(false), 'panic status should be 500');
    $this->assertEquals($h[503], header::unavailable(false), 'unavailable status should be 503');
  }

  public function testStatus_CodeOnly() {
    $h = $this->statusHeaders;

    // code only
    $this->assertEquals(
      $h[200], header::status(200, false),
      'Accepts integer status code'
    );
    $this->assertEquals(
      $h[200], header::status('200', false),
      'Accepts string status code'
    );
    $this->assertEquals(
      $h[451], header::status('451', false),
      'Can send HTTP 451 code (RFC 7725)'
    );
    $this->assertEquals(
      $h[500], header::status(null, false),
      'Null code results in 500'
    );
    $this->assertEquals(
      header::status(500, false), header::status(999, false),
      'Unknown code results in 500'
    );

    // with reason in code parameter
    $this->assertEquals(
      $h[200], header::status('200 OK', false),
      'Can send "200 OK"'
    );
    $this->assertEquals(
      'HTTP/1.1 999 Custom Header', header::status('999 Custom Header', false),
      'Can send a well-formed custom status code and reason'
    );
    $this->assertEquals(
      $h[500], header::status("999 This is\nNOT OKAY", false),
      'Newlines inside of reason results in a 500 code'
    );
  }

  public function testRedirect() {
    $h = $this->statusHeaders;
    $this->assertEquals(
      $h[301] . "\r\nLocation:/x", header::redirect('/x', 301, false),
      'Can send a 301 redirect'
    );
    $this->assertEquals(
      $h[302] . "\r\nLocation:/x", header::redirect('/x', 302, false),
      'Can send a 302 redirect'
    );
  }

  public function testContentType() {
    $this->assertEquals(
      'Content-type: video/webm',
      header::contentType('webm', '', false),
      'Can send Content-type header with no encoding'
    );
    $this->assertEquals(
      'Content-type: application/json; charset=ISO-8859-1',
      header::contentType('json', 'ISO-8859-1', false),
      'Can send Content-type header with custom charset'
    );
  }

}
