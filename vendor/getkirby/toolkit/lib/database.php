<?php

/**
 *
 * Database
 *
 * The ingenius Kirby Database class
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Database {

  public static $connectors = array();

  // a global array of started connections
  public static $connections = array();

  // the established connection
  protected $connection;

  // dsn
  protected $dsn;

  // the database type (mysql, sqlite)
  protected $type;

  // the connection id
  protected $id;

  // the optional prefix for table names
  protected $prefix;

  // the PDO query statement
  protected $statement;

  // whitelists for tables and their columns
  protected $tableWhitelist;
  protected $columnWhitelist = array();

  // the number of affected rows for the last query
  protected $affected;

  // the last insert id
  protected $lastId;

  // the last query
  protected $lastQuery;

  // the last result set
  protected $lastResult;

  // the last error
  protected $lastError;

  // set to true to throw exceptions on failed queries
  protected $fail = false;

  // an array with all queries which are being made
  protected $trace = array();

  /**
   * Constructor
   */
  public function __construct($params = null) {
    $this->connect($params);
  }

  /**
   * Returns one of the started instance
   *
   * @param string $id
   * @return object
   */
  public static function instance($id = null) {
    return (is_null($id)) ? a::last(static::$connections) : a::get(static::$connections, $id);
  }

  /**
   * Returns all started instances
   *
   * @return array
   */
  public static function instances() {
    return static::$connections;
  }

  /**
   * Connects to a database
   *
   * @param mixed $params This can either be a config key or an array of parameters for the connection
   * @return object
   */
  public function connect($params = null) {

    $defaults = array(
      'database' => null,
      'type'     => 'mysql',
      'prefix'   => null,
      'user'     => null,
      'password' => null,
      'id'       => uniqid()
    );

    $options = array_merge($defaults, $params);

    // store the database information
    $this->database = $options['database'];
    $this->type     = $options['type'];
    $this->prefix   = $options['prefix'];
    $this->id       = $options['id'];

    if(!isset(static::$connectors[$this->type])) {
      throw new Exception('Invalid database connector: ' . $this->type);
    }

    // fetch the dsn and store it
    $this->dsn = call_user_func(static::$connectors[$this->type], $options);

    // try to connect
    $this->connection = new PDO($this->dsn, $options['user'], $options['password']);
    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // store the connection
    static::$connections[$this->id] = $this;

    // return the connection
    return $this->connection;

  }

  /**
   * Returns the currently active connection
   *
   * @return object
   */
  public function connection() {
    return $this->connection;
  }

  /**
   * Sets the exception mode for the next query
   *
   * @param boolean $fail
   */
  public function fail($fail = true) {
    $this->fail = $fail;
    return $this;
  }

  /**
   * Returns the used database type
   *
   * @return string
   */
  public function type() {
    return $this->type;
  }

  /**
   * Returns the used table name prefix
   *
   * @return string
   */
  public function prefix() {
    return $this->prefix;
  }

  /**
   * Escapes a value to be used for a safe query
   * NOTE: Prepared statements using bound parameters are more secure and solid
   *
   * @param string $value
   * @return string
   */
  public function escape($value) {
    return substr($this->connection()->quote($value), 1, -1);
  }

  /**
   * Adds a value to the db trace and also returns the entire trace if nothing is specified
   *
   * @param array $data
   * @return array
   */
  public function trace($data = null) {
    if(is_null($data)) return $this->trace;
    $this->trace[] = $data;
  }

  /**
   * Returns the number of affected rows for the last query
   *
   * @return int
   */
  public function affected() {
    return $this->affected;
  }

  /**
   * Returns the last id if available
   *
   * @return int
   */
  public function lastId() {
    return $this->lastId;
  }

  /**
   * Returns the last query
   *
   * @return string
   */
  public function lastQuery() {
    return $this->lastQuery;
  }

  /**
   * Returns the last set of results
   *
   * @return mixed
   */
  public function lastResult() {
    return $this->lastResult;
  }

  /**
   * Returns the last db error (exception object)
   *
   * @return object
   */
  public function lastError() {
    return $this->lastError;
  }

  /**
   * Private method to execute database queries.
   * This is used by the query() and execute() methods
   *
   * @param string $query
   * @param array $bindings
   * @return mixed
   */
  protected function hit($query, $bindings = array()) {

    // try to prepare and execute the sql
    try {

      $this->statement = $this->connection->prepare($query);
      $this->statement->execute($bindings);

      $this->affected  = $this->statement->rowCount();
      $this->lastId    = $this->connection->lastInsertId();
      $this->lastError = null;

      // store the final sql to add it to the trace later
      $this->lastQuery = $this->statement->queryString;

    } catch(\Exception $e) {

      // store the error
      $this->affected  = 0;
      $this->lastError = $e;
      $this->lastId    = null;
      $this->lastQuery = $query;

      // only throw the extension if failing is allowed
      if($this->fail) throw $e;

    }

    // add a new entry to the singleton trace array
    $this->trace(array(
      'query'    => $this->lastQuery,
      'bindings' => $bindings,
      'error'    => $this->lastError
    ));

    // reset some stuff
    $this->fail = false;

    // return true or false on success or failure
    return is_null($this->lastError);

  }

  /**
   * Exectues a sql query, which is expected to return a set of results
   *
   * @param string $query
   * @param array $bindings
   * @param array $params
   * @return mixed
   */
  public function query($query, $bindings = array(), $params = array()) {

    $defaults = array(
      'flag'     => null,
      'method'   => 'fetchAll',
      'fetch'    => 'Obj',
      'iterator' => 'Collection',
    );

    $options = array_merge($defaults, $params);

    if(!$this->hit($query, $bindings)) return false;

    // define the default flag for the fetch method
    $flags = $options['fetch'] == 'array' ? PDO::FETCH_ASSOC : PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE;

    // add optional flags
    if(!empty($options['flag'])) $flags |= $options['flag'];

    // set the fetch mode
    if($options['fetch'] == 'array') {
      $this->statement->setFetchMode($flags);
    } else {
      $this->statement->setFetchMode($flags, $options['fetch']);
    }

    // fetch that stuff
    $results = $this->statement->{$options['method']}();

    if($options['iterator'] == 'array') return $this->lastResult = $results;
    return $this->lastResult = new $options['iterator']($results);

  }

  /**
   * Executes a sql query, which is expected to not return a set of results
   *
   * @param string $query
   * @param array $bindings
   * @return boolean
   */
  public function execute($query, $bindings = array()) {
    return $this->lastResult = $this->hit($query, $bindings);
  }

  /**
   * Sets the current table, which should be queried
   *
   * @param string $table
   * @return object Returns a Query object, which can be used to build a full query for that table
   */
  public function table($table) {
    return new Database\Query($this, $this->prefix() . $table);
  }

  /**
   * Checks if a table exists in the current database
   *
   * @param string $table
   * @return boolean
   */
  public function validateTable($table) {
    if(!$this->tableWhitelist) {
      // Get the table whitelist from the database
      $sql     = new SQL($this);
      $query   = $sql->tableList($this->database);
      $results = $this->query($query, $sql->bindings($query));
      
      if($results) {
        $this->tableWhitelist = $results->pluck('name');
      } else {
        return false;
      }
    }
    
    return in_array($table, $this->tableWhitelist);
  }
  
  /**
   * Checks if a column exists in a specified table
   *
   * @param string $table
   * @param string $column
   * @return boolean
   */
  public function validateColumn($table, $column) {
    if(!isset($this->columnWhitelist[$table])) {
      if(!$this->validateTable($table)) {
        $this->columnWhitelist[$table] = array();
        return false;
      }
      
      // Get the column whitelist from the database
      $sql     = new SQL($this);
      $query   = $sql->columnList($this->database, $table);
      $results = $this->query($query, $sql->bindings($query));
      
      if($results) {
        $this->columnWhitelist[$table] = $results->pluck('name');
      } else {
        return false;
      }
    }
    
    return in_array($column, $this->columnWhitelist[$table]);
  }

  /**
   * Creates a new table
   *
   * @param string $table
   * @param array $columns
   * @return boolean
   */
  public function createTable($table, $columns = array()) {
    $sql     = new SQL($this);
    $query   = $sql->createTable($table, $columns);
    $queries = str::split($query, ';');

    foreach($queries as $query) {
      $query = trim($query);
      
      if(!$this->execute($query, $sql->bindings($query))) return false;
    }

    return true;

  }

  /**
   * Drops a table
   *
   * @param string $table
   * @return boolean
   */
  public function dropTable($table) {
    $sql   = new SQL($this);
    $query = $sql->dropTable($table);
    return $this->execute($query, $sql->bindings($query));
  }

  /**
   * Magic way to start queries for tables by
   * using a method named like the table.
   * I.e. $db->users()->all()
   */
  public function __call($method, $arguments = null) {
    return $this->table($method);
  }

}


/**
 * MySQL database connector
 */
database::$connectors['mysql'] = function($params) {

  if(!isset($params['host']) && !isset($params['socket'])) {
    throw new Error('The mysql connection requires either a "host" or a "socket" parameter');
  } 
  
  if(!isset($params['database'])) {
    throw new Error('The mysql connection requires a "database" parameter');
  }

  $parts = array();

  if(!empty($params['host'])) {
    $parts[] = 'host=' . $params['host'];
  }

  if(!empty($params['port'])) {
    $parts[] = 'port=' . $params['port'];
  }

  if(!empty($params['socket'])) {
    $parts[] = 'unix_socket=' . $params['socket'];
  }

  if(!empty($params['database'])) {
    $parts[] = 'dbname=' . $params['database'];
  }

  $parts[] = 'charset=' . a::get($params, 'charset', 'utf8');

  return 'mysql:' . implode(';', $parts);

};


/**
 * SQLite database connector
 */
database::$connectors['sqlite'] = function($params) {
  if(!isset($params['database'])) throw new Error('The sqlite connection requires a "database" parameter');
  return 'sqlite:' . $params['database'];
};
