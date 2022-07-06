<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
$CCOI_requireslogin=true;
//$appid = $_SESSION['currentlyloadedapp'];
//include('./ccoi_session.php');		// header has this already
//$db=mysqli_connect("localhost","ccoi_editor","8rW4jpfs67bD",'newerccoi');
//if(!$db){$err=mysqli_error();		exit ("Error: could not connect to the CCOI Database! -- $access -- $err");}
include('./ccoi_dbhookup.php');

$root='/ccoivids';
//$root='/var/www/html/ccoi.education.ufl.edu/ccoivids';

if(!empty($_FILES['derfile'])){
		$root='/ccoivids';		// yes this is really off the drive root
		list($firstbit,$scramblename)=makeScramble();	
		$origfilename=preg_replace('/[^A-Za-z0-9._-]/','_',$_FILES['derfile']['name']);		// preg_replace -- dash has to be at end
		//$filename_sans_ext=substr($origfilename,0,(strrpos($origfilename,'.')));
		//$filename_ext=substr($origfilename,(strrpos($origfilename,'.'))+1);
		$newfilename="{$scramblename}_{$origfilename}";

		if (empty($_FILES['derfile']) || $_FILES['derfile']['tmp_name']=='none'){$errors.='<li>No file uploaded.</li>';}
		if ($_FILES['derfile']['size']==0){$errors.="<li>File ({$_FILES['derfile']['tmp_name']}) is zero bytes.</li>";}

		if(isset($errors)){
			echo "$errors";	error_log("FileCatcher: FARCKLE: $errors");	exit;
		}else{
			if(move_uploaded_file($_FILES['derfile']['tmp_name'],"$root/$newfilename")){
				$dername=mysqli_real_escape_string($db,$_POST['name']);
				$derviddate=mysqli_real_escape_string($db,$_POST['viddate']);
				$derteach=mysqli_real_escape_string($db,$_POST['teacher']);
				$dercompno=mysqli_real_escape_string($db,$_POST['compno']);
				$derurl=mysqli_real_escape_string($db,$origfilename);				// shouldnt need this with the preg_replace above, but hey...
				$appid = mysqli_real_escape_string($db,$_POST['appid']);
				$query="INSERT INTO tbVideos (appid,name,url,scramble,viddate,teacher,compno,videxists) VALUES ('$appid','$dername','$derurl','$scramblename','$derviddate','$derteach','$dercompno','1')";			// additional data can be provided through the video manager
				$return=mysqli_query($db,$query);
				if(mysqli_affected_rows($db)==1){$msg="<p>{$_POST['name']} uploaded.</p>";}else{$msg="<p>Error in {$_POST['name']} upload.</p>";}
		//		echo $query;
			}else{
		//		echo 'XF';
			}
		}
}

