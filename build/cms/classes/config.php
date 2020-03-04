<?php

class Config extends Dictionary
{
	protected static $key_values;
	protected static $file_name;

	function __construct($file_name = '')
	{
		if (empty($file_name)) $file_name = '.config';
		parent::__construct($file_name);
	}
}