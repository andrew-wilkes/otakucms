<?php

/**
 * This is a concrete class that implements an interface
 */

class DataFromJSON implements iData
{
	const T_BAD_PATH = "Path does not exist! ";
	const T_NOT_OBJECT = "Data item is not an object!";
	const T_KEY_NOT_SET = "Data object property 'key' not set in: ";
	const T_DATA_NOT_ARRAY = "Data items are not an array!";
	const T_MISSING_FILE = "Missing file: ";

	private static $path;

	public function __construct($path = 'data') // Full path or name of folder
	{
		if (false === strpos($path, '/'))
			$path = dirname(__DIR__) . "/$path/";

		if (file_exists($path))
			self::$path = $path;
		else
			throw new Exception(self::T_BAD_PATH . $path);
	}

	public function get_path()
	{
		return self::$path;
	}

	// Transform Frontend JSON data ({ }) style to PHP dictionary (Key => object) style
	public function get($file_name, $transform = true, $ignore_missing_file = false)
	{
		$file = self::$path . $file_name;

		$data = null;

		if (file_exists($file))
			$data = file_get_contents($file);

		if ($transform)		
		{
			$items = (array) json_decode($data);

			$data = [];

			foreach($items as $item)
			{
				if ( ! is_object($item))
					throw new Exception(self::T_NOT_OBJECT);
				
				if ( ! isset($item->key))
					throw new Exception(self::T_KEY_NOT_SET . $file_name);

				$data[$item->key] = $item;				
			}
		}
		
		if (is_null($data))
		{
			if ($ignore_missing_file)
				$data = '[]';
			else
				throw new Exception(self::T_MISSING_FILE . $file);
		}

		return $data;
	}

	public function save($file, $items)
	{
		if ( ! is_array($items))
			throw new Exception(self::T_DATA_NOT_ARRAY);

		file_put_contents(self::$path . $file, json_encode(array_values($items), JSON_PRETTY_PRINT));
	}
}