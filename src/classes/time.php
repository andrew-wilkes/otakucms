<?php

class Time
{
	public static function get_time_ms($time = false)
	{
		if ($time)
		{
			if ( ! is_numeric($time))
				$time = 1000 * strtotime($time . " UTC");
		}
		else
			$time = 1000 * time();

		return $time;
	}
}