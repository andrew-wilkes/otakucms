<?php

class Comment
{
	const T_BAD_DATA = "Bad data!";
	const T_AUTHOR_NAME_TOO_SHORT = "Author name is too short!";
	const T_INVALID_EMAIL = "Invalid email!";
	const T_CONTENT_TOO_SHORT = "Content is too short!";

	public $id = 1;
	public $author = '';
	public $email = '';
	public $website = '';
	public $avatar = '';
	public $gravatar = false;
	public $ip = '';
	public $content = '';
	public $approved = false;
	public $parent = 0;
	public $score = 0;
	public $ips = [];
	public $time = 0;
	public $deleted = false;
	public $flagged = false;

	function  __construct($data)
	{
		if ( ! isset($data->comment))
			throw new Exception(self::T_BAD_DATA);

		$this->time = Time::get_time_ms();
		$this->ip = Session::$ip;

		// sanitize data
		$this->id = (int) $data->id; // id has been sanitized already
		$data = $data->comment;

		$this->parent = (int) $data->parent;
		
		if (isset($data->author))
			$this->author = ucwords(strtolower(substr(strip_tags(trim($data->author)), 0, 32)));
		if (strlen($this->author) < 2)
			throw new Exception(self::T_AUTHOR_NAME_TOO_SHORT);

		if (isset($data->email))
			$this->email = filter_var(strtolower(strip_tags(trim($data->email))), FILTER_VALIDATE_EMAIL);
		if (empty($this->email))
			throw new Exception(self::T_INVALID_EMAIL);
		
		if (isset($data->website))
		{
			$website = trim($data->website);
			if (0 !== strpos($website, 'http'))
				$website = 'http://' . $website;
			$this->website = filter_var($website, FILTER_SANITIZE_URL);
		}

		$this->avatar = self::getAvatarUrl($data, $this->email);

		$this->gravatar = isset($data->gravatar);

		if (isset($data->content))
			$this->content = str_replace("<br>", "\n", substr(strip_tags(trim($data->content)), 0, 1024));

		if (strlen($this->content) < 4)
			throw new Exception(self::T_CONTENT_TOO_SHORT);

		return $this;
	}

	public static function getAvatarUrl($data, $email)
	{
		$avatar = '';

		if ( ! empty($data->avatar))
		{
			$url = filter_var($data->avatar, FILTER_SANITIZE_URL);
			if (preg_match('/(.jpg|.png|.gif|.svg|=wavatar|=identicon|=monsterid|=retro)$/', $url))
				$avatar = $url;
		}
		else
		{
			if (isset($data->gravatar))
				$avatar = 'http://www.gravatar.com/avatar/' . md5($email) . '?s=64&d=wavatar';
		}

		return $avatar;
	}
}