if(!empty($_POST['whee'])){
	//$checkboxes=array('admin','coder','usermgr');
	$bits=array_keys($_POST);
	foreach($bits as $post){
		if($post != 'whee'){
			list($prefield,$id)=explode('_',$post);		// now we have first and 75
			if(is_numeric($id)){

				if($prefield=='inactive'){
					$ischeckbox='check';  $field='inactive';
					if($_POST[$post]=='true'){$val="'1'";}else{$val='NULL';}
				}else{
					$ischeckbox='notcheck';
					$field=mysqli_real_escape_string($db,$prefield);
					$val="'".mysqli_real_escape_string($db,$_POST[$post])."'";
				}
				$query="UPDATE tbVideos SET $field=$val WHERE id='$id' LIMIT 1";
				$return=mysqli_query($db,$query);
				if(mysqli_affected_rows($db)==1){echo "$post||$ischeckbox";}else{echo 'X';}
		//		echo "$post||$ischeckbox||$query";	
			}
		}
	}
}else{

$extraCSS=<<<ENDCSS
<style type="text/css">
	div.markoutercontent {background-color:#f0f8ff; padding: 20px 0 40px 0;} 
	div.markcontent {width: 780px; margin:10px auto;}
	div.video {margin:6px 0;}
	div.video input {border: 1px solid #ddd; border-radius:4px; padding: 0 6px; margin-right: 9px;}
	input.name {width: 280px;}
	input.origfi__lename {width: 270px;}
	input.scr__amble {width: 180px;}
	input.viddate {width: 100px;}
	input.teacher {width: 140px;}
	input.compno {width: 60px;}
	div.checks {width:80px; float: right; margin-top: 5px; font-size: 10px; color: #666;}
	span.checkwrapperspan {padding: 8px 0 3px 4px; border-radius: 4px; border: 1px solid transparent;}
	span.inactivespan {padding-right: 6px; vertical-align: top;}
	span.inactivechecked {color: red; font-size: 12px;padding-right: 0; line-height: 14px;}
	div.derid {width: 40px; font-weight: bold; margin:0; float:left; text-align:right; padding-right:8px;}
	
	div#marks-overlay {background-color:rgba(0,0,0,.3);position:fixed; top:0; left:0; width:100%;height:100%;z-index:601;display:none;}
	div#marks-overlay div#marks-overcontent {width: 376px;  height: 470px; position:absolute; top:50%; margin-top: -200px; left:50%; margin-left: -188px; border: 1px solid #666; padding: 20px; border-radius: 8px; background-color:#f8fbff; box-shadow: 0px 2px 48px #444;}
	div#marks-overlay div#marks-overcontent input[type=text] {width: 330px; margin:3px 0; border-radius: 6px; border: 1px solid #ccc; padding: 2px 8px;}
	div#new_video_button {background-color: #2b528e; width: 220px; float: right; border-radius: 24px; font-size: 18px; text-align:left; color: white; padding: 10px 10px 10px 16px; margin-top: 6px; cursor: pointer;}
	div#new_video_button:hover {background-color: #ffb851; color: #444;}
	div#new_video_button span.obvid {font-size: 14px; line-height: 10px;}
	div#new_video_button span.oi {float:right; font-size: 24px; top: 4px;}
</style>
ENDCSS;
$includeroot= $_SERVER['DOCUMENT_ROOT'];			$devprodroot='/newestccoi';			$serverroot=$_SERVER['SERVER_NAME'];
$extraJS=<<<ENDJS
<script language='javascript'>
	var derServer='{$devprodroot}/api/';
	function GetAjaxReturnObject(mimetype){var xmlHttp=null; if (window.XMLHttpRequest) {xmlHttp = new XMLHttpRequest(); if (xmlHttp.overrideMimeType) {xmlHttp.overrideMimeType(mimetype);}} else if (window.ActiveXObject) {try {xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");}catch (e) {try {xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");}catch (e) {}}} return xmlHttp;}
	function getHTML(httpRequest) {if (httpRequest.readyState===4) {if (httpRequest.status === 200) {return httpRequest.responseText;}}}
	function doUpdate(e){
		var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
		xmlHttp.onreadystatechange = function() {var data=getHTML(xmlHttp);
			if(data){ 							console.log(data);
				if(data != 'X'){
					bits=data.split('||');			//console.log(bits[2]);	// show the query for debug
					var flash=document.getElementById(bits[0]);
					if(bits[1]=='check'){
						if(bits[0].substring(0,8)=='inactive'){
							var inputs=flash.parentNode.parentNode.parentNode.getElementsByTagName('input');
							for(var n=0; n<inputs.length; n++){
								if(inputs[n].id != bits[0]){		// inactive checkbox is always active
									if(inputs[n].disabled==true){inputs[n].disabled=false;}else{inputs[n].disabled=true;}
								}
							}
						}else{
							flash.parentNode.style.borderColor='#0c0'; setTimeout(function(){flash.parentNode.style.borderColor='transparent';},2000);
						}
					}else{
						flash.style.color='#0c0'; setTimeout(function(){flash.style.color='#444';},2000);
					}
				}else{
					// ERROR
				}
			}
		}
		var bit;		if(e.target.type=='checkbox'){bit=e.target.checked;}else{bit=e.target.value;}
		var sendStr = 'whee=1&'+e.target.id+'='+bit;
		var url = derServer+'ccoi_videomanager2.php?'+sendStr;					console.log(url);
		xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
	}
	function blah(e){
		var bit;		if(e.target.type=='checkbox'){bit=e.target.checked;}else{bit=e.target.value;}
		console.log('clicked: '+e.target.id+' / '+bit);
	}

	function launchVideoPreview(scramble, url, title){
		console.log("test1");
		let popoutWindow = window.open("/video-player");
		console.log("test2");
		let videoSrc = "/ccoivids/" + scramble + "_" + url;
		console.log("test3");
		popoutWindow.src = videoSrc;
		popoutWindow.videoTitle = title;
	}

</script>
ENDJS;

	include('../includes/header.php');
	//echo "_SESSION['currentlyloadedapp'] = ".$_SESSION['currentlyloadedapp'];
	//echo "query: ". "INSERT INTO tbVideos (appid,name,url,scramble,viddate,teacher,compno,videxists) VALUES ('{$_SESSION['currentlyloadedapp']}')";	; 
	if( !($_SESSION['roles'][$_SESSION['currentlyloadedapp']]['admin']) ){
		header("Location: {$devprodroot}");
		//echo "get redirected, idiot";
	}
	else {
		//$_SESSION['currentlyloadedapp'] = $appid;
	}
	echo "<div class='markoutercontent'><div class='markcontent'>\n";
	
	if(!empty($msg)){echo $msg;}
	
	echo '<div id="new_video_button">Upload New Video <span class="oi oi-cloud-upload px-2" title="Upload New Video"></span></div>';
	echo "\n<h2>Video Management</h2>\n<p>The following videos have been uploaded to the system.<br />They are sorted alphabetically by file name.<br />Changes are live; there is no need to submit or save.</p>";

//	$origfilename=preg_replace("/[^A-Za-z0-9._-]/",'_','Moores\' "2017-12-08" C21');		//echo "x $origfilename";
	//preg_replace("/[^A-Za-z0-9. -]/",'',$_POST['address']);

	$return=mysqli_query($db,"SELECT * FROM tbVideos WHERE appid='{$_SESSION['currentlyloadedapp']}' order by inactive, name");
	while($p=mysqli_fetch_assoc($return)){
		if($count==5){$count=0;$gap='<br />';}else{$gap='';}  $count++; 
		if(!empty($p['inactive'])){
			$inactivechecked=' CHECKED';
			$inactivechecked2=' inactivechecked';
			$inactiveblock=' DISABLED';
		}//'/ccoivids/{$p['scramble']}_{$p['url']}'
		//href='/ccoivids/{$p['scramble']}_{$p['url']}'
		//target='ccoi_popout2'
		if(!empty($p['videxists'])){$popoutlink="<a onclick='launchVideoPreview(\"{$p['scramble']}\", \"{$p['url']}\", \"{$p['name']}\")'><span class='oi oi-external-link px-2' title='View Video'></span></a>";}else{$popoutlink='';}
		echo "<div class='video'>{$gap}<div class='derid'>{$p['id']}</div> <input type='text' class='name' title='{$p['url']} / {$p['scramble']}' id='name_{$p['id']}' value='{$p['name']}'{$inactiveblock} />";
		echo "<input type='text' class='viddate' id='viddate_{$p['id']}' value='{$p['viddate']}'{$inactiveblock} /><input type='text' class='teacher' id='teacher_{$p['id']}' value='{$p['teacher']}'{$inactiveblock} /><input type='text' class='compno' id='compno_{$p['id']}' value='{$p['compno']}'{$inactiveblock} />";
		echo "{$popoutlink}<div class='checks'><span class='checkwrapperspan'><span class='inactivespan{$inactivechecked2}'>Inactive</span><input type='checkbox' id='inactive_{$p['id']}'{$inactivechecked} /></span></div>";
		echo "</div>\n";
	}

echo '</div></div>';

	echo "<div id='marks-overlay'><div id='marks-overcontent'><h3>Upload New Video</h3><form method='POST' action='ccoi_videomanager2.php' enctype='multipart/form-data'>";
	echo "<input type='hidden' id='appid' name='appid' value='{$_SESSION['currentlyloadedapp']}'";
	echo "<label>Video Name<input type='text' id='name' name='name' placeholder='Programming Challenge Student 2' /></label><br />";
	echo "<label>Date Recorded<input type='text' id='viddate' name='viddate' placeholder='2020-01-01' /></label><br />";
	echo "<label>Teacher Name<input type='text' id='teacher' name='teacher' placeholder='Mr. Example' /></label><br />";
	echo "<label>Student #<input type='text' id='compno' name='compno' placeholder='12345678' /></label><br /><br />";
	echo "<label><input type='file' id='derfile' name='derfile' /><br /><br /><input id='subby' type='submit' value='Upload Video' /><br /><span style='font-size: 10px;'>(Current file size limit: 50MB)</span></form></div></div>";

echo<<<ENDJS2
<script language='javascript'>
	var overlay=document.getElementById('marks-overlay');						if(overlay){overlay.addEventListener('click',function(e){overlay.style.display='none';e.stopPropagation();},false);}
	var overcontent=document.getElementById('marks-overcontent');		if(overcontent){overcontent.addEventListener('click',function(e){e.stopPropagation();},false);}
	var vidbutton=document.getElementById('new_video_button');			if(vidbutton){vidbutton.addEventListener('click',function(e){overlay.style.display='block';e.stopPropagation();},false);}
	
	var videos=document.getElementsByClassName('video');
	if(videos){for(e=0;e<videos.length;e++){
		for(var n=0; n<videos[e].childNodes.length; n++){
			videos[e].childNodes[n].addEventListener('change',function(e){doUpdate(e);e.stopPropagation();},true);
	}}}
</script>
ENDJS2;

include('../includes/footer.php');
}





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