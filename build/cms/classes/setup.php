<?php
include 'class-autoloader.php';
new ClassAutoloader();

new Data(new DataFromJSON('data')); // Inject a data handler that uses flat files

new Config();
new Settings();

// Detect an Ajax request rather than a Browser request
if (isset($_GET['class']))
{
  if ($_SERVER['REQUEST_METHOD'] == 'POST')
    $data = json_decode(file_get_contents("php://input"));
  else
    $data = '';
  new Session($data);
}