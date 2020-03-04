<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') exit(json_encode(new Server()));
/*
This class is used to check the server compatibility and to provide info. to the front-end.
*/

class App
{
  public $title = "OtakuCMS Installation Wizard";
  public $testName = '';
  public $error = false;

  public function __construct()
  {
    try
    {
      $this->check_php_version();
      $this->check_vars();
      $this->check_writing_file();
    }
    catch (Exception $e)
    {
      $this->error = $e->getMessage();
    }
  }

  public function get_server_type()
  {
    $server = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
    if (stripos($server, 'icrosoft'))
      return 'windows';
    elseif (stripos($server, 'ginx'))
      return 'nginx';
    else
      return 'linux';
  }

  public function check_php_version($fail = false)
  {
    $this->testName = 'Check PHP version';

    if (version_compare(phpversion(), '5.3.9', '<') or $fail)
      throw new Exception('This script requires PHP version 5.4.0 or higher to run. You are using version: ' . phpversion());
  }

  public function check_vars($fail = false)
  {
    $this->testName = 'Check server vars';

    if (empty($_SERVER['HTTP_HOST']) or $fail)
      throw new Exception('The required HTTP_HOST $_SERVER variable was not available.');
  }

  public function check_writing_file()
  {
    $this->testName = 'Check writing file';

    $fn = 'temp.php';
    $a = array('item1', 'item2');
    
    if (false === @ file_put_contents($fn, serialize($a)))
      throw new Exception("Unable to create/write to $fn file. You will need to change the file permissions of your website.");

    $this->testName = 'Get data from file';

    $b = @ unserialize( @ file_get_contents($fn));
    if ( ! is_array($b) || $a[0] !== $b[0])
      throw new Exception("Unable to retrieve data from test file $fn.");

    $this->testName = 'Delete file';

    if ( ! unlink($fn))
      throw new Exception("Unable to delete $fn file.");
  }

  public function rand_str($min = 6, $max = 12)
  {
    return substr(md5(time() + rand(0,99)), 0, rand($min, $max));
  }

  public function get_base_path()
  {
    $path = str_replace('\\', '/', substr(__DIR__, strlen( @ $_SERVER['DOCUMENT_ROOT']))) . '/';
    return $path;
  }

  public function get_sub_domain()
  {
    preg_match_all('/([-0-9a-z]+)\./U', $_SERVER['HTTP_HOST'], $matches);
    $sub_domain = $matches[1][0];
    if ($sub_domain == 'www')
      $sub_domain = $matches[1][1];
    return $sub_domain;
  }

  public function dashboard_name()
  {
    $names = ['dashboard', 'cpanel', 'admin'];
    shuffle($names);
    return $names[0];
  }

  public function domain()
  {
    preg_match_all('/([-0-9a-z]+)\./U', $_SERVER['HTTP_HOST'], $matches);
    $sub_domain = $matches[1][0];
    if ($sub_domain == 'www')
      $sub_domain = $matches[1][1];
    return $sub_domain;
  }
}

$app = new App();

?><!DOCTYPE html>
<html>
  <head>
    <title><?php echo $app->title; ?></title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic">
    <style>
        .help {
            display: block;
            font-size: 0.8em;
            margin-top: -1rem;
        }
        .row.help {
            margin-top: 0;
        }
        .danger, .fail {
            color: #ff3860;
        }
        .input.danger, .input:focus.danger {
            border-color: #ff3860;
        }
        .input {
            margin-bottom: 1rem;
        }
        .pass {
          color: #090;
        }
