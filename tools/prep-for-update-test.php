<?php

// Prepare test install files for update test

$class_dir = glob('../test/classes*')[0];

if (empty($class_dir)) die('Test site is not installed!');

$update_class_file = $class_dir . '/update.php';

$code = file_get_contents($update_class_file);
$code = preg_replace("#'https://[^']+?'#", "'http://otaku6.api/?action=get-cms'", $code);
file_put_contents($update_class_file, $code);

$status_class_file = $class_dir . '/status.php';
$code = file_get_contents($status_class_file);
$code = preg_replace("#thisVersion = '.+?'#", "thisVersion = '1.0'", $code);
file_put_contents($status_class_file, $code);

echo "Done!";