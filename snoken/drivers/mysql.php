<?php
namespace Snoken\Drivers;

class SQLiteDriver extends Driver
{
  
  /***
   * @type \PDO
   */
  private $_connection;
  
  public function __construct($dsn)
  {
    $this->_connection = new \PDO("mysql:{$conn}");
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
    {
      if ($order[0] == '-')
        $order = substr($order, 1) . " DESC";
      $sql .= " ORDER BY {$order}";
    }
    if ($limit)
      $sql .= " LIMIT {$limit}";
    $sql .= ";";
    echo $sql, "\n";
    print_r($cond);
    $stmnt = $this->_connection->prepare($sql);
    if (!$stmnt) print_r($this->_connection->errorInfo());
    $stmnt->execute($cond);
    return new PDOResultSet($stmnt);
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
    throw new \Snoken\SnokenException("MySQL driver does not support table generation yet.");
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

class PDOResultSet extends ResultSet
{
  private $_stmnt;
  public function __construct($stmnt)
  {
    $this->_stmnt = $stmnt;
  }
  public function count()
  {
    return $this->_stmnt->rowCount();
  }
  public function next()
  {
    return $this->_stmnt->fetch(\PDO::FETCH_ASSOC);
  }
}
