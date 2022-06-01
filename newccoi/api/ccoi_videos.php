<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
//$CCOI_requireslogin=true;
include('./ccoi_session.php');

$root='/ccoivids';

$db=mysqli_connect("localhost","ccoi_editor","8rW4jpfs67bD",'newccoi');
if(!$db){$err=mysqli_error();		exit ("Error: could not connect to the CCOI Database! -- $access -- $err");}

if(!empty($_GET['vid']) && is_numeric($_GET['vid'])){
	$vid=$_GET['vid']+0;
	if(is_numeric($vid)){
	
		$return=mysqli_query($db,"SELECT v.* FROM tbVideos v WHERE v.id='$vid' LIMIT 1");		
		$d=mysqli_fetch_assoc($return);
		if(!empty($_SESSION['myapps'][$d['appid']])){		// === need to add check for SESSION['apps'][aid] to make sure you're allowed this video - and of course logged in
			$url="{$d['scramble']}_{$d['url']}";
			header("Content-Type: video/webm"); 
			header("Content-Disposition: inline");
			readfile("$root/{$url}");
			exit;
		}else{
			echo 'ccoi: video not linked to your application';
		}
	}
}