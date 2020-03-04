<?php

/* OtakuCMS ©2016 Andrew Wilkes – http://otakucms.com
 * Released under the MIT license – http://otakucms.com/mit.txt
 */

class Themes
{
	public function __construct()
	{
	}

	public function get()
	{
		$themes = json_encode(array_map('details', glob(dirname(__DIR__) . '/' . Config::get_value('folders')->themes . '/*', GLOB_ONLYDIR)));

		return json_decode($themes);
	}
}

function details($file)
{
	$details = Theme::get_info($file);

	if (empty($details->description))
		$details->description = $details->name;

	return $details;
}