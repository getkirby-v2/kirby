<?php

// date tag
kirbytext::$tags['date'] = array(
  'attr' => array(),
  'html' => function($tag) {
    return strtolower($tag->attr('date')) == 'year' ? date('Y') : date($tag->attr('date'));
  }
);

// email tag
kirbytext::$tags['email'] = array(
  'attr' => array(
    'class',
    'title',
    'text',
    'rel'
  ),
  'html' => function($tag) {
    return html::email($tag->attr('email'), html($tag->attr('text')), array(
      'class' => $tag->attr('class'),
      'title' => $tag->attr('title'),
      'rel'   => $tag->attr('rel'),
    ));
  }
);

// file tag
kirbytext::$tags['file'] = array(
  'attr' => array(
    'text',
    'class',
    'title',
    'rel',
    'target',
    'popup'
  ),
  'html' => function($tag) {

    // build a proper link to the file
    $file = $tag->file($tag->attr('file'));
    $text = $tag->attr('text');

    if(!$file) return $text;

    // use filename if the text is empty and make sure to
    // ignore markdown italic underscores in filenames
    if(empty($text)) $text = str_replace('_', '\_', $file->name());

    return html::a($file->url(), html($text), array(
      'class'  => $tag->attr('class'),
      'title'  => html($tag->attr('title')),
      'rel'    => $tag->attr('rel'),
      'target' => $tag->target(),
    ));

  }
);

// image tag
kirbytext::$tags['image'] = array(
  'attr' => array(
    'width',
    'height',
    'alt',
    'text',
    'title',
    'class',
    'imgclass',
    'linkclass',
    'caption',
    'link',
    'target',
    'popup',
    'rel'
  ),
  'html' => function($tag) {

    $url     = $tag->attr('image');
    $alt     = $tag->attr('alt');
    $title   = $tag->attr('title');
    $link    = $tag->attr('link');
    $caption = $tag->attr('caption');
    $file    = $tag->file($url);

    // use the file url if available and otherwise the given url
    $url = $file ? $file->url() : url($url);

    // alt is just an alternative for text
    if($text = $tag->attr('text')) $alt = $text;

    // try to get the title from the image object and use it as alt text
    if($file) {

      if(empty($alt) and $file->alt() != '') {
        $alt = $file->alt();
      }

      if(empty($title) and $file->title() != '') {
        $title = $file->title();
      }

    }

    // at least some accessibility for the image
    if(empty($alt)) $alt = ' ';

    // link builder
    $_link = function($image) use($tag, $url, $link, $file) {

      if(empty($link)) return $image;

      // build the href for the link
      if($link == 'self') {
        $href = $url;
      } else if($file and $link == $file->filename()) {
        $href = $file->url();
      } else if($tag->file($link)) {
        $href = $tag->file($link)->url();
      } else {
        $href = $link;
      }

      return html::a(url($href), $image, array(
        'rel'    => $tag->attr('rel'),
        'class'  => $tag->attr('linkclass'),
        'title'  => $tag->attr('title'),
        'target' => $tag->target()
      ));

    };

    // image builder
    $_image = function($class) use($tag, $url, $alt, $title) {
      return html::img($url, array(
        'width'  => $tag->attr('width'),
        'height' => $tag->attr('height'),
        'class'  => $class,
        'title'  => $title,
        'alt'    => $alt
      ));
    };

    if(kirby()->option('kirbytext.image.figure') or !empty($caption)) {
      $image  = $_link($_image($tag->attr('imgclass')));
      $figure = new Brick('figure');
      $figure->addClass($tag->attr('class'));
      $figure->append($image);
      if(!empty($caption)) {
        $figure->append('<figcaption>' . html($caption) . '</figcaption>');
      }
      return $figure;
    } else {
      $class = trim($tag->attr('class') . ' ' . $tag->attr('imgclass'));
      return $_link($_image($class));
    }

  }
);

