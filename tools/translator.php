<?php

/*
Tool to:
	1. Scan classes for texts that may be translated
*/

include '../kint.php';

Translator::scan();

class Translator
{
	public static function scan()
	{
		$texts = [];

		$files = glob("../src/classes/*.php");
		foreach($files as $fn)
		{
			$code = file_get_contents($fn);
			if (preg_match_all('/const (T_[_A-Z0-9]+) = "(.+)"/', $code, $matches, PREG_SET_ORDER))
			{
				$items = [];
				foreach($matches as $match)
				{
					$items[] = (object) [
						'tag' => $match[1],
						'eng' => $match[2]
					];
				}
				$texts[] = (object) [
					'filename' => basename($fn),
					'texts' => $items
				];
			}
		}
		file_put_contents('texts.json', json_encode($texts, JSON_PRETTY_PRINT));
	}
}