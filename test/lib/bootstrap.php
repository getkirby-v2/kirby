<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$dir = realpath(dirname(__FILE__));

if(!defined('TEST_ROOT'))     define('TEST_ROOT',     dirname($dir));
if(!defined('TEST_ROOT_ETC')) define('TEST_ROOT_ETC', TEST_ROOT . DIRECTORY_SEPARATOR . 'etc');
if(!defined('TEST_ROOT_LIB')) define('TEST_ROOT_LIB', $dir);
if(!defined('TEST_ROOT_TMP')) define('TEST_ROOT_TMP', TEST_ROOT_ETC . DIRECTORY_SEPARATOR . 'tmp');

// set the timezone for all date functions
date_default_timezone_set('UTC');

// include the kirby bootstrapper file
require_once(dirname(TEST_ROOT) . DIRECTORY_SEPARATOR . 'bootstrap.php');

// include the kirby testcase file
require_once(__DIR__ . DS . 'testcase.php');

// dummy classes
class Asset extends AssetAbstract {}
class Avatar extends AvatarAbstract {}
class Page extends PageAbstract {}
class Pages extends PagesAbstract {}
class Children extends ChildrenAbstract {}
class Content extends ContentAbstract {}
class Field extends FieldAbstract {}
class File extends FileAbstract {}
class Files extends FilesAbstract {}
class Kirbytext extends KirbytextAbstract {}
class Kirbytag extends KirbytagAbstract {}
class Role extends RoleAbstract {}
class Roles extends RolesAbstract {}
class Site extends SiteAbstract {}
class Users extends UsersAbstract {}
class User extends UserAbstract {}