<?php

namespace Database;

use A;
use Error;
use Pagination;
use Str;
use Sql;

/**
 *
 * Database Query
 *
 * The query builder is used by the Database class
 * to build SQL queries in a fluent, jquery-style way
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Query {

  const ERROR_INVALID_QUERY_METHOD = 0;

  protected $database = null;

  // The object which should be fetched for each row
  protected $fetch = 'Obj';

  // The iterator class, which should be used for result sets
  protected $iterator = 'Collection';

  // An array of bindings for the final query
  protected $bindings = array();

  // The table name
  protected $table;

  // The name of the primary key column
  protected $primaryKeyName = 'id';

  // An array with additional join parameters
  protected $join;

  // A list of columns, which should be selected
  protected $select;

  // Boolean for distinct select clauses
  protected $distinct;

  // Boolean for if exceptions should be thrown on failing queries
  protected $fail = false;

  // A list of values for update and insert clauses
  protected $values;

  // WHERE clause
  protected $where;

  // GROUP BY clause
  protected $group;

  // HAVING clause
  protected $having;

  // ORDER BY clause
  protected $order;

  // The offset, which should be applied to the select query
  protected $offset = 0;

  // The limit, which should be applied to the select query
  protected $limit;

  // Boolean to enable query debugging
  protected $debug = false;

  /**
   * Constructor
   *
   * @param Database $database Database object
   * @param string $table Optional name of the table, which should be queried
   */
  public function __construct($database, $table) {
    $this->database = $database;
    
    $this->table($table);
    if(!$this->table) throw new Error('Invalid table ' . $table);
  }

  /**
   * Reset the query class after each db hit
   */
  protected function reset() {
    $this->bindings = array();
    $this->join     = null;
    $this->select   = null;
    $this->distinct = null;
    $this->fail     = false;
    $this->values   = null;
    $this->where    = null;
    $this->group    = null;
    $this->having   = null;
    $this->order    = null;
    $this->offset   = 0;
    $this->limit    = null;
    $this->debug    = false;
  }

  /**
   * Enables query debugging.
   * If enabled, the query will return an array with all important info about
   * the query instead of actually executing the query and returning results
   *
   * @param boolean $debug
   * @return object
   */
  public function debug($debug = true) {
    $this->debug = $debug;
    return $this;
  }

  /**
   * Enables distinct select clauses.
   *
   * @param boolean $distinct
   * @return object
   */
  public function distinct($distinct = true) {
    $this->distinct = $distinct;
    return $this;
  }

  /**
   * Enables failing queries.
   * If enabled queries will no longer fail silently but throw an exception
   *
   * @param boolean $fail
   * @return object
   */
  public function fail($fail = true) {
    $this->fail = $fail;
    return $this;
  }

  /**
   * Sets the object class, which should be fetched
   * Set this to array to get a simple array instead of an object
   *
   * @param string $fetch
   * @return object
   */
  public function fetch($fetch) {
    if(!is_null($fetch)) $this->fetch = $fetch;
    return $this;
  }

  /**
   * Sets the iterator class, which should be used for multiple results
   * Set this to array to get a simple array instead of an iterator object
   *
   * @param string $iterator
   * @return object
   */
  public function iterator($iterator) {
    if(!is_null($iterator)) $this->iterator = $iterator;
    return $this;
  }

  /**
   * Sets the name of the table, which should be queried
   *
   * @param string $table
   * @return object
   */
  public function table($table) {
    if(!is_null($table) && $this->database->validateTable($table)) $this->table = $table;
    return $this;
  }

  /**
   * Sets the name of the primary key column
   *
   * @param string $primaryKeyName
   * @return object
   */
  public function primaryKeyName($primaryKeyName) {
    $this->primaryKeyName = $primaryKeyName;
    return $this;
  }

  /**
   * Sets the columns, which should be selected from the table
   * By default all columns will be selected
   *
   * @param mixed $select Pass either a string of columns or an array
   * @return object
   */
  public function select($select) {
    $this->select = $select;
    return $this;
  }

  /**
   * Adds a new join clause to the query
   *
   * @param string $table Name of the table, which should be joined
   * @param string $on The on clause for this join
   * @param string $type The join type. Uses an inner join by default
   * @return object
   */
  public function join($table, $on, $type = '') {

    $join = array(
      'table' => $table,
      'on'    => $on,
      'type'  => $type
    );

    $this->join[] = $join;
    return $this;

  }

  /**
   * Shortcut for creating a left join clause
   *
   * @param string $table Name of the table, which should be joined
   * @param string $on The on clause for this join
   * @return object
   */
  public function leftJoin($table, $on) {
    return $this->join($table, $on, 'left');
  }

  /**
   * Shortcut for creating a right join clause
   *
   * @param string $table Name of the table, which should be joined
   * @param string $on The on clause for this join
   * @return object
   */
  public function rightJoin($table, $on) {
    return $this->join($table, $on, 'right');
  }

  /**
   * Shortcut for creating an inner join clause
   *
   * @param string $table Name of the table, which should be joined
   * @param string $on The on clause for this join
   * @return object
   */
  public function innerJoin($table, $on) {
    return $this->join($table, $on, 'inner');
  }

  /**
   * Sets the values which should be used for the update or insert clause
   *
   * @param mixed $values Can either be a string or an array of values
   * @return object
   */
  public function values($values = array()) {
    if(!is_null($values)) $this->values = $values;
    return $this;
  }

  /**
   * Attaches additional bindings to the query.
   * Also can be used as getter for all attached bindings by not passing an argument.
   *
   * @param mixed $bindings Array of bindings or null to use this method as getter
   * @return mixed
   */
  public function bindings($bindings = null) {

    if(is_array($bindings)) {
      $this->bindings = array_merge($this->bindings, $bindings);
      return $this;
    }

    return $this->bindings;

  }

  /**
   * Attaches an additional where clause
   *
   * All available ways to add where clauses
   *
   * ->where('username like "myuser"');                        (args: 1)
   * ->where(array('username' => 'myuser'));                   (args: 1)
   * ->where(function($where) { $where->where('id', '=', 1) }) (args: 1)
   * ->where('username like ?', 'myuser')                      (args: 2)
   * ->where('username', 'like', 'myuser');                    (args: 3)
   *
   * @param list
   * @return object
   */
  public function where() {
    $this->where = $this->filterQuery(func_get_args(), $this->where);
    return $this;
  }

  /**
   * Shortcut to attach a where clause with an OR operator.
   * Check out the where() method docs for additional info.
   *
   * @param list
   * @return object
   */
  public function orWhere() {

    $args = func_get_args();
    $mode = a::last($args);

    // if there's a where clause mode attribute attached…
    if(in_array($mode, array('AND', 'OR'))) {
      // remove that from the list of arguments
      array_pop($args);
    }

    // make sure to always attach the OR mode indicator
    $args[] = 'OR';

    call_user_func_array(array($this, 'where'), $args);
    return $this;
  }

  /**
   * Shortcut to attach a where clause with an AND operator.
   * Check out the where() method docs for additional info.
   *
   * @param list
   * @return object
   */
  public function andWhere() {

    $args = func_get_args();
    $mode = a::last($args);

    // if there's a where clause mode attribute attached…
    if(in_array($mode, array('AND', 'OR'))) {
      // remove that from the list of arguments
      array_pop($args);
    }

    // make sure to always attach the AND mode indicator
    $args[] = 'AND';

    call_user_func_array(array($this, 'where'), func_get_args());
    return $this;
  }

  /**
   * Attaches a group by clause
   *
   * @param string $group
   * @return object
   */
  public function group($group) {
    $this->group = $group;
    return $this;
  }

  /**
   * Attaches an additional having clause
   *
   * All available ways to add having clauses
   *
   * ->having('username like "myuser"');                           (args: 1)
   * ->having(array('username' => 'myuser'));                      (args: 1)
   * ->having(function($having) { $having->having('id', '=', 1) }) (args: 1)
   * ->having('username like ?', 'myuser')                         (args: 2)
   * ->having('username', 'like', 'myuser');                       (args: 3)
   *
   * @param list
   * @return object
   */
  public function having() {
    $this->having = $this->filterQuery(func_get_args(), $this->having);
    return $this;
  }

  /**
   * Attaches an order clause
   *
   * @param string $order
   * @return object
   */
  public function order($order) {
    $this->order = $order;
    return $this;
  }

  /**
   * Sets the offset for select clauses
   *
   * @param int $offset
   * @return object
   */
  public function offset($offset) {
    $this->offset = $offset;
    return $this;
  }

  /**
   * Sets the limit for select clauses
   *
   * @param int $limit
   * @return object
   */
  public function limit($limit) {
    $this->limit = $limit;
    return $this;
  }

  /**
   * Builds the different types of SQL queries
   * This uses the SQL class to build stuff.
   *
   * @param string $type (select, update, insert)
   * @return string The final query
   */
  public function build($type) {

    $sql = new SQL($this->database, $this);

    switch($type) {
      case 'select':

        return $sql->select(array(
          'table'    => $this->table,
          'columns'  => $this->select,
          'join'     => $this->join,
          'distinct' => $this->distinct,
          'where'    => $this->where,
          'group'    => $this->group,
          'having'   => $this->having,
          'order'    => $this->order,
          'offset'   => $this->offset,
          'limit'    => $this->limit
        ));

      case 'update':

        return $sql->update(array(
          'table'  => $this->table,
          'where'  => $this->where,
          'values' => $this->values,
        ));

      case 'insert':

        return $sql->insert(array(
          'table'  => $this->table,
          'values' => $this->values,
        ));

      case 'delete':

        return $sql->delete(array(
          'table' => $this->table,
          'where' => $this->where,
        ));

    }

  }

  /**
   * Builds a count query
   *
   * @return object
   */
  public function count() {
    return $this->aggregate('COUNT');
  }

  /**
   * Builds a max query
   *
   * @param string $column
   * @return object
   */
  public function max($column) {
    return $this->aggregate('MAX', $column);
  }

  /**
   * Builds a min query
   *
   * @param string $column
   * @return object
   */
  public function min($column) {
    return $this->aggregate('MIN', $column);
  }

  /**
   * Builds a sum query
   *
   * @param string $column
   * @return object
   */
  public function sum($column) {
    return $this->aggregate('SUM', $column);
  }

  /**
   * Builds an average query
   *
   * @param string $column
   * @return object
   */
  public function avg($column) {
    return $this->aggregate('AVG', $column);
  }

  /**
   * Builds an aggregation query.
   * This is used by all the aggregation methods above
   *
   * @param string $method
   * @param string $column
   * @param string $default An optional default value, which should be returned if the query fails
   * @return object
   */
  public function aggregate($method, $column = '*', $default = 0) {

    // reset the sorting to avoid counting issues
    $this->order = null;

    // validate column
    if($column !== '*') {
      $sql = new SQL($this->database, $this);
      list($table, $columnPart) = $sql->splitIdentifier($this->table, $column);
      if(!$this->database->validateColumn($table, $columnPart)) {
        throw new Error('Invalid column ' . $column);
      }
      
      $column = $sql->combineIdentifier($table, $columnPart);
    }

    $fetch  = $this->fetch;
    $row    = $this->select($method . '(' . $column . ') as aggregation')->fetch('Obj')->first();
    if($this->debug) return $row;
    $result = $row ? $row->get('aggregation') : $default;
    $this->fetch($fetch);
    return $result;
  }

  /**
   * Used as an internal shortcut for firing a db query
   *
   * @param string $query
   * @param array $params
   * @return mixed
   */
  protected function query($query, $params = array()) {

    if($this->debug) return array(
      'query'    => $query,
      'bindings' => $this->bindings(),
      'options'  => $params
    );

    if($this->fail) $this->database->fail();

    $result = $this->database->query($query, $this->bindings(), $params);
    $this->reset();
    return $result;

  }

  /**
   * Used as an internal shortcut for executing a db query
   *
   * @param string $query
   * @param array $params
   * @return mixed
   */
  protected function execute($query, $params = array()) {

    if($this->debug) return array(
      'query'    => $query,
      'bindings' => $this->bindings(),
      'options'  => $params
    );

    if($this->fail) $this->database->fail();

    $result = $this->database->execute($query, $this->bindings(), $params);
    $this->reset();
    return $result;

  }

  /**
   * Selects only one row from a table
   *
   * @return object
   */
  public function first() {
    return $this->query($this->offset(0)->limit(1)->build('select'), array(
      'fetch'    => $this->fetch,
      'iterator' => 'array',
      'method'   => 'fetch',
    ));
  }

  /**
   * Selects only one row from a table
   *
   * @return object
   */
  public function row() {
    return $this->first();
  }

  /**
   * Selects only one row from a table
   *
   * @return object
   */
  public function one() {
    return $this->first();
  }

  /**
   * Automatically adds pagination to a query
   *
   * @param int $page
   * @param int $limit The number of rows, which should be returned for each page
   * @param array $params Optional params for the pagination object
   * @return object Collection iterator with attached pagination object
   */
  public function page($page, $limit, $params = array()) {

    $defaults = array(
      'page' => $page
    );

    $options = array_merge($defaults, $params);

    // clone this to create a counter query
    $counter = clone $this;

    // count the total number of rows for this query
    $count = $counter->debug(false)->count();

    // pagination
    $pagination = new Pagination($count, $limit, $options);

    // apply it to the dataset and retrieve all rows. make sure to use Collection as the iterator to be able to attach the pagination object
    $iterator = $this->iterator;
    $collection = $this->offset($pagination->offset())->limit($pagination->limit())->iterator('Collection')->all();
    $this->iterator($iterator);

    // return debug information if debug mode is active
    if($this->debug) {
      $collection['totalcount'] = $count;
      return $collection;
    }

    // store all pagination vars in a separate object
    if($collection) $collection->paginate($pagination);

    // return the limited collection
    return $collection;

  }

  /**
   * Returns all matching rows from a table
   *
   * @return mixed
   */
  public function all() {

    return $this->query($this->build('select'), array(
      'fetch'    => $this->fetch,
      'iterator' => $this->iterator,
    ));
  }

  /**
   * Returns only values from a single column
   *
   * @param string $column
   * @return mixed
   */
  public function column($column) {

    $sql = new SQL($this->database, $this);
    $primaryKey = $sql->combineIdentifier($this->table, $this->primaryKeyName);
    
    $results = $this->query($this->select(array($column))->order($primaryKey . ' ASC')->build('select'), array(
      'iterator' => 'array',
      'fetch'    => 'array',
    ));
    if($this->debug) return $results;

    $results = a::extract($results, $column);

    if($this->iterator == 'array') return $results;

    $iterator = $this->iterator;
    return new $iterator($results);

  }

  /**
   * Find a single row by column and value
   *
   * @param string $column
   * @param mixed $value
   * @return mixed
   */
  public function findBy($column, $value) {
    return $this->where(array($column => $value))->first();
  }

  /**
   * Find a single row by its primary key
   *
   * @param mixed $id
   * @return mixed
   */
  public function find($id) {
    return $this->findBy($this->primaryKeyName, $id);
  }

  /**
   * Fires an insert query
   *
   * @param array $values You can pass values here or set them with ->values() before
   * @return mixed Returns the last inserted id on success or false.
   */
  public function insert($values = null) {
    $query = $this->execute($this->values($values)->build('insert'));
    if($this->debug) return $query;
    return ($query) ? $this->database->lastId() : false;
  }

  /**
   * Fires an update query
   *
   * @param array $values You can pass values here or set them with ->values() before
   * @param mixed $where You can pass a where clause here or set it with ->where() before
   * @return boolean
   */
  public function update($values = null, $where = null) {
    return $this->execute($this->values($values)->where($where)->build('update'));
  }

  /**
   * Fires a delete query
   *
   * @param mixed $where You can pass a where clause here or set it with ->where() before
   * @return boolean
   */
  public function delete($where = null) {
    return $this->execute($this->where($where)->build('delete'));
  }

  /**
   * Enables magic queries like findByUsername or findByEmail
   *
   * @param string $method
   * @param array $arguments
   * @return mixed
   */
  public function __call($method, $arguments) {

    if(preg_match('!^findBy([a-z]+)!i', $method, $match)) {
      $column = str::lower($match[1]);
      return $this->findBy($column, $arguments[0]);
    } else {
      throw new Error('Invalid query method: ' . $method, static::ERROR_INVALID_QUERY_METHOD);
    }

  }
  
  /**
   * Builder for where and having clauses
   *
   * @param array $args Arguments, see where() description
   * @param string $current Current value (like $this->where)
   * @return string
   */
  protected function filterQuery($args, $current) {

    $mode  = a::last($args);
    $result = '';

    // if there's a where clause mode attribute attached…
    if(in_array($mode, array('AND', 'OR'))) {
      // remove that from the list of arguments
      array_pop($args);
    } else {
      $mode = 'AND';
    }

    switch(count($args)) {
      case 1:

        if(is_null($args[0])) {

          return $current;

        // ->where('username like "myuser"');
        } else if(is_string($args[0])) {

          // simply add the entire string to the where clause
          // escaping or using bindings has to be done before calling this method
          $result = $args[0];

        // ->where(array('username' => 'myuser'));
        } else if(is_array($args[0])) {

          $sql = new SQL($this->database, $this);

          // simple array mode (AND operator)
          $result = $sql->values($this->table, $args[0], ' AND ', true, true);

        } else if(is_callable($args[0])) {

          $query = clone $this;
          call_user_func($args[0], $query);
          $result = '(' . $query->where . ')';

        }

        break;
      case 2:

        // ->where('username like :username', array('username' => 'myuser'))
        if(is_string($args[0]) && is_array($args[1])) {

          // prepared where clause
          $result = $args[0];

          // store the bindings
          $this->bindings($args[1]);

        // ->where('username like ?', 'myuser')
        } else if(is_string($args[0]) && is_string($args[1])) {

          // prepared where clause
          $result = $args[0];

          // store the bindings
          $this->bindings(array($args[1]));

        }

        break;
      case 3:

        // ->where('username', 'like', 'myuser');
        if(is_string($args[0]) && is_string($args[1])) {
          
          // validate column
          $sql = new SQL($this->database, $this);
          list($table, $column) = $sql->splitIdentifier($this->table, $args[0]);
          if(!$this->database->validateColumn($table, $column)) {
            throw new Error('Invalid column ' . $args[0]);
          }
          $key = $sql->combineIdentifier($table, $column);

          // ->where('username', 'in', array('myuser', 'myotheruser'));
          if(is_array($args[2])) {

            $predicate = trim(strtoupper($args[1]));
            if(!in_array($predicate, array(
              'IN', 'NOT IN'
            ))) throw new Error('Invalid predicate ' . $predicate);

            // build a list of bound values
            $values   = array();
            $bindings = array();
            foreach($args[2] as $value) {
              $valueBinding = $sql->generateBindingName('value');
              $bindings[$valueBinding] = $value;
              $values[] = $valueBinding;
            }

            // add that to the where clause in parenthesis
            $result = $key . ' ' . $predicate . ' (' . implode(', ', $values) . ')';

            $this->bindings($bindings);

          // ->where('username', 'like', 'myuser');
          } else {

            $predicate = trim(strtoupper($args[1]));
            if(!in_array($predicate, array(
              '=', '>=', '>', '<=', '<', '<>', '!=', '<=>',
              'IS', 'IS NOT',
              'BETWEEN', 'NOT BETWEEN',
              'LIKE', 'NOT LIKE',
              'SOUNDS LIKE',
              'REGEXP', 'NOT REGEXP'
            ))) throw new Error('Invalid predicate/operator ' . $predicate);
              
            $valueBinding = $sql->generateBindingName('value');
            $bindings[$valueBinding] = $args[2];
            
            $result = $key . ' ' . $predicate . ' ' . $valueBinding;
            
            $this->bindings($bindings);

          }

        }

        break;

    }

    // attach the where clause
    if(!empty($current)) {
      return $current . ' ' . $mode . ' ' . $result;
    } else {
      return $result;
    }

  }

}