<?php echo CSS::$milligram; ?>
    </style>
  </head>
  <body>
    <div class="container">
    <h1><?php echo $app->title; ?></h1>
    <hr>
    <div class="row">
      <div class="column" id="app">
        <template v-if="error">
          <h3>{{testName}}</h3>
          <p class="danger">{{error}}</p>
        </template>
        <template v-else>
          <settings v-if="showSettings" v-on:done="processData($event)"></settings>
          <result v-if="showResult" :result="resultData"></result>
        </template>
      </div>
    </div>
  </div>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/vee-validate/2.0.0-beta.25/vee-validate.min.js"></script>
    <script src="https://unpkg.com/vue"></script>
  <script>
    Vue.use(VeeValidate, { delay: 1000 });

    new Vue({
        el: "#app",
        data: function() {
            return {
              showSettings: true,
              showResult: false,
              resultData: null,
              testName: "<?php echo $app->testName; ?>",
              error: "<?php echo $app->error; ?>"
            }
        },
        methods: {
          processData: function(data) {
            showSettings: false;
            this.showResult = true;
            this.resultData = data;
          }
        },
        components: {
            'settings': {
                data: function() {
                    return {
                      showSettings: true,
                  showSpinner: false,
                      config: {},
                      servers: [
                        {
                          id: "windows",
                          label: "IIS (Windows)"
                        },
                        {
                          id: "linux",
                          label: "Apache (most common on Linux servers)"
                        },
                        {
                          id: "nginx",
                          label: "NGINX"
                        }
                      ]
                    }
                },
            methods: {
              submit: function() {
                this.showSettings = false;
                this.showSpinner = true;
                let _this = this;
                        axios.post('', this.config).then( function(response) {
                          _this.showSpinner = false;
                          _this.$emit('done', response.data);
                        })
                        .catch( function(error) {
                            alert(error.response.statusText);
                        })            
              }
            },
          mounted: function() {
            this.config = {
              basePath: '<?php echo $app->get_base_path(); ?>',
              multiSite: false,
              subdomains: '',
              topDomain: '<?php echo $app->domain(); ?>',
              server: '<?php echo $app->get_server_type(); ?>',
              exampleContent: true,
              dashboard: '<?php echo $app->dashboard_name(); ?>',
              classes: 'classes-<?php echo $app->rand_str(4,6); ?>',
              themes: 'themes',
              images: 'images',
              photos: 'photos',
              thumbs: 'thumbs',
              dataFolder: 'data-<?php echo $app->rand_str(4,6); ?>',
              jsFolder: 'js',
              redirect: 'goto'
            };
          },
                template: 
                  `<div>
                  <div v-if="showSpinner">
                    <h2>Installing Your website ...</h2>
                    <div><?php echo SVG::$spinner; ?></div>
                  </div>
                  <div v-if="showSettings">
                    <h2>Website Configuration Settings</h2>
                    <p>Take some time to customize the settings for your website or accept the suggested settings. Finally, click on the button at the bottom of the page to start the install process.</p>
                    <form v-on:submit.prevent>
                        <fieldset>
                        <h3>Website Details</h3>
                          <label for="basePathField">Path to index.php file</label>
                  <input id="basePathField" type="text" name="basePath" v-model="config.basePath"
                  v-validate="'required|min:1'" :class="{'input': true, 'danger': errors.has('basePath') }">
                  <span v-show="errors.has('basePath')" class="help danger">{{ errors.first('basePath') }}</span>

                  <input type="checkbox" id="multiSiteField" name="multiSite" v-model="config.multiSite">
                  <label class="label-inline" for="multiSiteField">Is this website a multiple subdomain site?</label>

                  <div v-show="config.multiSite">
                    <label for="subdomainsField">Allowed sub domains</label>
                    <input type="text" placeholder="sub1,sub2,sub3" id="subdomainsField" name="subdomains"
                    v-model="config.subdomains">
                    <p>Comma-separated list of allowed sub domains for use with multi-site option</p>
                  </div>

                  <label for="topDomainField">Top level domain</label>
                  <input type="text" placeholder="yourdomain" name="topDomain" id="topDomainField"
                  v-model="config.topDomain" v-validate="'required'" :class="{'input': true, 'danger': errors.has('topDomain') }">
                  <p>Domain name minus country code e.g. "mysite.com" becomes "mysite"</p>
                  <span v-show="errors.has('topDomain')" class="help danger">{{ errors.first('topDomain') }}</span>

                  <fieldset>
                      <legend>Type of web server</legend>
                      <template v-for="server in servers">
                      <input type="radio" name="server" :id="server.id" :value="server.id" v-model="config.server">
                      <label class="label-inline" :for="server.id">{{server.label}}</label>
                    </template>
                  </fieldset>

                  <h3>Example Content</h3>
                  <fieldset>
                    <input type="checkbox" id="exampleContentField" name="exampleContent" v-model="config.exampleContent">
                    <label class="label-inline" for="exampleContentField">Install example content?</label>
                  </fieldset>

                  <h3>Folder Names</h3>

                  <p>Here is a chance to customize the locations of files to help with obscuring your site fingerprint.<br>
                  However, Javascript and image folders are easily discovered via URLs in the website code.</p>

                  <label for="dashboardField">Dashboard folder</label>
                  <input type="text" name="dashboard" id="dashboardField"
                  v-model="config.dashboard" v-validate="'required'" :class="{'input': true, 'danger': errors.has('dashboard') }">
                  <span v-show="errors.has('dashboard')" class="help danger">{{ errors.first('dashboard') }}</span>
                  <p>* Make a note of this location since it may not be linked from theme templates.</p>

                  <label for="classesField">Classes (plugins) folder</label>
                  <input type="text" placeholder="Enter folder name" name="classes" id="classesField"
                  v-model="config.classes" v-validate="'required'" :class="{'input': true, 'danger': errors.has('classes') }">
                  <span v-show="errors.has('classes')" class="help danger">{{ errors.first('classes') }}</span>

                  <label for="themesField">Themes folder</label>
                  <input type="text" placeholder="Enter themes folder name" name="themes" id="themesField"
                  v-model="config.themes" v-validate="'required'" :class="{'input': true, 'danger': errors.has('themes') }">
                  <span v-show="errors.has('themes')" class="help danger">{{ errors.first('themes') }}</span>

                  <label for="imagesField">Images folder</label>
                  <input type="text" placeholder="Enter images folder name" name="images" id="imagesField"
                  v-model="config.images" v-validate="'required'" :class="{'input': true, 'danger': errors.has('images') }">
                  <span v-show="errors.has('images')" class="help danger">{{ errors.first('images') }}</span>

                  <label for="thumbsField">Thumbnails folder</label>
                  <input type="text" placeholder="Enter thumbnails folder name" name="thumbs" id="thumbsField"
                  v-model="config.thumbs" v-validate="'required'" :class="{'input': true, 'danger': errors.has('thumbs') }">
                  <span v-show="errors.has('thumbs')" class="help danger">{{ errors.first('thumbs') }}</span>

                  <label for="photosField">Photos folder</label>
                  <input type="text" placeholder="Enter photos folder name" name="photos" id="photosField"
                  v-model="config.photos" v-validate="'required'" :class="{'input': true, 'danger': errors.has('photos') }">
                  <span v-show="errors.has('photos')" class="help danger">{{ errors.first('photos') }}</span>

                  <label for="dataField">Data folder</label>
                  <input type="text" placeholder="Enter data folder name" name="dataFolder" id="dataField"
                  v-model="config.dataFolder" v-validate="'required'" :class="{'input': true, 'danger': errors.has('dataFolder') }">
                  <span v-show="errors.has('dataFolder')" class="help danger">{{ errors.first('dataFolder') }}</span>

                  <label for="jsField">Javascript folder</label>
                  <input type="text" placeholder="Enter javascript folder name" name="jsFolder" id="jsField"
                  v-model="config.jsFolder" v-validate="'required'" :class="{'input': true, 'danger': errors.has('jsFolder') }">
                  <span v-show="errors.has('jsFolder')" class="help danger">{{ errors.first('jsFolder') }}</span>

                  <h3>Miscellaneous Settings</h3>

                  <label for="redirectField">URL redirect stub</label>
                  <input type="text" placeholder="Enter value for URL redirect stub" name="redirect" id="redirectField" v-model="config.redirect">

                  <h3>Email Settings</h3>

<h4>Mail server details</h4>
<label for="hostField">Host</label>
<input type="text" placeholder="smtp.zoho.com" name="host" id="hostField" v-model="config.host">

<label for="portField">Port</label>
<input type="text" placeholder="587" name="port" id="portField" v-model="config.port">

<label for="encryption_methodField">Encryption Method</label>
<select id="encryption_methodField">
<option value="tls">TLS</option>
<option value="ssl">SSL</option>
</select>

<label for="usernameField">Username</label>
<input type="text" placeholder="" name="username" id="usernameField" v-model="config.username">

<label for="passwordField">Password</label>
<input type="text" placeholder="" name="password" id="passwordField" v-model="config.password">

<h4>Default sender and recipient details</h4>
<label for="to_nameField">To name</label>
<input type="text" placeholder="Mailbox" name="to_name" id="to_nameField" v-model="config.to_name">

<label for="to_addressField">To email address</label>
<input type="text" placeholder="mailbox@mysite.com" name="to_address" id="to_addressField" v-model="config.to_address">

<label for="from_nameField">From name</label>
<input type="text" placeholder="Website" name="from_name" id="from_nameField" v-model="config.from_name">

<label for="from_addressField">From email address</label>
<input type="text" placeholder="mailbox@mysite.com" name="from_address" id="from_addressField" v-model="config.from_address">
                  <div class="row column">
                                <input
                                    class="button-primary"
                                    type="submit"
                                    value="Install"
                                    :disabled="errors.any()"
                                    @click="submit()">
                            </div>
                        </fieldset>
                      </form>
                  </div>
                  </div>`
        },
        'result': {
                props: ['result'],
                template:
                  `<div>
                    <h2>Result</h2>
                    <h3 class="danger" v-if="result.error">Error: {{result.error}}</h3>
                    <p class="pass" v-else><strong>Success!</strong></p>
                    <home_button :result="result"></home_button>
                    <ul><li v-for="item in result.log" :class="item.class">{{item.heading}}</li></ul>
                    <home_button :result="result"></home_button>                    
                  </div>`,
                components: {
                  'home_button': {
                    props: ['result'],
                    methods: {
                      home: function() {
                        window.location = this.result.dashboard;                        
                      }
                    },
                    template: '<button ng-if="!result.error" class="button-primary" @click="home()">View Website</button>'
                  }
                }
        }
      }
    });
  </script>
  </body>
