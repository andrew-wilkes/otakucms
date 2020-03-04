<?php

class Menu
{
	const T_UNKNOWN_GROUP = "Unknown family member group: ";

	private static $family;

	function __construct($menu_name, $include_host = true)
	{
		self::$family = Page::get_menu_family($menu_name, $include_host);
	}

	// Method to output menu list items
	public static function html($items, $separator = false, $post = '', $pre = '', $render = true)
	{
		if (empty(self::$family))
			return;
		
		$family_members = [];
		foreach((array)$items as $group)
		{
			if ( ! property_exists(self::$family, $group))
				throw new Exception(self::T_UNKNOWN_GROUP . $group);

			if (isset(self::$family->$group))
			{
				if (is_array(self::$family->$group))
					$family_members = array_merge($family_members, self::$family->$group);
				else
					$family_members[] = self::$family->$group;
			}
		}

		$links = [];
		if (Page::get_value('id') == 1 && ! empty($pre))
			$links[] = $pre;

		foreach ($family_members as $page)
		{
			$link = $page->title;
			if (Page::get_value('id') != $page->id)
			{
				if ($page->key == 'home')
					$page->key = '';
				$link = HTML::hyperlink($page->key, $link);
			}
			if ($separator === false)
				$link = '<li>' . $link . '</li>';
			$links[] = $link;
		}
		if (Page::get_value('id') == 1 && ! empty($post))
			$links[] = $post;

		return HTML::renderer(implode($separator, $links), $render);
	}
}