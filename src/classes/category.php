<?php

/*
This class may be instantiated when the category route is used.
A route of category/key means that a specific category is selected.
*/

class Category
{
	const T_CATEGORY_TITLE = "Topics in the #CATEGORY# category";

	private static $pages = [];
	private static $category;

	function __construct($key = 0)
	{
		if ( ! empty($key))
		{
			$key = (array) $key; // Route parameters are passed as an array
			self::$pages = Pages::get_pages_in_category($key[0]);
			self::$category = Categories::get_category($key[0]);
			Tokens::add(['#TITLE#' => str_replace('#CATEGORY#', self::$category->title, self::T_CATEGORY_TITLE)]);
		}
	}

	public static function get_pages()
	{
		return self::$pages;
	}

	public static function list_pages()
	{
		$html = [];

		foreach (self::$pages as $page) 
		{
			$html[] = '<li>' . HTML::hyperlink($page->key, $page->title) . '</li>';
		}

		echo implode("\n", $html);
	}
}