<?php

// With SOLID design principles, this class should probably be abstract,
// but I want to refer to it directly and have the constructor injected
// with a handler for the data interface to initialize it.
// Probably I am using the Decorator Design Pattern but in reverse.
// The advantage of this is that plugins etc. will simply use Data rather
// than a derived class name which they would have to infer from the context

class Data implements iData
{
	const T_INTERFACE_NOT_IMPLEMENTED = "iData interface not implemented!";
	const T_HANDLER_NOT_SET = "Data handler instance not set!";

	private static $handler;
	
	function __construct($ob = null)
	{
		if ($ob)
		{
			if (in_array('iData', class_implements((object)$ob)))
				self::$handler = $ob;
			else
				throw new Exception(self::T_INTERFACE_NOT_IMPLEMENTED);
		}
	}

	public function get_path()
	{
		$this->check_handler_is_set();
		return self::$handler->get_path();
	}

	public function get($table, $transform = true, $ignore_missing_file = false)
	{
		$this->check_handler_is_set();
		return self::$handler->get($table, $transform, $ignore_missing_file);
	}

	public function save($table, $data)
	{
		$this->check_handler_is_set();
		self::$handler->save($table, $data);
	}

	private function check_handler_is_set()
	{
		if (empty(self::$handler))
			throw new Exception(self::T_HANDLER_NOT_SET);
	}
}
