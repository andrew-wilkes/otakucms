<?php

class ClassAutoloader
{
	const T_MISSING_CLASS_FILE = "Missing class file: "; // T_... tags language text that may be translated

	private static $path;

    public function __construct($dir = '')
    {
    	if (empty($dir)) $dir = __DIR__ . '/';
    	
    	self::$path = $dir;

        spl_autoload_register([$this, 'loader']);
    }

	private function loader($class)
	{	
		// The regex transforms a ClassName to class-name (PascalCase to kebab-case) for the file name and route-matching
		$class_file = strtolower(self::$path . rtrim(preg_replace('/([A-Z]+[a-z0-9]*)+?/', '$1-', $class), '-') . '.php');
		if (file_exists($class_file))
			include $class_file;
		else
			throw new Exception(self::T_MISSING_CLASS_FILE . $class_file);
	}
}