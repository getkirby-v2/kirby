<?php

abstract class AvatarAbstract extends Media {
  
  public $user  = null;
  public $kirby = null;  

  use Kirby\Traits\Image;

  public function __construct(User $user) {

    // store the parent user object
    $this->user = $user;

    // this should rather be coming from the user object
    $this->kirby = kirby::instance();

    // try to find the avatar
    if($file = f::resolve($this->kirby->roots()->avatars() . DS . $user->username(), ['jpg', 'jpeg', 'gif', 'png'])) {
      $filename = f::filename($file);      
    } else {
      $filename = $user->username() . '.jpg';
      $file     = $this->kirby->roots()->avatars() . DS . $filename;
    }

    parent::__construct($file, $this->kirby->urls()->avatars() . '/' . $filename);

  }

}