</html>

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

/*
Class to provide Result objects
*/

class Result
{
  public $heading;
  public $class;
  public $txt;

  public function __construct($heading = '')
  {
    $this->heading = $heading;
  }

  public function pass($txt)
  {
    $this->txt = $txt;
    $this->class = 'pass';
  }

  public function fail($txt)
  {
    $this->txt = $txt;
    $this->class = 'fail';
  }

}

class CSS
{
  public static $milligram = "
/*!
 * Milligram v1.3.0
 * https://milligram.github.io
 *
 * Copyright (c) 2017 CJ Patoilo
 * Licensed under the MIT license
 */

*,*:after,*:before{box-sizing:inherit}html{box-sizing:border-box;font-size:62.5%}body{color:#606c76;font-family:'Roboto', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;font-size:1.6em;font-weight:300;letter-spacing:.01em;line-height:1.6}blockquote{border-left:0.3rem solid #d1d1d1;margin-left:0;margin-right:0;padding:1rem 1.5rem}blockquote *:last-child{margin-bottom:0}.button,button,input[type='button'],input[type='reset'],input[type='submit']{background-color:#9b4dca;border:0.1rem solid #9b4dca;border-radius:.4rem;color:#fff;cursor:pointer;display:inline-block;font-size:1.1rem;font-weight:700;height:3.8rem;letter-spacing:.1rem;line-height:3.8rem;padding:0 3.0rem;text-align:center;text-decoration:none;text-transform:uppercase;white-space:nowrap}.button:focus,.button:hover,button:focus,button:hover,input[type='button']:focus,input[type='button']:hover,input[type='reset']:focus,input[type='reset']:hover,input[type='submit']:focus,input[type='submit']:hover{background-color:#606c76;border-color:#606c76;color:#fff;outline:0}.button[disabled],button[disabled],input[type='button'][disabled],input[type='reset'][disabled],input[type='submit'][disabled]{cursor:default;opacity:.5}.button[disabled]:focus,.button[disabled]:hover,button[disabled]:focus,button[disabled]:hover,input[type='button'][disabled]:focus,input[type='button'][disabled]:hover,input[type='reset'][disabled]:focus,input[type='reset'][disabled]:hover,input[type='submit'][disabled]:focus,input[type='submit'][disabled]:hover{background-color:#9b4dca;border-color:#9b4dca}.button.button-outline,button.button-outline,input[type='button'].button-outline,input[type='reset'].button-outline,input[type='submit'].button-outline{background-color:transparent;color:#9b4dca}.button.button-outline:focus,.button.button-outline:hover,button.button-outline:focus,button.button-outline:hover,input[type='button'].button-outline:focus,input[type='button'].button-outline:hover,input[type='reset'].button-outline:focus,input[type='reset'].button-outline:hover,input[type='submit'].button-outline:focus,input[type='submit'].button-outline:hover{background-color:transparent;border-color:#606c76;color:#606c76}.button.button-outline[disabled]:focus,.button.button-outline[disabled]:hover,button.button-outline[disabled]:focus,button.button-outline[disabled]:hover,input[type='button'].button-outline[disabled]:focus,input[type='button'].button-outline[disabled]:hover,input[type='reset'].button-outline[disabled]:focus,input[type='reset'].button-outline[disabled]:hover,input[type='submit'].button-outline[disabled]:focus,input[type='submit'].button-outline[disabled]:hover{border-color:inherit;color:#9b4dca}.button.button-clear,button.button-clear,input[type='button'].button-clear,input[type='reset'].button-clear,input[type='submit'].button-clear{background-color:transparent;border-color:transparent;color:#9b4dca}.button.button-clear:focus,.button.button-clear:hover,button.button-clear:focus,button.button-clear:hover,input[type='button'].button-clear:focus,input[type='button'].button-clear:hover,input[type='reset'].button-clear:focus,input[type='reset'].button-clear:hover,input[type='submit'].button-clear:focus,input[type='submit'].button-clear:hover{background-color:transparent;border-color:transparent;color:#606c76}.button.button-clear[disabled]:focus,.button.button-clear[disabled]:hover,button.button-clear[disabled]:focus,button.button-clear[disabled]:hover,input[type='button'].button-clear[disabled]:focus,input[type='button'].button-clear[disabled]:hover,input[type='reset'].button-clear[disabled]:focus,input[type='reset'].button-clear[disabled]:hover,input[type='submit'].button-clear[disabled]:focus,input[type='submit'].button-clear[disabled]:hover{color:#9b4dca}code{background:#f4f5f6;border-radius:.4rem;font-size:86%;margin:0 .2rem;padding:.2rem .5rem;white-space:nowrap}pre{background:#f4f5f6;border-left:0.3rem solid #9b4dca;overflow-y:hidden}pre>code{border-radius:0;display:block;padding:1rem 1.5rem;white-space:pre}hr{border:0;border-top:0.1rem solid #f4f5f6;margin:3.0rem 0}input[type='email'],input[type='number'],input[type='password'],input[type='search'],input[type='tel'],input[type='text'],input[type='url'],textarea,select{-webkit-appearance:none;-moz-appearance:none;appearance:none;background-color:transparent;border:0.1rem solid #d1d1d1;border-radius:.4rem;box-shadow:none;box-sizing:inherit;height:3.8rem;padding:.6rem 1.0rem;width:100%}input[type='email']:focus,input[type='number']:focus,input[type='password']:focus,input[type='search']:focus,input[type='tel']:focus,input[type='text']:focus,input[type='url']:focus,textarea:focus,select:focus{border-color:#9b4dca;outline:0}select{background:url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"14\" viewBox=\"0 0 29 14\" width=\"29\"><path fill=\"#d1d1d1\" d=\"M9.37727 3.625l5.08154 6.93523L19.54036 3.625\"/></svg>') center right no-repeat;padding-right:3.0rem}select:focus{background-image:url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"14\" viewBox=\"0 0 29 14\" width=\"29\"><path fill=\"#9b4dca\" d=\"M9.37727 3.625l5.08154 6.93523L19.54036 3.625\"/></svg>')}textarea{min-height:6.5rem}label,legend{display:block;font-size:1.6rem;font-weight:700;margin-bottom:.5rem}fieldset{border-width:0;padding:0}input[type='checkbox'],input[type='radio']{display:inline}.label-inline{display:inline-block;font-weight:normal;margin-left:.5rem}.container{margin:0 auto;max-width:112.0rem;padding:0 2.0rem;position:relative;width:100%}.row{display:flex;flex-direction:column;padding:0;width:100%}.row.row-no-padding{padding:0}.row.row-no-padding>.column{padding:0}.row.row-wrap{flex-wrap:wrap}.row.row-top{align-items:flex-start}.row.row-bottom{align-items:flex-end}.row.row-center{align-items:center}.row.row-stretch{align-items:stretch}.row.row-baseline{align-items:baseline}.row .column{display:block;flex:1 1 auto;margin-left:0;max-width:100%;width:100%}.row .column.column-offset-10{margin-left:10%}.row .column.column-offset-20{margin-left:20%}.row .column.column-offset-25{margin-left:25%}.row .column.column-offset-33,.row .column.column-offset-34{margin-left:33.3333%}.row .column.column-offset-50{margin-left:50%}.row .column.column-offset-66,.row .column.column-offset-67{margin-left:66.6666%}.row .column.column-offset-75{margin-left:75%}.row .column.column-offset-80{margin-left:80%}.row .column.column-offset-90{margin-left:90%}.row .column.column-10{flex:0 0 10%;max-width:10%}.row .column.column-20{flex:0 0 20%;max-width:20%}.row .column.column-25{flex:0 0 25%;max-width:25%}.row .column.column-33,.row .column.column-34{flex:0 0 33.3333%;max-width:33.3333%}.row .column.column-40{flex:0 0 40%;max-width:40%}.row .column.column-50{flex:0 0 50%;max-width:50%}.row .column.column-60{flex:0 0 60%;max-width:60%}.row .column.column-66,.row .column.column-67{flex:0 0 66.6666%;max-width:66.6666%}.row .column.column-75{flex:0 0 75%;max-width:75%}.row .column.column-80{flex:0 0 80%;max-width:80%}.row .column.column-90{flex:0 0 90%;max-width:90%}.row .column .column-top{align-self:flex-start}.row .column .column-bottom{align-self:flex-end}.row .column .column-center{-ms-grid-row-align:center;align-self:center}@media (min-width: 40rem){.row{flex-direction:row;margin-left:-1.0rem;width:calc(100% + 2.0rem)}.row .column{margin-bottom:inherit;padding:0 1.0rem}}a{color:#9b4dca;text-decoration:none}a:focus,a:hover{color:#606c76}dl,ol,ul{list-style:none;margin-top:0;padding-left:0}dl dl,dl ol,dl ul,ol dl,ol ol,ol ul,ul dl,ul ol,ul ul{font-size:90%;margin:1.5rem 0 1.5rem 3.0rem}ol{list-style:decimal inside}ul{list-style:circle inside}.button,button,dd,dt,li{margin-bottom:1.0rem}fieldset,input,select,textarea{margin-bottom:1.5rem}blockquote,dl,figure,form,ol,p,pre,table,ul{margin-bottom:2.5rem}table{border-spacing:0;width:100%}td,th{border-bottom:0.1rem solid #e1e1e1;padding:1.2rem 1.5rem;text-align:left}td:first-child,th:first-child{padding-left:0}td:last-child,th:last-child{padding-right:0}b,strong{font-weight:bold}p{margin-top:0}h1,h2,h3,h4,h5,h6{font-weight:300;letter-spacing:-.1rem;margin-bottom:2.0rem;margin-top:0}h1{font-size:4.6rem;line-height:1.2}h2{font-size:3.6rem;line-height:1.25}h3{font-size:2.8rem;line-height:1.3}h4{font-size:2.2rem;letter-spacing:-.08rem;line-height:1.35}h5{font-size:1.8rem;letter-spacing:-.05rem;line-height:1.5}h6{font-size:1.6rem;letter-spacing:0;line-height:1.4}img{max-width:100%}.clearfix:after{clear:both;content:' ';display:table}.float-left{float:left}.float-right{float:right}
";
}

class Templates
{
  public static $nginx = "
# Otakucms server configuration template for NGINX

server {
  server_name #HOST#;
  root #FOLDER#;
  access_log /var/log/nginx/#HOST#.access.log;

  listen 80;
  listen 443 ssl;

  # You need to generate these ssl certificates. Visit: https://letsencrypt.org/
  # ssl_certificate /etc/letsencrypt/live/#HOST#/fullchain.pem;
  # ssl_certificate_key /etc/letsencrypt/live/#HOST#/privkey.pem;
  # ssl_trusted_certificate /etc/letsencrypt/live/#HOST#/chain.pem;

  index index.php index.html;

  location ~ ^/#DATA_FOLDER# {
    deny all;
    return 404;
  }

