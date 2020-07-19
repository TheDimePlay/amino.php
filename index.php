<?php
include "amino.php";
include "vendor/autoload.php";

$amino = new Amino("email", "password");
$auth = $amino->auth();
$client = new WebSocket\Client("wss://ws1.narvii.com?signbody=015051B67B8D59D0A86E0F4A78F47367B749357048DD5F23DF275F05016B74605AAB0D7A6127287D9C%7C".(time()*1000)."&sid=".$auth["sid"]);
while (true) {
    try {
        $result = json_decode($client->receive(),true);
        if($result["t"] == 1000){ // Если пришло новое сообщение
        	$community_id = $result["o"]["ndcId"];
        	$author = $result["o"]["chatMessage"]["author"]["uid"];
        	$avatar = $result["o"]["chatMessage"]["author"]["icon"];
        	$reputation = $result["o"]["chatMessage"]["author"]["reputation"];
        	$role = $result["o"]["chatMessage"]["author"]["role"];
        	$nickname = $result["o"]["chatMessage"]["author"]["nickname"];
        	$level = $result["o"]["chatMessage"]["author"]["level"];
        	$thread_id = $result["o"]["chatMessage"]["threadId"];
        	$content = $result["o"]["chatMessage"]["content"];

        	// Reaction on message
        	if($content == "hi!"){
        		$amino->send("{$nickname}, hello, how are u?", $community_id, $thread_id);
        	}
        }
    } catch (\WebSocket\ConnectionException $e) {
        
    }
}
?>
