<?php

/* OtakuCMS ©2016 Andrew Wilkes – http://otakucms.com
 * Released under the MIT license – http://otakucms.com/mit.txt
 */

class Theme
{
	public function __construct()
	{
	}

	public function get($theme)
	{
		$folder = dirname(__DIR__) . '/' . Config::get_value('folders')->themes . '/' . $theme->name;

		$info = self::get_info($folder);

		// Scan for template files
		$scan = glob($folder . '/*.php');
		foreach($scan as $path)
		{
			if (preg_match('#/([-a-z0-9]+)\.php#', $path, $matches))
			{
				$name = $matches[1];
				if ($name != 'index')
					$info->templates[] = $name;
			}
		}

		return $info;
	}

	public static function get_info($folder)
	{
		$info = json_decode((new Data(new DataFromJSON($folder)))->get('/package.json', false, false));

		$info->name = basename($folder);

		return $info;
	}

	public function set($data)
	{
		$p = $data->palette;

		$content = sprintf("angular.module('otakucms')

.constant('PALETTE',
{
	primary: '%s',
	accent: '%s',
	warn: '%s',
	background: '%s',
	dark: %s
})",	Filter::string($p, 'primary'),
		Filter::string($p, 'accent'),
		Filter::string($p, 'warn'),
		Filter::string($p, 'background'),
		(Filter::bool($p, 'dark') ? 'true':'false'));
		file_put_contents(dirname(__DIR__) . '/' . Config::get_value('folders')->dashboard . '/js/palette.js', $content);
	}
}