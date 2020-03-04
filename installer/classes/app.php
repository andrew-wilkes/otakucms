<?php

/*
This class is used to check the server compatibility and to provide info. to the front-end.
*/

class App
{
  public $title = "OtakuCMS Installation Wizard";
  public $testName = '';
  public $error = false;

  public function __construct()
  {
    try
    {
      $this->check_php_version();
      $this->check_vars();
      $this->check_writing_file();
    }
    catch (Exception $e)
    {
      $this->error = $e->getMessage();
    }
  }

  public function get_server_type()
  {
    $server = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
    if (stripos($server, 'icrosoft'))
      return 'windows';
    elseif (stripos($server, 'ginx'))
      return 'nginx';
    else
      return 'linux';
  }

  public function check_php_version($fail = false)
  {
    $this->testName = 'Check PHP version';

    if (version_compare(phpversion(), '5.3.9', '<') or $fail)
      throw new Exception('This script requires PHP version 5.4.0 or higher to run. You are using version: ' . phpversion());
  }

  public function check_vars($fail = false)
  {
    $this->testName = 'Check server vars';

    if (empty($_SERVER['HTTP_HOST']) or $fail)
      throw new Exception('The required HTTP_HOST $_SERVER variable was not available.');
  }

  public function check_writing_file()
  {
    $this->testName = 'Check writing file';

    $fn = 'temp.php';
    $a = array('item1', 'item2');
    
    if (false === @ file_put_contents($fn, serialize($a)))
      throw new Exception("Unable to create/write to $fn file. You will need to change the file permissions of your website.");

    $this->testName = 'Get data from file';

    $b = @ unserialize( @ file_get_contents($fn));
    if ( ! is_array($b) || $a[0] !== $b[0])
      throw new Exception("Unable to retrieve data from test file $fn.");

    $this->testName = 'Delete file';

    if ( ! unlink($fn))
      throw new Exception("Unable to delete $fn file.");
  }

  public function rand_str($min = 6, $max = 12)
  {
    return substr(md5(time() + rand(0,99)), 0, rand($min, $max));
  }

  public function get_base_path()
  {
    $path = str_replace('\\', '/', substr(__DIR__, strlen( @ $_SERVER['DOCUMENT_ROOT']))) . '/';
    return $path;
  }

  public function get_sub_domain()
  {
    preg_match_all('/([-0-9a-z]+)\./U', $_SERVER['HTTP_HOST'], $matches);
    $sub_domain = $matches[1][0];
    if ($sub_domain == 'www')
      $sub_domain = $matches[1][1];
    return $sub_domain;
  }

  public function dashboard_name()
  {
    $names = ['dashboard', 'cpanel', 'admin'];
    shuffle($names);
    return $names[0];
  }

  public function domain()
  {
    preg_match_all('/([-0-9a-z]+)\./U', $_SERVER['HTTP_HOST'], $matches);
    $sub_domain = $matches[1][0];
    if ($sub_domain == 'www')
      $sub_domain = $matches[1][1];
    return $sub_domain;
  }
}

$app = new App();

?>