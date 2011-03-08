<?php
error_reporting(E_ALL | E_NOTICE);
require_once 'snoken/drivers/driver.php';
require_once 'snoken/drivers/sqlite.php';
require_once 'snoken/manager.php';
require_once 'snoken/base.php';

class User extends Snoken\Base
{
  public static $table = 'user';
  public static $fields = array(
    'name' => array('string', 'max_length' => 64, 'required' => true),
    'age' => array('integer', 'required' => true),
    'email' => array('string', 'max_length' => 128, 'required' => true),
  );
  public static $has = array(
    'posts' => array('post', 'field' => 'user_id', /* OTHER OPTIONS */),
  );
}

class Post extends Snoken\Base
{
  public static $table = 'post';
  public static $fields = array(
    'title' => array('string', 'max_length' => 64, 'required' => true, 'filter' => 'title_filter'),
    'content' => array('text', 'max_length' => 4096, 'required' => true, 'validator' => 'content_valid'),
    'user_id' => array('integer', 'required' => true),
  );
  public static $belongs_to = array(
    'user' => array('user', 'field' => 'user_id', /* OTHER OPTIONS */),
  );
  
  protected function title_filter($name, $value)
  {
  	return strtoupper($value); // CAPS LOCK IS CRUISE CONTROL FOR COOL
  }
  
  protected function content_valid($content, &$error)
  {
  	$error = "I don't like it.";
  	return true;
  }
  
  protected function before_save()
  {
  	$this->title .= '!';
  	return true;
  }
}

if (file_exists('./test.db')) unlink('./test.db');

$driver = new Snoken\Drivers\SQLiteDriver(dirname(__FILE__) . "/" . "test.db");
Snoken\Manager::setDriver($driver);
Snoken\Manager::registerModel('User');
Snoken\Manager::registerModel('Post');
Snoken\Manager::createTables();

$user = new User();
$user->name = 'itsbth';
$user->age = 19;
$user->email = 'foobar@example.com';

$post = new Post();
$post->title = "Hello, World!";
$post->content = "CONTENT HERE KTXH.";
$user->save();

$post->user = $user;
$post->save();

$user2 = User::getById($user->id);

$user2->age = "not a number";
$errors = null;
if (!$user2->validate($errors))
  print_r($errors);
//$user2->save(); // Throws an exception

$user3 = User::select(array('age > ?', 18), null, 1)->one();

$posts = $user3->posts;
while ($post = $posts->next())
{
  echo "-> ", $post->title, "\n";
}
$user3->delete();
