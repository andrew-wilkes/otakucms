<?php

class URL
{
	private static $base;
	private static $host;
	private static $sub_domain;
	private static $protocol;

	function __construct($url_or_folder = '', $sub_domain = '')
	{
		self::$host = $_SERVER['HTTP_HOST'];

		self::$protocol = $this->extract_protocol();

		if (empty($sub_domain))
			self::$sub_domain = $this->extract_sub_domain();
		else
			self::$sub_domain = $sub_domain;

		self::$base = self::$protocol . '://' . self::$host . '/';
		if (empty($url_or_folder))
			return;

		$url_or_folder = trim($url_or_folder, '/');

		if (strpos($url_or_folder, ':'))
			self::$base = $url_or_folder . '/';
		else
			self::$base .= $url_or_folder . '/';
	}

	public static function get_host()
	{
		return self::$host;
	}

	public static function get_base()
	{
		return self::$base;
	}

	public static function get_sub_domain()
	{
		return self::$sub_domain;
	}

	public static function get_protocol()
	{
		return self::$protocol;
	}

	public function extract_protocol()
	{
		if (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'])
			return 'https';
		else
			return 'http';
	}

	public function extract_sub_domain()
	{
		preg_match_all('/([-0-9a-z]+)\./U', self::$host, $matches);
		$sub_domain = $matches[1][0];
		if ($sub_domain == 'www')
			$sub_domain = $matches[1][1];
		return $sub_domain;
	}
}