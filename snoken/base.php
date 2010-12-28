<?php
namespace Snoken;

class Base
{
  private $_id;
  private $_fields;
  private $_changed;
  private $_cache;
  
  public function __construct($fields = array(), $id = null)
  {
    $this->_fields = $fields;
    $this->_id = $id;
    $this->_changed = array();
    $this->_cache = array();
  }
  
  public function __get($field)
  {
    if ($field == 'id')
      return $this->_id;
    if (isset($this->_fields[$field]))
      return $this->_fields[$field];
    if (isset(static::$fields[$field]))
      return null;    
    if (isset(static::$has[$field]))
      return Manager::select(static::$has[$field][0], array(static::$has[$field]['field'] => $this->_id));
    if (isset(static::$belongs_to[$field]))
      return Manager::getById(static::$belongs_to[$field][0], $this->{$belongs_to[$field]['field']});
    // TODO: Create new exception
    throw new \SnokenException("Field '{$field}' not in model.");
  }
  
  public function __set($field, $value)
  {
    if ($field == 'id') throw new \SnokenException("Field 'id' can not be modified.");
    // TODO: Create new exception
    if (isset(static::$fields[$field]))
    {
      if (isset($this->_fields[$field]) && $this->_fields[$field] === $value) return; 
      $this->_fields[$field] = $value;
      if (!in_array($field, $this->_changed))
        $this->_changed[] = $field;
    }
    else if (isset(static::$belongs_to[$field]))
    {
      $info = static::$belongs_to[$field];
      $field = $info['field'];
      echo $field, "\n";
      if (!$value->saved()) $value->save();
      $this->{$field} = $value->id;
    }
    else
    {
      throw new \SnokenException("Field '{$field}' not in model.");
    }
  }
  
  public function validate(&$errors = null)
  {
    $errors = array();
    foreach (static::$fields as $name => $field)
    {
      $type = array_shift($field);
      $value = isset($this->_fields[$name]) ? $this->_fields[$name] : null;
      if (isset($field['required']) && $field['required'] && $value == null)
      {
        $errors[] = "Field {$name} is required.";
      }
      else if ($type == 'integer')
      {
        if (!is_numeric($value))
          $errors[] = "Field {$name} is not an integer.";
      }
      else if ($type == 'string')
      {
        if (isset($field['max_length']) && strlen($value) > $field['max_length'])
        {
          $errors[] = "Field {$name} is exceeding maximum length.";
        }
      }
    }
    return count($errors) == 0;
  }
  
  public function saved()
  {
    return $this->_id != null;
  }
  
  public function save()
  {
    $errors = null;
    if (!$this->validate($errors))
    {
      print_r($errors);
      throw new \SnokenException("Object is not valid.");
    }
    /*
    foreach (static::$belongs_to as $name => $info)
    {
      if (!isset($this->_fields[$name])) continue;
      $type = array_shift($info);
      $field = $info['field'];
      $object = $this->{$name};
      // TODO: Fix case
      // if (get_class($object) != )
      if (!$object->id)
        $object->save();
      $this->{$field} = $object->id;
    }
    */
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
    if (!$this->_id) throw new \SnokenException("You must save it first, you dolt.");
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
  
  protected static $has = array();
  protected static $belongs_to = array();

}

class SnokenException extends \Exception
{
}
