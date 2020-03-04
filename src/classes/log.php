<?php

class Log
{
	protected static $items;
	protected static $file_name;

	function __construct($file_name = '')
	{
		if (empty($file_name)) $file_name = '.log';
		static::$file_name = $file_name;
		static::$items = (array) json_decode((new Data())->get($file_name, false, true));
	}

	public static function add($data, $max_count = 100)
	{
		if ($max_count)
		{
			$new_array = [$data];

			$max_count = abs(1 - (int) $max_count);

			foreach(static::$items as $i => $item)
				if ($i < $max_count)
					$new_array[] = $item;

			static::$items = $new_array;
		}
		else
			static::$items[] = $data;
	}

	public static function save_data()
	{
		return (new Data())->save(static::$file_name, static::$items);
	}
}
