<?php

/*
 Seeds is a class that stores the time in seconds for the next time
 to require an update to features of a web page that change with time.
 For example, Ad displays or different versions of content.
 It achieves this via setting the random number generator seed value to something that will get the same results until the next update.
*/

class Seeds extends Settings
{
	protected static $key_values;
	protected static $file_name;

	function __construct($file_name = '')
	{
		if (empty($file_name)) $file_name = '.seeds';
		parent::__construct($file_name);
	}

	public static function set($id)
	{
		if (array_key_exists($id, static::$key_values))
			$update_time = (int) static::$key_values[$id]->time;
		else
			$update_time = 0;

		$timing = Settings::get_value('timing');
		$now = time();

		if ($now >= $update_time)
		{
			$update_time = $now + rand((int) $timing->nextUpdateIntervalMin, (int) $timing->nextUpdateIntervalMax);
			static::$key_values[$id] = (object) ['key' => $id, 'time' => $update_time];
			static::save_data();
		}

		srand(static::$key_values[$id]->time);
		return static::$key_values;
	}
}