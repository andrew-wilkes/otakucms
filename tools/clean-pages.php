<?php

// Remove obsolete pages, re-index ids in page data, synce ids to pages htm files.

include '../kint.php';

$pages = json_decode(file_get_contents('../src/data/.pages'));

d($pages);

$ids = [];

foreach ($pages as $page)
{
	$ids[] = $page->id;
}

sort($ids);

d($ids);

$dir = '../src/data/pages/';

$files = glob($dir . '*.htm');

d($files);

$pids = [];

foreach ($files as $page)
{
	preg_match('#(\d+)\.htm$#', $page, $m);
	$pids[] = $m[1];
}

d($pids);

$to_delete = array_diff($pids, $ids);

d($to_delete);

foreach ($to_delete as $id)
{
	unlink($dir . $id . '.htm');
}

$contents = [];

foreach($pages as $key => $page)
{
	if ($key < 1) continue;
	$contents[$key] = file_get_contents($dir . $page->id . '.htm');
	unlink($dir . $page->id . '.htm');
	$pages[$key]->id = $key;
}

file_put_contents('../src/data/.pages', json_encode($pages, JSON_PRETTY_PRINT));

foreach($contents as $key => $html)
{
	file_put_contents($dir . $key . '.htm', $html);
}
