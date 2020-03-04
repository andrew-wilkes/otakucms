<?php

abstract class Dictionary
{
	const T_NOT_FOUND = "Item key not found: ";
	const T_MISSING_FILE_NAME = "Missing file name!";

	function __construct($file_name = '')
	{
		if (empty($file_name))
			throw new Exception(self::T_MISSING_FILE_NAME); // Adds more clarity to the error of the missing parameter

		// Use late static binding
		static::$file_name = $file_name;
		static::$key_values = (new Data)->get($file_name);
	}

	public static function get_value($key)
	{
		if (array_key_exists($key, static::$key_values))
			return static::$key_values[$key];
		else
			throw new Exception(self::T_NOT_FOUND . $key . ' in:' . static::$file_name);
	}
}
