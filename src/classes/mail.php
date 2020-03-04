<?php

class Mail
{
	private static $mailer;
	private static $email;

	public function __construct($debug = 0)
	{
		$email = Config::get_value('email');
		if (empty($email->host))
			throw new Exception("Email setup incomplete!");
			
		$mailer = new PHPMailer();
		$mailer->SMTPDebug = $debug; // 0 - 4
		if ($debug)
			$mailer->Debugoutput = 'echo';
		$mailer->isSMTP(); // Use SMTP
		$mailer->Host = $email->host;
		$mailer->SMTPAuth = true;
		$mailer->Username = $email->username;
		$mailer->Password = $email->password;
		$mailer->SMTPSecure = $email->encryption_method;
		$mailer->Port = $email->port;
		$mailer->CharSet = 'UTF-8';
		self::$mailer = $mailer;
		self::$email = $email;
	}

	public static function send($subject, $text = '', $html = '', $to_name = '', $to_address = '', $from_name = '', $from_address = '')
	{
		self::$mailer->Subject = $subject;

		if (empty($to_name))
			$to_name = self::$email->to_name;

		if (empty($to_address))
			$to_address = self::$email->to_address;

		if (empty($from_name))
			$from_name = self::$email->from_name;

		if (empty($from_address))
			$from_address = self::$email->from_address;

		self::$mailer->FromName = $from_name;
		self::$mailer->From = $from_address;
		self::$mailer->addAddress($to_address, $to_name);

		if (empty($html))
		{
			self::$mailer->isHTML(false);
			self::$mailer->Body = $text;
		}
		else
		{
			self::$mailer->isHTML(true);
			self::$mailer->Body = $html;
			self::$mailer->AltBody = $text;
		}

		if (self::$mailer->send())
			return true;
		else
			return self::$mailer->ErrorInfo;
	}
}