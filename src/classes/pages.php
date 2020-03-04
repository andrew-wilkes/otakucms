<?php

class Pages extends Settings
{
	protected static $key_values;
	protected static $file_name;
	protected static $recent_posts;
	protected static $archive;

	function __construct($file_name = '')
	{
		if (empty($file_name)) $file_name = '.pages';
		parent::__construct($file_name);
	}

	public function get()
	{
		return array_values(static::$key_values);
	}

	public function save($items)
	{
		// Sanitize $items
		$items[0] = Filter::root();
		$pages = Filter::map(get_class($this), $items);

		// Delete removed pages
		$deleted_pages = array_udiff(static::$key_values, $pages, ['Pages', 'compare_ids']);

		$path = (new Data)->get_path() . 'pages/';
		
		if (count($deleted_pages))
		{
			foreach ($deleted_pages as $page)
			{
				$fn = $path . $page->id . '.json';
				if (file_exists($fn)) unlink($fn);
			}			
		}

		static::$key_values = $pages;
		self::save_data();
		return $pages;
	}

	public static function filter($i)
	{
		if (Filter::$index > 0)
			$i = (object) array
			(
				'id'			=> Filter::id($i),
				'title'			=> Filter::words($i,		'title'),
				'key'			=> Filter::key($i),
				'parent'		=> Filter::int($i,			'parent'),
				'depth'			=> Filter::int($i,			'depth'),
				'category'		=> Filter::int($i,			'category'),
		        'description'	=> Filter::string($i,		'description'),
		        'menu'			=> Filter::string_array($i,		'menu'),
		        'published'		=> Filter::datetime($i,		'published'),
		        'tags'			=> Filter::string_array($i,	'tags'),
		        'template'		=> Filter::file_name($i,	'template'),
		        'live'			=> Filter::bool($i,			'live')
			);

		Filter::$index++;

		return $i;
	}

	public function compare_ids($a, $b)
	{
		return strcmp($a->id, $b->id);
	}

	public static function get_recent_posts()
	{
		self::generate_post_lists();
		return self::$recent_posts;
	}

	public static function get_archive()
	{
		self::generate_post_lists();
		return self::$archive;
	}

	public static function get_menu_family($host, $menu_name, $include_host)
	{
		if (empty($host->id))
			return false;
		
		$family = new MenuFamily();

		// Find parent
		foreach(self::$key_values as $page)
		{
			if ($page->id > 0 && $page->id == $host->parent)
			{
				$family->parent = $page;
				break;
			}
		}

		// Find family members that are using the menu
		foreach(self::$key_values as $page)
		{
			if (0 == $page->id) continue;

			if ($page->id != $host->id || $include_host)
			{
				if (in_array($menu_name, (array) $page->menu) && Page::is_public($page))
				{
					$family->all[] = $page;

					if ($page->parent == $host->id)
						$family->children[] = $page;

					if ($page->parent == $host->parent)
						$family->siblings[] = $page;

					if (isset($family->parent->parent) && $family->parent->parent == $page->parent)
						$family->parent_siblings[] = $page;
				}
			}
		}

		return $family;
	}
	
	public static function get_pages_in_category($key)
	{
		$pages = [];

		$id = Categories::get_id($key);

		foreach(self::$key_values as $page)
		{
			if ($page->id == 0) continue;
			
			$cats = (array) $page->category;
			if (in_array($id, $cats) && Page::is_public($page))
				$pages[] = $page;
		}

		return $pages;
	}

	public static function count_pages_in_category($id)
	{
		$count = 0;

		foreach(self::$key_values as $page)
		{
			if ($page->id == 0) continue;
			
			$cats = (array) $page->category;
			if (in_array($id, $cats) && Page::is_public($page))
				$count++;
		}

		return $count;
	}

	public static function generate_sitemap()
	{
		$sitemap = [
			'<?xml version="1.0" encoding="UTF-8"?>',
			'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
		];

		foreach (self::$key_values as $page)
		{
			if ($page->id == 0) continue;

			$route = $page->key;
			if ($route == 'home')
				$route = '';

			$sitemap[] = '<url><loc>' . URL::get_base() . htmlspecialchars($route) . '</loc></url>';
		}

		$sitemap[] = '</urlset>';

		return implode("\n", $sitemap);
	}

	public static function generate_post_lists()
	{
		self::$recent_posts = [];
		self::$archive = [];
		$items = [];
		$times = [];

		foreach (self::$key_values as $page)
		{
			if ($page->id == 0) continue;

			if ($page->template == 'post')
			{
				$times[$page->id] = Time::get_time_ms($page->published);
				$items[$page->id] = $page;
			}
		}

		// Sort the list in reverse order of publishing time
		arsort($times);
		
		$count = 0;
		foreach($times as $id => $time)
		{
			if ($count++ < 100)
				self::$recent_posts[] = $items[$id];

			// [year][month][] = post
			self::$archive[date("Y", $time / 1000)][date("m", $time / 1000)][] = $items[$id];
		}
	}
}