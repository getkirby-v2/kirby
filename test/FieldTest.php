<?php

require_once('lib/bootstrap.php');

class FieldTest extends KirbyTestCase {

  public function testField() {

    $field = new Field('mypage', 'mykey', 'myvalue');

    $field->assertEquals('mypage', $field->page);
    $field->assertEquals('mypage', $field->page());

    $field->assertEquals('mykey', $field->key);
    $field->assertEquals('mykey', $field->key());

    $field->assertEquals('myvalue', $field->value);
    $field->assertEquals('myvalue', $field->value());
    $field->assertEquals('myvalue', $field->toString());
    $field->assertEquals('myvalue', (string)$field);

  }

  public function testModification() {

    $field = new Field('page-a', 'key-a', 'value-a');

    $field->page  = 'page-b';
    $field->key   = 'key-b';
    $field->value = 'value-b';

    $field->assertEquals('page-b', $field->page);
    $field->assertEquals('page-b', $field->page());

    $field->assertEquals('key-b', $field->key);
    $field->assertEquals('key-b', $field->key());

    $field->assertEquals('value-b', $field->value);
    $field->assertEquals('value-b', $field->value());
    $field->assertEquals('value-b', $field->toString());
    $field->assertEquals('value-b', (string)$field);

  }

}