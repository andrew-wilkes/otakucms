<?php

// Build tool

include '../kint.php';
include '../src/classes/zip.php';

include '../vendor/autoload.php';

include 'file-system.php';

$b = new Build('src','build/cms');
$b->wipe_test_site();
$b->wipe_target_directory();
$b->copy_files();
$b->delete_js_map_files();
$b->productionize_files(['', 'classes', 'js']);
$b->modify_theme();
$b->make_zip_file();
$b->update_repo();

class Build
{
	public $src;
	public $dest;

	public function __construct($src, $dest)
	{
		$this->src = FileSystem::get_path($src);
		$this->dest = FileSystem::get_path($dest);
	}

	public function delete_js_map_files()
	{
		$files = glob($this->dest . 'dashboard/js/*.map');
		foreach($files as $file)
			unlink($file);
	}
	
	public function modify_theme()
	{
		// Concatenate css files
		$path = $this->dest . '/themes/default/';
		$styles = $path . 'styles.css';
		$css = file_get_contents($styles);
		$normalize = $path . 'normalize.css';
		$alignment = $path . 'alignment.css';
		file_put_contents($styles, file_get_contents($normalize));
		file_put_contents($styles, $css, FILE_APPEND);
		file_put_contents($styles, file_get_contents($alignment), FILE_APPEND);
		unlink($normalize);
		unlink($alignment);
		$header = $path . '/header.php';
		file_put_contents($header, str_replace("'normalize.css','styles.css','alignment.css'", "'styles.css'", file_get_contents($header)));
	}

	public function update_repo()
	{
		$repo = dirname(__DIR__) . '/dist/api/repo/';
		// Move zip file to repo
		rename($this->dest . 'cms.zip', $repo . 'cms.zip');

		// Copy installer
		$installer = dirname(__DIR__) . '/installer/build/index.php';
		copy($installer, dirname(__DIR__) . '/test/index.php');
		copy($installer, $repo . 'index.php');
		$fn = function($code) {
			return $this->productionize_code($code);
		};
		$this->process_file($repo . 'index.php', $fn);
	}

	public function wipe_test_site()
	{
		$dest = dirname(__DIR__) . '/test/';
		FileSystem::wipe_dir($dest);
	}

	public function make_zip_file()
	{
		$this->dest = dirname($this->dest) . '/';
		$zip = new Zip();
		if ($zip->open($this->dest . 'cms.zip', ZIPARCHIVE::CREATE) === true)
		{
			$zip->base = $this->dest;
			$zip->add_directory($this->dest);
			$status = $zip->status;
			$zip->close();
		}
	}

	public function process_file($file, $fn)
	{
		file_put_contents($file, $fn(file_get_contents($file)));
	}

	public function productionize_files($folders)
	{
		foreach($folders as $folder)
		{
			$pattern = $this->dest . $folder . '/*.php';
			$files = glob(str_replace('//', '/', $pattern));
			$fn = function($code) {
				return $this->productionize_code($code);
			};
			foreach($files as $file)
			{
				$this->process_file($file, $fn);
			}
		}
	}
	/*
	Remove any code from the start of a line until after the //BUILD marker.
	Trim off any leading white space chr
	If the line is now empty, discard it.
	*/
	public function productionize_code($code)
	{
		$marker = "//BUILD";
		$offset = strlen($marker);
		$production_code = [];
		$lines = explode("\n", str_replace("\r", '', $code));
		foreach($lines as $line)
		{
			$index = strpos($line, $marker);
			if ($index !== false)
			{
				$line = preg_replace("#.*$marker\s?#", '', $line);
				if (empty($line))
					continue;
			}
			$production_code[] = $line;
		}
		return implode("\n", $production_code);
	}

	public function test_productionize_code()
	{
		$input = "//BUILD\nline2 //BUILD test\nline3//BUILD\n//BUILD line4\n//BUILDline5\r\n//BUILD\t\tline6\nok";
		$expected_output = "test\nline4\nline5\n\tline6\nok";

		return ($expected_output == $this->productionize_code($input)) ? 'PASSED' : 'FAILED';
	}

	public function copy_files()
	{
		$root_files = ['index.php'];
		foreach($root_files as $file)
		{
			copy($this->src . $file, $this->dest . $file);
		}
		foreach(['classes/','dashboard/','js/','themes/'] as $folder)
		{
			FileSystem::copy_directory($this->src . $folder, $this->dest . $folder);
		}
	}

	public function process_javascript_files()
	{
		$licence_notice = "// (c) Andrew Wilkes 2016-" . date("Y") . " https://otakucms.com\n// License: MIT\n";

		// Identify js files
		$path = $this->dest . 'js/';

		$js_files = ['angular.min.js']; // File names that must be in a specific order
		$js_files = array_merge($js_files, array_diff(array_map('basename', glob($path . '*.js')), $js_files)); // All of the .js file names
		$js_min_files = glob($path . '*.min.js'); // Full paths to all of the .min.js files

		// Delete unwanted map files
		array_map('unlink', glob($this->dest . 'dashboard/js/*.js.map'));

		// Produce bundled minified code file
		$js_bundle = '';
		foreach($js_files as $file_name)
		{
			// Don't bundle certain files
			if ( ! in_array($file_name, ['palette.js', 'zxcvbn.js']))
			{
				$file = $path . $file_name;
				$js = file_get_contents($file);
				if ( ! in_array($file, $js_min_files))
					$js = $licence_notice . \JShrink\Minifier::minify($js);

				$js_bundle .= "\n\n// " . $file_name . "\n\n" . $js;
				unlink($file);
			}
		}
		file_put_contents($this->dest . 'dashboard/js/bundle.min.js', trim($js_bundle));
	}

	public function wipe_target_directory()
	{
		FileSystem::wipe_dir($this->dest);
	}
}