<?php

/**
 * SQL
 *
 * SQL Query builder
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>, Lukas Bestle <lukas@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Sql {

  // list of literals which should not be escaped in queries
  public static $literals = array('NOW()', null);
  
  // sql formatting methods, defined below
  public static $methods = array();
  
  // the parent database connection and database query
  public $database;
  public $dbquery;
  
  // list of bindings by sql query string that defines them
  protected $bindings = array();

  /**
   * Constructor
   *
   * @param Database $database
   * @param Database\Query $dbquery Database query that is used to set the bindings directly
   */
  public function __construct($database, $dbquery = null) {
    $this->database = $database;
    $this->dbquery  = $dbquery;
  }

  /**
   * Sets and returns query-specific bindings
   *
   * @param string $query SQL query string that contains the bindings
   * @param array $values Array of bindings to set (null to get the bindings)
   * @return array
   */
  public function bindings($query, $values = null) {
    if(is_null($values)) {
      return a::get($this->bindings, $query, array());
    } else {
      if(!is_null($query)) $this->bindings[$query] = $values;
      
      // directly register bindings if possible
      if($this->dbquery) $this->dbquery->bindings($values);
    }
  }
  
  /**
   * Calls an SQL method using the correct database type
   *
   * @param string $method
   * @param array $arguments
   * @return mixed
   */
  public function __call($method, $arguments) {
    $type = $this->database->type();
    
    if(isset(static::$methods[$type][$method])) {
      $method = static::$methods[$type][$method];
    } else {
      // fallback to shared method
      if(!isset(static::$methods['_shared'][$method])) {
        throw new Error('SQL method ' . $method . ' is not defined for database type ' . $type);
      }
      
      $method = static::$methods['_shared'][$method];
    }
    
    // pass the sql object as first argument
    array_unshift($arguments, $this);
    return call($method, $arguments);
  }
  
  /**
   * Registers a method for a specified database type
   * The function must take this SQL object as first parameter and set bindings on it
   *
   * @param string $name
   * @param callable $function
   * @param string $type 'mysql', 'sqlite' or '_shared'
   */
  public static function registerMethod($name, $function, $type = '_shared') {
    if(!isset(static::$methods[$type])) static::$methods[$type] = array();
    static::$methods[$type][$name] = $function;
  }

}

/**
 * Returns a randomly generated binding name
 *
 * @param string $label String that contains lowercase letters and numbers to use as a readable identifier
 * @return string
 */
sql::registerMethod('generateBindingName', function($sql, $label) {
  // make sure that the binding name is valid to prevent injections
  if(!preg_match('/^[a-z0-9]+$/', $label)) $label = 'invalid';
  
  return ':' . $label . '_' . uniqid();
});

/**
 * Builds a select clause
 *
 * @param array $params List of parameters for the select clause. Check out the defaults for more info.
 * @return string
 */
