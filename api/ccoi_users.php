<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
$CCOI_requireslogin=true; $CCOI_requiresadmin=true;
//include('./ccoi_session.php');		// header has this already
include('./ccoi_dbhookup.php');
//$db=mysqli_connect("localhost","ccoi_editor","8rW4jpfs67bD",'newccoi');
//if(!$db){$err=mysqli_error();		exit ("Error: could not connect to the CCOI Database! -- $access -- $err");}


if(!empty($_POST['yahoo']) && !empty($_POST['email']) && !empty($_POST['newpass'])){
	foreach($_POST as $k=>$v){$_SCRUBBED[$k]=mysqli_real_escape_string($db,$v);}
	$passhash=password_hash($_POST['newpass'],PASSWORD_BCRYPT);
	$query="INSERT INTO tbPeople (last, first, email,passhash) VALUES ('{$_SCRUBBED['last']}','{$_SCRUBBED['first']}','{$_SCRUBBED['email']}','$passhash')";
//	echo $query;
	$return=mysqli_query($db,$query);	
	if(mysqli_affected_rows($db)==1){
		$newid=mysqli_insert_id($db);
		$query="INSERT INTO tbPersonAppRoles (personid,appid,role) VALUES ('$newid','1','coder')";			//echo $query;	// user always exists even if the person is de-activated
		$return=mysqli_query($db,$query);
	}else{$msgbox='<div id="msgbox">There was a problem adding your new user</div>';}
}


