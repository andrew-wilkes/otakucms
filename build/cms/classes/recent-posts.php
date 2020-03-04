<?php

/**
 * Creates a recent posts list to add to a token for use in templates
 */
class RecentPosts
{
	const MAX_TO_LIST = 10;

	public function __construct()
	{
		$html = "";
	
		$posts = Pages::get_recent_posts();

		if (count($posts))
		{
			$html .= "<ul>\n";
			$count = 0;
			foreach($posts as $post)
			{
				if (++$count > self::MAX_TO_LIST) break;
				$html .= "\t<li>" .  HTML::hyperlink($post->key, $post->title) . "</li>\n";
			}
			$html .= "</ul>";
		}

		Tokens::add(['#RECENT#' => $html]);
	}
}