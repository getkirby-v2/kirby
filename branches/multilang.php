<?php

/**
 * Unmodified classes
 */
class Pages     extends PagesAbstract {}
class Children  extends ChildrenAbstract {}
class Field     extends FieldAbstract {}
class Files     extends FilesAbstract {}
class Kirbytext extends KirbytextAbstract {}
class Kirbytag  extends KirbytagAbstract {}
class Role      extends RoleAbstract {}
class Roles     extends RolesAbstract {}
class Users     extends UsersAbstract {}
class User      extends UserAbstract {}

/**
 * Modified classes
 */
load(array(
  'content'   => __DIR__ . DS . 'multilang' . DS . 'content.php',
  'file'      => __DIR__ . DS . 'multilang' . DS . 'file.php',
  'language'  => __DIR__ . DS . 'multilang' . DS . 'language.php',
  'languages' => __DIR__ . DS . 'multilang' . DS . 'languages.php',
  'page'      => __DIR__ . DS . 'multilang' . DS . 'page.php',
  'site'      => __DIR__ . DS . 'multilang' . DS . 'site.php',
));