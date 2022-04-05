<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
//$CCOI_requireslogin=true;
//include('./ccoi_session.php');

//$debug=true;

$db=mysqli_connect("localhost","ccoi_editor","8rW4jpfs67bD",'newccoi');
if(!$db){$err=mysqli_error();		exit ("Error: could not connect to the CCOI Database! -- $access -- $err");}

$goodtogo=false;
if(isset($_GET['uid']) && is_numeric($_GET['uid'])){					// NOTE: in many cases the user is not registered and just futzing around -- UID:zero -- still legit though (not null)
	$uid=$_GET['uid']+0;		// send me text and I'll turn it into a zero
	if($uid>=0){
		if(!empty($_GET['playid']) && is_numeric($_GET['playid'])){
			$playid=$_GET['playid']+0;
			if($playid > 0){				// if the number is positive -- then normal (move from playground to real data) if negative, then mark's tool to move shitty data to playground
				$tb1a='tbPeopleAppPlaygrounds';  $tb1b='tbPeopleAppSessions';
				$tb2a='tbPlaygrounds';					$tb2b='tbSessions';
				$tb3a='tbPlaygroundActivity';		$tb3b='tbSessionActivity';
				$query="SELECT id FROM {$tb1a} WHERE personid='$uid' AND appid='{$_SESSION['currentlyloadedapp']}' AND sessionid='$playid'";
				if(!$debug){$return=mysqli_query($db,$query);}else{echo "$query<br />\n";}
				if(mysqli_num_rows($return)==1){$goodtogo=true;}
		//		$goodtogo=true;
			}else{
				$playid=abs($playid);
				$tb1a='tbPeopleAppSessions';	$tb1b='tbPeopleAppPlaygrounds';
				$tb2a='tbSessions';					$tb2b='tbPlaygrounds';
				$tb3a='tbSessionActivity';		$tb3b='tbPlaygroundActivity';
				if($uid==32){$goodtogo=true;}								// ========== this tool is for mark to use a tiny bit to scrub out shit
			}

			if($goodtogo){
				$query="SELECT * FROM {$tb2a} WHERE id='$playid'";
				$return=mysqli_query($db,$query);
				if($debug){echo "$query<br />\n";}
				while($d=mysqli_fetch_assoc($return)){$inputdata['s']=$d;}

				$query="SELECT * FROM {$tb3a} WHERE sessionid='$playid' ORDER BY seconds, subsession, id";
				$return=mysqli_query($db,$query);
				if($debug){echo "$query<br />\n";}
				while($d=mysqli_fetch_assoc($return)){$inputdata['sa'][]=$d;$needyays++;}

				foreach($inputdata['s'] as $k=>$v){if(!empty($v) && $k!='id'){$qd[$k]=$v;}}
				$ks=implode(',',array_keys($qd));
				$vs=implode("','",array_values($qd));
				$query1="INSERT INTO {$tb2b} ({$ks}) VALUES ('{$vs}')";
				if(!$debug){$return=mysqli_query($db,$query1);$noob=mysqli_insert_id($db);}else{echo "$query1<br />\n";$noob=99;}

				if($noob > 0){
					$query1="INSERT INTO {$tb1b} (personid,appid,sessionid) VALUES ('{$uid}','1','{$noob}')";
					if(!$debug){$return=mysqli_query($db,$query1);}else{echo "$query1<br />\n";}
					foreach($inputdata['sa'] as $d){
						$qd=array();
						foreach($d as $k=>$v){if(!empty($v) && $k!='id'){$qd[$k]=$v;}}
							$qd['sessionid']=$noob;
						$ks=implode(',',array_keys($qd));
						$vs=implode("','",array_values($qd));
						$query2="INSERT INTO {$tb3b} ({$ks}) VALUES ('{$vs}')";
						if(!$debug){$return=mysqli_query($db,$query2);}else{echo "$query2<br />\n";}
						if(mysqli_affected_rows($db)>0){$yays++;}
					}
					if($yays==$needyays){
						$query="UPDATE {$tb1a} SET inactive='1' WHERE personid='$uid' AND appid='{$_SESSION['currentlyloadedapp']}' AND sessionid='$playid'";
						if(!$debug){$return=mysqli_query($db,$query);}else{echo "$query<br />\n";}
						$query="UPDATE {$tb2a} SET inactive='1' WHERE id='$playid'";
						if(!$debug){$return=mysqli_query($db,$query);}else{echo "$query<br />\n";}
						$query="UPDATE {$tb3a} SET inactive='1' WHERE sessionid='$playid'";
						if(!$debug){$return=mysqli_query($db,$query);}else{echo "$query<br />\n";}
						
						echo "SUCCESS:$noob";		// return the new session id
						
					}else{echo 'ERROR:  activity migration failed';}
				}else{echo 'ERROR:  session migration failed';}
			}else{echo 'ERROR:  user not associated with session';}
		}else{echo 'ERROR:  sessionid missing';}
	}else{echo 'ERROR:  uid error';}
}else{echo 'ERROR:  uid missing';}



?>