sql::registerMethod('select', function($sql, $params = array()) {
  
  $defaults = array(
    'table'    => '',
    'columns'  => '*',
    'join'     => false,
    'distinct' => false,
    'where'    => false,
    'group'    => false,
    'having'   => false,
    'order'    => false,
    'offset'   => 0,
    'limit'    => false,
  );

  $options  = array_merge($defaults, $params);
  $query    = array();
  $bindings = array();

  $query[] = 'SELECT';

  // select distinct values
  if($options['distinct']) $query[] = 'DISTINCT';
  
  // validate table
  if(!$sql->database->validateTable($options['table'])) throw new Error('Invalid table ' . $options['table']);
  
  // columns
  if(empty($options['columns'])) {
    $query[] = '*';
  } else if(is_array($options['columns'])) {
    // validate columns
    $columns = array();
    foreach($options['columns'] as $column) {
      list($table, $columnPart) = $sql->splitIdentifier($options['table'], $column);
      if(!$sql->database->validateColumn($table, $columnPart)) {
        throw new Error('Invalid column ' . $column);
      }
      
      $columns[] = $sql->combineIdentifier($table, $columnPart);
    }
    
    $query[] = implode(', ', $columns);
  } else {
    $query[] = $options['columns'];
  }
  
  // table
  $query[] = 'FROM ' . $sql->quoteIdentifier($options['table']);
  
  // join
  if(!empty($options['join'])) {
    foreach($options['join'] as $join) {
      $joinType = ltrim(strtoupper(a::get($join, 'type', '')) . ' JOIN');
      if(!in_array($joinType, array(
        'JOIN', 'INNER JOIN',
        'OUTER JOIN',
        'LEFT OUTER JOIN', 'LEFT JOIN',
        'RIGHT OUTER JOIN', 'RIGHT JOIN',
        'FULL OUTER JOIN', 'FULL JOIN',
        'NATURAL JOIN',
        'CROSS JOIN',
        'SELF JOIN'
      ))) throw new Error('Invalid join type ' . $joinType);
      
      // validate table
      if(!$sql->database->validateTable($join['table'])) throw new Error('Invalid table ' . $join['table']);
      
      // ON can't be escaped here
      $query[] = $joinType . ' ' . $sql->quoteIdentifier($join['table']) . ' ON ' . $join['on'];
    }
  }
  
  // where
  if(!empty($options['where'])) {
    // WHERE can't be escaped here
    $query[] = 'WHERE ' . $options['where'];
  }
  
  // group
  if(!empty($options['group'])) {
    // GROUP BY can't be escaped here
    $query[] = 'GROUP BY ' . $options['group'];
  }
  
  // having
  if(!empty($options['having'])) {
    // HAVING can't be escaped here
    $query[] = 'HAVING ' . $options['having'];
  }

  // order
  if(!empty($options['order'])) {
    // ORDER BY can't be escaped here
    $query[] = 'ORDER BY ' . $options['order'];
  }
  
  // offset and limit
  if($options['offset'] > 0 || $options['limit']) {
    if(!$options['limit']) $options['limit'] = '18446744073709551615';
    
    $offsetBinding = $sql->generateBindingName('offset');
    $bindings[$offsetBinding] = $options['offset'];
    $limitBinding = $sql->generateBindingName('limit');
    $bindings[$limitBinding] = $options['limit'];
    
    $query[] = 'LIMIT ' . $offsetBinding . ', ' . $limitBinding;
  }

  $query = implode(' ', $query);
  
  $sql->bindings($query, $bindings);
  return $query;

});

/**
 * Builds an insert clause
 *
 * @param array $params List of parameters for the insert clause. See defaults for more info.
 * @return string
 */
sql::registerMethod('insert', function($sql, $params = array()) {

  $defaults = array(
    'table'  => '',
    'values' => false,
  );

  $options  = array_merge($defaults, $params);
  $query    = array();
  $bindings = array();
  
  // validate table
  if(!$sql->database->validateTable($options['table'])) throw new Error('Invalid table ' . $options['table']);
  
  $query[] = 'INSERT INTO ' . $sql->quoteIdentifier($options['table']);
  $query[] = $sql->values($options['table'], $options['values'], ', ', false);

  $query = implode(' ', $query);
  
  $sql->bindings($query, $bindings);
  return $query;

});

/**
 * Builds an update clause
 *
 * @param array $params List of parameters for the update clause. See defaults for more info.
 * @return string
 */
sql::registerMethod('update', function($sql, $params = array()) {

  $defaults = array(
    'table'  => '',
    'values' => false,
    'where'  => false,
  );

  $options  = array_merge($defaults, $params);
  $query    = array();
  $bindings = array();
  
  // validate table
  if(!$sql->database->validateTable($options['table'])) throw new Error('Invalid table ' . $options['table']);
  
  $query[] = 'UPDATE ' . $sql->quoteIdentifier($options['table']) . ' SET';
  $query[] = $sql->values($options['table'], $options['values']);

  if(!empty($options['where'])) {
    // WHERE can't be escaped here
    $query[] = 'WHERE ' . $options['where'];
  }

  $query = implode(' ', $query);
  
  $sql->bindings($query, $bindings);
  return $query;

});

/**
 * Builds a delete clause
 *
 * @param array $params List of parameters for the delete clause. See defaults for more info.
 * @return string
 */
sql::registerMethod('delete', function($sql, $params = array()) {

  $defaults = array(
    'table'  => '',
    'where'  => false,
  );

  $options  = array_merge($defaults, $params);
  $query    = array();
  $bindings = array();
  
  // validate table
  if(!$sql->database->validateTable($options['table'])) throw new Error('Invalid table ' . $options['table']);
  
  $query[] = 'DELETE FROM ' . $sql->quoteIdentifier($options['table']);

  if(!empty($options['where'])) {
    // WHERE can't be escaped here
    $query[] = 'WHERE ' . $options['where'];
  }

  $query = implode(' ', $query);
  
  $sql->bindings($query, $bindings);
  return $query;

});

