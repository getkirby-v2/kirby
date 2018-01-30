<?php

require_once('lib/bootstrap.php');

class StrTest extends PHPUnit_Framework_TestCase {

  protected $sample = 'Super Äwesøme String';

  public function testHtml() {
    $this->assertEquals('Super &Auml;wes&oslash;me String', str::html($this->sample));
  }

  public function testUnhtml() {
    $this->assertEquals($this->sample, str::unhtml('Super &Auml;wes&oslash;me String'));
  }

  public function testXml() {
    $this->assertEquals('Super &#196;wes&#248;me String', str::xml($this->sample));
  }

  public function testUnxml() {
    $this->assertEquals($this->sample, str::unxml('Super &#196;wes&#248;me String'));
  }

  public function testParse() {
    $array = array(
      'test' => array(
        'cool' => 'nice'
      ),
      'super' => 'genious'
    );

    $this->assertEquals(str::parse('<xml><test><cool>nice</cool></test><super>genious</super></xml>', 'xml'), $array);

    $this->assertEquals(str::parse('{"test":{"cool":"nice"},"super":"genious"}'), $array);

    $this->assertEquals(str::parse('test[cool]=nice&super=genious', 'query'), $array);
  }

  public function testLink() {

    // without text
    $this->assertEquals('<a href="http://getkirby.com">http://getkirby.com</a>', str::link('http://getkirby.com'));

    // with text
    $this->assertEquals('<a href="http://getkirby.com">Kirby</a>', str::link('http://getkirby.com', 'Kirby'));

  }

  public function testShort() {

    // too long
    $this->assertEquals('Super…', str::short($this->sample, 5));

    // not too long
    $this->assertEquals($this->sample, str::short($this->sample, 100));

    // zero chars
    $this->assertEquals($this->sample, str::short($this->sample, 0));

    // with different ellipsis character
    $this->assertEquals('Super---', str::short($this->sample, 5, '---'));

  }

  public function testSubstr() {

    $this->assertEquals($this->sample, str::substr($this->sample, 0));

    $this->assertEquals('Super', str::substr($this->sample, 0, 5));

    $this->assertEquals(' Äwes', str::substr($this->sample, 5, 5));

    $this->assertEquals(' Äwesøme String', str::substr($this->sample, 5));

    $this->assertEquals('tring', str::substr($this->sample, -5));

  }

  public function testLower() {
    $this->assertEquals('super äwesøme string', str::lower($this->sample));
  }

  public function testUpper() {
    $this->assertEquals('SUPER ÄWESØME STRING', str::upper($this->sample));
  }

  public function testLength() {
    $this->assertEquals(20, str::length($this->sample));
  }

  public function testContains() {

    $this->assertTrue(str::contains($this->sample, 'Äwesøme'));
    $this->assertTrue(str::contains($this->sample, 'äwesøme'));

    // don't ignore upper/lowercase
    $this->assertFalse(str::contains($this->sample, 'äwesøme', false));

    // check for something which isn't there
    $this->assertFalse(str::contains($this->sample, 'Peter'));

  }

  public function testRandom() {
    // choose a high length for a high probability of occurrence of a character of any type
    $length = 200;

    $this->assertRegexp("/^[[:alnum:]]+$/", str::random());
    $this->assertInternalType('string', str::random());
    $this->assertEquals($length, strlen(str::random($length)));

    $this->assertRegexp("/^[[:alpha:]]+$/", str::random($length, 'alpha'));

    $this->assertRegexp("/^[[:upper:]]+$/", str::random($length, 'alphaUpper'));

    $this->assertRegexp("/^[[:lower:]]+$/", str::random($length, 'alphaLower'));

    $this->assertRegexp("/^[[:digit:]]+$/", str::random($length, 'num'));

    $this->assertFalse(str::random($length, 'something invalid'));
  }

