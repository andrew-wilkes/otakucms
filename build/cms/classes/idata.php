<?php

interface iData
{
	public function get($table, $transform = true);

	public function save($table, $data);

	public function get_path();
}