<?php

// Build the Dashboard code and copy the files to a src/dashboard folder for testing on the PHP server

$b = new Build();
$b->run();
$b->wipe();
$b->copy();

class Build
{
  const FILES_INDEX = 0;
  const DIR_INDEX = 1;

  public $src;

  public function __construct($src = 'D:/webdev/apps/otaku6/dashboard/dist/')
  {
    $this->src = $src;
  }

  public function run()
  {
    exec('npm run build');
  }

  public function wipe()
  {
    $dir = 'D:/webdev/apps/otaku6/src/dashboard/';
    self::wipe_dir($dir);
    mkdir($dir . 'css');
    mkdir($dir . 'js');
  }

  public function copy()
  {
    $files = glob($this->src . 'static/css/*.css');
    // Get the app.js file name
    $app = glob($this->src . 'static/js/app*.js')[0];
    // Replace the absolute url to the test php server with a relative path
    file_put_contents($app, str_replace('http://otaku6.local', '..', file_get_contents($app)));
    $files = array_merge($files, glob($this->src . 'static/js/*.*')); // Used to have: {app,vendor,manifest}*.js', GLOB_BRACE
    foreach ($files as $src)
    {
      $dest = str_replace('dashboard/dist/static', 'src/dashboard', $src);
      copy($src, $dest);
    }
    $index = $this->src . 'index.html';
    $html = str_replace('/static/', '', file_get_contents($index));
    file_put_contents(str_replace('dashboard/dist', 'src/dashboard', $index), $html);
  }

  // Wipes every file and folder except for the root folder
  public static function wipe_dir($dir)
  {
    $items = self::get_item_names($dir);

    array_map('unlink', $items[self::FILES_INDEX]);

    foreach ($items[self::DIR_INDEX] as $dir)
    {
      self::wipe_dir($dir);
      rmdir($dir);
    }     
  }

  public static function get_item_names($dir)
  {
    $files = array_filter(glob($dir . '{.*,*.*}', GLOB_BRACE), function($str) {
      return '.' != $str[strlen($str) - 1];
    });
    $dirs = glob($dir . '*', GLOB_ONLYDIR | GLOB_MARK); // GLOB_MARK - Adds a slash to each directory returned
    return [$files, $dirs];
  }
}
