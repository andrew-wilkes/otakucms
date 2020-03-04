<?php

class FileSystem
{
	const FILES_INDEX = 0;
	const DIR_INDEX = 1;

	public static function copy_directory($src, $dst)
	{
		$dir = opendir($src); 
		@mkdir($dst); 
		while(false !== ( $file = readdir($dir)) )
		{ 
			if (( $file != '.' ) && ( $file != '..' ))
			{ 
				if ( is_dir($src . '/' . $file) )
				{ 
					self::copy_directory($src . '/' . $file,$dst . '/' . $file); 
				} 
				else
				{ 
					copy($src . '/' . $file,$dst . '/' . $file); 
				} 
			} 
		} 
		closedir($dir); 
	}

	// Wipes every file and folder except for the root folder
	public static function wipe_dir($dir)
	{
		$items = self::get_item_names($dir);

		array_map('unlink', $items[self::FILES_INDEX]);

		foreach ($items[self::DIR_INDEX] as $dir)
		{
			self::wipe_dir($dir);
			rmdir($dir);
		}			
	}

	public static function get_item_names($dir)
	{
		$files = array_filter(glob($dir . '{.*,*.*}', GLOB_BRACE), function($str) {
			return '.' != $str[strlen($str) - 1];
		});
		$dirs = glob($dir . '*', GLOB_ONLYDIR | GLOB_MARK); // GLOB_MARK - Adds a slash to each directory returned
		return [$files, $dirs];
	}

	public static function get_path($folder_name)
	{
		return dirname(__DIR__) . '/' . $folder_name . '/';
	}
}