<?php

/*
This class deals with POST data sent by the front-end
*/

class Server
{
  public $log;
  public $error;
  public $dashboard = '';

  private $htaccess;
  private $zip_location = 'http://otaku6.api/?action=get-cms'; //BUILD  public $zip_location = 'https://api.otakucms.com?action=get-cms';
  private $multi_site = false;
  private $server_type =  '';
  private $data_folder = 'data';
  private $data;
  private $config;

  public function __construct()
  {
    $this->data = (array) json_decode(file_get_contents("php://input"));
    try
    {
      $this->build_config_data();
      $this->server_config();
      $this->create_data_folders();
      if ($this->data['exampleContent'])
      {
        $this->generate_pages();
        $this->write_categories();
      }
      else
        $this->dashboard = $this->config[0]->dashboard; // Cause a redirect to the Dashboard later
      $this->write_config();
      $this->write_settings();
      $this->get_zip_file();
    } catch (Exception $e) {
      $this->error = $e->getMessage();
    }
  }

  public function modify_files()
  {
    $file = 'cms/index.php';
    file_put_contents($file, str_replace("'classes/", "'{$this->config[0]->classes}/", file_get_contents($file)));

    $file = 'cms/classes/setup.php';
    file_put_contents($file, str_replace("'data'", "'{$this->config[0]->dataFolder}'", file_get_contents($file)));
  }

  public function write_config()
  {
    $r = new Result('Write config data');
    $r->pass('OK');

    file_put_contents($this->data_folder . '.config', json_encode($this->config, JSON_PRETTY_PRINT));

    $this->log[] = $r;
  }

  public function write_settings()
  {
    $r = new Result('Write settings data');
    $r->pass('OK');

    file_put_contents($this->data_folder . '.settings', Settings::$data);

    $this->log[] = $r;
  }

  public function write_categories()
  {
    $r = new Result('Write categories data');
    $r->pass('OK');

    file_put_contents($this->data_folder . '.categories', Categories::$data);

    $this->log[] = $r;
  }

  public function generate_pages()
  {
    $r = new Result('Generate example content');
    $r->pass('OK');

    foreach (Regions::$content as $key => $value)
    {
      $value = str_replace('#DASHBOARD#', '<a href=\"' . $this->config[0]->dashboard . '/\" target=\"_blank\">Dashboard</a>', $value);
      file_put_contents($this->data_folder . 'pages/' . $key . '.json', $value);
    }
    foreach (Comments::$content as $key => $value)
    {
      file_put_contents($this->data_folder . '.comments-' . $key, $value);
    }
    file_put_contents($this->data_folder . '.pages', Pages::$page_data);

    $this->log[] = $r;
  }

