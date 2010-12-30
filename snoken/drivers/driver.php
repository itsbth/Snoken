<?php
namespace Snoken\Drivers;

abstract class Driver
{
  // CRUD
  public abstract function insert($table, $fields);
  public abstract function select($table, $condition, $order = null, $limit = null);
  public abstract function update($table, $fields, $condition);
  public abstract function delete($table, $condition);
  
  public abstract function createTable($table, $fields); 
}

abstract class ResultSet
{
  public abstract function count();
  public abstract function next();
  
  public function one()
  {    
    if ($this->count() > 1) throw new \Exception("More than one row returned.");
    return $this->next();
  } 
}