if(!empty($_POST['whee'])){
		$checkboxes=array('admin','coder','usermgr');
		$bits=array_keys($_POST);
		foreach($bits as $post){
			if($post != 'whee'){
				list($prefield,$id)=explode('_',$post);		// now we have first and 75
				if(is_numeric($id)){
					if(in_array($prefield,$checkboxes)){			// checkboxes edit a different table and get different actions on return -- NOTE -- inactive checkbox does not go here -- see below
						$ischeckbox='check';
						$field=mysqli_real_escape_string($db,$prefield);
						$prequery="SELECT id FROM tbPersonAppRoles WHERE personid='$id' AND appid='{$_SESSION['currentlyloadedapp']}' AND role='$field' LIMIT 1";
						$return=mysqli_query($db,$prequery);
						if($return->num_rows > 0){
							if($_POST[$post]=='false'){
								$query="DELETE FROM tbPersonAppRoles WHERE personid='$id' AND appid='{$_SESSION['currentlyloadedapp']}' AND role='$field' LIMIT 1";
							}    // if we already have it set and we're trying to set again, something farted -- ignore
						}else{
							if($_POST[$post]=='true'){
								$query="INSERT INTO tbPersonAppRoles (personid,appid,role) VALUES ('$id','1','$field')";
							}  	  // if we dont have it set and we're trying to remove it, something farted -- ignore
						}
						if(!empty($query)){
							$return=mysqli_query($db,$query);
							if(mysqli_affected_rows($db)==1){echo "$post||$ischeckbox";}else{echo 'X';}
					//		echo "$post||$ischeckbox||$query";
						}
					}else{
						if($prefield=='inactive'){
							$ischeckbox='check';  $field='inactive';
							if($_POST[$post]=='true'){$val="'1'";}else{$val='NULL';}
						}else{
							$ischeckbox='notcheck';
							$field=mysqli_real_escape_string($db,$prefield);
							if($field=='newpass'){
								$val="'".password_hash($_POST[$post],PASSWORD_BCRYPT)."'";		// have to do the " ' " thing for the NULL (could do zero, but I like null)
								$field='passhash';
							}else{
								$val="'".mysqli_real_escape_string($db,$_POST[$post])."'";
							}
						}
						$query="UPDATE tbPeople SET $field=$val WHERE id='$id' LIMIT 1";
						$return=mysqli_query($db,$query);
						if(mysqli_affected_rows($db)==1){echo "$post||$ischeckbox";}else{echo 'X';}
						echo "$post||$ischeckbox||$query";
					}
				}
			}
		}
		exit;		// we dont return anything by default for an AJAX POST

}else{

//$msgbox='<div id="msgbox">There was a problem adding your new user</div>';

$extraCSS=<<<ENDCSS
<style type="text/css">
	div.markoutercontent {background-color:#f0f8ff; padding: 20px 0 40px 0;} 
	div.markcontent {width: 900px; margin:10px auto;}
	div.user {margin:6px 0;}
	div.user input {border: 1px solid #ddd; border-radius:4px; padding: 0 6px;}
	
	div.newuser {margin:6px 0;}
	div.newuser input {border: 1px solid #ddd; border-radius:4px; padding: 0 6px;}
	
	input.last {width: 180px; text-align:right;}
	input.first {width: 100px;}
	input.email {width: 280px;}
	input.newpass {width: 120px;}
	div.checks {width:170px; float: right; margin-top: 5px; font-size: 10px; color: #666;}
	span.checkwrapperspan {padding: 8px 0 3px 4px; border-radius: 4px; border: 1px solid transparent;}
	span.inactivespan {padding-right: 6px;}
	span.inactivechecked {color: red; font-size: 18px;padding-right: 0; line-height: 14px;}
	
	div#msgbox {width: 50%; min-width: 300px; margin: 0 auto 2em auto; text-align: center; border-radius: 8px; padding: 12px; border: 1px solid #0064bb; background-color: #d4ebff;}
</style>
ENDCSS;

$extraJS=<<<ENDJS
<script language='javascript'>
	var derServer='{$serverroot}{$devprodroot}/api/';
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
		var bit;		if(e.target.type=='checkbox'){bit=e.target.checked;}else{bit=encodeURIComponent(e.target.value);}
		var sendStr = 'whee=1&'+e.target.id+'='+bit;
		var url = derServer+'ccoi_users.php?'+sendStr;					console.log(url);
		xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
	}
	function blah(e){
		var bit;		if(e.target.type=='checkbox'){bit=e.target.checked;}else{bit=e.target.value;}
		console.log('clicked: '+e.target.id+' / '+bit);
	}
</script>
ENDJS;

include('../includes/header.php');
echo "<div class='markoutercontent'><div class='markcontent'>{$msgbox}<h2>User Management</h2><p>The following user accounts are registered in the system.  Changes are live; there is no need to submit or save.</p>";

echo "<div class='newuser'><form name='derform' method='POST' action='ccoi_users.php'><input type='hidden' name='yahoo' value='1' /><input type='text' name='last' class='last' id='last_0' placeholder='Last Name' />, <input type='text' name='first' class='first' id='first_0' placeholder='First Name' />";
echo " <input type='text' name='email' class='email' id='email_0' placeholder='Email' /> <input type='text' name='newpass' class='newpass' id='newpass_0' placeholder='new password' /> <input type='submit' value='Create New User' style='width: 170px; margin-bottom: 10px; border-color: #888;' /></form></div>";

$return=mysqli_query($db,"SELECT personid, role FROM tbPersonAppRoles WHERE appid='{$_SESSION['currentlyloadedapp']}'");			// APPID and role required to be in the list
while($p=mysqli_fetch_assoc($return)){$roles[$p['personid']][$p['role']]=1;}
$pidstext=implode(',',array_keys($roles));

$return=mysqli_query($db,"SELECT * FROM tbPeople WHERE id IN ($pidstext) order by inactive, last, first");
while($p=mysqli_fetch_assoc($return)){

	$adminchecked=$coderchecked=$usermgrchecked=$inactivechecked=$inactivechecked2=$inactiveblock='';
	if(!empty($roles[$p['id']]['admin'])){$adminchecked=' CHECKED';}
	if(!empty($roles[$p['id']]['coder'])){$coderchecked=' CHECKED';}
	if(!empty($roles[$p['id']]['usermgr'])){$usermgrchecked=' CHECKED';}
	if(!empty($p['inactive'])){
		$inactivechecked=' CHECKED';
		$inactivechecked2=' inactivechecked';
		$inactiveblock=' DISABLED';
	}

	echo "<div class='user'><input type='text' class='last' id='last_{$p['id']}' value='{$p['last']}'{$inactiveblock} />, <input type='text' class='first' id='first_{$p['id']}' value='{$p['first']}'{$inactiveblock} />";
	echo " <input type='text' class='email' id='email_{$p['id']}' value='{$p['email']}'{$inactiveblock} /> <input type='text' class='newpass' id='newpass_{$p['id']}' placeholder='new password'{$inactiveblock} /><div class='checks'>";
	echo "<span class='checkwrapperspan'>A <input{$inactiveblock} type='checkbox' id='admin_{$p['id']}'{$adminchecked} /></span> ";
	echo "<span class='checkwrapperspan'>C <input{$inactiveblock} type='checkbox' id='coder_{$p['id']}'{$coderchecked} /></span> ";
	echo "<span class='checkwrapperspan'>U <input{$inactiveblock} type='checkbox' id='usermgr_{$p['id']}'{$usermgrchecked} /></span> ";
	echo "<span class='checkwrapperspan'><span class='inactivespan{$inactivechecked2}'>I</span><input type='checkbox' id='inactive_{$p['id']}'{$inactivechecked} /></span>";
	echo "</div></div>\n";
}

echo '</div></div>';
echo<<<ENDJS2
<script language='javascript'>
	var users=document.getElementsByClassName('user');
	if(users){for(e=0;e<users.length;e++){
		for(var n=0; n<users[e].childNodes.length; n++){
			users[e].childNodes[n].addEventListener('change',function(e){doUpdate(e);e.stopPropagation();},true);
	}}}
</script>
ENDJS2;

include('../includes/footer.php');
} // end ELSE for POST check -- if POST then we dont return a page
?>