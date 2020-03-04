<?php

/* OtakuCMS ©2016 Andrew Wilkes – http://otakucms.com
 * Released under the MIT license – http://otakucms.com/mit.txt
 */
 
class Images
{
	public static $type;
	public static $width;
	public static $height;
	public static $sizes;

	public function __construct()
	{
		self::$sizes = Settings::get_value('images');
	}

	public function get()
	{
		// Scan for images
		$photos = self::scan('photos');
		$images = self::scan('images');
		$thumbs = self::scan('thumbs');

		// Look for needed images
		$new = array_diff($photos, $images);

		self::create_images($new, 'photos', '', self::$sizes->image); // $data->image is the max width for an image (regular sized image)

		$images = array_merge($images, $new);

		// Look for needed thumbs
		$new = array_diff($images, $thumbs);

		self::create_images($new, '', 'thumbs', self::$sizes->thumb);

		$thumbs = array_merge($thumbs, $new);

		// Look for unwanted thumbs
		$old = array_diff($thumbs, $images);

		self::delete($old, 'thumbs');

		// Pass images data back to the front-end
		return (object) array
		(
			'images' => $images,
			'photos' => $photos,
			'paths' => (object) array
			(
				'thumbs' => self::get_path('thumbs'),
				'images' => self::get_path(),
				'photos' => self::get_path('photos')
			)
		);
	}

	public function remove($data) // Erase all the size combinations of a particular image
	{
		$path = self::get_path('thumbs', false);
		unlink($path . $data->image);

		$path = self::get_path('images', false);
		unlink($path . $data->image);

		$path = self::get_path('photos', false);
		unlink($path . $data->image);
	}

	public static function scan($folder = '')
	{
		$ext = '{*.png,*.gif,*.jpg,*.jpeg,*.PNG,*.GIF,*.JPG,*.JPEG}'; // Allow for the upper case names of camera photos

		$path = self::get_path($folder, false);

		return array_map('sanitize_name', glob($path . $ext, GLOB_BRACE)); // The sanitize_name callback function also renames files as needed
	}

	public static function delete($images, $folder = '') // Erase a bunch of images in a particular folder such as thumbs
	{
		$path = self::get_path($folder, false);

		foreach ($images as $image)
		{
			unlink($path . $image);
		}
	}

	public static function upload($data)
	{
		$m = new Manager('upload');
		$target = self::get_path('photos', false) . filter_var(basename($data->image), FILTER_SANITIZE_URL);

		$m->fetch_file($data->image, $target);

		if ($m->log[0]->class != 'pass')
			Session::abort($m->log[0]->txt);

		$size = getimagesize($target);

		if ( ! is_array($size))
			return Session::abort("Error with image file: " . $data->image);

		// Move small images from photos to images folder
		if ($size[0] <= self::$sizes->image)
			rename($target, self::get_path('images', false) . basename($target));

		return $m->log[0]->txt;
	}

	public static function uploaded() // We may upload 1 file or many where the $_FILES differs
	{
		$result = false;
		if (isset($_FILES['img']))
		{
			$name = $_FILES['img']['name'];
			$uploadedfile = $_FILES['img']['tmp_name'];

			// Do security check on uploaded file
			if ( ! is_uploaded_file($uploadedfile))
				return false;

			$result = self::save_uploaded_image($name, $uploadedfile);
		}
		else
		{
			foreach ($_FILES as $index => $file)
			{
				if ( ! is_uploaded_file($file['tmp_name']))
					return false;
				$result = self::save_uploaded_image($file['name'], $file['tmp_name']);
			}
		}
		return $result;
	}

	public static function save_uploaded_image($name, $file)
	{
		$img = self::load_image($file);

		if (false == $img)
			return false;

		if (file_exists($file))
			unlink($file);
			
		if (self::$width > self::$sizes->image)
			self::create_image($img, self::get_path('photos', false) . $name, self::$sizes->photo);
		else
			self::create_image($img, self::get_path('images', false) . $name, self::$sizes->image);

		return true;
	}

	public static function create_images($images, $src, $dest, $max_size)
	{
		$src = self::get_path($src, false);

		$dest = self::get_path($dest, false);
		
		foreach ($images as $image)
		{
			$img = self::load_image($src . $image);
			if ($img !== false)
				self::create_image($img, $dest . $image, $max_size);
		}
	}

	public static function create_image($img, $dest, $max_size)
	{
		// Limit the width to max_size
		if (self::$width > $max_size)
		{
			$dest_width = $max_size;
			$dest_height = (int) (self::$height * $max_size / self::$width);
		}
		else
		{
			$dest_width = self::$width;
			$dest_height = self::$height;
		}

		$img = self::resize_image($img, self::$width, self::$height, $dest_width, $dest_height);
		self::save_image($img, $dest);
		imagedestroy($img); // Reduce memory useage
	}

	public static function load_image($file)
	{
		$info = getimagesize($file);

		if ( ! is_array($info))
			return false;

		list(self::$width, self::$height, self::$type) = $info;
		
		// Create image from file
		switch(self::$type)
		{
			case IMAGETYPE_GIF:
				return @ imagecreatefromgif($file);
				
			case IMAGETYPE_JPEG:
				return @ imagecreatefromjpeg($file);
				
			case IMAGETYPE_PNG:
				return @ imagecreatefrompng($file);
		}
	}

	public static function save_image($img, $fname)
	{
		// Create the new image file
		switch(self::$type)
		{
			case IMAGETYPE_GIF:
			case 'GIF':
				imagepng($img, $fname);
				self::$type = 'GIF';
				break;
				
			case IMAGETYPE_JPEG:
			case 'JPEG':
				imagejpeg($img, $fname);
				self::$type = 'JPEG';
				break;
				
			case IMAGETYPE_PNG:
			case 'PNG':
				imagepng($img, $fname);
				self::$type = 'PNG';
				break;
		}
	}

	public static function resize_image($source, $src_width, $src_height, $dest_width, $dest_height, $dest_x = 0, $dest_y = 0, $src_x = 0, $src_y = 0)
	{
		// The following code produces a best quality resized image
		$img = imagecreatetruecolor($dest_width, $dest_height);
		imagesavealpha($img, true);
		imagealphablending($img, false);
		imagecopyresampled($img, $source, $dest_x, $dest_y, $src_x, $src_y, $dest_width, $dest_height, $src_width, $src_height);
		return $img;
	}

	public static function get_path($folder = '', $url = true)
	{
		if ($url)
		{
			new URL();
			$path = URL::get_base();
		}
		else
			$path = dirname(__DIR__) . '/';

		$path .= Config::get_value('folders')->images . '/';

		if ('thumbs' == $folder)
			$path .= Config::get_value('folders')->thumbs . '/';

		if ('photos' == $folder)
			$path .= Config::get_value('folders')->photos . '/';

		return $path;
	}
}

// This is a callback function
function sanitize_name($path)
{
	$old_name = basename($path);
	$path = dirname($path) . '/';
	$new_name = filter_var($old_name, FILTER_SANITIZE_URL);

	if ($old_name != $new_name)
	{
		if ('.' == $new_name[0])
			$new_name = mt_rand() . $new_name;
		rename($path . $old_name, $path . $new_name);
	}

	return $new_name;
}