  location ~ ^/#CLASSES_FOLDER# {
    deny all;
    return 404;
  }

  location = /favicon.ico {
    log_not_found off;
    access_log off;
  }

  location = /robots.txt {
    allow all;
    log_not_found off;
    access_log off;
  }
  
  location / {
    try_files \$uri \$uri/ /index.php?route=\$uri;
  }

  # static file 404's aren't logged and expires header is set to maximum age
  location ~* \.(jpg|jpeg|gif|css|png|js|ico|html)$ {
    access_log off;
    log_not_found off;
    expires max;
  }
  
  location ~ \.php$ {
    try_files  \$uri =404;
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php7.0-fpm.sock;
    fastcgi_intercept_errors on;
  }

  location ~ /\.ht {
    deny all;
  }
}";

  public static $windows = '<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Imported Rule 1" stopProcessing="true">
                    <match url="^data" ignoreCase="false" />
                    <action type="Redirect" url="error404" redirectType="Temporary" />
                </rule>
                <rule name="Imported Rule 2" stopProcessing="true">
                    <match url="^classes" ignoreCase="false" />
                    <action type="Redirect" url="error404" redirectType="Temporary" />
                </rule>
                <rule name="Imported Rule 3" stopProcessing="true">
                    <match url="^([-a-z0-9_./=]+)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php?route={R:1}" appendQueryString="false" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>';

  public static function htaccess($params)
  {
    return '
ErrorDocument 404 /error404

RewriteEngine On

RewriteBase ' . $params['base'] . '

RewriteRule ^classes error404 [L,R=404]
RewriteRule ^data error404 [L,R=404]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([-a-z0-9_./=]+)$ index.php?route=$1 [L]
';
  }
}

