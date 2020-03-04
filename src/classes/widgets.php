<?php

class Widgets extends Settings
{
	protected static $key_values;
	protected static $file_name;

	function __construct($file_name = '')
	{
		if (empty($file_name)) $file_name = '.widgets';
		parent::__construct($file_name);
	}

	public function get()
	{
		return array_values(static::$key_values);
	}
	
	public static function insert($id)
	{
		if (array_key_exists($id, static::$key_values))
			echo static::$key_values[$id]->value;
		else
			return false;
	}
}