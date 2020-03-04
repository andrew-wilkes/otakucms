<?php

// Content Spinner that provides methods to spin content using the Spintax format

class Spintax
{
	public static function spin_items(&$items)
	{
		foreach($items as $key => $text)
			$items[$key] = self::spin($text);
	}
	
	public static function spin($text)
	{
		if (strlen($text) < 1)
			return $text;
		$chrs = str_split($text);
		$chunk = '';
		$segCount = 0;
		$max = 0;
		$maxI = 0;
		$min = 0;
		$minI = 0;
		$pass = 1;
		// Create an array of all the text / spin format segments
		foreach($chrs as $i => $c) {
			switch($c) {
				case '{':
					if ($segCount == 0) {
						if(!empty($chunk))
							$a[] = $chunk;
						$chunk = '';
					} else
						$chunk .= $c;
					$segCount++;
					// Track the start point for a maximum depth
					if ($segCount > $max) {
						$maxI = $i;
						$max = $segCount;
					}
					break;
				case '}':
					$segCount--;
					// Track the start point for a minimum depth
					if ($segCount < $min) {
						$minI = $i;
						$min = $segCount;
					}
					if ($segCount == 0) {
						$a[] = $chunk;
						$chunk = '';
					} else $chunk .= $c;
					break;
				default:
					$chunk .= $c;
					break;
			}
		}
		if(!empty($chunk))
			$a[] = $chunk;

		// Check for errors
		if ($segCount != 0) {
			$e = " missing ";
			if ($segCount > 0) {
				$e = $segCount . $e . "closing";
				$start = $maxI;
			}
			if ($segCount < 0) {
				$e = -$segCount . $e . "opening";
				$start = $minI;
			}
			$start -= 48;
			if ($start < 0)
				$start = 0;
			$near = substr($text, $start, 48);
			$e .= " bracket";
			if (abs($segCount) > 1)
				$e .= "s";
			$text = "ERROR: $e near: $near\n";
			//("Invalid spin syntax in text.");
			return $text;
		}
		// Condense each segment by choosing one of the variants within it
		$chunk = '';
		$text = '';
		foreach($a as $k => $seg) {
			$chrs = str_split($seg);
			foreach($chrs as $c) {
				switch($c) {
					case '{':
						$segCount++;
						$chunk .= $c;
						break;
					case '}':
						$segCount--;
						$chunk .= $c;
						break;
					case '|':
						if ($segCount == 0) {
							$choices[] = $chunk;
							$chunk = '';
						} else $chunk .= $c;
						break;
					default:
						$chunk .= $c;
						break;
				}
			}
			$choices[] = $chunk;
			$chunk = '';
			shuffle($choices);
			$text .= $choices[0];
			unset($choices);
		}
		// Check for any more segment patterns and recursively call this function if they exist
		if (strpos($text, '{') !== false)
			$text = spin($text);
		return $text;
	}
}
