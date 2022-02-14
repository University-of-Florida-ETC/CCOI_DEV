<?php
	session_start();			//echo 'mark test';
	$salts=array('Mv1E22cz5efc','1n8OmX6j3kjs','xe9nyS4eH3i1','dlW5it6re21S','z9Z3fwwY5iy2','Ocrb4nG334lm','2n4vV3ppvN4v','6N7fK4jny2ya','91hIvfg35brR','rx98b7AliGj5','s7JocG3t13cc','4Mz3d1hkL3no','8dm86fo2llWD','vI9v65xUj7wh','526Soyy3khOv','52cI36Mvkocc','beG7m2ck9gV3','M9zmS2z5tny5','U7buajg9T9v1','a4o1u4yM5Xtz','1ntLjx456Icw','f9cr9Wvi9gA1','438ps2yLulxV','5g8SnfJ4ph8x','61Ac6uYhh2rg','Xqr2rjC76v3b','VQbom9132wpb','4fw5a4Kqq4tU','9shDljV1q7o4','rWa61Kno3ua5','JkbV8m6i7lv9','Kmt93oUkv5v5','V8uO5ip8ju1m','x1u56Ym4mnRs','l8mzZye36N3x','x457f3bnhIxM','ij52hF2Swdo7','T9wjze8gQ7f6','eJppS6ek5a15','pj91efVw4k7C','GkTcc8b26a8g','wVtP2ph9y6a5','d2qX277dnoNz','vd58uc53bUoJ','gRg1Po33n4oy','xj1f32GAv3ad','y8yc77iVYn6n','15Juw3kUw3wy','18ri7D4nejxT','PtF6ei7xj1a2','wgup72yL2c8W','fsl669GTpci8','hxNM2u3sb79e','5oojzdbR8S55','idv11He4Ds3l','u57k69wJQlvi','5i5Yga57lhQw','b8Nl3j4cpUq1','eeb1sC1xVg37','rt4C4fqz8M4h','35FrzgKrne59','8VPncebb95k8','5j6sG1Alb6dz','1jp7r1epHt4N','75r25fSIwnau','pf9wUai494Np','999WXqvxa1ty','iHzx57l3wIx6','w6iFvpR86un5','x8u38YmokU9f','Lo7ka93b1gJb','exf81o6Smb5L','xyL7Ec8nno59','8zwM1X3jg1km','Kfq3I3v4fwx9','uc17g2A5yvTe','52jMq9Oghty8','JdJn5o2rq3o7','81pt9Hib8Eeb','c4vW2fgl9Ep3','k1j6js9C4cxU','rikH9g8cp42I','G7j2or42uKak','v5fbLM7av8y6','35rfryWa17pT','sc47w9unM7vB','Wz5y2pxIj99h','hCci5J9ysy84','gF6Ld81r3fit','J4krC5u9qkr3','Rq8ufh2sDn61','bKqyckz927T2','po5wlD4qZi99','Gc33t8sv7Umt','33wcGrxY39oq','n3nSu7Nsbr52','v1nhtruP986K','1b9w2aeBxl6W','9zky6k8Uf3lW','cwmJ1dJi54l4');

	// ==== always create a token if they dont have one and store that in session for use by the pages...
	if(empty($_SESSION['csrfragment'])){
		// need to make the various token bits and return them
		$_SESSION['arrival']=time();    	$presalt=substr($_SESSION['arrival'],-2);    $presalt+=0;		// grab the last 2 digits of arrival seconds
		$_SESSION['extrasalt']=$salts[$presalt];
		$billy=session_id().$_SESSION['arrival'].$_SESSION['extrasalt'];
		$options = ['cost' => 11];
		$steve=password_hash($billy, PASSWORD_BCRYPT, $options);
		$_SESSION['csrfragment']=substr($steve,0,10);
		$_SESSION['csrftoken']=substr($steve,10);		// we store this so the remainging php can insert it where needed...
//		echo "<p>made a token: {$steve}</p>";
		setcookie("dertoken", $_SESSION['csrftoken'], time()+3600);
	}else{
		if($CCOI_requirestoken){
			// ======= we;ve set up a token for them, lets see if the token they returned is good...  you should only see this if they HAVE a token, now we see if its the right one
			$checktoken=$_SESSION['csrfragment'].$_COOKIE['dertoken'];
			$billy=session_id().$_SESSION['arrival'].$_SESSION['extrasalt'];
			if( !password_verify($billy,$checktoken) ){
//				echo "<p>Mark test: You need a CSRF token and either you didnt send one, or it was not the right one</p>";			echo "$billy / $checktoken / {$_SESSION['csrfragment']}";
				// ===== here is where we either return a failure code, or route them to a login
				$msg['errorType']='Token failure';     $output=json_encode($msg);     echo $output;     exit;
			}else{
//				echo '<p>your token is good</p>';
			}
		}
	}
		
	if($CCOI_requireslogin && empty($_SESSION['pid'])){
		//echo 'mark test: you need to login';
		$_SESSION['loginroute']="https://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
		header("Location: https://{$_SERVER['SERVER_NAME']}/login.php");		// ===== add "newlogin" for prod =======================================
	}

	if($CCOI_requiressuperadmin && empty($_SESSION['roles'][1]['superadmin'])){
		$msg['errorType']='Role Failure: SuperAdmin';     $output=json_encode($msg);     echo $output;     //exit;
	}
	
	if($CCOI_requiresadmin && empty($_SESSION['roles'][1]['admin'])){
		$msg['errorType']='Role Failure: Admin';     $output=json_encode($msg);     echo $output;     //exit;
	}

	if($CCOI_requirescoder && empty($_SESSION['roles'][1]['coder'])){
		$msg['errorType']='Role Failure: Coder';     $output=json_encode($msg);     echo $output;     //exit;
	}
	
// 	if($CCOI_requiresvideosync && empty($_SESSION['roles']['videosync'])){
// 		$msg['errorType']='Role Failure: Videosync';     $output=json_encode($msg);     echo $output;     //exit;				// videosync and usermgr are retired
// 	}

	if(!empty($_SESSION['pid'])){
		$jsUserVars="<script>var jsUserVars=new Object();";
			$jsUserVars.="jsUserVars['pid']='{$_SESSION['pid']}';";
			$jsUserVars.="jsUserVars['first']='{$_SESSION['first']}';";
			$jsUserVars.="jsUserVars['last']='{$_SESSION['last']}';";
			$jsUserVars.="jsUserVars['email']='{$_SESSION['email']}';";
			if($_SESSION['roles']['coder']){$jsUserVars.="jsUserVars['isCoder']=true;";}
			if($_SESSION['roles']['admin']){$jsUserVars.="jsUserVars['isAdmin']=true;";}
		$jsUserVars.="</script>";
	}
	
//	if($_SESSION['pid']=='32'){
	//	echo 'Hi Mark!';
	//	print_r($_SESSION);
//	}

?>