<?php

/* OtakuCMS ©2016 Andrew Wilkes – http://otakucms.com
 * Released under the MIT license – http://otakucms.com/mit.txt
 *
 * The logged-in user is stored in: $_SESSION['user']
 * Any errors trigger a call to: Session::report_error which sends an error response to the client
 */

class User extends Settings
{
	public $key = 0;
	public $name = '';
	public $email = '';
	public $password = '';
	public $logon_time;
	public $last_time;
	public $initiated;
	public $ip;
	public $last_ip;
	public $url;
	public $lock_period = 1;
	public $release_time = 0;

	const USER_FILE = '.admin';
	const MAX_LOCK_TIME_S = 36;

	// Text
	const T_NOT_FOUND = 'Not found!';
	const T_LOCKED = 'Locked!';
	const T_INCORRECT = 'Incorrect credentials!';
	const T_NO_EMAIL = 'Missing email!';
	const T_INVALID_EMAIL = 'Invalid email!';
	const T_MISSING_PW = 'Missing password!';
	const T_PLOGIN = 'Please login with your email and password';
	const T_ALREADY_REGISTERED = 'Denied! Admin user exists.';

	public function __construct()
	{
		$users = (new Data())->get(self::USER_FILE);
		if (count($users) > 0)
		{
			$props = (array) $users[0];
			foreach($props as $property => $value)
			{
				$this->$property = $value;
			}
		}
	}

	public function get()
	{
		if (isset($_SESSION['user']))
			return $_SESSION['user'];
		if (empty($this->password))
			return 'register';
		else
			return 'login';			
	}

	public function logOn($data)
	{
		if (empty($this->password))
			Session::report_error(self::T_NOT_FOUND, $data);
		else
		{
			if ($this->release_time > time())
				Session::report_error(self::T_LOCKED, $data);

			if ($this->password == md5($data->pass . Config::get_value('salt')->value) && $this->email == $data->email)
			{
				$this->last_time = $this->logon_time;
				$this->last_ip =  $this->ip;
				$this->logon_time = time();
				$this->lock_period = 1;
				$this->release_time = 0;
				new URL();
				$this->url = URL::get_base();
				$this->ip = preg_replace('/[^.:0-9a-z]/', '', $_SERVER['REMOTE_ADDR']);
				$this->save();
				unset($this->password);
				$_SESSION['user'] = $this;
				return [$this];
			}
			else
			{
				$max_lock_period = self::MAX_LOCK_TIME_S;
				$this->lock_period *= 2;
				if ($this->lock_period > $max_lock_period)
					$this->lock_period = $max_lock_period;

				$this->release_time = time() + $this->lock_period;
				$this->save();
				Session::report_error(self::T_INCORRECT, $data);
			}
		}
	}

	public function logOff()
	{
		unset($_SESSION['user']);
	}

	public function register($data)
	{
		if ( ! empty($this->password))
			Session::report_error(self::T_ALREADY_REGISTERED);

		$name = (isset($data->name) ? trim(strip_tags($data->name)) : '');

		if (empty($data->email))
			Session::report_error(self::T_NO_EMAIL);

		$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
		if ( ! filter_var($email, FILTER_VALIDATE_EMAIL))
			Session::report_error(self::T_INVALID_EMAIL);

		if (empty($data->pass))
			Session::report_error(self::T_MISSING_PW);

		$this->name = $name;
		$this->email = $email;
		$this->password =md5($data->pass . Config::get_value('salt')->value);
		$this->initiated = time();

		$this->save();
		return (object) array('msg' => self::T_PLOGIN);					
	}

	public function save($data = null)
	{
		(new Data())->save(self::USER_FILE, [$this]);
	}
}
