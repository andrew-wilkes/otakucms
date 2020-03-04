<?php

$license = "<?php

/* OtakuCMS ©2016 Andrew Wilkes – http://otakucms.com
 * Released under the MIT license – http://otakucms.com/mit.txt
 */";

include '../../../php/kint/Kint.class.php';

$root = '../src/';

$files = [];

$start = $root . 'plugins';

ListFiles($start, $files);

$start = $root . 'php/';

ListFiles($start, $files);

function ListFiles($str, &$files) {
    global $start;
    if(is_file($str)) {
		//$f = file_get_contents($str);
		//printf("%s\t%s\n", $str, md5($f));
        $files[] = $str;
        return;
    } elseif(is_dir($str)) {
        $scan = glob(rtrim($str,'/').'/*');
        foreach($scan as $index => $path){
            ListFiles($path, $files);
        }
        return;
    }
}

$changed = [];

foreach ($files as $fn)
{
    $content = file_get_contents($fn);
    if ($content != '' && strpos($content, 'license') === false)
    {
        $changed[] = $fn;

        file_put_contents($fn, str_replace('<?php', $license, $content));

    }
}

d($changed);