  public function testQuickRandom() {
    // choose a high length for a high probability of occurrence of a character of any type
    $length = 200;

    $this->assertRegexp("/^[[:alnum:]]+$/", str::quickRandom());
    $this->assertInternalType('string', str::quickRandom());
    $this->assertEquals($length, strlen(str::quickRandom($length)));

    $this->assertRegexp("/^[[:alpha:]]+$/", str::quickRandom($length, 'alpha'));

    $this->assertRegexp("/^[[:upper:]]+$/", str::quickRandom($length, 'alphaUpper'));

    $this->assertRegexp("/^[[:lower:]]+$/", str::quickRandom($length, 'alphaLower'));

    $this->assertRegexp("/^[[:digit:]]+$/", str::quickRandom($length, 'num'));

    $this->assertFalse(str::quickRandom($length, 'something invalid'));
  }

  public function testSlug() {

    // Double dashes
    $this->assertEquals('a-b', str::slug('a--b'));

    // Dashes at the end of the line
    $this->assertEquals('a', str::slug('a-'));

    // Dashes at the beginning of the line
    $this->assertEquals('a', str::slug('-a'));

    // Underscores converted to dashes
    $this->assertEquals('a-b', str::slug('a_b'));

    // Unallowed characters
    $this->assertEquals('a-b', str::slug('a@b'));

    // Spaces characters
    $this->assertEquals('a-b', str::slug('a b'));

    // Double Spaces characters
    $this->assertEquals('a-b', str::slug('a  b'));

    // Custom separator
    $this->assertEquals('a+b', str::slug('a-b', '+'));

    // Allow underscores
    $this->assertEquals('a_b', str::slug('a_b', '-', 'a-z0-9_'));

    // store default defaults
    $defaults = str::$defaults['slug'];

    // Custom str defaults
    str::$defaults['slug']['separator'] = '+';
    str::$defaults['slug']['allowed']   = 'a-z0-9_';

    $this->assertEquals('a+b', str::slug('a-b'));
    $this->assertEquals('a_b', str::slug('a_b'));

    // Reset str defaults
    str::$defaults['slug'] = $defaults;

  }

  public function testSplit() {

    $array = array('Super', 'Äwesøme', 'String');
    $this->assertEquals($array, str::split($this->sample, ' '));

    $array = array('Äwesøme', 'String');
    $this->assertEquals($array, str::split($this->sample, ' ', 6));

    $array = array('design', 'super', 'fun', 'great', 'nice/super');
    $this->assertEquals($array, str::split('design, super,, fun, great,,, nice/super'));

  }

  public function testUcwords() {

    $string = str::lower($this->sample);
    $this->assertEquals($this->sample, str::ucwords($string));

  }

  public function testUcfirst() {

    $string = str::lower($this->sample);

    $this->assertEquals('Super äwesøme string', str::ucfirst($string));

  }

  public function testUtf8() {

    $this->assertEquals($this->sample, str::utf8($this->sample));

  }

  public function testBefore() {

    $this->assertEquals('str', str::before('string', 'i'), 'string before i should be str');
    $this->assertEquals(false, str::before('string', '.'), 'function with non-existing character should return false');

  }

  public function testUntil() {

    $this->assertEquals('stri', str::until('string', 'i'), 'string until i should be stri');
    $this->assertEquals(false, str::until('string', '.'), 'function with non-existing character should return false');

  }

  public function testAfter() {

    $this->assertEquals('ng', str::after('string', 'i'), 'string after i should be ng');
    $this->assertEquals(false, str::after('string', '.'), 'function with non-existing character should return false');

  }

  public function testFrom() {

    $this->assertEquals('ing', str::from('string', 'i'), 'string from i should be ing');
    $this->assertEquals(false, str::from('string', '.'), 'function with non-existing character should return false');

  }

  public function testBetween() {

    $this->assertEquals('trin', str::between('string', 's', 'g'), 'string between s and g should be trin');
    $this->assertEquals(false, str::between('string', 's', '.'), 'function with non-existing character should return false');
    $this->assertEquals(false, str::between('string', '.', 'g'), 'function with non-existing character should return false');

  }

