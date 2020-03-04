<?php

// Get page content

$code = "[";

for ($i = 1; $i < 8; $i++)
{
	$code .= "\n'" . file_get_contents('../src/data/pages/' . $i . '.htm') . "',";
}

$code .= "\n]'";

echo $code;