class SVG
{
  public static $spinner = '<?xml version="1.0" encoding="utf-8"?><svg width="106px" height="106px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="uil-squares"><rect x="0" y="0" width="100" height="100" fill="none" class="bk"></rect><rect x="15" y="15" width="20" height="20" fill="#9b4dca" class="sq"><animate attributeName="fill" from="#9b4dca" to="#6a2782" repeatCount="indefinite" dur="1s" begin="0.0s" values="#6a2782;#6a2782;#9b4dca;#9b4dca" keyTimes="0;0.1;0.2;1"></animate></rect><rect x="40" y="15" width="20" height="20" fill="#9b4dca" class="sq"><animate attributeName="fill" from="#9b4dca" to="#6a2782" repeatCount="indefinite" dur="1s" begin="0.125s" values="#6a2782;#6a2782;#9b4dca;#9b4dca" keyTimes="0;0.1;0.2;1"></animate></rect><rect x="65" y="15" width="20" height="20" fill="#9b4dca" class="sq"><animate attributeName="fill" from="#9b4dca" to="#6a2782" repeatCount="indefinite" dur="1s" begin="0.25s" values="#6a2782;#6a2782;#9b4dca;#9b4dca" keyTimes="0;0.1;0.2;1"></animate></rect><rect x="15" y="40" width="20" height="20" fill="#9b4dca" class="sq"><animate attributeName="fill" from="#9b4dca" to="#6a2782" repeatCount="indefinite" dur="1s" begin="0.875s" values="#6a2782;#6a2782;#9b4dca;#9b4dca" keyTimes="0;0.1;0.2;1"></animate></rect><rect x="65" y="40" width="20" height="20" fill="#9b4dca" class="sq"><animate attributeName="fill" from="#9b4dca" to="#6a2782" repeatCount="indefinite" dur="1s" begin="0.375" values="#6a2782;#6a2782;#9b4dca;#9b4dca" keyTimes="0;0.1;0.2;1"></animate></rect><rect x="15" y="65" width="20" height="20" fill="#9b4dca" class="sq"><animate attributeName="fill" from="#9b4dca" to="#6a2782" repeatCount="indefinite" dur="1s" begin="0.75s" values="#6a2782;#6a2782;#9b4dca;#9b4dca" keyTimes="0;0.1;0.2;1"></animate></rect><rect x="40" y="65" width="20" height="20" fill="#9b4dca" class="sq"><animate attributeName="fill" from="#9b4dca" to="#6a2782" repeatCount="indefinite" dur="1s" begin="0.625s" values="#6a2782;#6a2782;#9b4dca;#9b4dca" keyTimes="0;0.1;0.2;1"></animate></rect><rect x="65" y="65" width="20" height="20" fill="#9b4dca" class="sq"><animate attributeName="fill" from="#9b4dca" to="#6a2782" repeatCount="indefinite" dur="1s" begin="0.5s" values="#6a2782;#6a2782;#9b4dca;#9b4dca" keyTimes="0;0.1;0.2;1"></animate></rect></svg>';
}

