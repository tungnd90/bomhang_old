<?php
/*
 ********************************************************************
 * TASK: Convert data VBB forum to Tamtay Forum
 * AUTHOR: TungND
 * Created: 27-06-2013
 * Description: Convert category topic, reply, poll, poll vote in VBB to Tamtay forum
 ********************************************************************
 */
 
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('HELPER_PATH', ROOT . DS . 'application' . DS . 'helpers' . DS);

require_once(HELPER_PATH . 'convert.php');
echo ">";

$dbConfig = require_once('database_inc.php');

// connect to db phpBB
$connect_vbb	= mysql_connect('localhost', $dbConfig['vbb']['username'], $dbConfig['vbb']['password']) or die('Could not connect: ' . mysql_error());
$db_phpbb			= mysql_select_db($dbConfig['vbb']['database'], $connect_vbb) or die('Could not select database.');


// Select data of phpBB (includes: topics, replies, polls, votes)
$db_cate 		= mysql_query("SELECT * FROM forum");
$db_topics 		= mysql_query("SELECT * FROM thread");
$db_replies 	= mysql_query("SELECT * FROM post");
$db_poll 			= mysql_query("SELECT * FROM  `poll` ");
$db_poll_vote	= mysql_query("SELECT P.*, U.username FROM  `pollvote` as P JOIN user as U ON U.userid = P.userid");



// close connect to db VBB
mysql_close($connect_vbb);


// new connect, connect to db ttforum
$connect_t2f	= mysql_connect('localhost', $dbConfig['ttforum']['username'], $dbConfig['ttforum']['password']) or die('Could not connect: ' . mysql_error());
$db_t2f			= mysql_select_db($dbConfig['ttforum']['database'], $connect_t2f) or die('Could not select database.');



// insert category data
while ($row = mysql_fetch_assoc($db_cate)) {
	
	$position = explode(",",$row['parentlist']);
	$cid							= $row['forumid'];
	$name						= $row['title'];
	$parent_id				= ($row['parentid'] == "-1") ? 0 : $row['parentid'];
	$position					= $position[0];
	$description				= $row['description'];
	$last_topic_id			= $row['lastthreadid'];
	$last_topic_name	= $row['lastthread'];
	$type						= 0;
	
	$sql = "INSERT INTO t2f_categories(cid, name, parent_id, position, description, last_topic_id, last_topic_name, type) ";
	$sql .= "VALUES ($cid , '$name', $parent_id, $position, '$description', $last_topic_id , '$last_topic_name' , $type)";
	
	//echo $sql ."<br /><br />";
	mysql_query($sql) or die('[category] Could not connect: ' . mysql_error());

}



// insert topic data
$list_topic_id = $polls = array();
while ($row = mysql_fetch_assoc($db_topics)) {
	$category_id 				= $row['forumid'];
	$title			 				= $row['title'];
	$author 						= $row['postusername'];
	$author_id 					= $row['postuserid'];
	$user_update_name 	= $row['lastposter'];
	$user_update_id 		= $row['lastposterid'];
	$created 						= $row['dateline'];
	$updated 					= $row['lastpost'];
	$total_view 				= $row['views'];
	$total_reply					= $row['replycount'];
	$ip 								= 0;
	$is_poll						= (empty($row['pollid'])) ? 0 : 1;
	
	$sql = "INSERT INTO t2f_topics(tid, category_id, title, author, author_id, user_update_name, user_update_id, created, updated, ip, is_poll, status, total_view, total_reply) ";
	$sql .= "VALUES (NULL, $category_id , '$title', '$author', $author_id, '$user_update_name', $user_update_id , $created , $updated , $ip, $is_poll, 1, $total_view, $total_reply)";
	
	//echo $sql ."<br /><br />";
	mysql_query($sql) or die('[topic] Could not connect: ' . mysql_error());
		
	$tid = $row['threadid'];
	$last_topic_id = mysql_insert_id();
	$list_topic_id[$tid] = $last_topic_id;
	
	if (!empty($row['pollid']))
	{
		$polls[$row['pollid']]	= $last_topic_id;
	}
	
}



// insert reply data
while ($row = mysql_fetch_assoc($db_replies)) {
	$username 	= $row['username'];
	$uid			 	= $row['userid'];
	$content 		= bb2html($row['pagetext']);
	$topic_id 		= $list_topic_id[$row['threadid']];
	$created 		= $row['dateline'];
	$modify 		= $row['dateline'];
	$ip 				= $row['ipaddress'];
	
	$sql  = "INSERT INTO t2f_replies(rid, username, uid, content, topic_id, created, modify, ip) ";
	$sql .= "VALUES (NULL, '$username', $uid, '$content', $topic_id, $created , $modify, '$ip' )";
	
	//echo $sql ."<br /><br />";
	mysql_query($sql) or die('[reply]Could not connect: ' . mysql_error() . $sql);
}



// insert poll data
$list_option = array();
while ($row = mysql_fetch_assoc($db_poll)) {
	
	$question	= $row['question'];
	$created		= $row['dateline'];
	$start_time	= $row['dateline'];
	$end_time	= strtotime("+".$row['timeout']." days",$row['dateline']);
	$topic_id		= $polls[$row['pollid']];
	$description	= '';
	
	$options 	= explode("|||",$row['options']);
	$votes	 	= explode("|||",$row['votes']);
	
	$sql  = "INSERT INTO t2f_polls(pid, question, created, start_time, end_time, topic_id, description) ";
	$sql .= "VALUES (NULL, '$question', $created, $start_time, $end_time, $topic_id , '$description' )";
	
	//echo $sql ."<br /><br />";
	mysql_query($sql) or die('[option] Could not connect: ' . mysql_error() . $sql);
	$pid = mysql_insert_id();
	
	for ($i = 0; $i < count($options); $i++) {
		$title 		= $options[$i];
		$poll_id	= $pid;
		$count 	= $votes[$i];
		
		$sql  = "INSERT INTO t2f_answers(aid, title, poll_id, count) ";
		$sql .= "VALUES (NULL, '$title', $poll_id, $count)";
		
		//echo $sql ."<br /><br />";
		mysql_query($sql) or die('[answer] Could not connect: ' . mysql_error() . $sql);
		$list_option[$row['pollid']][$i+1] = mysql_insert_id();
	}
	
}



// insert vote data
while ($row = mysql_fetch_assoc($db_poll_vote)) {
		echo "<pre>";
	print_r($row);
	echo "</pre>";

	$uid 			= $row['userid'];
	$username	= $row['username'];
	$aid 			= $list_option[$row['pollid']][$row['voteoption']];
	$created 		= $row['votedate'];
	$ip 				= '';
	$tid 				= $polls[$row['pollid']];
	
	$sql = "INSERT INTO t2f_votes (`uid`, `username`, `aid`, `created`, `ip`, `tid`) ";
	$sql .= "VALUES ($uid, '$username', $aid, $created, '$ip', $tid)";
	
	//echo $sql ."<br /><br />";
	mysql_query($sql) or die('[vote] Could not connect: ' . mysql_error() . $sql);
}


mysql_close($connect_t2f);
die("DONE");

?>