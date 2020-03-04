<?php

/*
Installer build script
*/

$target = 'build/index.php';

$test = '../test/index.php';

$code = "<?php
if (\$_SERVER['REQUEST_METHOD'] == 'POST') exit(json_encode(new Server()));";
file_put_contents($target, $code);
file_put_contents($test, $code);

$files_to_add = [
  'classes/app.php',
  'templates/index.php',
  'classes/server.php',
  'classes/result.php',
  'classes/css.php',
  'templates/templates.php',
  'templates/svg.php',
  'data/pages.php',
  'data/regions.php',
  'data/comments.php',
  'data/categories.php',
  'data/settings.php'
];

foreach ($files_to_add as $key => $filename)
{
  $code = file_get_contents($filename);
  $code = str_replace("\r", '', $code);
  if ($key != 1) $code = str_replace("<?php\n", '', $code);
  file_put_contents($target, $code, FILE_APPEND);
  file_put_contents($test, $code, FILE_APPEND);
}

echo "Done\n";
