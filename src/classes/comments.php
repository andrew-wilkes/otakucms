<?php

class Comments
{
	// Error messages
	const T_ALREADY_VOTED = "Denied! You authored or have already voted on this comment.";
	const T_NO_ACTION = "No action was specified!";
	const T_BAD_EMAIL = "Invalid email!";
	const T_EMPTY_MESSAGE = "Empty message!";
	const T_CANNOT = "You can't  your own comment.";
	const T_NOT_PERMITTED = "You are not permitted to administer this comment.";
	const T_INVALID_ID = "Invalid ID!";
	const T_NO_MATCHING_COMMENT = "No matching comment for given ID!";
	const T_UNKNOWN_ACTION = "Unknown action!";
	const T_CANNOT_FLAG = "You can't flag a comment after previous input to it.";

	// Success feedback messages
	const T_UP_VOTED = "Comment was up-voted.";
	const T_DOWN_VOTED = "Comment was down-voted.";
	const T_ADDED = "Your comment was added.";
	const T_UPDATED = "Your comment was updated.";
	const T_DELETED = "The comment was deleted.";
	const T_WAS_APPROVED = "The comment was approved.";

	private static $items = [];
	private static $file_name;
	private static $next_id = 1;

	function __construct()
	{
		try
		{
			self::$file_name = '.comments-' . $_SESSION['page']->id;
			$items = json_decode((new Data)->get(self::$file_name, false));

			$next_id = 1;
			$id_not_set = true;
			foreach($items as $item)
			{
				// Map id to array key
				self::$items[$item->id] = $item;

				// Get a value for the next vacant array key index (comment id)
				if ($id_not_set && $item->id > $next_id)
				{
					self::$next_id = $next_id;
					$id_not_set = false;
				}
				else
					$next_id++;
			}
			if ($id_not_set)
				self::$next_id = $next_id;
		}
		catch(Exception $e)
		{
			self::$items = [];
		}		
	}

	public function get_status($msg = '')
	{
		return (object)[
			'comments' => array_values(self::nest_comments(0)[1]),
			'unlocked' => empty($_SESSION['page']->locked),
			'ip' => Session::$ip,
			'moderator' => Session::$is_moderator,
			'msg' => $msg
		];
	}

	public function api($data)
	{
		if (empty($data->action))
			throw new Exception(self::T_NO_ACTION);

		$id = (isset($data->id) ? (int) $data->id : -1);

		if ($id < 0)
			throw new Exception(self::T_INVALID_ID);

		if ($id > 0)
		{
			if (isset(self::$items[$id]))
				$comment = self::$items[$id];
			else
				throw new Exception(self::T_NO_MATCHING_COMMENT);
		}

		$is_admin = Session::$is_moderator;

		$is_author = isset($comment) && (Session::$ip == $comment->ip);

		$msg = '';

		switch ($data->action)
		{
			case 'add':
				// Add to the end or insert as a reply
				$comment = new Comment($data);
				$comment->id = self::$next_id;
				if ($id > 0) $comment->parent = $id;
				self::$items[self::$next_id] = $comment;
				$msg = self::T_ADDED;
				break;

			case 'update':
				// If author or admin then update content
				if ($is_admin || $is_author)
				{
					self::$items[$id] = new Comment($data);
					$msg = self::T_UPDATED;
				}
				else
					throw new Exception(self::T_NOT_PERMITTED);
				break;

			case 'delete':
				// Remove if admin or author
				// If has replies and author, hide content and mark as deleted
				if ($is_admin || $is_author)
				{
					$children = self::nest_comments($id)[0];

					if (!$is_admin && $is_author && count($children) > 0)
						self::$items[$id]->deleted = true;
					else
					{
						unset(self::$items[$id]);
						foreach($children as $id)
						{
							unset(self::$items[$id]);
						}
					}	
				}
				else
					throw new Exception(self::T_NOT_PERMITTED);
				break;
				
			case 'approve':
				if ($is_admin)
				{
					self::$items[$id]->approved = true;
				}
				else
					throw new Exception(self::T_NOT_PERMITTED);
				break;
				
			case 'plus':
				// If ip not in list for comment then add 1
				if ($is_author || in_array(Session::$ip, $comment->ips))
					throw new Exception(self::T_ALREADY_VOTED);
				else
				{
					$comment->score++;
					if ( ! $is_admin) $comment->ips[] = Session::$ip;
				}
				break;
				
			case 'minus':
				// If ip not in list for comment then deduct 1
				if ($is_author || in_array(Session::$ip, $comment->ips))
					throw new Exception(self::T_ALREADY_VOTED);
				else
				{
					$comment->score--;
					if ( ! $is_admin) $comment->ips[] = Session::$ip;
				}
				break;
				
			case 'flag':
				// Set flagged property if admin or not author and not in ip list
				if (!$is_admin && ($is_author || in_array(Session::$ip, $comment->ips)))
					throw new Exception(self::T_CANNOT_FLAG);
				else
				{
					$comment->flagged = true;
					if ( ! $is_admin) $comment->ips[] = Session::$ip;
				}
				break;

			case 'unflag':
				// If admin then unset flagged property
				if ($is_admin)
					$comment->flagged = false;
				else
					throw new Exception(self::T_NOT_PERMITTED);				
				break;

			default:
				throw new Exception(self::T_UNKNOWN_ACTION);
				break;
		}

		self::save();
		return self::get_status($msg);
	}

	public function save()
	{
		return (new Data)->save(self::$file_name, self::$items);
	}

	public static function nest_comments($parent)
	{
		$comments = [];
		$ids = [];
		foreach (self::$items as $key => $item)
		{
			if ($parent == $item->parent)
			{
				$children = self::nest_comments($key);
				$ids = array_merge($ids, [$key], $children[0]);
				$item->comments = $children[1];
				$comments[] = $item;
			}	
		}
		return [$ids, $comments];
	}
}