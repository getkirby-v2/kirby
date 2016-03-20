<?php

namespace Kirby\Component;

use Exception;
use Page;
use Tpl;

class Template extends \Kirby\Component {

  public function data(Page $page, $data = []) {
    // apply the basic template vars
    return tpl::$data = array_merge(tpl::$data, array(
      'kirby' => $this->kirby,
      'site'  => $this->kirby->site(),
      'pages' => $this->kirby->site()->children(),
      'page'  => $page
    ), $page->templateData(), $data, $page->controller($data));
  }

  public function render(Page $page, $data = []) {

    if(!file_exists($page->templateFile())) {
      throw new Exception('The default template could not be found');
    }

    $this->data($page, $data);

    return tpl::load($page->templateFile());

  }

}