/**
 * Builds a safe list of values for insert, select or update queries
 *
 * @param string $table Table name
 * @param mixed $values A value string or array of values
 * @param string $separator A separator which should be used to join values
 * @param boolean $set If true builds a set list of values for update clauses
 * @param boolean $enforceQualified Always use fully qualified column names
 * @return string
 */
sql::registerMethod('values', function($sql, $table, $values, $separator = ', ', $set = true, $enforceQualified = false) {

  if(!is_array($values)) return $values;
  
  if($set) {

    $output   = array();
    $bindings = array();

    foreach($values as $key => $value) {
      // validate column
      list($table, $column) = $sql->splitIdentifier($table, $key);
      if(!$sql->database->validateColumn($table, $column)) {
        throw new Error('Invalid column ' . $key);
      }
      $key = $sql->combineIdentifier($table, $column, $enforceQualified !== true);
      
      if(in_array($value, sql::$literals, true)) {
        $output[] = $key . ' = ' . (($value === null)? 'null' : $value);
        continue;
      } elseif(is_array($value)) {
        $value = json_encode($value);
      }
      
      $valueBinding = $sql->generateBindingName('value');
      $bindings[$valueBinding] = $value;
      
      $output[] = $key . ' = ' . $valueBinding;
    }
    
    $sql->bindings(null, $bindings);
    return implode($separator, $output);

  } else {

    $fields   = array();
    $output   = array();
    $bindings = array();

    foreach($values as $key => $value) {
      // validate column
      list($table, $column) = $sql->splitIdentifier($table, $key);
      if(!$sql->database->validateColumn($table, $column)) {
        throw new Error('Invalid column ' . $key);
      }
      $key = $sql->combineIdentifier($table, $column, $enforceQualified !== true);
      
      $fields[] = $key;
      
      if(in_array($value, sql::$literals, true)) {
        $output[] = ($value === null)? 'null' : $value;
        continue;
      } elseif(is_array($value)) {
        $value = json_encode($value);
      }
      
      $valueBinding = $sql->generateBindingName('value');
      $bindings[$valueBinding] = $value;
      
      $output[] = $valueBinding;
    }

    $sql->bindings(null, $bindings);
    return '(' . implode($separator, $fields) . ') VALUES (' . implode($separator, $output) . ')';

  }

});

/**
 * Creates the sql for dropping a single table
 *
 * @param string $table
 * @return string
 */
sql::registerMethod('dropTable', function($sql, $table) {
  
  // validate table
  if(!$sql->database->validateTable($table)) throw new Error('Invalid table ' . $table);
  
  return 'DROP TABLE ' . $sql->quoteIdentifier($table);

});

/**
 * Creates a table with a simple scheme array for columns
 * Default version for MySQL
 *
 * @todo  add more options per column
 * @param string $table The table name
 * @param array $columns
 * @return string
 */
sql::registerMethod('createTable', function($sql, $table, $columns = array()) {

  $output   = array();
  $keys     = array();
  $bindings = array();

  foreach($columns as $name => $column) {
    // column type
    if(!isset($column['type'])) throw new Error('No column type given for column ' . $name);
    switch($column['type']) {
      case 'id':
        $template = '{column.name} INT(11) UNSIGNED NOT NULL AUTO_INCREMENT';
        $column['key'] = 'PRIMARY';
        break;
      case 'varchar':
        $template = '{column.name} varchar(255) {column.null} {column.default}';
        break;
      case 'text':
        $template = '{column.name} TEXT';
        break;
      case 'int':
        $template = '{column.name} INT(11) UNSIGNED {column.null} {column.default}';
        break;
      case 'timestamp':
        $template = '{column.name} TIMESTAMP {column.null} {column.default}';
        break;
      default:
        throw new Error('Unsupported column type: ' . $column['type']);
    }

    // null
    if(a::get($column, 'null') === false) {
      $null = 'NOT NULL';
    } else {
      $null = 'NULL';
    }

    // indexes/keys
    $key = false;
    if(isset($column['key'])) {
      $column['key'] = strtoupper($column['key']);
      
      // backwards compatibility
      if($column['key'] === 'PRIMARY') $column['key'] = 'PRIMARY KEY';
      
      if(in_array($column['key'], array('PRIMARY KEY', 'INDEX'))) {
        $key = $column['key'];
        $keys[$name] = $key;
      }
    }

    // default value
    $defaultBinding = null;
    if(isset($column['default'])) {
      $defaultBinding = $sql->generateBindingName('default');
      $bindings[$defaultBinding] = $column['default'];
    }

    $output[] = trim(str::template($template, array(
      'column.name'    => $sql->quoteIdentifier($name),
      'column.null'    => $null,
      'column.default' => r(!is_null($defaultBinding), 'DEFAULT ' . $defaultBinding),
    )));

  }

  // combine columns
  $inner = implode(',' . PHP_EOL, $output);

  // add keys
  foreach($keys as $name => $key) {
    $inner .= ',' . PHP_EOL . $key . ' (' . $sql->quoteIdentifier($name) . ')';
  }

  // make it a string
  $query = 'CREATE TABLE ' . $sql->quoteIdentifier($table) . ' (' . PHP_EOL . $inner . PHP_EOL . ')';

  $sql->bindings($query, $bindings);
  return $query;

});

