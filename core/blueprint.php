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

  public function subpages() {
    
    if(isset($this->yaml['subpages']) and $this->yaml['subpages'] == false) {
      return false;
    }

    $defaults = array(
      'template' => array(),
      'sortable' => true,
      'limit'    => 20
    );

    if(!isset($this->yaml['subpages']) or $this->yaml['subpages'] === true) {
      $defaults['template'] = static::all();      
    } else if(is_string($this->yaml['subpages'])) {
      $defaults['template'][] = static::find($this->yaml['subpages']);
    } else if(isset($this->yaml['subpages']['template'])) {
      $template = $this->yaml['subpages']['template'];

      if(is_string($template)) {
        $defaults['template'] = static::find($template);
      } else if(is_array($template)) {

        foreach($template as $t) {
          $defaults['template'][] = static::find($t);
        }
      }
    } 

    if(isset($this->yaml['subpages']['sortable']) and $this->yaml['subpages']['sortable'] == false) {
      $defaults['sortable'] = false;
    }

    if(isset($this->yaml['subpages']['limit'])) {
      $defaults['limit'] = $this->yaml['subpages']['limit'];
    }

    return $defaults;

  }

  public function files() {

    if(isset($this->yaml['files']) and $this->yaml['files'] == false) {
      return false;
    }

    $defaults = array(
      'fields' => array()
    );

    if(!isset($this->yaml['files']) or $this->yaml['files'] === true) {
      return $defaults;
    }

    if(isset($this->yaml['files']['fields'])) {
      $defaults['fields'] = $this->yaml['files']['fields'];
    }

    return $defaults;

  }

  public function filefields() {
    $files = $this->files();
    return (array)$files['fields'];
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