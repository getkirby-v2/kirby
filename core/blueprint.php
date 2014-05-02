<?php

abstract class BlueprintAbstract {

  protected $file = null;
  protected $yaml = array();

  public function __construct($file) {

    if(!file_exists($file)) throw new Exception('The blueprint could not be found');

    $this->file = $file;
    $this->yaml = yaml(trim(f::read($this->file)));

    // remove the unwanted first line
    unset($this->yaml[0]);

    if(!is_array($this->yaml)) throw new Exception('The blueprint could not be parsed');

  }

  public function fields() {
    return a::get($this->yaml, 'fields');
  }

  public function file() {
    return $this->file;
  }

  public function title() {
    return $this->yaml['title'];
  }

  public function name() {
    return f::name($this->file);
  }

  public function pages() {

    $pages = a::get($this->yaml, 'pages');

    if($pages === false) return false;

    $settings = new Obj();
    $settings->template     = array();
    $settings->sortable     = true;
    $settings->sort         = false;
    $settings->limit        = 20;
    $settings->num          = $this->num($pages);

    if($pages === true or empty($pages)) {
      $settings->template = static::all();
    } else if(is_string($pages)) {
      $settings->template[] = static::find($pages);
    } else if(is_array($pages)) {

      if(isset($pages['template'])) {
        $template = $pages['template'];
        if(is_string($template)) {
          $settings->template[] = static::find($template);
        } else if(is_array($template)) {
          foreach($template as $t) {
            $settings->template[] = static::find($t);
          }
        }
      }

      if(isset($pages['sortable']) and $pages['sortable'] == false) {
        $settings->sortable = false;
      }

      if(isset($pages['limit'])) {
        $settings->limit = $pages['limit'];
      }

      if(isset($pages['sort'])) {
        dump($pages);
        $settings->sort = $pages['sort'];
      }

    }

    return $settings;

  }

  public function num() {

    $pages = a::get($this->yaml, 'pages', array());
    $obj   = new Obj();

    $obj->mode   = 'default';
    $obj->field  = null;
    $obj->format = null;

    $num = is_array($pages) ? a::get($pages, 'num') : 'default';

    if(is_array($num)) {
      foreach($num as $k => $v) $obj->$k = $v;
    } else if(!empty($num)) {
      $obj->mode = $num;
    }

    return $obj;

  }

  public function files() {

    $files    = a::get($this->yaml, 'files');
    $settings = new Obj();
    $settings->fields = array();

    if($files === false) return false;
    if($files === true)  return $settings;

    if(isset($files['fields'])) {
      $settings->fields = (array)$files['fields'];
    }

    return $settings;

  }

  static public function find($id) {

    if(is_a($id, 'Page')) {

      $file = c::get('root.blueprints') . DS . $id->intendedTemplate() . '.php';

      if(!file_exists($file)) {
        $file = c::get('root.blueprints') . DS . $id->template() . '.php';
      }

    } else if(file_exists($id)) {
      $file = $id;
    } else {
      $file = c::get('root.blueprints') . DS . $id . '.php';
    }

    if(!file_exists($file)) {
      $file = c::get('root.blueprints') . DS . 'default.php';
    }

    return new static($file);

  }

  static public function all() {

    $root       = c::get('root.blueprints');
    $files      = dir::read($root);
    $blueprints = array();

    foreach($files as $file) {
      // skip invalid blueprints
      if(f::extension($file) != 'php') continue;
      $blueprints[] = new Blueprint($root . DS . $file);
    }

    return $blueprints;

  }

}