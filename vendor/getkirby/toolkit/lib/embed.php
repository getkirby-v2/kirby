<?php

/**
 * Embed
 *
 * Simple embedding of stuff like
 * flash, youtube videos, vimeo videos or gists
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Embed {

  /**
   * Embeds a youtube video by passing the Youtube url
   *
   * @param string $url Youtube url i.e. http://www.youtube.com/watch?v=d9NF2edxy-M
   * @param array $attr Additional attributes for the iframe
   * @return string
   */
  public static function youtube($url, $attr = array()) {

    // youtube embed domain
    $domain = 'youtube.com';

    // http://www.youtube.com/embed/d9NF2edxy-M
    if(preg_match('!youtube.com\/embed\/([a-z0-9_-]+)!i', $url, $array)) {
      $id = $array[1];
    // https://www.youtube-nocookie.com/embed/d9NF2edxy-M
    } else if(preg_match('!youtube-nocookie.com\/embed\/([a-z0-9_-]+)!i', $url, $array)) {
      $id     = $array[1];
      $domain = 'www.youtube-nocookie.com';
    // http://www.youtube.com/watch?feature=player_embedded&v=d9NF2edxy-M#!
    } elseif(preg_match('!v=([a-z0-9_-]+)!i', $url, $array)) {
      $id = $array[1];
    // http://youtu.be/d9NF2edxy-M
    } elseif(preg_match('!youtu.be\/([a-z0-9_-]+)!i', $url, $array)) {
      $id = $array[1];
    }

    // no id no result!
    if(empty($id)) return false;

    // default options
    if(!empty($attr['options'])) {
      $options = '?' . http_build_query($attr['options']);      
      // options should not propagate to the attr list
      unset($attr['options']);
    } else {
      $options = '';
    }

    // default attributes
    $attr = array_merge(array(
      'src'                   => '//' . $domain . '/embed/' . $id . $options,
      'frameborder'           => '0',
      'webkitAllowFullScreen' => 'true',
      'mozAllowFullScreen'    => 'true',
      'allowFullScreen'       => 'true',
      'width'                 => '100%',
      'height'                => '100%',
    ), $attr);

    return html::tag('iframe', '', $attr);

  }

  /**
   * Embeds a vimeo video by passing the vimeo url
   *
   * @param string $url vimeo url i.e. http://vimeo.com/52345557
   * @param array $attr Additional attributes for the iframe
   * @return string
   */
  public static function vimeo($url, $attr = array()) {

    // get the uid from the url
    if(preg_match('!vimeo.com\/([0-9]+)!i', $url, $array)) {
      $id = $array[1];
    } else {
      $id = null;
    }

    // no id no result!
    if(empty($id)) return false;

    // default options
    if(!empty($attr['options'])) {
      $options = '?' . http_build_query($attr['options']);      
      // options should not propagate to the attr list
      unset($attr['options']);
    } else {
      $options = '';
    }

    // default attributes
    $attr = array_merge(array(
      'src'                   => '//player.vimeo.com/video/' . $id . $options,
      'frameborder'           => '0',
      'webkitAllowFullScreen' => 'true',
      'mozAllowFullScreen'    => 'true',
      'allowFullScreen'       => 'true',
      'width'                 => '100%',
      'height'                => '100%',
    ), $attr);

    return html::tag('iframe', '', $attr);

  }

  /**
   * Embeds a github gist
   *
   * @param string $url Gist url: i.e. https://gist.github.com/2924148
   * @param string $file The name of a particular file from the gist, which should displayed only.
   * @return string
   */
  public static function gist($url, $file = null) {

    // url for the script file
    $url = $url . '.js' . r(!is_null($file), '?file=' . $file);

    // load the gist
    return html::tag('script', '', array('src' => $url));

  }

}