/*
Class to provide page data
*/

class Pages
{
    public static $page_data = '[
    {
        "id": 0,
        "title": "",
        "parent": null,
        "depth": 0,
        "key": 0
    },
    {
        "id": 1,
        "title": "OtakuCMS",
        "key": "home",
        "parent": 0,
        "depth": 1,
        "category": 0,
        "description": "Otaku CMS website publishing platform",
        "menu": [
            "top",
            "footer"
        ],
        "published": "2017-06-17T13:57",
        "tags": [
            "otaku",
            "cms"
        ],
        "template": "home",
        "live": true
    },
    {
        "id": 2,
        "title": "Blog",
        "key": "blog",
        "parent": 0,
        "depth": 1,
        "category": 0,
        "description": "",
        "menu": [
            "top",
            "footer"
        ],
        "published": "2017-06-19T05:27",
        "tags": [],
        "template": "blog",
        "live": true
    },
    {
        "id": 3,
        "title": "Blog Post 1",
        "key": "blog-post-1",
        "parent": 2,
        "depth": 2,
        "category": 1,
        "description": "",
        "menu": [
            "sidebar",
            "blog"
        ],
        "published": "2017-06-19T05:27",
        "tags": [],
        "template": "post",
        "live": true
    },
    {
        "id": 4,
        "title": "Blog Post 2",
        "key": "blog-post-2",
        "parent": 2,
        "depth": 2,
        "category": 2,
        "description": "",
        "menu": [
            "sidebar",
            "blog"
        ],
        "published": "2017-06-19T05:28",
        "tags": [],
        "template": "post",
        "live": true
    },
    {
        "id": 5,
        "title": "About",
        "key": "about",
        "parent": 0,
        "depth": 1,
        "category": 0,
        "description": "",
        "menu": [
            "footer"
        ],
        "published": "2017-06-19T05:28",
        "tags": [],
        "template": "page",
        "live": true
    },
    {
        "id": 6,
        "title": "Contact",
        "key": "contact",
        "parent": 0,
        "depth": 1,
        "category": 0,
        "description": "",
        "menu": [
            "footer"
        ],
        "published": "2017-06-19T05:29",
        "tags": [],
        "template": "contact",
        "live": true
    },
    {
        "id": 7,
        "title": "Category",
        "key": "category",
        "parent": 0,
        "depth": 1,
        "category": 0,
        "description": "",
        "menu": [],
        "published": "2017-06-18T08:09",
        "tags": [],
        "template": "category",
        "live": true
    },
    {
        "id": 8,
        "title": "Archive",
        "key": "archive",
        "parent": 0,
        "depth": 1,
        "category": 0,
        "description": "",
        "menu": [],
        "published": "2017-06-18T09:21",
        "tags": [],
        "template": "archive",
        "live": true
    }
]';
}

