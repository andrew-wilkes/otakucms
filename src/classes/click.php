<?php

class Click
{
	public $id;
	public $ip;
	public $referer;
	public $user_agent;
	public $time;

	public function __construct($testing = true)
	{
		if (empty($_GET['id']))
			throw new Exception('No ID!');

		$id = preg_replace('/[^a-z_0-9A-Z]/', '', $_GET['id']);
		if (empty($id))
			throw new Exception('Bad ID!');

		$this->id = $id;
	
		// Get visitor info.
		$this->ip = Session::$ip;
		if (isset($_SERVER['HTTP_REFERER']))
			$this->referer = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
		if (isset($_SERVER['HTTP_USER_AGENT']))
			$this->user_agent = strip_tags($_SERVER['HTTP_USER_AGENT']);
		$this->time  = date('Y-m-d H:i:s');

		new Log('.clicks');
		Log::add($this);
		Log::save_data();

		if (!$testing) exit(true);
	}
}
