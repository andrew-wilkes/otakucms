<?php

/*
This class may be instantiated when the category route is used.
A route of category/key means that a specific category is selected.
*/

class Archive
{
	const T_ARCHIVES = "Archives";
	const T_ARCHIVES_FOR = "Archives for ";
	const T_ARCHIVE_EMPTY = "The archive is empty.";

	private static $display_mode;
	private static $year = 0;
	private static $month = 0;

	function __construct($params)
	{
		// Set page title
		switch(count($params))
		{
			// Archives
			case 0:
				$title = self::T_ARCHIVES;
				self::$display_mode = 'LINKS';
				break;

			// E.g. Archives for 2017
			case 1:
				self::$year = (int) $params[0];
				$title = self::T_ARCHIVES_FOR . self::$year;
				self::$display_mode = 'YEAR';
				break;

			// E.g. Archives for January 2017
			case 2:
				self::$year = (int) $params[0];
				self::$month = $params[1];
				$title = self::T_ARCHIVES_FOR . date('F', strtotime('22-' . (int) self::$month . '-2017')) . ' ' . (int) $params[0];
				self::$display_mode = 'MONTH';
				break;			
		}

		Tokens::add(['#TITLE#' => $title]);		
	}

	public static function get_content()
	{
		$html = [];
		$pages = [];

		$items = Pages::get_archive();

		if (count($items))
		{
			// List the archive links
			foreach($items as $y => $mths)
			{
				$h = $y;
				if (self::$display_mode == 'MONTH' or $y != self::$year)
					$h = '<a href="' . URL::get_base() . 'archive/' . $y . '/">' . $y . '</a>';
				$html[] = "<p>$h";

				foreach($mths as $m => $p)
				{
					$h = date("M", strtotime("22-$m-$y"));
					if ($m != self::$month or $y != self::$year)
						$h = '<a href="' . URL::get_base() . 'archive/' . $y . '/' . $m . '/">' . $h . '</a>';
					else
						if (self::$display_mode == 'MONTH')
							$pages = $p;
					if (self::$display_mode == 'YEAR' && $y == self::$year)
						$pages = array_merge($pages, $p);
					$html[] = " $h";
				}
				$html[] = "</p>";
			}

			// List the associated page links
			if (count($pages))
			{
				$html[] = '<ul>';
				foreach ($pages as $page)
					$html[] =  "\t<li>" . HTML::hyperlink($page->key, $page->title) . '</li>';
				$html[] = '</ul>';
			}
		}
		else
		{
			$html[] = '<p>' . self::T_ARCHIVE_EMPTY . '</p>';
		}

		echo implode("\n", $html);
	}
}