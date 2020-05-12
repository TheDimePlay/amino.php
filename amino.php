<?php 
	class Amino{

		private $email = "";
		private $password = "";

		public function __construct($email, $password){
			$this->email = $email;
			$this->password = $password;
		}

		// Authorization in account
		public function auth(){
			return $this->request("g/s/auth/login", ["email"=>$this->email,"secret"=>"0 ".$this->password,"deviceID"=>"015051B67B8D59D0A86E0F4A78F47367B749357048DD5F23DF275F05016B74605AAB0D7A6127287D9C","clientType"=>100,"action"=>"normal","timestamp"=>(time()*100)]);
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

		// Send simple message in chat
		public function send($content, $com, $thread){
			$sid = $this->auth()["sid"];
			return $this->request("x${com}/s/chat/thread/${thread}/message?sid=".$sid,["content"=>$content,"type"=>0,"clientRefId"=>43196704,"timestamp"=>(time()*100)]);
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
			return file_get_contents("https://service.narvii.com/api/v1/x{$com}/s/blog?type=user&q={$uid}&start=0&size=50");
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
			return file_get_contents("https://service.narvii.com:443/api/v1/x{$community}/s/chat/thread/{$sid}/member/{$member}?allowRejoin={$rejoin}");
		}
		
		public function deleteBlog($com, $postID){
			$sid = $this->auth()["sid"];
			return file_get_contents("https://service.narvii.com/api/v1/x{$com}/s/blog/{$postID}?sid=".$sid);
		}
		
		public function getCommunityBlogs($com){
			$sid = $this->auth()["sid"];
			return file_get_contents("https://service.narvii.com/api/v1/x{$com}/s/feed/blog-all?start=0&size=50&sid=".$sid);
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
