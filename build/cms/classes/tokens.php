<?php

class Tokens
{
	private static $items = [];

	public static function add($kvs = [])
	{
		// Merge old after new so that templates may nest tokens and have their inner tokens replaced
		self::$items = array_merge($kvs, self::$items);
		return self::$items;
	}

	public static function replace($buffer)
	{
		foreach(self::$items as $key => $value)
		{
			$buffer = str_replace($key, $value, $buffer);
		}

		return $buffer;
	}
}