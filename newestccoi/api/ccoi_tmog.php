<?php
	session_start();

	// transmogrifier for Mark
	if($_SESSION['pid']==32 && !empty($_GET['tmog'])){
//		$db=mysqli_connect("localhost","ccoi_editor","8rW4jpfs67bD",'newccoi');
//		if(!$db){$err=mysqli_error();		exit ("Error: could not connect to the CCOI Database! -- $access -- $err");}
		include('./ccoi_dbhookup.php');
		
		$tmog=$_GET['tmog']+0;$return=mysqli_query($db,"SELECT * FROM tbPeople WHERE id='$tmog'");		$persondata=mysqli_fetch_assoc($return);
		
		$return=mysqli_query($db,"SELECT * FROM tbPersonAppRoles WHERE personid='{$persondata['id']}'");		// need to add      AND inactive IS NULL
			while($roledata=mysqli_fetch_assoc($return)){
		//		$_SESSION['roles'][$roledata['role']]=true;
				switch($roledata['role']){
					case 'superadmin': 	$_SESSION['roles'][$roledata['appid']]['superadmin']=true;		// highest level gets all lower levels -- no "break" for cases
					case 'admin': 			$_SESSION['roles'][$roledata['appid']]['admin']=true;
		//			case 'usermgr': 		$_SESSION['roles'][$roledata['appid']]['usermgr']=true;
					case 'coder': 			$_SESSION['roles'][$roledata['appid']]['coder']=true;
		//			case 'superadmin': 	$_SESSION['roles']['superadmin']=true;
		//			case 'superadmin': 	$_SESSION['roles']['superadmin']=true;
				}
	//			if($roledata['role']=='admin'){$_SESSION['roles']['coder']=true;$_SESSION['roles']['usermgr']=true;$_SESSION['roles']['videosync']=true;}
			}
			
			$return=mysqli_query($db,"SELECT pas.*, s.* FROM tbPeopleAppSessions pas, tbSessions s WHERE pas.personid='{$persondata['id']}' AND pas.inactive IS NULL AND pas.sessionid=s.id AND s.inactive IS NULL");
			while($data=mysqli_fetch_assoc($return)){$_SESSION['sessions'][$data['appid']][$data['sessionid']]=$data['name'];}
			
			$return=mysqli_query($db,"SELECT pap.*, pl.* FROM tbPeopleAppPlaygrounds pap, tbPlaygrounds pl WHERE pap.personid='{$persondata['id']}' AND pap.inactive IS NULL AND pap.sessionid=pl.id AND pl.inactive IS NULL");
			while($data=mysqli_fetch_assoc($return)){$_SESSION['playgrounds'][$data['appid']][$data['sessionid']]=$data['name'];}
			
			$_SESSION['pid']=$persondata['id'];
			$_SESSION['first']=$persondata['first'];
			$_SESSION['last']=$persondata['last'];
			$_SESSION['email']=$persondata['email'];
			
			$_SESSION['currentlyloadedapp']=1;		// CCOI is it for now
			
	//		$_SESSION['myapps'][1]=true;
			
			header("Location: https://{$_SERVER['SERVER_NAME']}/newobserve.php");					session_write_close();		exit;

	}
	
?>