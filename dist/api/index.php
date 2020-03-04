<?php

define('VERSION_NUMBER', '6.08');
define('VERSION_NOTES', '["Avoid displaying the word: Array."]');

$act = @ $_GET['action'];

switch($act)
{
	case 'stats101':
		API::stats();
		break;

	case 'installer-php':
		API::installer_php();
		break;

	case 'installer-zip':
		API::installer_zip();
		break;

	case 'get-cms':
		API::get_cms();
		break;

	case 'version':
		API::version();
		break;

	case 'get-plugin':
		API::get_plugin();
		break;

	case 'plugin-version':
		API::plugin_version();
		break;

	case 'get-theme':
		API::get_theme();
		break;

	case 'theme-version':
		API::get_theme_version();
		break;

	default:
		if ( ! empty($_GET))
			die('Action not recognized!');

		API::home();
		break;
}

class API
{
	public static function na()
	{
		echo "Sorry, this feature has not been implemented yet!";
	}

	public static function installer_php()
	{
		self::download('index.php');
	}

	public static function installer_zip()
	{
		self::download('otakucms-install.zip');
	}

	public static function get_cms()
	{
		self::download('cms.zip');
	}

	public static function version()
	{
		exit( '{ "number": "' . VERSION_NUMBER . '", "notes": ' . VERSION_NOTES . ' }' );
	}

	public static function get_plugin()
	{
		self::na();
	}

	public static function plugin_version()
	{
		self::na();
	}

	public static function get_theme()
	{
		self::na();
	}

	public static function theme_version()
	{
		self::na();
	}

	public static function stats()
	{
		$txt = "File\tIP\tTime\tReferer\tAgent\n\n" . @ file_get_contents('repo/.hits');
		self::display($txt);
	}

	public static function log($fn)
	{
		$ip    = @ $_SERVER['REMOTE_ADDR'];
		$ref   = @ $_SERVER['HTTP_REFERER'];
		$agent = @ $_SERVER['HTTP_USER_AGENT'];

		$time  = date('Y-m-d H:i:s');

		$new = array("$fn\t$ip\t$time\t$ref\t$agent");

		if (strpos($agent, 'bot.') !== false)
			return;

		if (strpos($agent, '.build') !== false)
			return;

		$hits = @ file_get_contents('repo/.hits');

		if (empty($hits))
			$hits = array();
		else
			$hits = explode("\n", $hits);

		foreach($hits as $i => $hit)
			if ($i < 999)
				$new[] = $hit;

		file_put_contents('repo/.hits', implode("\n", $new));
	}

	public static function download($fn)
	{
		$file = 'repo/' . $fn;
        if(file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;
        }
		else
			die("File $fn does not exist!");
	}

	public static function display($txt, $title = '')
	{
		$rows = explode("\n", $txt);
		$grid = '';
		foreach ($rows as $row)
		{
			$data = explode("\t", $row);
			$grid .= '<tr>';
			foreach ($data as $td)
			{
				$grid .= '<td>' . $td . '</td>';
			}
			$grid .= '</tr>';
		}

		self::header();

		if ( ! empty($title))
			echo "<h1>$title</h1>";
?>
<table id="table">
<thead><tr><th>Version</th><th>Notes</th>
<tbody><?php echo $grid; ?></tbody>
</table>
<?php
		self::footer();
	}

	public static function home()
	{
		self::header();
?>
<h1>Otaku CMS API</h1>
<p>Useage: <span class="link">http://api.otakucms.com?action=NAME_OF_ACTION<i>&amp;param1=VALUE1&amp;param2=VALUE2</i></span> etc.</i></p>

<h2>Available Actions</h2>
<table id="table">
<thead><tr><th>Action</th><th>Parameters</th><th>Response</th><th>Notes</th></tr></thead>
<tbody>
	<tr><td><a title="Download" href="http://api.otakucms.com?action=installer-php" rel="nofollow">installer-php</a></td><td>-</td><td>index.php</td><td>Download the installer script</td></tr>
	<tr><td><a title="Download" href="http://api.otakucms.com?action=get-cms" rel="nofollow">get-cms</a></td><td>-</td><td>cms.zip</td><td>Download the archived CMS script files</td></tr>
	<tr><td>version</td><td>-</td><td>Version details</td><td>Check the latest version</td></tr>
	<tr><td>get-plugin</td><td>id=PLUGIN_ID</td><td>plugin.zip</td><td>Download a particular plugin</td></tr>
	<tr><td>plugin-version</td><td>id=PLUGIN_ID</td><td>Version details</td><td>Check the latest version</td></tr>
	<tr><td>get-theme</td><td>id=THEME_ID</td><td>theme.zip</td><td>Download a particular theme</td></tr>	
	<tr><td>theme-version</td><td>id=THEME_ID</td><td>Version details</td><td>Check the latest version</td></tr>
</tbody>
</table>
<?php
		self::footer();
	}

	public static function header()
	{
		?><!DOCTYPE html>
<html>
<head>
<title>Otaku CMS API</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style type="text/css">
	body {background-color: #eeeeee; color: #000000; font: 10pt arial;}
	#page {margin: 20px 100px; padding: 10px 20px; background-color: #ffffff; border-radius: 10px;}
	h1 {color: #999999;}
	h2 {color: #666666;}
	#table {width: 100%; border-collapse: collapse;}
	th{text-align: left;}
	th, td {padding: 10px;}
	tr:hover {background-color: #eeeeee}
	.link {color: #0000cc}
	footer {border-top: 1px solid #cccccc; margin-top: 20px; padding: 10px 0 0;}
	footer, footer a {color: #666666; font-size: 8pt;}
	td a {color: #000000; text-decoration: none;}
	td a:hover {color: #cc0000; text-decoration: underline;}
</style>
<body>
<div id="page">
<?php
	}

	public static function footer()
	{ ?>
<footer>&copy; <?php echo date("Y"); ?> <a href="http://otakucms.com">Otaku CMS</a> - <a href="http://api.otakucms.com/">API</a></footer>
</div>
</body>
</head>
</html>
<?php
	}
}
