<?php
namespace SimpleORM\Drivers;

class SQLiteDriver extends Driver
{
  
  /***
   * @type \PDO
   */
  private $_connection;
  
  public function __construct($conn)
  {
    $this->_connection = new \PDO("sqlite:{$conn}");
    $this->_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
  }
  
  public function insert($table, $fields)
  {
    $fieldnames = implode(', ', array_keys($fields));
    $values = implode(', ', array_fill(0, count($fields), '?'));
    $sql = "INSERT INTO {$table} ($fieldnames) VALUES ($values);";
    $stmnt = $this->_connection->prepare($sql);
    if (!$stmnt) print_r($this->_connection->errorInfo());
    $stmnt->execute(array_values($fields));
    return $this->_connection->lastInsertId();
  }
  public function select($table, $condition, $order = null, $limit = null)
  {
    $cond = $this->conditionToSQL($condition);
    $sql = "SELECT * FROM {$table} WHERE " . array_shift($cond);
    if ($order)
      $sql .= " ORDER BY {$order}";
    if ($limit)
      $sql .= " LIMIT {$limit}";
    $sql .= ";";
    $stmnt = $this->_connection->prepare($sql);
    if (!$stmnt) print_r($this->_connection->errorInfo());
    $stmnt->execute($cond);
    return $stmnt;
  }
  public function update($table, $fields, $condition)
  {
    $fieldnames = implode(', ', array_keys($fields));
    $values = array();
    foreach ($fields as $name => $value)
    {
      $values[] = "{$name} = ?";
    }
    $values = implode(', ', $values);
    $cond = $this->conditionToSQL($condition);
    $where = array_shift($cond);
    $sql = "UPDATE {$table} SET {$values} WHERE {$where};";
    $stmnt = $this->_connection->prepare($sql);
    if (!$stmnt) print_r($this->_connection->errorInfo());
    $stmnt->execute(array_merge(array_values($fields), $cond));
  }
  public function delete($table, $condition)
  {
    $cond = $this->conditionToSQL($condition);
    $sql = "DELETE FROM {$table} WHERE " . array_shift($cond) . ";";
    $stmnt = $this->_connection->prepare($sql);
    if (!$stmnt) print_r($this->_connection->errorInfo());
    $stmnt->execute($cond);
  }
  
  public function createTable($table, $fields)
  {
    $sql = array();
    foreach ($fields as $name => $options)
    {
      $type = array_shift($options);
      // SQLite is not type-safe, pass it as-is
      $field = array("{$name} {$type}");
      if (isset($options['primary']) && $options['primary'])
      {
        $field[] = "PRIMARY KEY";
      }
      if (isset($options['autoincrement']) && $options['autoincrement'])
      {
        $field[] = "AUTOINCREMENT";
      }
      if (isset($options['required']) && $options['required'])
      {
        $field[] = "NOT NULL";
      }
      if (isset($options['unique']) && $options['unique'])
      {
        $field[] = "UNIQUE";
      }
      $sql[] = implode(' ', $field);
    }
    $this->_connection->exec(sprintf("CREATE TABLE %s (%s);", $table, implode(', ', $sql)));
  }
  
  private function conditionToSQL($condition)
  {
    // Raw SQL
    if (is_string($condition)) return array($condition);
    // Prepared (array($condition, $values))
    if (isset($condition[0])) return $condition;
    $out = array();
    foreach ($condition as $field => $value)
    {
      $out[] = "{$field} = ?";
    }
    return array_merge(array(implode(' AND ', $out)), array_values($condition));
  }
}