/**
 * Creates a table with a simple scheme array for columns
 * SQLite version
 *
 * @todo  add more options per column
 * @param string $table The table name
 * @param array $columns
 * @return string
 */
sql::registerMethod('createTable', function($sql, $table, $columns = array()) {

  $output   = array();
  $keys     = array();
  $bindings = array();

  foreach($columns as $name => $column) {
    // column type
    if(!isset($column['type'])) throw new Error('No column type given for column ' . $name);
    switch($column['type']) {
      case 'id':
        $template = '{column.name} INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE';
        break;
      case 'varchar':
        $template = '{column.name} TEXT {column.null} {column.key} {column.default}';
        break;
      case 'text':
        $template = '{column.name} TEXT {column.null} {column.key} {column.default}';
        break;
      case 'int':
        $template = '{column.name} INTEGER {column.null} {column.key} {column.default}';
        break;
      case 'timestamp':
        $template = '{column.name} INTEGER {column.null} {column.key} {column.default}';
        break;
      default:
        throw new Error('Unsupported column type: ' . $column['type']);
    }

    // null
    if(a::get($column, 'null') === false) {
      $null = 'NOT NULL';
    } else {
      $null = 'NULL';
    }

    // indexes/keys
    $key = false;
    if(isset($column['key'])) {
      $column['key'] = strtoupper($column['key']);
      
      // backwards compatibility
      if($column['key'] === 'PRIMARY') $column['key'] = 'PRIMARY KEY';
      
      if(in_array($column['key'], array('PRIMARY KEY', 'INDEX'))) {
        $key = $column['key'];
        $keys[$name] = $key;
      }
    }

    // default value
    $default = null;
    if(isset($column['default'])) {
      // Apparently SQLite doesn't support bindings for default values
      $default = "'" . $sql->database->escape($column['default']) . "'";
    }

    $output[] = trim(str::template($template, array(
      'column.name'    => $sql->quoteIdentifier($name),
      'column.null'    => $null,
      'column.key'     => r($key && $key != 'INDEX', $key),
      'column.default' => r(!is_null($default), 'DEFAULT ' . $default),
    )));

  }

  // combine columns
  $inner = implode(',' . PHP_EOL, $output);

  // make it a string
  $query = 'CREATE TABLE ' . $sql->quoteIdentifier($table) . ' (' . PHP_EOL . $inner . PHP_EOL . ')';

  // set bindings for our first query
  $sql->bindings($query, $bindings);
  
  // add index keys
  foreach($keys as $name => $key) {
    if($key != 'INDEX') continue;
  
    $indexQuery = 'CREATE INDEX ' . $sql->quoteIdentifier($table . '_' . $name) . ' ON ' . $sql->quoteIdentifier($table) . ' (' . $sql->quoteIdentifier($name) . ')';
    $query .= ';' . PHP_EOL . $indexQuery;
  }
  
  return $query;

}, 'sqlite');

/**
 * Splits a (qualified) identifier into table and column
 * 
 * @param $table string Default table if the identifier is not qualified
 * @param $identifier string
 * @return array
 */
sql::registerMethod('splitIdentifier', function($sql, $table, $identifier) {

  // split by dot, but only outside of quotes
  $parts = preg_split('/(?:`[^`]*`|"[^"]*")(*SKIP)(*F)|\./', $identifier);
  
  switch(count($parts)) {
    // non-qualified identifier
    case 1:
      return array($table, $sql->unquoteIdentifier($parts[0]));
    
    // qualified identifier
    case 2:
      return array($sql->unquoteIdentifier($parts[0]), $sql->unquoteIdentifier($parts[1]));
    
    // every other number is an error
    default:
      throw new Error('Invalid identifier ' . $identifier);
  }

});