/*
Class to provide content region data
*/

class Regions
{
  public static $content = [ 1 => '[
    {
        "key": "main-content",
        "value": "<p>Welcome to <b>OtakuCMS<\/b> (Otaku Content Management System).<\/p>\n<p><img class=\"align-left\" src=\"https:\/\/otakucms.com\/images\/logo.png\">To become the administrator user, the first step is to go to the #DASHBOARD# and Register, then you may log in.<\/p>\n<p>When logged in, you will be able to directly edit the web page content like we have here. A tool panel will show up with familiar WYSIWYG editing tools. When you are done editing, click a green arrow icon in the top left corner of the page to save your changes and exit the editor mode.<\/p>\n<p class=\"note\">In the bottom left are icons related to the current web page element that you can click on to change attributes.<\/p>\n<p>A word count appears in the bottom right corner.<\/p>\n<p>The image uploader allows you to crop to size before you upload to the cloud where the image will optimized, saved, and inserted into the page content. You may then resize the image and move it around.<\/p>\n<p>Check out the <a href=\"http:\/\/getcontenttools.com\/demo\" target=\"_blank\">Content Tools Demo<\/a> for full details of how to edit page content.<\/p>\n<p>Find out about the <a href=\"http:\/\/cloudinary.com\/\" target=\"_blank\">Cloudinary Image Management<\/a> solution that we make use of.<\/p>"
    }
]', 3 => '[
    {
        "key": "main-content",
        "value": "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse laoreet non metus non eleifend. Duis pharetra maximus leo ut consectetur. Vestibulum sit amet interdum justo, eu ornare ex. Mauris eleifend pretium neque tempor pulvinar. Ut pellentesque lectus imperdiet odio sagittis, sollicitudin imperdiet mi rhoncus.<\/p>\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse laoreet non metus non eleifend. Duis pharetra maximus leo ut consectetur. Vestibulum sit amet interdum justo, eu ornare ex. Mauris eleifend pretium neque tempor pulvinar. Ut pellentesque lectus imperdiet odio sagittis, sollicitudin imperdiet mi rhoncus.<\/p>\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse laoreet non metus non eleifend. Duis pharetra maximus leo ut consectetur. Vestibulum sit amet interdum justo, eu ornare ex. Mauris eleifend pretium neque tempor pulvinar. Ut pellentesque lectus imperdiet odio sagittis, sollicitudin imperdiet mi rhoncus.<\/p>"
    }
]', 4 => '[
    {
        "key": "main-content",
        "value": "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse laoreet non metus non eleifend. Duis pharetra maximus leo ut consectetur. Vestibulum sit amet interdum justo, eu ornare ex. Mauris eleifend pretium neque tempor pulvinar. Ut pellentesque lectus imperdiet odio sagittis, sollicitudin imperdiet mi rhoncus.<\/p>"
    }
]', 5 => '[
    {
        "key": "main-content",
        "value": "<h1>About Us<\/h1>\n<p>A few words about what we do.&nbsp;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse laoreet non metus non eleifend. Duis pharetra maximus leo ut consectetur. Vestibulum sit amet interdum justo, eu ornare ex. Mauris eleifend pretium neque tempor pulvinar. Ut pellentesque lectus imperdiet odio sagittis, sollicitudin imperdiet mi rhoncus.<\/p>"
    }
]', 6 => '[
    {
        "key": "main-content",
        "value": "<p>Contact us using the form below.<\/p>"
    }
]'];
}

