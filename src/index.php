<?php

try
{
	include 'classes/setup.php';

	new Seeds();
	new Pages();
	new Categories();
	new Widgets();
	new URL();
	new Route(['category','archive']); // List classes that get activated with matching routes
	new Session();
	new Page();
	Page::render();
	new Hit();
}

catch (Exception $e)
{
	exit($e->getMessage());
}
