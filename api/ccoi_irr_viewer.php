<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
$CCOI_requireslogin=true; $CCOI_requiresadmin=true;
include('./ccoi_dbhookup.php');

$extraCSS=<<<ENDCSS
<style type="text/css">
		table.ObTable {padding: 20px; border: 1px solid black; font-size: 14px; text-align:center; border-collapse: collapse;border-spacing: 0;}
		table.ObTable tr.pathhead {font-size: 18px; font-weight: bold;}
		table.ObTable tr.pathcats {font-size: 16px; font-weight: bold;}
		table.ObTable td {padding: 6px 12px; border: 1px solid #888; text-align:center;}
		table.ObTable tr.check {background-color:none;}
		table.ObTable tr.ack {background-color:#fdd;}
		table.ObTable tr.bigack {background-color:#fdd;}
		div#irr_errors {border: 1px solid red; background-color: #fdd; padding: 20px; margin: 20px 10px; width: 700px;}
		div#dercontent select {display:block; margin: 10px 0; font-size: 20px; padding: 4px 10px 4px 4px; border-radius:4px;}
		div#dercontent {padding: 20px;}
</style>
ENDCSS;

$extraJS=<<<ENDJS
<script language='javascript'>
//	var derServer='{$includeroot}{$devprodroot}/api/';
	function GetAjaxReturnObject(mimetype){var xmlHttp=null; if (window.XMLHttpRequest) {xmlHttp = new XMLHttpRequest(); if (xmlHttp.overrideMimeType) {xmlHttp.overrideMimeType(mimetype);}} else if (window.ActiveXObject) {try {xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");}catch (e) {try {xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");}catch (e) {}}} return xmlHttp;}
	function getHTML(httpRequest) {if (httpRequest.readyState===4) {if (httpRequest.status === 200) {return httpRequest.responseText;}}}
	function getIRR(){
		var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
		xmlHttp.onreadystatechange = function() {var data=getHTML(xmlHttp);
			if(data){ 							console.log(data);
				if(data != 'X'){
					document.getElementById('viewer').innerHTML=data;
				}
			}
		}
		var irrA=sessionA.options[sessionA.selectedIndex].value;
		var irrB=sessionB.options[sessionB.selectedIndex].value;
		if(irrA==irrB){alert('can\'t review the same session');return false;}
		
		var sendStr = 'irrA='+irrA+'&irrB='+irrB;
		var url = derServer+'api/ccoi_ajax.php?'+sendStr;					console.log(url);
		xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
	}
	function getSessions(e){
		var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
		xmlHttp.onreadystatechange = function() {var data=getHTML(xmlHttp);
			if(data){ 							console.log(data);
				if(data != 'X'){
					sessionA.innerHTML='<option disabled selected>Select a session to compare</option>'+data;
					sessionB.innerHTML='<option disabled selected>Select a session to compare</option>'+data;
				}
			}
		}
		var sendStr = 'irrvid='+vidList.options[vidList.selectedIndex].value;
		var url = derServer+'api/ccoi_ajax.php?'+sendStr;					console.log(url);
		xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
	}
</script>
ENDJS;

include('../includes/header.php');
echo "<script language='javascript'>var derServer='https://{$serverroot}{$devprodroot}/';var derDevProd='{$devprodroot}';</script>\n";		// sigh -- needs to be after header, but before JS below
echo "<div id='dercontent'><h1>IRR Viewer</h1>";

$query="SELECT s.id, s.videoid FROM tbSessions s, tbPeopleAppSessions pas WHERE pas.appid='1' AND pas.sessionid=s.id AND videoid IS NOT NULL AND s.inactive IS NULL AND pas.inactive IS NULL";				$return=mysqli_query($db,$query);
while($p=mysqli_fetch_assoc($return)){$sesscount[$p['videoid']]++;}			//print_r($sesscount);

$query="SELECT id, name FROM tbVideos WHERE appid='1' AND inactive IS NULL";					$return=mysqli_query($db,$query);
while($p=mysqli_fetch_assoc($return)){$vidlist.="<option value='{$p['id']}'>{$p['name']} ({$sesscount[$p['id']]})</option>";}
echo "<select id='vidList'><option disabled selected>Select a video</option>{$vidlist}</select>";
?>

<select id='sessionA'><option disabled selected>Select a session to compare</option></select>
<select id='sessionB'><option disabled selected>Select a session to compare</option></select>
<button id='derbutton' onclick='getIRR();'>Compare</button>

<div id='viewer'>&nbsp;</div>
</div>
<script language='javascript'>
	var vidList=document.getElementById('vidList');
		if(vidList){vidList.addEventListener('change',function(e){getSessions(e);e.stopPropagation();},false);}
	var viewer=document.getElementById('viewer');
	var sessionA=document.getElementById('sessionA');
	var sessionB=document.getElementById('sessionB');
</script>

<?php
include('../includes/footer.php');
?>