<?php

/**
The route is obtained from the URL query string e.g.
?route=/blog/page-1
The first stub may be used to instantiate a class from it's id and parameters passed to it e.g.
?route=/class-id/param1/param2
**/
class Route
{
	const ROUTE_KEY = 'route';

	protected static $route;

	public function __construct($class_ids = [])
	{
		$route = '';

		if (isset($_GET[self::ROUTE_KEY]))
			$route = ltrim(filter_var($_GET[self::ROUTE_KEY], FILTER_SANITIZE_URL), '/');

		if (empty($route))
			$route = 'home';

		$parts = explode('/', rtrim($route, '/'));
		$class_id = array_shift($parts);

		if (in_array($class_id, $class_ids))
		{
			$route = $class_id;
			$class = str_replace(' ', '', ucwords(str_replace('-', ' ', $class_id)));
			new $class($parts);
		}

		static::$route = $route;
	}

	public static function get()
	{
		return static::$route;
	}
}