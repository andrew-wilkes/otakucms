<?php

class Categories extends Settings
{
	protected static $key_values;
	protected static $file_name;

	function __construct($file_name = '')
	{
		if (empty($file_name)) $file_name = '.categories';
		parent::__construct($file_name);
	}
	
	public function get()
	{
		return array_values(static::$key_values);
	}

	public function save($items)
	{
		// Sanitize and save the $items
		$items[0] = Filter::root();
		static::$key_values = Filter::map(get_class($this), $items);
		self::save_data();
		return static::$key_values;
	}

	// This method is required by the Filter class
	public static function filter($i)
	{
		if (Filter::$index > 0)
			$i = (object) array
			(
				'id'		=> Filter::id($i),
				'title'		=> Filter::words($i, 'title'),
				'key'		=> Filter::key($i),
				'parent'	=> Filter::int($i, 'parent'),
				'depth'		=> Filter::int($i, 'depth'),
				'count'		=> Filter::int($i, 'count')
			);

		Filter::$index++;

		return $i;
	}

	public static function get_children($parent = 0)
	{
		$children = [];

		foreach(static::$key_values as $category)
		{
			if (0 == $category->id) continue;

			if ($category->parent == $parent)
				$children[] = (object)
					[
						'category' => $category,
						'children' => static::get_children($category->id)
					];
		}

		return $children;
	}

	public static function get_id($key)
	{
		$category = static::get_category($key);

		if ($category)
			return $category->id;
	}

	public static function get_category($key)
	{
		if (array_key_exists($key, static::$key_values))
			return static::$key_values[$key];

		return false;
	}

	public static function print_list($show_count = true)
	{
		$html = static::generate_html(static::$key_values, $show_count);
		echo implode("\n", $html);
	}

	public static function generate_html($items, $show_count = true)
	{
		if (empty($items)) return;

		$html = ['<ul>'];

		foreach ($items as $category) 
		{
			if ($category->id == 0) continue;

			$inner_html = static::generate_html($category->children);

			$count = '';
			if ($show_count)
				$count = ' (' . Pages::count_pages_in_category($category->id) . ')';
			
			$html[] = '<li>' . HTML::hyperlink('category/' . $category->key, $category->title . $count). $inner_html . '</li>';
		}

		$html[] = '</ul>';
		return  $html;
	}
}