  public function get_zip_file()
  {
    $zip_file = 'cms.zip';

    if ( ! file_exists($zip_file) && ! $this->fetch_file($this->zip_location, $zip_file))
      throw new Exception('Failed to get zip file!');

    // Unpack the files
    if (self::zip_open($zip_file))
    {
      $this->modify_files();

      $r = new Result('Moving files');

      // Move the files and/or folders
      $error = $this->deep_move('cms/classes', __DIR__ . '/' . $this->config[0]->classes);
      $error .= $this->deep_move('cms/themes', __DIR__ . '/' . $this->config[0]->themes);
      $error .= $this->deep_move('cms/js', __DIR__ . '/' . $this->config[0]->jsFolder);
      $error .= $this->deep_move('cms/dashboard', __DIR__ . '/' . $this->config[0]->dashboard);

      $scan = glob('{cms/*.php,cms/*.txt}', GLOB_BRACE);

      foreach($scan as $fn)
        $error .= $this->move($fn, __DIR__ . '/' . str_replace('cms/', '', $fn));

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

  public function create_data_folders()
  {
    $this->data_folder = __DIR__ . '/' . $this->config[0]->dataFolder . '/';
    $img_dir = __DIR__ . '/' . $this->config[0]->images . '/';

    $this->create_directory($this->data_folder, 'data');    

    // The data folder must be blocked from viewing in a web browser so we install a .htaccess file
    $r = new Result('Write to data folder');
    $r->pass('OK');

    if ( ! empty($this->htaccess))
      file_put_contents($this->data_folder . '.htaccess', $this->htaccess);

    $this->create_directory($this->data_folder . 'pages/', 'pages');
    $this->create_directory($this->data_folder . 'lang/', 'languages');

    // Set up the images folder
    $this->create_directory($img_dir, 'images');
    $this->create_directory($img_dir . $this->config[0]->thumbs . '/', 'thumbnails');
    $this->create_directory($img_dir . $this->config[0]->photos . '/', 'photos');
  }

  public function create_directory($dir, $name)
  {
    $mode = 0777; // File permissions to use

    if ( ! file_exists($dir))
    {
      $r = new Result('Creating ' . $name . ' folder');
      if (mkdir($dir, $mode, true))
        $r->pass('OK');
      else
      {
        $this->log[] = $r;
        throw new Exception('Failed to create directory!');
      }

      $this->log[] = $r;
    }

    if ( ! is_writable($dir))
    {
      $r = new Result('Making ' . $name . ' writable');
      if (chmod($dir, $mode))
        $r->pass('OK');
      else
      {
        $this->log[] = $r;
        throw new Exception('Cannot change file permissions!');
      }

      $this->log[] = $r;
    }

    $r = new Result('Writing to ' . $name);

    if (false === file_put_contents($dir . 'index.php', ''))
    {
      $this->log[] = $r;
      throw new Exception('Failed to write to file!');
    }
    else
      $r->pass('OK'); 

    $this->log[] = $r;  
  }

  public function server_config()
  {
    if (empty($this->data['server'])) return;

    switch($this->data['server'])
    {
      case 'windows':
        $this->create_windows_config_file();
        break;

      case 'nginx':
        $this->create_nginx_file();
        break;

      case 'linux':
        $this->create_htaccess_file();
        $this->htaccess = "Order Deny,Allow\nDeny from all";
        break;
    }
  }

  public function create_htaccess_file()
  {
    $r = new Result('Installing main .htaccess file');

    if (false === @ file_put_contents('.htaccess', Templates::htaccess(['base' => $this->config[0]->basePath])))
    {
      $this->log[] = $r;
      throw new Exception('Failed to write .htaccess file!');
    }
    else
      $r->pass('OK'); 

    $this->log[] = $r;
  }

  public function robots()
  {
    $r = new Result('Installing robots.txt file');

    if (false === @ file_put_contents('robots.txt', 'Sitemap: ' . $this->config[0]->basePath . "sitemap.xml\nUser-agent: *\nDisallow: /" . $this->config[0]->redirect . "/\n"))
    {
      $this->log[] = $r;
      throw new Exception('Failed to write robots.txt file!');
    }
    else
      $r->pass('OK'); 

    $this->log[] = $r;
  }

  public function create_windows_config_file()
  {
    $r = new Result('Create web.config file');

    $config = Templates::$windows;

    if (file_put_contents('web.config', $config) === false)
    {
      $this->log[] = $r;
      throw new Exception('Failed to write web.config file!');
    }
    else
      $r->pass('OK');

    $this->log[] = $r;
  }

  public function create_nginx_file()
  {
    $r = new Result('Create nginx.txt file');
    $nginx = Templates::$nginx;
    $nginx = str_replace('#FOLDER#', __DIR__, $nginx);
    $nginx = str_replace('#HOST#', $_SERVER['HTTP_HOST'], $nginx);
    $nginx = str_replace('#DATA_FOLDER#', preg_replace('/-.+/', '-', $this->config[0]->dataFolder), $nginx);
    $nginx = str_replace('#CLASSES_FOLDER#',  preg_replace('/-.+/', '-', $this->config[0]->classes), $nginx);

    if (file_put_contents('nginx.txt', $nginx) === false)
    {
      $this->log[] = $r;
      throw new Exception('Failed to write nginx.txt file!');
    }
    else
      $r->pass('OK');

    $this->log[] = $r;
  }

  public function build_config_data()
  {
    $config =
    [
      [
        "key" => "folders",
        "dataFolder" => "data",
        "classes" => "classes",
        "themes" => "themes",
        "jsFolder" => "js",
        "images" => "images",
        "photos" => "photos",
        "thumbs" => "thumbs",
        "dashboard" => "dashboard",
        "redirect" => "goto",
        "basePath" => "/"
      ],
      (object)
      [
        "key" => "meta_tags",
        "value" =>
        [
          (object) ["charset" => "UTF-8"],
          (object) ["viewport" => "width=device-width"],
          (object) ["stylesheet" => "styles.css"]
        ]
      ],
      (object)
      [
        "key" => "javascripts",
        "value" => []
      ],
      (object)
      [
        "key" => "salt",
        "value" => rand(1,999)
      ],
      (object)
      [
        "key" => "subdomains",
        "value" => $this->get_subdomains_list()
      ],
      (object)
      [
        "key" => "email",
        "host" => "",
        "port" => "",
        "encryption_method" => "",
        "username" => "",
        "password" => "",
        "to_name" => "",
        "to_address" => "",
        "from_name" => "",
        "from_address" => ""
      ],
      (object)
      [
        "key" => "classes",
        "User" => ["get","api","logOn","logOff","register"],
        "Comments" => ["get_status","api"],
        "Contact" => [],
        "Click" => []
      ]
    ];

    // Sanitize folder names
    $folders = $config[0];
    foreach ($folders as $key => $value)
    {
      $folders[$key] = $this->filter_folder_name($key, $value);
    }
    $config[0] = (object) $folders;

    $this->config = $config;
  }

  public function filter_folder_name($key, $value)
  {
    $name = isset($this->data[$key]) ? preg_replace('#[^-a-z0-9_A-Z/]#', '', trim($this->data[$key], ' -_/')) : $value;
    return $name ? $name : $value;
  }

  public function get_subdomains_list()
  {
    $key = 'subdomains';
    if (empty($this->data[$key])) return '';

    $subs = $this->data[$key];

    if (strlen($subs) > 0)
    {
      $r = new Result('Validate subdomains list');

      $subs = explode(',', $subs);
      foreach ($subs as $k => $v)
      {
        $v = preg_replace('/[^-a-z0-9_A-Z]/', '', $v);
        if (strlen($v) < 1)
          unset($subs[$k]);
        else
          $subs[$k] = $v;
      }
      $subs = implode(',', $subs);
      if ($subs == $_POST[$key])
        $r->pass($subs);
      else
        throw new Exception('Modified to: ' . $subs);

      $this->log[] = $r;
      return $subs;
    }
  }
}
