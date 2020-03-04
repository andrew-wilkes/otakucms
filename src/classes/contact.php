<?php

class Contact
{
	const T_BAD_DATA = "Bad data!";
	const T_AUTHOR_NAME_TOO_SHORT = "Author name is too short!";
	const T_INVALID_EMAIL = "Invalid email!";
	const T_CONTENT_TOO_SHORT = "Message is too short!";
	const T_OK = "Thank you for your message!";
	const T_LOGGED = "Your message was logged. Thanks!";
	const T_MAIL_SUBJECT = "New contact form submission";

	public $author = '';
	public $email = '';
	public $ip = '';
	public $message = '';
	public $time = 0;

	function  __construct($data)
	{
		if (empty($data))
			throw new Exception(self::T_BAD_DATA);

		$this->time = Time::get_time_ms();
		$this->ip = Session::$ip;

		// sanitize data
		if (isset($data->author))
			$this->author = ucwords(strtolower(substr(strip_tags(trim($data->author)), 0, 32)));
		if (strlen($this->author) < 2)
			throw new Exception(self::T_AUTHOR_NAME_TOO_SHORT);

		if (isset($data->email))
			$this->email = filter_var(strtolower(strip_tags(trim($data->email))), FILTER_VALIDATE_EMAIL);
		if (empty($this->email))
			throw new Exception(self::T_INVALID_EMAIL);

		if (isset($data->message))
			$this->message = trim($data->message);

		if (strlen($this->message) < 4)
			throw new Exception(self::T_CONTENT_TOO_SHORT);

		try
		{
			// Send email
			new Mail();
			$result = Mail::send(self::T_MAIL_SUBJECT, sprintf("%s\n%s\n\n%s", $data->author, $data->email, $data->message));
			if (true === $result)
				exit(self::T_OK);
			else
				exit($result);

		}
		catch (Exception $e)
		{
			// Save data
			new Log('.messages');
			Log::add($this);
			Log::save_data();
			exit(self::T_LOGGED);	
		}
	}
}