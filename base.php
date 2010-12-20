<?php
namespace SimpleORM;

class Base
{
  private $_id;
  private $_fields;
  private $_changed;
  
  public function __construct($fields = array(), $id = null)
  {
    $this->_fields = $fields;
    $this->_id = $id;
    $this->_changed = array();
  }
  
  public function __get($field)
  {
    if ($field == 'id') return $this->_id;
    if (isset($this->_fields[$field])) return $this->_fields[$field];
    if (isset(static::$fields[$field])) return null;
    // TODO: Create new exception
    throw new Exception("Field '{$field}' not in model.");
  }
  
  public function __set($field, $value)
  {
    if ($field == 'id') throw new Exception("Field 'id' can not be modified.");
    // TODO: Create new exception
    if (!isset(static::$fields[$field])) throw new Exception("Field '{$field}' not in model.");
    if (isset($this->_fields[$field]) && $this->_fields[$field] === $value) return; 
    $this->_fields[$field] = $value;
    if (!in_array($field, $this->_changed))
      $this->_changed[] = $field;
  }
  
  public function validate()
  {
    // TODO: Implement validation
    return true;
  }
  
  public function save()
  {
    if (!$this->validate()) throw new Exception("INVALID KEKEKE");
    if (count($this->_changed) == 0) return;
    $fields = array();
    foreach ($this->_changed as $field)
    {
      $fields[$field] = $this->_fields[$field];
    }
    if ($this->_id)
    {
      Manager::getDriver()->update(static::$table, $fields, array('id' => $this->_id));
    }
    else
    {
      $this->_id = Manager::getDriver()->insert(static::$table, $fields);
    }
  }
  
  public function delete()
  {
    if (!$this->_id) throw new Exception("You must save it first, you dolt.");
    Manager::getDriver()->delete(static::$table, array('id' => $this->_id));
  }
  
  public static function getById($id)
  {
    return Manager::getById(static::$table, $id);
  }
  
  public static function select($query, $order = null, $limit = null)
  {
    return Manager::select(static::$table, $query, $order, $limit);
  }

}
