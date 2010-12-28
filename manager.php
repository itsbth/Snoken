<?php
namespace SimpleORM;

/**
 * Static manager class.
 */
class Manager
{
  /**
   * @var \SimpleORM\Driver
   */
  private static $_driver;
  
  /**
   * Sets the driver.
   */
  public static function setDriver($driver)
  {
    self::$_driver = $driver;
  }
  
  /**
   * Gets the driver.
   */
  public static function getDriver()
  {
    return self::$_driver;
  }
  
  private static $_models = array();
  
  /**
   * Register a model.
   */
  public static function registerModel($model)
  {
    $table = $model::$table;
    self::$_models[$table] = $model;
  }
  
  /**
   * Generate and run the SQL to create all registered tables.
   */
  public static function createTables()
  {
    foreach(self::$_models as $table => $model)
    {
      $fields = $model::$fields;
      if (!isset($fields['id']))
        $fields['id'] = array('integer', 'primary' => true, 'autoincrement' => true);
      self::$_driver->createTable($table, $fields);
    }
  }
  
  private static $_cache = array();
  
  /**
   * Get record by table and id.
   */
  public static function getById($table, $id)
  {
    if (!isset(self::$_cache[$table])) self::$_cache[$table] = array();
    if (isset(self::$_cache[$table][$id])) return self::$_cache[$table][$id];
    $row = self::$_driver->select($table, array('id' => $id), null, 1)->one();
    if (!$row) return null;
    $class = self::$_models[$table];
    $object = new $class($row, $id);
    self::$_cache[$table][$id] = $object;
    return $object;
  }
  
  public static function select($table, $query, $order = null, $limit = null)
  {
    $res = self::$_driver->select($table, $query, $order, $limit);
    $class = self::$_models[$table];

    return new QueryResult($class, $res);
  }
}

class QueryResult
{
  private $_class;
  private $_resultSet;
  
  public function __construct($class, $resultSet)
  {
    $this->_class = $class;
    $this->_resultSet = $resultSet;
  }
  
  public function count()
  {
    return $this->_resultSet->count();
  }
  
  public function next()
  {
    $class = $this->_class;
    $row = $this->_resultSet->next();
    if(!$row) return null;
    return new $class($row, $row['id']);
  }
  
  public function one()
  {
    if ($this->count() > 1) throw new \Exception("More than one row returned.");
      return $this->next();
  }
}