// link tag
kirbytext::$tags['link'] = array(
  'attr' => array(
    'text',
    'class',
    'role',
    'title',
    'rel',
    'lang',
    'target',
    'popup'
  ),
  'html' => function($tag) {

    $link = url($tag->attr('link'), $tag->attr('lang'));
    $text = $tag->attr('text');

    if(empty($text)) {
      $text = $link;
    } 

    if(str::isURL($text)) {
      $text = url::short($text);
    }

    return html::a($link, $text, array(
      'rel'    => $tag->attr('rel'),
      'class'  => $tag->attr('class'),
      'role'   => $tag->attr('role'),
      'title'  => $tag->attr('title'),
      'target' => $tag->target(),
    ));

  }
);

// tel tag
kirbytext::$tags['tel'] = array(
  'attr' => array(
    'text',
    'class',
    'title'
  ),
  'html' => function($tag) {

    $text = $tag->attr('text');
    $tel  = str_replace(array('/', ' ', '-'), '', $tag->attr('tel'));

    if(empty($text)) $text = $tag->attr('tel');

    return html::a('tel:' . $tel, html($text), array(
      'rel'    => $tag->attr('rel'),
      'class'  => $tag->attr('class'),
      'title'  => html($tag->attr('title'))
    ));
  }
);


// twitter tag
kirbytext::$tags['twitter'] = array(
  'attr' => array(
    'class',
    'title',
    'text',
    'rel',
    'target',
    'popup',
  ),
  'html' => function($tag) {

    // get and sanitize the username
    $username = str_replace('@', '', $tag->attr('twitter'));

    // build the profile url
    $url = 'https://twitter.com/' . $username;

    // sanitize the link text
    $text = $tag->attr('text', '@' . $username);

    // build the final link
    return html::a($url, $text, array(
      'class'  => $tag->attr('class'),
      'title'  => $tag->attr('title'),
      'rel'    => $tag->attr('rel'),
      'target' => $tag->target(),
    ));

  }
);

kirbytext::$tags['youtube'] = array(
  'attr' => array(
    'width',
    'height',
    'class',
    'caption'
  ),
  'html' => function($tag) {

    $caption = $tag->attr('caption');

    if(!empty($caption)) {
      $figcaption = '<figcaption>' . escape::html($caption) . '</figcaption>';
    } else {
      $figcaption = null;
    }

    return '<figure class="' . $tag->attr('class', kirby()->option('kirbytext.video.class', 'video')) . '">' . embed::youtube($tag->attr('youtube'), array(
      'width'   => $tag->attr('width',  kirby()->option('kirbytext.video.width')),
      'height'  => $tag->attr('height', kirby()->option('kirbytext.video.height')),
      'options' => kirby()->option('kirbytext.video.youtube.options')
    )) . $figcaption . '</figure>';

  }
);

kirbytext::$tags['vimeo'] = array(
  'attr' => array(
    'width',
    'height',
    'class',
    'caption'
  ),
  'html' => function($tag) {

    $caption = $tag->attr('caption');

    if(!empty($caption)) {
      $figcaption = '<figcaption>' . escape::html($caption) . '</figcaption>';
    } else {
      $figcaption = null;
    }

    return '<figure class="' . $tag->attr('class', kirby()->option('kirbytext.video.class', 'video')) . '">' . embed::vimeo($tag->attr('vimeo'), array(
      'width'   => $tag->attr('width',  kirby()->option('kirbytext.video.width')),
      'height'  => $tag->attr('height', kirby()->option('kirbytext.video.height')),
      'options' => kirby()->option('kirbytext.video.vimeo.options')
    )) . $figcaption . '</figure>';

  }
);

kirbytext::$tags['gist'] = array(
  'attr' => array(
    'file'
  ),
  'html' => function($tag) {
    return embed::gist($tag->attr('gist'), $tag->attr('file'));
  }
);
