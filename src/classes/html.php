<?php

class HTML
{
	const T_MUST_BE_ARRAY = "Meta tags parameter must be an array of key => value pairs!";
	const T_JS_MUST_BE_ARRAY = "The javascripts parameter must be an array of file names or URLs";
	const T_FILE_NAMES_MUST_BE_ARRAY = "file_names parameter must be an array";
	const T_SCRIPT_META_DATA_MUST_BE_ARRAY = "Script meta data must be an array";

	private static $style_sheet_path;
	private static $js_style_sheet_path;
	private static $meta_tags = [];
	private static $scripts = [];

	// Input an array of script file names that need to be loaded
	public static function register_scripts($file_names)
	{
		if ( ! is_array($file_names))
			throw new Exception(self::T_FILE_NAMES_MUST_BE_ARRAY);
		
		self::$scripts = array_merge(self::$scripts, $file_names);
	}

	// Input meta tags that should be appended to the regular meta tags that are passed to meta_tags()
	public static function script_meta_tags($kvs)
	{
		if ( ! is_array($kvs))
			throw new Exception(self::T_SCRIPT_META_DATA_MUST_BE_ARRAY);
		
		self::$meta_tags = array_merge(self::$meta_tags, $kvs);
	}

	public static function meta_tags($tags = [], $render = true)
	{
		if ( ! is_array($tags))
			throw new Exception(self::T_MUST_BE_ARRAY);

		$tags = array_merge($tags, self::$meta_tags);

		$meta = [];

		$default_tags = Config::get_value('meta_tags')->value;

		// Add default key => values to meta tags array
		foreach($default_tags as $tag)
		{
			$a = (array) $tag; // Cast object to array key => value
			$kv = each($a);
			$meta[$kv['key']] = $kv['value'];
		}

		// Add/overwrite default values with passed-in values
		foreach($tags as $key => $value)
			$meta[$key] = $value;

		// Form the html code
		$html = [];

		foreach($meta as $name => $value)
		{
			switch($name)
			{
				case 'charset':
					$html[] = '<meta charset="' . $value . '">';
					break;

				case 'stylesheet':
					$styles = (array) $value;
					foreach ($styles as $style)
					{
						if (strpos($style, 'http') === false)
						{
							static::$style_sheet_path = sprintf(
								"%s%s/%s/",
								URL::get_base(),
								Config::get_value('folders')->themes,
								Settings::get_value('project')->theme
							);

							$style = static::$style_sheet_path . $style;
						}

						$html[] = '<link rel="stylesheet" href="' . $style . '">';
					}
					break;

				case 'css':
					$styles = (array) $value;
					foreach ($styles as $style)
					{
						if (strpos($style, 'http') === false)
						{
							static::$js_style_sheet_path = sprintf(
								"%s%s/css/",
								URL::get_base(),
								Config::get_value('folders')->jsFolder
							);

							$style = static::$js_style_sheet_path . $style;
						}

						$html[] = '<link rel="stylesheet" href="' . $style . '">';
					}
					break;

				case 'canonical':
					$html[] = '<link rel="canonical" href="' . URL::get_base() . $value . '">';
					break;

				default:
					$html[] = '<meta name="' . $name . '" content="' . $value . '">';
					break;
			}
		}
		return self::renderer(implode("\n", $html), $render);		
	}

	public static function renderer($html, $render)
	{
		if ($render)
			echo $html;
		else
			return $html;
	}

	public static function hyperlink($slug, $text)
	{
		return '<a href="' . URL::get_base() . $slug . '">' . $text . '</a>';
	}

	public static function javascripts($js = [], $render = true)
	{
		if ( ! is_array($js))
			throw new Exception(self::T_JS_MUST_BE_ARRAY);

		$scripts = (array) Config::get_value('javascripts')->value;
		$scripts = array_merge($scripts, $js, self::$scripts);

		$html = [];
		$done = [];
		$js_path = URL::get_base() . Config::get_value('folders')->jsFolder . '/';
		foreach($scripts as $js)
			if ( ! in_array($js, $done))
			{
				$done[] = $js;
				if (strpos($js, 'http') !== 0)
					$js = $js_path . $js;
				$html[] = '<script src="' . $js . '"></script>';
			}
		return self::renderer(implode("\n", $html), $render);
	}
}