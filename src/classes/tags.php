<?php

/* OtakuCMS ©2016 Andrew Wilkes – http://otakucms.com
 * Released under the MIT license – http://otakucms.com/mit.txt
 */

class Tags extends Settings
{
	protected static $key_values;
	protected static $file_name;

	function __construct($file_name = '')
	{
		if (empty($file_name)) $file_name = '.tags';
		parent::__construct($file_name);
	}

	public function get()
	{
		return array_values(static::$key_values);
	}
}
