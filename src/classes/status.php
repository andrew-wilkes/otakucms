<?php

/* OtakuCMS ©2016 Andrew Wilkes – http://otakucms.com
 * Released under the MIT license – http://otakucms.com/mit.txt
 */

class Status
{
	public $thisVersion = '6.08';

	public $latestVersion;

	public $result;

	public $administrator;

	public function __construct()
	{
		$this->latestVersion = $this->get_latest_software_version_details();
		$this->administrator = (URL::get_host() == URL::get_sub_domain());
	}

	public function get_latest_software_version_details()
	{
$ch = curl_init('https://api.otakucms.com/?action=version');
		curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_USERAGENT,'OtakuCMS/' . $this->thisVersion . ' (+' . URL::get_host() . ')');
	    $data = curl_exec($ch);
	    $err = curl_error($ch);
	    curl_close ($ch);

	    if (empty($err))
	    	return json_decode($data);
	    else
	    	return '?';
	}

	/**
	/* Downloads the latest code and updates the files
	*/
	public function update()
	{
		$this->result = new Update();
		return $this;
	}
}
