<?php
require_once 'drivers/driver.php';
require_once 'drivers/sqlite.php';
require_once 'manager.php';
require_once 'base.php';

class User extends SimpleORM\Base
{
  public static $table = 'user';
  public static $fields = array(
    'name' => array('string', 'max_length' => 64, 'required' => true),
    'age' => array('integer', 'required' => true),
    'email' => array('string', 'max_length' => 128, 'required' => true),
  );
}

$driver = new SimpleORM\Drivers\SQLiteDriver("sqlite:/home/itsbth/Aptana Studio 3 Workspace/SimpleORM/test.db");
SimpleORM\Manager::setDriver($driver);
SimpleORM\Manager::registerModel('User');
//SimpleORM\Manager::createTables();
//echo $driver->insert('user', array('name' => 'itsbth', 'age' => 19, 'email' => 'itsbth@itsbth.com')), "\n";
$user = new User();
$user->name = 'itsbth';
$user->age = 19;
$user->email = 'itsbth@itsbth.com';
$user->save();
echo $user->id, "\n";
$user2 = SimpleORM\Manager::getById('user', $user->id);
echo $user2->name, "\n";
$user2->age = 20;
$user2->save();
$user2->delete();