  public function testReplace() {

    // simple strings with limits
    $this->assertEquals('ths s a strng',         str::replace('this is a string', 'i', ''));
    $this->assertEquals('this is a string',      str::replace('this is a string', 'i', '', 0));
    $this->assertEquals('ths is a string',       str::replace('this is a string', 'i', '', 1));
    $this->assertEquals('ths s a string',        str::replace('this is a string', 'i', '', 2));
    $this->assertEquals('ths s a strng',         str::replace('this is a string', 'i', '', 3));
    $this->assertEquals('ths s a strng',         str::replace('this is a string', 'i', '', 1000));
    $this->assertEquals('th!s !s a string',      str::replace('this is a string', 'i', '!', 2));
    $this->assertEquals('th?!s ?!s a string',    str::replace('this is a string', 'i', '?!', 2));
    $this->assertEquals('that also is a string', str::replace('this is a string', 'this', 'that also', 1));
    $this->assertEquals('this is aeä string',    str::replace('this is ää string', 'ä', 'ae', 1));
    $this->assertEquals('this is aeae string',   str::replace('this is ää string', 'ä', 'ae', 2));
    $this->assertEquals('this is äa string',     str::replace('this is aa string', 'a', 'ä', 1));
    $this->assertEquals('this is ää string',     str::replace('this is aa string', 'a', 'ä', 2));

    // $subject as array
    $this->assertEquals(['ths', 's', 'a', 'strng'],     str::replace(['this', 'is', 'a', 'string'], 'i', ''));
    $this->assertEquals(['this', 'is', 'a', 'string'],  str::replace(['this', 'is', 'a', 'string'], 'i', '', 0));
    $this->assertEquals(['ths', 's', 'a', 'strng'],     str::replace(['this', 'is', 'a', 'string'], 'i', '', 1));
    $this->assertEquals(['ths', 's', 'a', 'strng'],     str::replace(['this', 'is', 'a', 'striing'], 'i', ''));
    $this->assertEquals(['this', 'is', 'a', 'striing'], str::replace(['this', 'is', 'a', 'striing'], 'i', '', 0));
    $this->assertEquals(['ths', 's', 'a', 'string'],    str::replace(['this', 'is', 'a', 'striing'], 'i', '', 1));
    $this->assertEquals(['ths', 's', 'a', 'strng'],     str::replace(['this', 'is', 'a', 'striing'], 'i', '', 2));

    // $subject as Collection
    $subjects = new Collection(['this', 'is', 'a', 'striing']);
    $this->assertEquals(['ths', 's', 'a', 'strng'],  str::replace($subjects, 'i', ''));
    $this->assertEquals(['ths', 's', 'a', 'string'], str::replace($subjects, 'i', '', 1));

    // $search as array/Collection
    $this->assertEquals('th!! !! a string', str::replace('this is a string', ['i', 's'], '!', 2));
    $this->assertEquals('th!! !! a string', str::replace('this is a string', new Collection(['i', 's']), '!', 2));
    $this->assertEquals('th!! i! a string', str::replace('this is a string', ['i', 's'], '!', [1, 2]));
    $this->assertEquals('th!! i! a !tring', str::replace('this is a string', ['i', 's'], '!', [1]));

    // $search and $replace as array/Collection
    $this->assertEquals('th!? !? a string', str::replace('this is a string', ['i', 's'], ['!', '?'], 2));
    $this->assertEquals('th! ! a string',   str::replace('this is a string', ['i', 's'], ['!'], 2));
    $this->assertEquals('th!? !? a string', str::replace('this is a string', new Collection(['i', 's']), new Collection(['!', '?']), 2));
    $this->assertEquals('th!? !? a string', str::replace('this is a string', new Collection(['i', 's']), ['!', '?'], 2));
    $this->assertEquals('th!? !? a string', str::replace('this is a string', ['i', 's'], new Collection(['!', '?']), 2));
    $this->assertEquals('th!? !s a string', str::replace('this is a string', ['i', 's'], ['!', '?'], [2, 1]));
    $this->assertEquals('th!s !s a string', str::replace('this is a string', ['i', 's'], ['!', '?'], [2, 0]));
    $this->assertEquals('th!? !? a ?tring', str::replace('this is a string', ['i', 's'], ['!', '?'], [2]));
    $this->assertEquals('th! ! a tring',    str::replace('this is a string', ['i', 's'], ['!'], [2]));
    $this->assertEquals('th! !s a string',  str::replace('this is a string', ['i', 's'], ['!'], [2, 1]));

    // replacement order
    $this->assertEquals('F',                str::replace('A', ['A', 'B', 'C', 'D', 'E'], ['B', 'C', 'D', 'E', 'F'], 1));
    $this->assertEquals('apearple p',       str::replace('a p', ['a', 'p'], ['apple', 'pear'], 1));
    $this->assertEquals('apearpearle p',    str::replace('a p', ['a', 'p'], ['apple', 'pear'], [1, 2]));
    $this->assertEquals('apearpearle pear', str::replace('a p', ['a', 'p'], ['apple', 'pear'], [1, 3]));

  }

