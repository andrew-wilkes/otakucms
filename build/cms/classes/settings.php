<?php

class Settings extends Dictionary
{
	protected static $key_values;
	protected static $file_name;

	function __construct($file_name = '')
	{
		if (empty($file_name)) $file_name = '.settings';
		parent::__construct($file_name);
	}

	public static function set_value($key, $value)
	{
		static::$key_values[$key] = $value;
	}

	public static function save_data()
	{
		return (new Data())->save(static::$file_name, static::$key_values);
	}

	public function get()
	{
		$limits = (object) [
			"key" => "limits",
			"fileSize" => self::convert_ini_size('upload_max_filesize'),
			"numFiles" => self::convert_ini_size('max_file_uploads'),
			"postSize" => self::convert_ini_size('post_max_size')
		];
		return array_merge(array_values(static::$key_values), [$limits]);
	}

	public function save($data)
	{
		// Additional array elements such as limits (see above) are discarded when the file is .settings
		if (static::$file_name == '.settings')
			static::$key_values = array_slice($data, 0, count(static::$key_values));
		else
			static::$key_values = $data;
		self::save_data();
	}

	public static function convert_ini_size($key)
	{
		$size = ini_get($key);

		if (empty($size))
			$size = 0;
			
		if ( ! is_numeric($size))
		{
			if (preg_match('/(\d+)([KMG])/i', $size, $matches)) // e.g. 20M
			{
				$qty = $matches[1];
				$unit = strtolower($matches[2]);
				switch ($unit)
				{
					case 'k':
						$size = $qty * 1024;
						break;
					case 'm':
						$size = $qty * 1048576;
						break;
					case 'g':
						$size = $qty * 1073741824;
						break;
				}
			}
			else
				$size = 0;
		}
		
		return $size;
	}
}