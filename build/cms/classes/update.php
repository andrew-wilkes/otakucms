<?php
class Update
{
	private $zip_location = 'https://api.otakucms.com?action=get-cms';

	public $log;

	public function __construct()
	{
		$this->get_zip_file();
	}

	public function get_zip_file()
	{
		$base_path = dirname(__DIR__) . '/';
		$zip_file = $base_path . 'cms.zip';

		if ( ! file_exists($zip_file) && ! $this->fetch_file($this->zip_location, $zip_file))
			throw new Exception('Failed to get zip file!');

		// Unpack the files
		if (self::zip_open($zip_file))
		{
			$this->modify_files();

			$r = new Result('Moving files');

			// Move the files and/or folders
			$error = $this->deep_move('cms/classes', $base_path . Config::get_value('folders')->classes);
			$error .= $this->deep_move('cms/themes', $base_path . Config::get_value('folders')->themes);
			$error .= $this->deep_move('cms/js', $base_path . Config::get_value('folders')->jsFolder);
			$error .= $this->deep_move('cms/dashboard', $base_path . Config::get_value('folders')->dashboard);

			$scan = glob('{cms/*.php,cms/*.txt}', GLOB_BRACE);

			foreach($scan as $fn)
				$error .= $this->move($fn, $base_path . str_replace('cms/', '', $fn));

			if (empty($error))
				$r->pass('Done');
			else
				throw new Exception(substr($error, 0, 255));
			
			$this->log[] = $r;

			if (empty($error))
			{
				$r = new Result('Deleting zip file');

				if ( @ unlink($zip_file))
					$r->pass('Done');
				else
					throw new Exception('Failed!');

				$this->log[] = $r;

				$r = new Result('Deleting cms temporary folder');
				$error = $this->delete_dir('cms');
				if (empty($error))
					$r->pass('Done');
				else
					throw new Exception($error);
				$this->log[] = $r;				
			}
		}
		else
			throw new Exception('Failed to open zip file!');
	}

	public function deep_move($src, $dest)
	{
		$error = '';

		if (is_dir($dest))
		{
			// Move the files to existing directory
			$scan = glob($src . '/{.htaccess,*}', GLOB_BRACE);
			foreach($scan as $item)
			{
				$new_dest = str_replace($src, $dest, $item);
				if (is_file($item))
					$error .= $this->move($item, $new_dest);
				else
					$error .= $this->deep_move($item, $new_dest);
			}
			// Remove the now empty directory
			$error = $this->delete_dir($src);
		}
		else
		{
			// Move new directory
			$error .= $this->move($src, $dest);
		}

		return $error;
	}

	public function move($src, $dest)
	{
		$error = '';

		if (file_exists($src))
		{
			if( ! rename($src, $dest))
				$error = "Could not move $src to $dest !";
		}
		else
			$error = "Missing source file: $src";
		
		return $error;
	}

	public function delete_dir($dir)
	{
		$error = '';

		if (file_exists($dir) && false === @ rmdir($dir))
			$error = 'Unable to delete temporary directory ' . $dir;

		return $error;
	}

	public function fetch_file($url, $filename)
	{
		$ok = false;
		$r = new Result('Downloading code');

		// Use cURL to fetch the file because it's faster and more likely to work for remote file access than file_get_contents
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_USERAGENT,'OtakuCMS/6 (+' . $_SERVER['HTTP_HOST'] . ')'); // This helps us to track where Otaku CMS is being deployed as well as being friendly towards Firewall filters
	    $raw = curl_exec($ch);
	    $err = curl_error($ch);
	    curl_close ($ch);

	    if (empty($err))
    	{
    		if (strlen($raw) < 1000)
			    throw new Exception($raw);

		    $fp = fopen($filename,'wb');
		    if ($fp === false)
		    	throw new Exception("Error opening file $filename");
		    else
		    {
			    $numbytes = fwrite($fp, $raw);
			    fclose($fp);
			    if ($numbytes === false)
			    	throw new Exception("Error writing to file $filename");
			    
			    $r->pass("Downloaded $url to $filename");
			}
		}
		else
			throw new Exception("Curl error: $err");

		$this->log[] = $r;
		return true;
	}

	public function zip_open($filename)
	{
		$ok = false;
		
		$r = new Result('Unzipping');

		$zip = new ZipArchive();

		if ($zip->open($filename) !== true)
			$r->fail("Failed to open $filename");
		else
		{
			$zip->extractTo('./');
			$r->pass('Unzipped ' . $zip->numFiles . ' files');
			$zip->close();
			$ok = true;
		}

		$this->log[] = $r;

		return $ok;
	}

	public function modify_files()
	{
    $file = 'cms/index.php';
    file_put_contents($file, str_replace("'classes/", "'" . Config::get_value('folders')->classes . "/", file_get_contents($file)));

    $file = 'cms/classes/setup.php';
    file_put_contents($file, str_replace("'data'", "'" . Config::get_value('folders')->dataFolder . "'", file_get_contents($file)));
	}
}
