<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);																// =================== need to test if the php process includes the upload time -- prob not
$CCOI_requireslogin=true; $CCOI_requiresadmin=true;

	$extraCSS=<<<ENDCSS
	<style type="text/css">
		div.markoutercontent {background-color:#f0f8ff; padding: 20px 0 40px 0;} 
		div.markcontent {width: 750px; margin:10px auto;}
		p#msg {width:320px; padding: 8px 20px; background-color: #dcefff; border: 1px solid #68f; border-radius: 4px;}
		form p {margin-bottom:4px;}
		input {border: 1px solid #ddd; border-radius:4px; padding: 0 6px; margin-right: 9px;}
		input#name {width: 440px;}
		input#viddate {width: 100px;}
		input#teacher {width: 210px;}
		input#compno {width: 110px;}
		input#derfile {width: 320px; border: none; padding: 0;}
		input#subby {width: 110px;}
	</style>
ENDCSS;

include('../includes/header.php');			//$msg='this is a test message.';
	
if(!empty($_FILES['derfile'])){

//		$db=mysqli_connect("localhost","ccoi_editor","8rW4jpfs67bD",'newerccoi');
//		if(!$db){$err=mysqli_error();		exit ("Error: could not connect to the CCOI Database! -- $access -- $err");}							// ================== need to move the user / pass info out of the web space
		include('./ccoi_dbhookup.php');

		$root='/ccoivids';		// yes this is really off the drive root

		list($firstbit,$scramblename)=makeScramble();	
		$origfilename=preg_replace('/[^A-Za-z0-9._-]/','_',$_FILES['derfile']['name']);		// preg_replace -- dash has to be at end
		//$filename_sans_ext=substr($origfilename,0,(strrpos($origfilename,'.')));
		//$filename_ext=substr($origfilename,(strrpos($origfilename,'.'))+1);
		$newfilename="{$scramblename}_{$origfilename}";

		if (empty($_FILES['derfile']) || $_FILES['derfile']['tmp_name']=='none'){$errors.='<li>No file uploaded.</li>';}
		if ($_FILES['derfile']['size']==0){$errors.='<li>File is zero bytes.</li>';}

		if(isset($errors)){
			echo "$errors";	error_log("FileCatcher: FARCKLE: $errors");	exit;
		}else{
			if(move_uploaded_file($_FILES['derfile']['tmp_name'],"$root/$newfilename")){
				$dername=mysqli_real_escape_string($db,$_POST['name']);
				$derviddate=mysqli_real_escape_string($db,$_POST['viddate']);
				$derteach=mysqli_real_escape_string($db,$_POST['teacher']);
				$dercompno=mysqli_real_escape_string($db,$_POST['compno']);
				$derurl=mysqli_real_escape_string($db,$origfilename);				// shouldnt need this with the preg_replace above, but hey...
				$query="INSERT INTO tbVideos (appid,name,url,scramble,viddate,teacher,compno) VALUES ('1','$dername','$derurl','$scramblename','$derviddate','$derteach','$dercompno')";			// additional data can be provided through the video manager
				$return=mysqli_query($db,$query);
				if(mysqli_affected_rows($db)==1){$msg="{$_POST['name']} uploaded.";}else{$msg="Error in {$_POST['name']} upload.";}
		//		echo $query;
			}else{
				echo 'XF';
			}
		}

}

	echo "<div class='markoutercontent'><div class='markcontent'><h2>Video Uploader</h2><p>Be sure to include all relevant information.<br />NOTE: large file uploads are not enabled on DEV</p>";
	
	if(!empty($msg)){echo "<p id='msg'>$msg</p>";}

	echo "<form method='POST' action='ccoi_video_catcher.php' enctype='multipart/form-data'>";
	echo "<p><input type='text' id='name' name='name' placeholder='Video Name' /></p>";
	echo "<p><input type='text' id='viddate' name='viddate' placeholder='2020-01-01' />";
	echo "<input type='text' id='teacher' name='teacher' placeholder='Teacher Name' />";
	echo "<input type='text' id='compno' name='compno' placeholder='Computer #' /></p>";
	echo "<p><input type='file' id='derfile' name='derfile' /><input id='subby' type='submit' value='Upload Video' /></p></form>";

	echo '</div></div>';
	include('../includes/footer.php');




function makeScramble(){
			$scramblearray=array();srand();
			for($e=0;$e<8;$e++){array_push($scramblearray,chr(rand(97,122)));}
			for($e=0;$e<8;$e++){array_push($scramblearray,chr(rand(49,57)));}
			for($e=0;$e<12;$e++){array_push($scramblearray,chr(rand(65,90)));}
			srand((float)microtime() * 1530);			shuffle($scramblearray);
			srand((float)microtime() * 140);				shuffle($scramblearray);
			$scramblename=implode('',$scramblearray);
			$firstbit=chr(rand(49,57));
			$scramblename=$firstbit.$scramblename;		// make sure starts with a number
			return array($firstbit,$scramblename);
}


?>