<?php
	session_start();
	if(!empty($_POST['useremail']) && !empty($_POST['password'])){
	
//		$db=mysqli_connect("localhost","ccoi_editor","8rW4jpfs67bD",'newccoi');
//		if(!$db){$err=mysqli_error();		exit ("Error: could not connect to the CCOI Database! -- $access -- $err");}
		include('./ccoi_dbhookup.php');
		$washedemail=mysqli_real_escape_string($db,$_POST['useremail']);
		$return=mysqli_query($db,"SELECT * FROM tbPeople WHERE email='$washedemail'");		$persondata=mysqli_fetch_assoc($return);

		if( !password_verify($_POST['password'],$persondata['passhash']) ){
			echo "login nogo";
		}else{
			//echo "yay, you're {$persondata['first']}!";			
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
			
			if(!empty($_SESSION['loginroute'])){
				header("Location: {$_SESSION['loginroute']}");
				$_SESSION['loginroute']='';				session_write_close();												exit;
			}else{
				header("Location: https://{$_SERVER['SERVER_NAME']}/");					session_write_close();		exit;

			}
		}
	}
	


/*

Set-Cookie: XSRF-TOKEN=eyJpdiI6IjVHdEpUUDFpNkhpbnM4M05jZ1g3M2c9PSIsInZhbHVlIjoiR25zdDVKcTZNSWNmXC9yVmJMZTZqSElyWDI2ZjNhcWo3WEQ4bFhQaUR0MXBMcVR0WlhseE8rWjRPXC9RNUY2b1ZWOEtJaSszTTZxZjZsZ0dsODNPTHQrUT09IiwibWFjIjoiZGZhM2NlOGE3NGI0N2ZmNDA5NjNmZDYwZTM1YTg2ZGEwZjA0OWIxZWM3YTZmMTUzNjA3NzA5OTk5NGJiNTUyMiJ9; expires=Sat, 11-Jul-2020 17:04:22 GMT; Max-Age=172800; path=/
Set-Cookie: laravel_session=eyJpdiI6ImxHMlUwRDFZak1FWVp3cERNOW80N2c9PSIsInZhbHVlIjoibnlnY2Y4QWFnMnJ5RVV5RGJZSGRyaThQclYxTVFUWGo4a3JvUHRORGtkYmNoT2xibDBHa3ZGcUNkc25UT0dnNmh4Nm1QZnY2ekE0dG5NbXZLSzNQdXc9PSIsIm1hYyI6Ijg5YWRjY2MyNDJhNWE5ODFiOTM3MWM5OTIzZmU5Y2NlYTIyZWNmNjc4MWU5OTRkNGExY2EyNTcwMjE5NjA4ZmUifQ%3D%3D; expires=Sat, 11-Jul-2020 17:04:22 GMT; Max-Age=172800; path=/; httponly

*/
	
?>