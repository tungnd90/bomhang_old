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
$dbConfig = require_once('database_inc.php');

// connect to db phpBB
$connect	= mysql_connect('localhost', $dbConfig['md']['username'], $dbConfig['md']['password']) or die('Could not connect: ' . mysql_error());
$db			= mysql_select_db($dbConfig['md']['database'], $connect) or die('Could not select database.');



$users = mysql_query("SELECT * FROM users WHERE md_id != 0") or die("loi");

while($row = mysql_fetch_assoc($users)) {
    $mdId = $row['md_id'];
    $mdKey = $row['md_key'];

    // lists workers
    $workers = json_decode(auto("https://www.mining-dutch.nl/pools/dogecoin.php?page=api&action=getuserworkers&api_key={$mdKey}&id={$mdId}"), true);

    if (!empty($workers['getuserworkers']['data'])) {
        foreach ($workers['getuserworkers']['data'] as $w) {
            $work = mysql_fetch_assoc(mysql_query("SELECT * FROM workers WHERE worker = '{$w['username']}' LIMIT 1"));

            if (!empty($work['worker'])) {
                mysql_query("UPDATE `workers` SET `updated` = '".time()."' WHERE `workers`.`id` = {$work['id']};");
                $id = $work['id'];
            }
            else {
                mysql_query("INSERT INTO `workers` (`id`, `worker`, `md_id`, `updated`) VALUES (NULL, '{$w['username']}', '{$mdId}', '".time()."');");
                $id = mysql_insert_id();
            }

            if ($id) {
                mysql_query("INSERT INTO `stats` (`worker_id`, `hashrate`, `time`) VALUES ('{$id}', '{$w['hashrate']}', '".time()."');");
            }
        }
    }
}

mysql_close($connect);

function auto($url){
    $data = curl_init();
    curl_setopt($data, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($data, CURLOPT_URL, $url);
    curl_setopt($data, CURLOPT_POST, 0);
    curl_setopt($data, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($data, CURLOPT_USERAGENT, 'MD Stats');
    $hasil = curl_exec($data);
    curl_close($data);
    return $hasil;
}
die("DONE");



?>