<?php

$fn = dirname(__DIR__) . '/dist/api/repo/cms.zip';

if (! file_exists($fn)) die("$fn does not exist!");

$content = file_get_contents($fn);

if (false !== $content)
{
	header('Content-type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . $fn . '"');
	header('Content-Transfer-Encoding: binary');
	echo $content;
	exit;
} else echo "Unable to load $fn";