/**
 * Unquotes an identifier (table *or* column)
 * 
 * @param $identifier string
 * @return string
 */
sql::registerMethod('unquoteIdentifier', function($sql, $identifier) {

  // remove quotes around the identifier
  if(in_array(str::substr($identifier, 0, 1), array('"', '`'))) $identifier = str::substr($identifier, 1);
  if(in_array(str::substr($identifier, -1),   array('"', '`'))) $identifier = str::substr($identifier, 0, -1);
  
  // unescape duplicated quotes
  return str_replace(array('""', '``'), array('"', '`'), $identifier);

});

/**
 * Combines an identifier (table and column)
 * Default version for MySQL
 * 
 * @param $table string
 * @param $column string
 * @param $values boolean Whether the identifier is going to be used for a values clause
 *                        Only relevant for SQLite
 * @return string
 */
sql::registerMethod('combineIdentifier', function($sql, $table, $column, $values = false) {

  return $sql->quoteIdentifier($table) . '.' . $sql->quoteIdentifier($column);

});

/**
 * Combines an identifier (table and column)
 * SQLite version
 * 
 * @param $table string
 * @param $column string
 * @param $values boolean Whether the identifier is going to be used for a values clause
 *                        Only relevant for SQLite
 * @return string
 */
sql::registerMethod('combineIdentifier', function($sql, $table, $column, $values = false) {
  
  // SQLite doesn't support qualified column names for VALUES clauses
  if($values) return $sql->quoteIdentifier($column);
  return $sql->quoteIdentifier($table) . '.' . $sql->quoteIdentifier($column);

}, 'sqlite');

/**
 * Quotes an identifier (table *or* column)
 * Default version for MySQL
 * 
 * @param $identifier string
 * @return string
 */
sql::registerMethod('quoteIdentifier', function($sql, $identifier) {

  // * is special
  if($identifier === '*') return $identifier;

  // replace every backtick with two backticks
  $identifier = str_replace('`', '``', $identifier);
  
  // wrap in backticks
  return '`' . $identifier . '`';

});

/**
 * Quotes an identifier (table *or* column)
 * SQLite version
 * 
 * @param $identifier string
 * @return string
 */
sql::registerMethod('quoteIdentifier', function($sql, $identifier) {

  // * is special
  if($identifier === '*') return $identifier;

  // replace every quote with two quotes
  $identifier = str_replace('"', '""', $identifier);
  
  // wrap in quotes
  return '"' . $identifier . '"';

}, 'sqlite');

/**
 * Returns a list of tables for a specified database
 * MySQL version
 *
 * @param string $database The database name
 * @return string
 */
sql::registerMethod('tableList', function($sql, $database) {

  $bindings = array();
  $databaseBinding = $sql->generateBindingName('database');
  $bindings[$databaseBinding] = $database;
  
  $query = 'SELECT TABLE_NAME AS name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ' . $databaseBinding;
  
  $sql->bindings($query, $bindings);
  return $query;

}, 'mysql');

/**
 * Returns a list of tables of the database
 * SQLite version
 *
 * @param string $database The database name
 * @return string
 */
sql::registerMethod('tableList', function($sql, $database) {
  
  return 'SELECT name FROM sqlite_master WHERE type = "table"';

}, 'sqlite');

/**
 * Returns a list of columns for a specified table
 * MySQL version
 *
 * @param string $database The database name
 * @param string $table The table name
 * @return string
 */
sql::registerMethod('columnList', function($sql, $database, $table) {

  $bindings = array();
  $databaseBinding = $sql->generateBindingName('database');
  $bindings[$databaseBinding] = $database;
  $tableBinding = $sql->generateBindingName('table');
  $bindings[$tableBinding] = $table;
  
  $query = 'SELECT COLUMN_NAME AS name FROM INFORMATION_SCHEMA.COLUMNS ';
  $query .= 'WHERE TABLE_SCHEMA = ' . $databaseBinding . ' AND TABLE_NAME = ' . $tableBinding;
  
  $sql->bindings($query, $bindings);
  return $query;

}, 'mysql');

/**
 * Returns a list of columns for a specified table
 * SQLite version
 *
 * @param string $database The database name
 * @param string $table The table name
 * @return string
 */
sql::registerMethod('columnList', function($sql, $database, $table) {

  // validate table
  if(!$sql->database->validateTable($table)) throw new Error('Invalid table ' . $table);
  
  return 'PRAGMA table_info(' . $sql->quoteIdentifier($table) . ')';

}, 'sqlite');
