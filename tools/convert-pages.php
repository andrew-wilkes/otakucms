<?php

// Convert old cms data to Otaku6

$path = "D:\\webdev\\websites\\var\\www\\weightlossninja.com\\data-a334\\";

$data = json_decode(
'[
    {
        "id": 0,
        "title": "",
        "parent": null,
        "depth": 0,
        "key": 0
    }
]');

$p_template = json_decode(
'{
        "id": 1,
        "title": "OtakuCMS",
        "key": "home",
        "parent": 0,
        "depth": 1,
        "category": 0,
        "description": "Otaku CMS website publishing platform",
        "menu": [],
        "published": "2017-06-17T06:57",
        "tags": [],
        "template": "home",
        "live": true
}');

$pages = json_decode(file_get_contents($path . ".pages"));
foreach($pages as $page)
{
	if ($page->id > 0)
	{
		$html = file_get_contents($path . "pages/" . $page->id . ".htm");
		$content = [(object)["key" => "main-content", "value" => $html]];
		file_put_contents($path . "new/" . $page->id . ".json", json_encode($content, JSON_PRETTY_PRINT));
		continue;
		$p = clone $p_template;
		$p->id = $page->id;
		$p->title = $page->title;
		$p->key = $page->slug;
		$p->parent = $page->parent;
		$p->depth = $page->depth;
		$p->category = $page->category;
		$p->description = $page->description;
		$p->menu = $page->menu;
		$p->published = $page->published;
		$p->tags = $page->tags;
		$p->template = $page->template;
		$p->live = $page->live;
		$data[] = $p;
	}
	//file_put_contents($path . "new/.pages", json_encode($data, JSON_PRETTY_PRINT));
}
