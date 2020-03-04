<?php

class Hit
{
	public $page_id;
	public $ip;
	public $referer;
	public $user_agent;
	public $time;

	protected static $record;

	public function __construct()
	{
		// Get visitor info.
		$this->ip = Session::$ip;
		if (isset($_SERVER['HTTP_REFERER']))
			$this->referer = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
		if (isset($_SERVER['HTTP_USER_AGENT']))
			$this->user_agent = strip_tags($_SERVER['HTTP_USER_AGENT']);
		$this->page_id = Page::get_value('id');
		$this->time  = date('Y-m-d H:i:s');

		static::$record = $this;

		// Check for error404 page
		if ($this->page_id == 'error404')
			$file = '.404s';
		else
			$file = '.hits';

		if (strpos($this->user_agent, 'bot') === false)
		{
			new Log($file);
			Log::add($this);
			Log::save_data();

			Tokens::add([
				'IP' => $this->ip,
				'REFERER' => $this->referer,
				'USER_AGENT' => $this->user_agent
			]);
		}
	}

	public static function get()
	{
		return static::$record;
	}
}
