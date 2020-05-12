<?php 
	include "amino.php";

	$amino = new Amino("jeripllay2@gmail.com", "4719zx");
	$auth = $amino->auth();

	$coms = $amino->getComs();
	for($i=0;$i<=count($coms[0]);$i++){

		$thread = $amino->getChats($coms[0][$i]["ndcId"]);

		for($t=0;$t<=2;$t++){

			$msg = $thread[0][$t]["lastMessageSummary"]["content"]; // Content message
			$from_id = $thread[0][$t]["lastMessageSummary"]["uid"]; // Author message
			$peer_id = $thread[0][$t]["threadId"]; // Chat sender
			$community_id = $coms[0][$i]["ndcId"]; // Community sender
			$args = explode(" ",$msg);
			if($args[0] == "!test"){
				$amino->send("I'm work.",$community_id,$peer_id);
			} elseif($args[0] == "!img"){
				$amino->sendImage("/root/phpamino/test.jpg", $community_id,$peer_id);
			} elseif($args[0] == "!adio"){
				$amino->sendAudio("/root/phpamino/a.ogg", $community_id,$peer_id);
			} elseif($args[0] == "!post"){
				$amino->postBlog("Title", "Content", $community_id);
				$amino->send("I'm success create wall. ", $community_id, $peer_id);
			} elseif($args[0] == "!getWall"){
				echo $amino->getUserBlogs($from_id, $community_id);
			} elseif($args[0] == "!nick"){
				$amino->setNickname($args[1], $community_id, $from_id);
				$amino->send("I'm success set new nick: ".$args[1], $community_id, $peer_id);
			}  elseif($args[0] == "!description"){
				$description = mb_substr($msg, 12, strlen($msg));
				$amino->setDescription($description, $community_id, $from_id);
				$amino->send("Success!",$community_id,$peer_id);
			}
		}
	}
?>