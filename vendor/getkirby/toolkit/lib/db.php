<?php

/**
 * DB
 *
 * Database shortcuts
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class DB {

  const ERROR_UNKNOWN_METHOD = 0;

  // query shortcuts
  public static $queries = array();

  // The singleton Database object
  public static $connection = null;

  /**
   * (Re)connect the database
   *
   * @param mixed $params Pass array() to use the default params from the config
   * @return object
   */
  public static function connect($params = null) {
    if(is_null($params) && !is_null(static::$connection)) return static::$connection;
    if(is_null($params)) {

      // try to connect with the default connection settings
      $params = array(
        'type'     => c::get('db.type', 'mysql'),
        'host'     => c::get('db.host', 'localhost'),
        'user'     => c::get('db.user', 'root'),
        'password' => c::get('db.password', ''),
        'database' => c::get('db.name', ''),
        'prefix'   => c::get('db.prefix', ''),
      );

    }

    return static::$connection = new Database($params);
  }

  /**
   * Returns the current database connection
   *
   * @return object
   */
  public static function connection() {
    return static::$connection;
  }

  /**
   * Sets the current table, which should be queried
   *
   * @param string $table
   * @return object Returns a DBQuery object, which can be used to build a full query for that table
   */
  public static function table($table) {
    $connection = db::connect();
    return $connection->table($table);
  }

  /**
   * Executes a raw sql query which expects a set of results
   *
   * @param string $query
   * @param array $bindings
   * @param array $params
   * @return mixed
   */
  public static function query($query, $bindings = array(), $params = array()) {
    $connection = db::connect();
    return $connection->query($query, $bindings, $params);
  }

  /**
   * Executes a raw sql query which expects no set of results (i.e. update, insert, delete)
   *
   * @param string $query
   * @param array $bindings
   * @return mixed
   */
  public static function execute($query, $bindings = array()) {
    $connection = db::connect();
    return $connection->execute($query, $bindings);
  }

  /**
   * Magic calls for other static db methods,
   * which are redircted to the database class if available
   *
   * @param string $method
   * @param mixed $arguments
   * @return mixed
   */
  public static function __callStatic($method, $arguments) {

    if(isset(static::$queries[$method])) {
      return call(static::$queries[$method], $arguments);
    } else if(!is_callable(array(static::$connection, $method))) {
      throw new Error('invalid static db method: ' . $method, static::ERROR_UNKNOWN_METHOD);
    } else {
      return call(array(static::$connection, $method), $arguments);
    }
  }

}

/**
 * Shortcut for select clauses
 *
 * @param string $table The name of the table, which should be queried
 * @param mixed $columns Either a string with columns or an array of column names
 * @param mixed $where The where clause. Can be a string or an array
 * @param mixed $order
 * @param int $offset
 * @param int $limit
 * @return mixed
 */
db::$queries['select'] = function($table, $columns = '*', $where = null, $order = null, $offset = 0, $limit = null) {
  return db::table($table)->select($columns)->where($where)->order($order)->offset($offset)->limit($limit)->all();
};

/**
 * Shortcut for selecting a single row in a table
 *
 * @param string $table The name of the table, which should be queried
 * @param mixed $columns Either a string with columns or an array of column names
 * @param mixed $where The where clause. Can be a string or an array
 * @param mixed $order
 * @param int $offset
 * @param int $limit
 * @return mixed
 */
db::$queries['first'] = db::$queries['row'] = db::$queries['one'] = function($table, $columns = '*', $where = null, $order = null) {
  return db::table($table)->select($columns)->where($where)->order($order)->first();
};

/**
 * Returns only values from a single column
 *
 * @param string $table The name of the table, which should be queried
 * @param mixed $column The name of the column to select from
 * @param mixed $where The where clause. Can be a string or an array
 * @param mixed $order
 * @param int $offset
 * @param int $limit
 * @return mixed
 */
db::$queries['column'] = function($table, $column, $where = null, $order = null, $offset = 0, $limit = null) {
  return db::table($table)->where($where)->order($order)->offset($offset)->limit($limit)->column($column);
};

/**
 * Shortcut for inserting a new row into a table
 *
 * @param string $table The name of the table, which should be queried
 * @param string $values An array of values, which should be inserted
 * @return boolean
 */
db::$queries['insert'] = function($table, $values) {
  return db::table($table)->insert($values);
};

/**
 * Shortcut for updating a row in a table
 *
 * @param string $table The name of the table, which should be queried
 * @param string $values An array of values, which should be inserted
 * @param mixed $where An optional where clause
 * @return boolean
 */
db::$queries['update'] = function($table, $values, $where = null) {
  return db::table($table)->where($where)->update($values);
};

/**
 * Shortcut for deleting rows in a table
 *
 * @param string $table The name of the table, which should be queried
 * @param mixed $where An optional where clause
 * @return boolean
 */
db::$queries['delete'] = function($table, $where = null) {
  return db::table($table)->where($where)->delete();
};

/**
 * Shortcut for counting rows in a table
 *
 * @param string $table The name of the table, which should be queried
 * @param string $where An optional where clause
 * @return int
 */
db::$queries['count'] = function($table, $where = null) {
  return db::table($table)->where($where)->count();
};

/**
 * Shortcut for calculating the minimum value in a column
 *
 * @param string $table The name of the table, which should be queried
 * @param string $column The name of the column of which the minimum should be calculated
 * @param string $where An optional where clause
 * @return mixed
 */
db::$queries['min'] = function($table, $column, $where = null) {
  return db::table($table)->where($where)->min($column);
};

/**
 * Shortcut for calculating the maximum value in a column
 *
 * @param string $table The name of the table, which should be queried
 * @param string $column The name of the column of which the maximum should be calculated
 * @param string $where An optional where clause
 * @return mixed
 */
db::$queries['max'] = function($table, $column, $where = null) {
  return db::table($table)->where($where)->max($column);
};

/**
 * Shortcut for calculating the average value in a column
 *
 * @param string $table The name of the table, which should be queried
 * @param string $column The name of the column of which the average should be calculated
 * @param string $where An optional where clause
 * @return mixed
 */
db::$queries['avg'] = function($table, $column, $where = null) {
  return db::table($table)->where($where)->avg($column);
};

/**
 * Shortcut for calculating the sum of all values in a column
 *
 * @param string $table The name of the table, which should be queried
 * @param string $column The name of the column of which the sum should be calculated
 * @param string $where An optional where clause
 * @return mixed
 */
db::$queries['sum'] = function($table, $column, $where = null) {
  return db::table($table)->where($where)->sum($column);
};