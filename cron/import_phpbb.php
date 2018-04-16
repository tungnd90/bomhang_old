<?php
/*
 ********************************************************************
 * TASK: Convert data PhpBB forum to Tamtay Forum
 * AUTHOR: TungND
 * Created: 27-06-2013
 * Description: Convert category topic, reply, poll, poll vote in PhpBB to Tamtay forum
 ********************************************************************
 */
 
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('HELPER_PATH', ROOT . DS . 'application' . DS . 'helpers' . DS);

require_once(HELPER_PATH . 'convert.php');
echo ">";
$dbConfig = require_once('database_inc.php');

// connect to db phpBB
$connect_phpbb	= mysql_connect('localhost', $dbConfig['phpbb']['username'], $dbConfig['phpbb']['password']) or die('Could not connect: ' . mysql_error());
$db_phpbb			= mysql_select_db($dbConfig['phpbb']['database'], $connect_phpbb) or die('Could not select database.');


// Select data of phpBB (includes: topics, replies, polls, votes)
//$topics = mysql_query("SELECT * FROM phpbb_topics AS T LEFT JOIN phpbb_posts AS P ON T.topic_id = P.topic_id");
$db_cate 		= mysql_query("SELECT * FROM phpbb_forums");
$db_topics 		= mysql_query("SELECT * FROM phpbb_topics");
$db_replies 	= mysql_query("SELECT P.*, U.username FROM phpbb_posts as P JOIN phpbb_users as U ON U.user_id = P.poster_id");
$db_poll 			= mysql_query("SELECT * FROM  `phpbb_poll_options` ");
$db_poll_vote	= mysql_query("SELECT P.*, U.username FROM  `phpbb_poll_votes` as P JOIN phpbb_users as U ON U.user_id = P.vote_user_id");


// close connect
mysql_close($connect_phpbb);


// new connect, connect to db ttforum
$connect_t2f	= mysql_connect('localhost', $dbConfig['ttforum']['username'], $dbConfig['ttforum']['password']) or die('Could not connect: ' . mysql_error());
$db_t2f			= mysql_select_db($dbConfig['ttforum']['database'], $connect_t2f) or die('Could not select database.');


// insert category data
while ($row = mysql_fetch_assoc($db_cate)) {
	$cid							= $row['forum_id'];
	$name						= $row['forum_name'];
	$parent_id				= $row['parent_id'];
	$position					= $row['left_id'];
	$description				= $row['forum_desc'];
	$last_topic_id			= $row['forum_last_post_id'];
	$last_topic_name	= $row['forum_last_post_subject'];
	$type						= 0;
	
	$sql = "INSERT INTO t2f_categories(cid, name, parent_id, position, description, last_topic_id, last_topic_name, type) ";
	$sql .= "VALUES ($cid , '$name', $parent_id, $position, '$description', $last_topic_id , '$last_topic_name' , $type)";

	mysql_query($sql) or die('[category] Could not connect: ' . mysql_error());
}

