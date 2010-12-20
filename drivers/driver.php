<?php
namespace SimpleORM\Drivers;

abstract class Driver
{
  // CRUD
  public abstract function insert($table, $fields);
  public abstract function select($table, $condition, $order = null, $limit = null);
  public abstract function update($table, $fields, $condition);
  public abstract function delete($table, $condition);
  
  public abstract function createTable($table, $fields); 
}