class Comments
{
  public static $content = [ 3 => '[
    {
        "id": 1,
        "author": "Ted Cleaver",
        "email": "ted@xyz.com",
        "website": "",
        "avatar": "http:\/\/www.gravatar.com\/avatar\/d92bffb7c3db64aa533ae9b2a680386c?s=64&d=wavatar",
        "gravatar": true,
        "ip": "127.0.0.2",
        "content": "Hello, this is the first comment.",
        "approved": true,
        "parent": 0,
        "score": 1,
        "ips": [
            "127.0.0.1"
        ],
        "time": 1491637509000,
        "deleted": false,
        "flagged": false
    },
    {
        "id": 2,
        "author": "Mary Jane",
        "email": "mj@xyz.com",
        "website": "http:\/\/xyz.xyz",
        "avatar": "",
        "gravatar": false,
        "ip": "127.0.0.3",
        "content": "Welcome to the blog Ted!",
        "approved": true,
        "parent": 1,
        "score": 0,
        "ips": [],
        "time": 1491637652000,
        "deleted": false,
        "flagged": false
    }
]'];

}

/*
Class to provide categories data
*/

class Categories
{
    public static $data = '[
    {
        "id": 0,
        "title": "",
        "parent": null,
        "depth": 0,
        "key": 0
    },
    {
        "id": 1,
        "title": "Cars",
        "key": "cars",
        "parent": 0,
        "depth": 1,
        "count": 1
    },
    {
        "id": 2,
        "title": "Bikes",
        "key": "bikes",
        "parent": 0,
        "depth": 1,
        "count": 0
    }
]';
}
/*
Class to provide settings data
*/

class Settings
{
    public static $data = '[
    {
        "key": "project",
        "name": "The Project Name",
        "theme": "default",
        "notes": "Some notes"
    },
    {
        "key": "timing",
        "nextUpdateIntervalMin": 1,
        "nextUpdateIntervalMax": 3
    },
    {
        "key": "images",
        "cloud_name": "",
        "upload_preset": "",
        "max_image_width": "800",
        "max_image_height": "800",
        "folder": ""
    },
    {
        "key": "styles",
        "value": [
            {
                "name": "Note",
                "class": "note",
                "tags": ["p"]
            }
        ]
    }
]';
}
