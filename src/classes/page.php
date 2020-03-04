<?php

class Page
{
	const T_MISSING_TEMPLATE = "Missing template file: ";
	const T_MISSING_PROPERTY = "Missing property: ";
	const T_MISSING_REGION = "Region data not found for: ";
	const T_HOME_PAGE = "Home Page";

	protected static $settings;
	protected static $now_ms;
	
	function __construct($route = '')
	{
		if (empty($route))
			$route = Route::get();

		if (empty($route))
			return;

		if ('sitemap.xml' == $route)
		{
			header("Content-type: text/xml");
			exit(Pages::generate_sitemap());
		}

		try
		{
			static::$settings = Pages::get_value($route);
			$_SESSION['page'] = static::$settings; // For use by Ajax server
		}
		catch(Exception $e)
		{
			header('HTTP/1.0 404 Not Found');

			// Avoid serving up content for something like a missing image.
			if (static::not_a_page($route))
				exit();

			static::$settings = new ErrorPage();
		}
	}

	public static function set_seed()
	{
		if (static::$settings->id < 1) // Allow for special cached pages such as Site map
			return false;

	}

	public static function not_a_page($route)
	{
		return (preg_match('#\.(jpe?g|png|gif|ico|cgi|map|svg)$#', $route) > 0);
	}

	public static function render()
	{
		$template = sprintf(
			"%s/%s/%s.php",
			Config::get_value('folders')->themes,
			Settings::get_value('project')->theme,
			static::$settings->template
		);

		if (file_exists($template))	
		{
			ob_start('Tokens::replace');
			include $template;
			ob_end_flush();
		}
		else
			throw new Exception(static::T_MISSING_TEMPLATE . $template);
	}

	public static function get_menu_family($menu_name, $include_host)
	{
		return Pages::get_menu_family(static::$settings, $menu_name, $include_host);
	}

	public static function display_content()
	{
		echo static::$settings->content;
	}

	public static function home_page_link($text = '', $link_home_to_home = false, $render = true)
	{
		if (empty($text))
		{
			if (static::$settings->key == 'home')
				$text = static::$settings->title;
			else
				$text = static::T_HOME_PAGE;
		}
		
		if ($link_home_to_home || static::$settings->key != 'home')
			$text = HTML::hyperlink('', $text);
		
		return HTML::renderer($text, $render);
	}

	public static function logo($image_path, $alt_text = 'logo', $link_home_to_home = false, $render = true)
	{
		$html = sprintf('<img src="%s" alt="%s" />', URL::get_base() . $image_path, $alt_text);

		return static::home_page_link($html, $link_home_to_home, $render);
	}

	public static function get_value($property)
	{
		if (isset(static::$settings->$property))
			return static::$settings->$property;
		else
			throw new Exception(static::T_MISSING_PROPERTY . $property);
	}

	public static function get_content($render = true)
	{
		if (isset(static::$settings->content))
			$content = static::$settings->content;
		else
			$content =  self::get_code(static::$settings->id);

		return HTML::renderer($content, $render);
	}

	public static function get_code($id, $json = false)
	{
		$path = 'pages/' . $id;
		if ($json)
			$fn = $path . '.json';
		else
			$fn = $path . '.htm';

		$code = (new Data)->get($fn, $json);

		if ($json)
			return $code;
		
		$array = json_decode($code);
		return is_array($array) ? $array[0] : $code;
	}

	public static function is_public($page)
	{
		static::$now_ms = Time::get_time_ms();

		return ($page->live && Time::get_time_ms($page->published) < static::$now_ms);
	}

	public static function get_region_content($region = 'main-content', $id = -1)
	{
		$content = "";
		
		if ($id < 1) $id = static::$settings->id;
		if (empty(static::$settings->content))
		{
			$content = self::get_code($id, true);
			if (empty($content)) $content = ""; // Avoid displaying the word: Array
			static::$settings->content = $content;
		}	

		if (array_key_exists($region, (array) static::$settings->content))
			$content = static::$settings->content[$region]->value;

		return $content;
	}

	public static function insert_region_content($region = 'main-content', $id = -1)
	{
		echo self::get_region_content($region, $id);
	}

	// Non-static methods are used by Javascript Ajax requests
	
	public function set_content($data)
	{
		$data->value = self::get_code($data->id);
		return $data;
	}

	public function save_content($data)
	{
		$content = isset($data->content) ? $data->content : '';
		return (new Data())->save('pages/' . $data->id . '.htm', [$content]);
	}

	public function save_regions($data)
	{
		$id = $data->pageId;
		$regions = self::get_code($id, true);
		foreach($data->regionList as $ob)
		{
			$html = $ob->value;
			// Change the img URLS to optimize the delivery file size based on the width
			preg_match_all('@<img.+?data-ce-max-width="(\d+)".+?src="(.+?)".+?width="(\d+)">@', $html, $matches, PREG_SET_ORDER);

			foreach ($matches as $img)
			{
			  if ($img[1] == $img[3]) continue;
			  $url = $img[2];
			  $url_parts = explode('/', $url);
			  if (strpos($url_parts[6], 'w_') === 0)
			    unset($url_parts[6]);
			  $url_parts[5] = 'upload/w_' .  $img[3] . ',c_fit';
			  $new_url = implode('/', $url_parts);
			  $html = str_replace($url, $new_url, $html);
			}

			$html = str_replace("\n    ", '', $html);
			$html = str_replace("\n</", '</', $html);

			$regions[$ob->key] = (object) [ 'key' => $ob->key, 'value' => $html ];
		}
		return (new Data())->save('pages/' . $id . '.json', $regions);
	}
}