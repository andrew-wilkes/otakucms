<?php

class Filter
{
	public static $index;
	public static $slugs = [];

	public static function map($class, $items)
	{
		return array_map([$class, 'filter'], $items);
	}

	public static function root()
	{
		// The root object is enforced
		self::$index = 0;
		return (object) [
			"id" => 0,
	        "title" => "",
	        "parent" => null,
	        "depth" => 0,
	        "key" => 0
        ];
	}

	public static function id($i)
	{
		// An invalid id value will be changed to a negative random number that will not clash with valid data
		$v = 9 - mt_rand(1000, PHP_INT_MAX);

		if (isset($i->id))
		{
			$n = filter_var($i->id, FILTER_VALIDATE_INT, ['default' => $v]);
			if ($n != 0) // An id of 0 is reserved for the root
				$v = $n;
		}

		return $v;
	}

	public static function int($i, $p)
	{
		$v = 0;
		if (isset($i->$p))
			$v = filter_var($i->$p, FILTER_VALIDATE_INT, ['default' => $v]);

		return $v;
	}

	public static function bool($i, $p)
	{
		$v = false;
		if (isset($i->$p))
			$v = filter_var($i->$p, FILTER_VALIDATE_BOOLEAN);
		return $v;
	}

	public static function string($i, $p)
	{
		$v = '';
		if (isset($i->$p))
			$v = self::escape($i->$p);

		return $v;
	}

	public static function string_array($i, $p)
	{
		$a = [];
		if (isset($i->$p))
			foreach((array) $i->$p as $s)
				$a[] = self::escape($s);

		return $a;
	}	

	public static function words($i, $p)
	{
		$v = '';
		if (isset($i->$p))
			$v = self::escape($i->$p);

		return $v;
	}

	public static function key($i)
	{
		$v = 0;
		if (isset($i->key))
			$v = trim($i->key);

		return $v;
	}

	public static function file_name($i, $p)
	{
		$v = '';
		if (isset($i->$p))
			$v = filter_var($i->$p, FILTER_SANITIZE_URL);

		if (empty($v))
			$v = 'page';

		return $v;
	}

	public static function datetime($i, $p)
	{
		// The date and time must be in sync between browser and server, so we store the value normalized to GMT
		$format = 'Y-m-d\TH:i';
		$v = gmdate($format); // Store date as GMT
		if (isset($i->$p))
			$v = date($format, strtotime(substr(self::escape($i->$p), 0, 16))); // Need to ignore the time zone offset
		return $v;
	}

	public static function escape($str)
	{
		return trim(htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));
	}
}