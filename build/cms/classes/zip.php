<?php

/* OtakuCMS ©2016 Andrew Wilkes – http://otakucms.com
 * Released under the MIT license – http://otakucms.com/mit.txt
 */

class Zip extends ZipArchive
{
	const FILES_TO_IGNORE = ".hg, .hgignore, node_modules";
	const T_CREATED = 'Created backup';
	const T_TIMED_OUT = "Backup timed out!";

	private $max_time;

	public $base;
	public $result;

  public function __construct()
  {
  	$t = (int) ini_get('max_execution_time');
  	if (empty($t))
  		$t = 30;

  	$this->max_time = time() + $t - 5;
  	$this->result = self::T_CREATED;
  }

  public function add_directory($dir)
	{
		$files_to_ignore = array_merge(['.', '..'], explode(',', str_replace(' ', '', self::FILES_TO_IGNORE)));

        foreach(scandir($dir) as $file)
		{
			if ( ! in_array($file, $files_to_ignore) && pathinfo($file, PATHINFO_EXTENSION) != 'zip')
			{
				$file = $dir . '/' . $file;
				if(is_dir($file))
					$this->add_directory($file);
				else
					$this->addFile($file, str_replace($this->base, '', $file));
			}
			
			if (time() > $this->max_time)
			{
				$this->result = self::T_TIMED_OUT;
				break;
			}
    }
  }
}