/*
// insert topic data
$list_topic_id = $polls = array();
$i = 0;
while ($row = mysql_fetch_assoc($db_topics)) {
	$category_id 				= $row['forum_id'];
	$title			 				= $row['topic_title'];
	$author 						= $row['topic_first_poster_name'];
	$author_id 					= $row['topic_poster'];
	$user_update_name 	= $row['topic_last_poster_name'];
	$user_update_id 		= $row['topic_last_poster_id'];
	$created 						= $row['topic_time'];
	$updated 					= $row['topic_last_post_time'];
	$total_view 				= $row['topic_views'];
	$total_reply					= $row['topic_replies'];
	$ip 								= 0;
	$is_poll						= (empty($row['poll_title'])) ? 0 : 1;
	
	$sql = "INSERT INTO t2f_topics(tid, category_id, title, author, author_id, user_update_name, user_update_id, created, updated, ip, is_poll, status, total_view, total_reply) ";
	$sql .= "VALUES (NULL, $category_id , '$title', '$author', $author_id, '$user_update_name', $user_update_id , $created , $updated , $ip, $is_poll, 0, $total_view, $total_reply)";

	mysql_query($sql) or die('[topic] Could not connect: ' . mysql_error());
		
	$tid = $row['topic_id'];
	$list_topic_id[$tid] = mysql_insert_id();
	
	if (!empty($row['poll_title']))
	{
		$polls[$i]['question']		= $row['poll_title'];
		$polls[$i]['created']			= $row['topic_time'];
		$polls[$i]['start_time']		= $row['topic_time'];
		$polls[$i]['end_time']		= $row['poll_length'];
		$polls[$i]['topic_id']			= $list_topic_id[$tid];
		$polls[$i]['description']	= "";
		$polls[$i]['tid_old']			= $tid;
		
		$i++;
	}
	
}



// insert reply data
while ($row = mysql_fetch_assoc($db_replies)) {
	$post_text = str_replace(":3j9tsqpw","",$row['post_text']);

	$username 	= $row['username'];
	$uid			 	= $row['poster_id'];
	$content 		= bb2html($post_text);
	$topic_id 		= $list_topic_id[$row['topic_id']];
	$created 		= $row['post_time'];
	$modify 		= $row['post_time'];
	$ip 				= $row['poster_ip'];
	
	$sql  = "INSERT INTO t2f_replies(rid, username, uid, content, topic_id, created, modify, ip) ";
	$sql .= "VALUES (NULL, '$username', $uid, '$content', $topic_id, $created , $modify, '$ip' )";
	echo $sql ."<br /><br />";
	//mysql_query($sql) or die('[reply]Could not connect: ' . mysql_error() . $sql);
}



// insert poll data
$list_poll_id = array();
foreach ($polls as $p) {
	$question	= $p['question'];
	$created		= $p['created'];
	$start_time	= $p['start_time'];
	$end_time	= $p['end_time'];
	$topic_id		= $p['topic_id'];
	$description	= $p['description'];
	
	$sql  = "INSERT INTO t2f_polls(pid, question, created, start_time, end_time, topic_id, description) ";
	$sql .= "VALUES (NULL, '$question', $created, $start_time, $end_time, $topic_id , '$description' )";
	
	mysql_query($sql) or die('[poll] Could not connect: ' . mysql_error());
	
	$tid = $p['tid_old'];
	$list_poll_id[$tid] = mysql_insert_id();
}


// insert options of poll
$option_id = array();
while ($row = mysql_fetch_assoc($db_poll)) {
	$title 		= $row['poll_option_text'];
	$poll_id	= $list_poll_id[$row['topic_id']];
	$count 	= $row['poll_option_total'];
	
	$sql  = "INSERT INTO t2f_answers(aid, title, poll_id, count) ";
	$sql .= "VALUES (NULL, '$title', $poll_id, $count)";
	
	mysql_query($sql) or die('[option] Could not connect: ' . mysql_error() . $sql);
	$pid = $row['poll_option_id'];
	$option_id[$row['topic_id']][$pid] = mysql_insert_id();
}


// insert vote data
while ($row = mysql_fetch_assoc($db_poll_vote)) {
	$uid 			= $row['vote_user_id'];
	$username	= $row['username'];
	$aid 			= $option_id[$row['topic_id']][$row['poll_option_id']];
	$created 		= time();
	$ip 				= $row['vote_user_ip'];
	$tid 				= $list_topic_id[$row['topic_id']];
	
	$sql = "INSERT INTO t2f_votes (`uid`, `username`, `aid`, `created`, `ip`, `tid`) ";
	$sql .= "VALUES ($uid, '$username', $aid, $created, '$ip', $tid)";
	
	mysql_query($sql) or die('[vote] Could not connect: ' . mysql_error() . $sql);
}
*/
mysql_close($connect_t2f);
die("DONE");



?>