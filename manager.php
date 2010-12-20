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
  
  /**
   * Get record by table and id.
   */
  public static function getById($table, $id)
  {
    $row = self::$_driver->select($table, array('id' => $id), null, 1)->fetch(\PDO::FETCH_ASSOC);
    if (!$row) return null;
    $class = self::$_models[$table];
    return new $class($row, $id);
  }
  
  public static function select($table, $query, $order = null, $limit = null)
  {
    $rows = self::$_driver->select($table, $query, $order, $limit)->fetchArray(\PDO::FETCH_ASSOC);
    $class = self::$_models[$table];
    $out = array();
    foreach ($rows as $row)
    {
      $out[] = new $class($row, $id);
    }
    return $out;
  }
  
}
