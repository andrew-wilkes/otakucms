<?php

class Result
{
	public $heading;
	public $class;
	public $txt;

	public function __construct($heading = '')
	{
		$this->heading = $heading;
	}

	public function pass($txt)
	{
		$this->txt = $txt;
		$this->class = 'pass';
	}

	public function fail($txt)
	{
		$this->txt = $txt;
		$this->class = 'fail';
	}
}