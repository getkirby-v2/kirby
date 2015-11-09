<?php 

/**
 * File
 */
class File extends FileAbstract {

  /**
   * Returns the full root for the content file
   * 
   * @return string
   */
  public function textfile($lang = null) {
    return $this->page->textfile($this->filename(), $lang);
  }

  /**
   * Get the meta information 
   * 
   * @param string $lang optional language code
   * @return Content
   */
  public function meta($lang = null) {

    // get the content for the current language
    if(is_null($lang)) {

      // the current language's content can be cached
      if(isset($this->cache['meta'])) return $this->cache['meta'];

      // get the current content
      $meta = $this->_meta($this->site->language->code);

      // get the fallback content 
      if($this->site->language->code != $this->site->defaultLanguage->code) {

        // fetch the default language content
        $defaultMeta = $this->_meta($this->site->defaultLanguage->code);

        // replace all missing fields with values from the default content
        foreach($defaultMeta->data as $key => $field) {      
          if(empty($meta->data[$key]->value)) {
            $meta->data[$key] = $field;            
          }
        }
        
      }

      // cache the meta for this language
      return $this->cache['meta'] = $meta;

    // get the meta for another language
    } else {    
      return $this->_meta($lang);
    }

  }

  /**
   * Private method to simplify meta fetching
   * 
   * @return Content
   */
  protected function _meta($lang) {

    // get the inventory
    $inventory = $this->page->inventory();      

    // try to fetch the content for this language
    $meta = isset($inventory['meta'][$this->filename][$lang]) ? $inventory['meta'][$this->filename][$lang] : null;

    // try to replace empty content with the default language content
    if(empty($meta) and isset($inventory['meta'][$this->filename][$this->site->defaultLanguage->code])) {
      $meta = $inventory['meta'][$this->filename][$this->site->defaultLanguage->code];
    }

    // find and cache the content for this language
    return new Content($this->page, $this->page->root() . DS . $meta);

  }

  /**
   * Renames the file and also its meta info txt
   *  
   * @param string $filename
   * @param boolean $safeName
   */
  public function rename($name, $safeName = true) {

    $filename = $this->createNewFilename($name, $safeName);
    $root     = $this->dir() . DS . $filename;

    if($root == $this->root()) return $filename;

    if(file_exists($root)) {
      throw new Exception('A file with that name already exists');
    }

    if(!f::move($this->root(), $root)) {
      throw new Exception('The file could not be renamed');
    }

    foreach($this->site->languages() as $lang) {

      // rename all meta files
      $meta = $this->textfile($lang->code());

      if(file_exists($meta)) {
        f::move($meta, $this->page->textfile($filename, $lang->code()));
      }

    }

    // reset the page cache
    $this->page->reset();

    // reset the basics
    $this->root     = $root;
    $this->filename = $filename;
    $this->name     = $name;
    $this->cache    = array();

    cache::flush();

    return $filename;

  }

  public function update($data = array(), $lang = null) {

    $data = array_merge((array)$this->meta()->toArray(), $data);    

    foreach($data as $k => $v) {
      if(is_null($v)) unset($data[$k]);
    }

    if(!data::write($this->textfile($lang), $data, 'kd')) {
      throw new Exception('The file data could not be saved');
    }

    return true;

  }

  public function delete() {

    foreach($this->site->languages() as $lang) {
      // delete the meta file for each language
      f::remove($this->textfile($lang->code()));
    }

    parent::delete();

    return true;

  }


}