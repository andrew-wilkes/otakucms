<?php

/*
class used to start a PHP session (cookie) and to instantiate a class
via the auto-loader and optionally execute a method on the class.
It either responds with success data or an error response.
 */

class Session
{
	const T_CLASS_NOT_FOUND = "Class not found: ";
	const T_UNKNOWN_METHOD = "Unknown method: ";
	const T_ACCESS_DENIED = "Access denied to: ";

	public static $ip;
	public static $is_moderator;

	private static $methods;

	function __construct($data = null)
	{
		session_start();

		self::$ip = preg_replace('/[^.:0-9a-z]/', '', $_SERVER['REMOTE_ADDR']);
		self::$is_moderator = isset($_SESSION['user']);

		if (empty($_GET['class'])) return;

		$class = self::gatekeeper('class');
		if (empty($class)) return;

		if ( ! class_exists($class))
			self::report_error(self::T_CLASS_NOT_FOUND . $class);

		if (isset($_GET['method']))
		{
			$method = self::gatekeeper('method');

			if (method_exists($class, $method))
				$data = (new $class())->$method($data);
			else
				self::report_error(self::T_UNKNOWN_METHOD . $method);
		}
		else
			$data = new $class($data);

		self::output($data);
	}

	public static function gatekeeper($type)
	{
		$name = $_GET[$type];

		// If user is logged in then allow access to any class and method
		if (isset($_SESSION['user']))
			return $name;

		// If user is not logged in then only allow access to particular classes
		if ($type == 'class')
		{
			try
			{
				$classes = Config::get_value('classes');
			}
			catch (Exception $e)
			{
				$classes = 	(object)
				[
					"key" => "classes",
					"User" => ["get","api","logOn","logOff","register"],
					"Comments" => ["get_status","api"],
					"Contact" => [],
					"Click" => []
				];
			}
			
			if (array_key_exists($name, $classes))
			{
				self::$methods = $classes->$name;							
				return $name;
			}
		}
		else
		{
			// Check for allowed methods of the accepted class for the non logged-in user
			if (in_array($name, self::$methods))
				return $name;
		}

		// At this stage the user was requesting a non white-listed method of the accepted classes for a non logged-in user
		self::report_error(self::T_ACCESS_DENIED . $type . ': ' . $name);
	}

	public static function report_error($txt = '', $data = null, $code = 403) // 403 means Forbidden
	{
		$header = $_SERVER['SERVER_PROTOCOL'] . " $code $txt"; // Note: See URL::extract_protocol() in case this line of code should be modified
		self::output($data, $header);		
	}

	public static function output($data, $header = '')
	{
		$data = json_encode($data);

		if (isset($_POST['TESTING']))
		{
			$_POST['TESTING'] = "$header $data";
			return;
		}

		if ($header) header($header);
		exit($data);
	}
}