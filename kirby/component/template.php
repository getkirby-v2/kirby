<?php

namespace Kirby\Component;

use Dir;
use Exception;
use F;
use Page;
use Tpl;

/**
 * Kirby Template Builder Component
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Template extends \Kirby\Component {

  /**
   * Collects all template data by page
   * 
   * @param mixed $page
   * @param array $data
   * @return array
   */
  public function data($page, $data = []) {

    if($page instanceof Page) {
      $data = array_merge(
        $page->templateData(), 
        $data, 
        $page->controller($data)
      );
    }

    // apply the basic template vars
    return array_merge(array(
      'kirby' => $this->kirby,
      'site'  => $this->kirby->site(),
      'pages' => $this->kirby->site()->children(),
      'page'  => $page
    ), $data);

  }

  /**
   * Returns all available template files
   *
   * @return array
   */
  public function files() {
    $files = dir::read($this->kirby->roots()->templates());
    $files = array_filter($files, function($file) {
      return f::extension($file) === 'php';
    });
    return array_map(function($file) {
      return f::name($file);
    }, $files);
  }

  /**
   * Returns a template file path by name
   *
   * @param string $name
   * @return string
   */
  public function file($name) {
    return $this->kirby->roots()->templates() . DS . str_replace('/', DS, $name) . '.php';
  }

  /**
   * Renders the template by page with the additional data
   * 
   * @param Page|string $template
   * @param array $data
   * @param boolean $return
   * @return string
   */
  public function render($template, $data = [], $return = true) {

    if($template instanceof Page) {
      $page = $template;
      $file = $page->templateFile();
      $data = $this->data($page, $data);
    } else {
      $file = $template;
      $data = $this->data(null, $data);
    }

    // check for an existing template
    if(!file_exists($file)) {
      throw new Exception('The template could not be found');
    }

    // merge and register the template data globally
    $tplData = tpl::$data;
    tpl::$data = array_merge(tpl::$data, $data);

    // load the template
    $result = tpl::load($file, null, $return);

    // reset the template data
    tpl::$data = $tplData;

    return $result;

  }

}
