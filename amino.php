<?php
	class Amino{

		///////////////////
			private $email = "";
			private $password = "";
			private $socket = "";
		/////////////////////
			private $thread_id;
			private $community_id;
			private $message_id;
		/////////////////////

		public function __construct($email, $password){
			$this->email = $email;
			$this->password = $password;
		}

		// Authorization in account
		public function auth(){
			$socket = $this->request("");
			return $this->request("g/s/auth/login", ["email"=>$this->email,"secret"=>"0 ".$this->password,"deviceID"=>"015051B67B8D59D0A86E0F4A78F47367B749357048DD5F23DF275F05016B74605AAB0D7A6127287D9C","clientType"=>100,"action"=>"normal","timestamp"=>(time()*100)]);
		}

		public function listen($anon){
			while(true){
				$anon();
			}
		}

		public function on($type, $anon){
			$coms = $this->getComs();
	        switch ($type) {
	            case 'message_new':
					for($i=0;$i<=(count($coms[0])-1);$i++){
						$thread = $this->getChats($coms[0][$i]["ndcId"]);
						for($t=0;$t<=2;$t++){
							if(!isset($thread[0][$t])) continue;
							$msg = $thread[0][$t]["lastMessageSummary"]["content"]; // Content message
							$from_id = $thread[0][$t]["lastMessageSummary"]["uid"]; // Author message
							$peer_id = $thread[0][$t]["threadId"]; // Chat sender
							$community_id = $coms[0][$i]["ndcId"]; // Community sender
							$result = array(
								"message"=>$msg,
								"author"=>$from_id,
								"thread_id"=>$peer_id,
								"community_id"=>$community_id,
								"message_id"=>$thread[0][$t]["lastMessageSummary"]["messageId"]
							);
							$this->thread_id = $peer_id;
							$this->community_id = $community_id;
							$this->message_id = $result["message_id"];
							$anon($result);
						}
					}
	            break;
	            case 'leave_chat':
					for($i=0;$i<=(count($coms[0])-1);$i++){
						$thread = $this->getChats($coms[0][$i]["ndcId"]);
						for($t=0;$t<=2;$t++){
							if(!isset($thread[0][$t]) || $thread[0][$t]["lastMessageSummary"]["type"] != 103) continue;
							$from_id = $thread[0][$t]["lastMessageSummary"]["uid"]; // Author message
							$peer_id = $thread[0][$t]["threadId"]; // Chat sender
							$community_id = $coms[0][$i]["ndcId"]; // Community sender
							$result = array(
								"author"=>$from_id,
								"thread_id"=>$peer_id,
								"community_id"=>$community_id
							);
							$this->thread_id = $peer_id;
							$this->community_id = $community_id;
							$anon($result);
							echo json_encode($thread[0][$t]["lastMessageSummary"],JSON_UNESCAPED_UNICODE);
						}
					}
	            break;
	        }
		}

		// Get all communitys
		public function getComs(){
			$sid = $this->auth()["sid"];
			$out = file_get_contents("https://service.narvii.com/api/v1/g/s/community/joined?start=0&size=50&sid=".$sid);
			$base = json_decode($out,true);
			foreach ($base as $key) {
				$res[] = $key;
			}
			return $res; 
		}

		public function getUser($com, $id){
			$sid = $this->auth()["sid"];
			return json_decode(file_get_contents("https://service.narvii.com/api/v1/x{$com}/s/user-profile/{$id}?action=visit&sid=".$sid),true);
		}

		// Get all chats in community
		public function getChats($com){
			$sid = $this->auth()["sid"];
			$out = file_get_contents("https://service.narvii.com/api/v1/x{$com}/s/chat/thread?type=joined-me&start=0&size=100&sid=".$sid);
			$base = json_decode($out,true);
			foreach ($base as $key) {
				$res[] = $key;
			}
			return $res;  
		}

		public function getMessages($com, $thread){
			$sid = $this->auth()["sid"];
			return $this->request("x{$com}/s/chat/thread/${thread}/message?sid=".$sid, ["v"=>2,"pagingType"=>"t","size"=>10,"timestamp"=>(time()*100)]);
		}

		// Send simple message in chat
		public function send($content, $com, $thread){
			$sid = $this->auth()["sid"];
			return $this->request("x${com}/s/chat/thread/${thread}/message?sid=".$sid,["content"=>$content,"type"=>0,"clientRefId"=>43196704,"timestamp"=>(time()*100)]);
		}

		public function reply($message){
			$sid = $this->auth()["sid"];
			return $this->request("x".$this->community_id."/s/chat/thread/".$this->thread_id."/message?sid=".$sid,["content"=>$message,"type"=>0,"clientRefId"=>43196704,"replyMessageId"=>$this->message_id ,"timestamp"=>(time()*100)]);
		}

		// Send image
		public function sendImage($image, $com, $thread){
			$sid = $this->auth()["sid"];
			$img = base64_encode(file_get_contents($image));
			return $this->request("x${com}/s/chat/thread/${thread}/message?sid=".$sid,["content"=>null,"type"=>0,"clientRefId"=>827027430,"timestamp"=>(time()*100),"mediaType"=>100,"mediaUploadValue"=>$img,"mediaUploadValueContentType" => "image/".substr(strrchr($image, '.'), 1),"mediaUhqEnabled"=>false,"attachedObject"=>null]);
		}

		// Send Audio Message
		public function sendAudio($audioFile, $com, $thread){
			$sid = $this->auth()["sid"];
			$audio = base64_encode(file_get_contents($audioFile));
			return $this->request("x${com}/s/chat/thread/${thread}/message?sid=".$sid, ["content"=>null,"type"=>2,"clientRefId"=>827027430,"timestamp"=>(time()*100),"mediaType"=>110,"mediaUploadValue"=>$audio,"attachedObject"=>null]);
		}

		// Create wall in community
		public function postBlog($title, $content, $com){
			$sid = $this->auth()["sid"];
			return $this->request("x${com}/s/blog?sid=".$sid, ["content"=>$content,"latitude"=>0,"longitude"=>0,"title"=>$title,"clientRefId"=>43196704,"eventSource"=>"GlobalComposeMenu","timestamp"=>(time()*100)]);
		}

		// Get all User Blogs 
		public function getUserBlogs($uid, $com){
			$sid = $this->auth()["sid"];
			return json_decode(file_get_contents("https://service.narvii.com/api/v1/x{$com}/s/blog?type=user&q={$uid}&start=0&size=50"),true)["blogList"];
		}

		// Set new nickname in community
		public function setNickname($nickname, $com, $id){
			$sid = $this->auth()["sid"];
			return $this->request("x${com}/s/user-profile/${id}?sid=".$sid, ["nickname"=>$nickname,"timestamp"=>(time()*100)]);
		}

		// Set new profile description
		public function setDescription($description, $com, $id){
			$sid = $this->auth()["sid"];
			return $this->request("x${com}/s/user-profile/${id}?sid=".$sid, ["content"=>$description,"timestamp"=>(time()*100)]);
		}

		public function ban($member, $communty, $rejoin){
			$sid = $this->auth()["sid"];
			$rejoin = (int)$rejoin;
			return json_decode(file_get_contents("https://service.narvii.com/api/v1/x{$community}/s/chat/thread/{$sid}/member/{$member}?allowRejoin={$rejoin}"),true);
		}

		public function deleteBlog($com, $postID){
			$sid = $this->auth()["sid"];
			return json_decode(file_get_contents("https://service.narvii.com/api/v1/x{$com}/s/blog/{$postID}?sid=".$sid),true);
		}

		public function getCommunityBlogs($com){
			$sid = $this->auth()["sid"];
			return json_decode(file_get_contents("https://service.narvii.com/api/v1/x{$com}/s/feed/blog-all?start=0&size=50&sid=".$sid),true)["blogList"];
		}

		public function commentBlog($content, $com, $postID){
			$sid = $this->auth()["sid"];
			return $this->request("x{$com}/s/blog/{$postID}/comment?sid=".$sid, ["content"=>$content,'mediaList'=> [],"eventSource"=>"PostDetailView","timestamp"=>(time()*100)]);
		}

		public function setLike($com, $postID){
			$sid = $this->auth()["sid"];
			return json_decode(file_get_contents("https://service.narvii.com/api/v1/x{$com}/s/blog/{$postID}/vote/?votedValueMap=0&sid=".$sid),true);
		}

		public function commentProfile($content, $com, $id){
			$sid = $this->auth()["sid"];
			return $this->request("x{$com}/s/user-profile/{$id}/comment?sid={$sid}", ["content"=>$content,'mediaList'=> [],"eventSource"=>"PostDetailView","timestamp"=>(time()*100)]);
		}

		public function request($method, $params = array()){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://service.narvii.com/api/v1/".$method);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			$out = curl_exec($ch);
			curl_close($ch);
			$base = json_decode($out,true);
			return $base;
		}
	}
?>
