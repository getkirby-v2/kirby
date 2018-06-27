<?php

require_once('lib/bootstrap.php');

class FieldTest extends KirbyTestCase {

  public function testField() {

    $field = new Field('mypage', 'mykey', 'myvalue');

    $this->assertEquals('mypage', $field->page);
    $this->assertEquals('mypage', $field->page());

    $this->assertEquals('mykey', $field->key);
    $this->assertEquals('mykey', $field->key());

    $this->assertEquals('myvalue', $field->value);
    $this->assertEquals('myvalue', $field->value());
    $this->assertEquals('myvalue', $field->toString());
    $this->assertEquals('myvalue', (string)$field);

  }

  public function testModification() {

    $field = new Field('page-a', 'key-a', 'value-a');

    $field->page  = 'page-b';
    $field->key   = 'key-b';
    $field->value = 'value-b';

    $this->assertEquals('page-b', $field->page);
    $this->assertEquals('page-b', $field->page());

    $this->assertEquals('key-b', $field->key);
    $this->assertEquals('key-b', $field->key());

    $this->assertEquals('value-b', $field->value);
    $this->assertEquals('value-b', $field->value());
    $this->assertEquals('value-b', $field->toString());
    $this->assertEquals('value-b', (string)$field);

  }

}
