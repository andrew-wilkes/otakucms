<?php

/* OtakuCMS ©2016 Andrew Wilkes – http://otakucms.com
 * Released under the MIT license – http://otakucms.com/mit.txt
 */

class Backup
{
	private $base_dir;
	private $backup_folder;
	private $timing;

	public function __construct()
	{
		$this->base_dir = dirname(__DIR__);
		$this->backup_folder = $this->base_dir . '/' . Config::get_value('folders')->backups . '/';

		$timing = json_decode((new Data())->get('.backup', false, true));
		
		if (empty($timing))
			$this->timing = (object) array(
				'lastTime' => 0,
				'backupIntervalHours' => 24,
				'backupFilesToKeep' => 4
			);
		else
			$this->timing = $timing[0];
	}

	public function set_last_time()
	{
		$this->timing->lastTime = time(); // Set the new backup time stamp
		(new Data())->save('.backup', [$this->timing]);
	}
	
	public function file_name()
	{
		return sprintf('%s-backup-%s.zip', str_replace('.', '-', basename($this->base_dir)), date('Y-m-d'));
	}

	public function do_backup()
	{
		$zip = new Zip();
		if ($zip->open($this->backup_folder . $this->file_name(), ZIPARCHIVE::CREATE) === true)
		{
			$zip->base = $this->base_dir;
			$zip->add_directory($this->base_dir);
			$status = $zip->status;
			$zip->close();
		}
		return $zip->result;
	}
	
	public function check()
	{
		if (($this->timing->lastTime + 3600 * $this->timing->backupIntervalHours) < time())
		{
			$result = $this->do_backup();

			$this->set_last_time();

			$files = $this->backup_files();
		}
		return $this->timing->lastTime;
	}

	// Return a sorted list of the backup files
	public function backup_files()
	{
		$files = array();
		foreach (glob($this->backup_folder . '*.zip') as $filename)
		{
			$files[$filename] = filemtime($filename);
		}
		
		// Arrange the files in order of age(youngest first)
		arsort($files);

		// Delete old backups
		$count = 0;
		foreach ($files as $fn => $time)
		{
			$count++;
			if ($count > $this->timing->backupFilesToKeep)
			{
				unlink($fn);
				unset($files[$fn]);
			}
		}

		return array_keys($files);
	}

	public function download()
	{
		if ((new Status())->administrator)
		{
			$files = $this->backup_files();
			if (count($files) > 0)
			{
				header('Content-type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . basename($files[0]) . '"');
				header('Content-Transfer-Encoding: binary');
				exit(file_get_contents($files[0]));
			}
			else
				die('No backups are available!');
		}
		else
			die('Permission denied!');
	}
		
}
