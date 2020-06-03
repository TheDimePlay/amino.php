<?php 
	include "amino.php";
	$amino = new Amino("email@gmail.com", "pass");
	$auth = $amino->auth();
	$coms = $amino->getComs();
	$data = json_decode(file_get_contents('php://output')); 
	$amino->listen(function() use($amino){
		$amino->on("message_new", function($data) use($amino){
			$args = explode(" ",$data["message"]);
			switch(mb_strtolower($args[0])){
				case "!id":
					$amino->reply($data["author"]);
				break;
			}
		});
	});