  /**
   * @expectedException Error
   */
  public function testReplaceInvalid1() {
    str::replace('some string', 'string', ['array'], 1);
  }

  /**
   * @expectedException Error
   */
  public function testReplaceInvalid2() {
    str::replace('some string', 'string', 'other string', 'some invalid string as limit');
  }

  /**
   * @expectedException Error
   */
  public function testReplaceInvalid3() {
    str::replace('some string', ['some', 'string'], 'other string', [1, 'string']);
  }

  public function testMakeReplacements() {

    // simple example
    $this->assertEquals([
      ['search' => 'a', 'replace' => 'b', 'limit' => 2]
    ], str::makeReplacements('a', 'b', 2));

    // multiple searches
    $this->assertEquals([
      ['search' => 'a', 'replace' => 'c', 'limit' => 2],
      ['search' => 'b', 'replace' => 'c', 'limit' => 2]
    ], str::makeReplacements(['a', 'b'], 'c', 2));

    // multiple replacements
    $this->assertEquals([
      ['search' => 'a', 'replace' => 'c', 'limit' => 2],
      ['search' => 'b', 'replace' => 'd', 'limit' => 2]
    ], str::makeReplacements(['a', 'b'], ['c', 'd'], 2));
    $this->assertEquals([
      ['search' => 'a', 'replace' => 'c', 'limit' => 2],
      ['search' => 'b', 'replace' => '', 'limit' => 2]
    ], str::makeReplacements(['a', 'b'], ['c'], 2));

    // multiple limits
    $this->assertEquals([
      ['search' => 'a', 'replace' => 'c', 'limit' => 2],
      ['search' => 'b', 'replace' => 'c', 'limit' => 1]
    ], str::makeReplacements(['a', 'b'], 'c', [2, 1]));
    $this->assertEquals([
      ['search' => 'a', 'replace' => 'c', 'limit' => 2],
      ['search' => 'b', 'replace' => 'd', 'limit' => 1]
    ], str::makeReplacements(['a', 'b'], ['c', 'd'], [2, 1]));
    $this->assertEquals([
      ['search' => 'a', 'replace' => 'c', 'limit' => 2],
      ['search' => 'b', 'replace' => 'd', 'limit' => -1]
    ], str::makeReplacements(['a', 'b'], ['c', 'd'], [2]));

  }

  /**
   * @expectedException Error
   */
  public function testMakeReplacementsInvalid() {
    str::makeReplacements('string', ['array'], 1);
  }

  public function testReplaceReplacements() {

    $this->assertEquals('other other string',
      str::replaceReplacements('some some string', [
        [
          'search'  => 'some',
          'replace' => 'other',
          'limit'   => -1
        ]
      ])
    );

    $this->assertEquals('other interesting string',
      str::replaceReplacements('some some string', [
        [
          'search'  => 'some',
          'replace' => 'other',
          'limit'   => -1
        ],
        [
          'search'  => 'other string',
          'replace' => 'interesting string',
          'limit'   => 1
        ]
      ])
    );

    // edge cases are tested in the Str::replace() unit test

  }

  /**
   * @expectedException Error
   */
  public function testReplaceReplacementsInvalid() {
    str::replaceReplacements('some string', [
      [
        'search'  => 'some',
        'replace' => 'other',
        'limit'   => 'string'
      ]
    ]);
  }

}
