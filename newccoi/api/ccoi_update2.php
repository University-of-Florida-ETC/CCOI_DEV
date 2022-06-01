<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
$CCOI_requireslogin=true;
include('./ccoi_session.php');
include('./ccoi_dbhookup.php');
$debug=false;
if($_SESSION['pid']=='32'){$debug=true;}

if($_POST['play']!='true'){$_POST['play']='true';}		/// ============  TEMP TEMP TEMP === FORCE PLAYGORUND

if(!empty($_GET['himark'])){$_POST=$_GET; echo "<pre>himark\n\n";}
//print_r($_POST);	echo "\n\n";		//$_POST['updates']='{"62":{"notes":"note\r\rnote2"}}';

if(!empty($_POST['dafunc']) && isset($_POST['play'])){		// we should explicitly require play=0 for research data

	$dafunc=$_POST['dafunc'];

	if($_POST['play']=='true'){$subplay='Playground'; $noteplay='Playground ';}else{$subplay='Session';}
	
	$json_bugbears_hunt=["\n","\r","\t"];		$json_bugbears_fix=['\n','\r','\t'];		// so... JS URL_Encode will encode "\n" as %0A and the auto URL_decode or POST will turn that into the actual hard return rather than '\n' -- this code f#cks up the php json_decoder

	if(!empty($_POST['updates'])){
		$_POST['updates']=str_replace($json_bugbears_hunt,$json_bugbears_fix,$_POST['updates']);
		$pupdates=json_decode($_POST['updates'],true);	//	print_r($pupdates);	echo "\n-----\n";  json_last_error_msg();
		foreach($pupdates as $t=>$u){		foreach($u as $n=>$d){			//echo "$t / $u / $d\n";					// ====== note - we're sending urlencoded data, but PHP auto decodes GET, POST, and REQUEST
			switch($n){
				case 'noob':		$noobs[]=$d; break;					// should only be one at a time, but hey...
				case 'nuke':		$nukes[]=$t; break;					// can often have many at once when snipping a branch
				case 'swap':		$swaps[$t]=$d; break;					// should only be one at a time, but hey...
				default:				$updates[$t][$n]=mysqli_real_escape_string($db,$d);				// $updates[1766]['pn']=128 (see above line 11 or so)
		}}}
		
		foreach($noobs as $attach){
			switch($dafunc){		// create a new row for the table and attach to the appopriate things -- for now, lets not auto-populate, but rather use defaults and let the updater work later
				case 'OBS':	$query="INSERT INTO tb{$subplay}s (pathid,name,createdon) VALUES ('{$attach}','New {$noteplay}Observation Set',NOW())";			// === NOTE == TODO == this path should not be set here, but in response to data sent in the request
										if(!$debug){$return=mysqli_query($db,$query);$lastid=mysqli_insert_id($db);}else{$returnData['queries'].="QUERIES - no processing - $query";$lastid=rand(9000,9999);}
									$query="INSERT INTO tbPeopleApp{$subplay}s (personid,appid,sessionid) VALUES ('{$_SESSION['pid']}','{$_SESSION['currentlyloadedapp']}','{$lastid}')";
										if(!$debug){$return=mysqli_query($db,$query);$lastid=mysqli_insert_id($db);}else{$returnData['queries'].="QUERIES - no processing - $query";$lastid=rand(9000,9999);}
									$query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('newobs','{$attach}','new','{$lastid}','{$_SESSION['pid']}',NOW())";		if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
									$returnData["{$dafunc}noob0"]="{$attach}_{$lastid}";
									break;		// create in tbSessions(Play) and attach to tbPeopleAppSessions(Play)-- should it also pre-create the first subsession -- and OE?
									
				case 'OB':		//list($attach1,$attach2)=explode('|',$attach);				// sessid and subnum to get correct id
									$query="SELECT subnum AS tempybit FROM tbSub{$subplay}s WHERE sessid='{$attach}' AND inactive IS NULL ORDER BY subnum DESC LIMIT 1";		// here, we're fetching a bit of data -- we'll store it in $lastselect for later use -- NOTE, if this is the first subnum, then there will be no return data
										//if(!$debug){
										$return=mysqli_query($db,$query);$tempy=mysqli_fetch_assoc($return); $lastselect=$tempy['tempybit'];
										//}else{$lastselect=rand(8800,8888);}
										$lastselect1=$lastselect+1;
									$query="INSERT INTO tbSub{$subplay}s (sessid,subnum,name) VALUES ('{$attach}','{$lastselect1}','New Observation')";
										if(!$debug){$return=mysqli_query($db,$query);$lastid=mysqli_insert_id($db);}else{$returnData['queries'].="QUERIES - no processing - $query";$lastid=rand(9000,9999);}
									$query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('newob','{$attach}','new','{$lastid}','{$_SESSION['pid']}',NOW())";		if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
									$returnData["{$dafunc}noob0"]="{$lastid}";
									break;		// create in tbSubSessions(Play) and attach to tbSessions(Play)
				case 'OE':		// actually -- for a new OE, we're really just sending a choice and seconds -- rest should be inferred -- depending on the reuse OE issue, this may get used more
									list($sid,$ssid,$newtarget,$secs)=explode('_',$attach);		// OE noobs are different as they have multiple attachments and initial data
									$return=mysqli_query($db,"SELECT id, node1 FROM tbPathNodes WHERE node1='{$newtarget}' AND choice='0' LIMIT 1");			$tempy=mysqli_fetch_assoc($return);		$npid=$tempy['id'];		$mega=$tempy['node1'];
									if($secs=='newob'){
										$query="SELECT seconds AS tempybit FROM tb{$subplay}Activity WHERE sessionid='{$sid}' AND inactive IS NULL ORDER BY seconds DESC LIMIT 1";		//$returnData['queries'].="QUERIES - no processing - $query";
										$return=mysqli_query($db,$query);$tempy=mysqli_fetch_assoc($return); $lastselect=$tempy['tempybit'];
										$secs=$lastselect;		if(empty($secs)){$secs=0;}
									}
									$query="INSERT INTO tb{$subplay}Activity (sessionid,ssid,nodepathid,seconds) VALUES ('{$sid}','{$ssid}','{$npid}','{$secs}')";		// create in tbSessionActivity(PlayAct) and attach to tbSubSessions(Play)
										if(!$debug){$return=mysqli_query($db,$query);$lastid=mysqli_insert_id($db);}else{$returnData['queries'].="QUERIES - no processing - $query";$lastid=rand(9000,9999);}
									$query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('newoe','{$attach}','new','{$lastid}','{$_SESSION['pid']}',NOW())";		if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
									$returnData["{$dafunc}noob0"]="{$npid}_{$mega}_{$secs}_{$lastid}";
									break;
									// NOTE --- OE could get rid of the initial sessionid in tbSA (since tbSS carries this now)
			}
		}

		foreach($nukes as $id){
			$action=array();	// clear it out
			switch($dafunc){		// nuke an item from the appropriate table - somethings like subnum might need updating (say one removes subnum 2 from 1,2,3,4 -- now its 1,2*,3*)
				case 'OBS':		$query="UPDATE tbPeopleApp{$subplay}s SET inactive='1' WHERE personid='{_______}' AND sessionid='{$id}' LIMIT 1;";	
											if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
										$query="UPDATE tb{$subplay}s SET inactive='1' WHERE sessionid='{$id}' LIMIT 1;";	$returnData["{$dafunc}nuke{$id}"]=$id;	// MAYBE not this one -- for when 3 people share a session, removing it from one keeps with others
											if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
										$query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('nukeobs','{$id}','nuke','0','{$_SESSION['pid']}',NOW())";		if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
										break;
				case 'OB':			$query="UPDATE tbSub{$subplay}s SET inactive='1' WHERE id='{$id}' LIMIT 1;";			$returnData["{$dafunc}nuke{$id}"]=$id;
											if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
										$query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('nukeob','{$id}','nuke','0','{$_SESSION['pid']}',NOW())";		if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
										break;
				case 'OE':			$query="UPDATE tb{$subplay}Activity SET inactive='1' WHERE id='{$id}' LIMIT 1;";		$returnData["{$dafunc}nuke{$id}"]=$id;
											if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
										$query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('nukeoe','{$id}','nuke','0','{$_SESSION['pid']}',NOW())";		if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
										break;
			}
		}
		
		foreach($swaps as $id=>$swap){				// swaps are really only for redirecting an existing OE to a new PNid
			$action=array();	// clear it out
			switch($dafunc){
				case 'OE':			$query="UPDATE tb{$subplay}Activity SET nodepathid='{$swap}' WHERE id='{$id}' LIMIT 1;";
											if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
										$query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('swapoe','{$id}','swap','{$swap}','{$_SESSION['pid']}',NOW())";		if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}

										$return=mysqli_query($db,"SELECT node1 FROM tbPathNodes WHERE id='{$swap}' LIMIT 1");			$tempy=mysqli_fetch_assoc($return);		$newmega=$tempy['node1'];   if(empty($newmega)){$newmega=0;}
										$returnData["{$dafunc}swap{$id}"]="{$swap}_{$newmega}_0";		//edits to OEs shoulre return 3 bits
										break;
			}
		}

		foreach($updates as $id=>$bits2update){
			foreach($bits2update as $name=>$data){
				$action=array();	// clear it out
				switch($dafunc){		// update bits in existing items -- PREWASHED above
					case 'OBS':		$query="UPDATE tb{$subplay}s SET {$name}='{$data}' WHERE id='{$id}' LIMIT 1;";				$returnData["{$dafunc}update{$id}"][$name]=$data;	// this is repeated as we dont want it set if no query (dafunc=catfood)
												if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
											$query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('updateobs','{$id}','{$name}','{$data}','{$_SESSION['pid']}',NOW())";		if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
											break;
					case 'OB':			$query="UPDATE tbSub{$subplay}s SET {$name}='{$data}' WHERE id='{$id}' LIMIT 1;";			$returnData["{$dafunc}update{$id}"][$name]=$data;
												if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
											$query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('updateob','{$id}','{$name}','{$data}','{$_SESSION['pid']}',NOW())";		if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
											break;
					case 'OE':			$query="UPDATE tb{$subplay}Activity SET {$name}='{$data}' WHERE id='{$id}' LIMIT 1;";	$returnData["{$dafunc}update{$id}"][$name]=$data;	// OE reuse just changes the nodepath of the existing SAid -- nuke and create will need more info
												if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
											$query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('updateoe','{$id}','{$name}','{$data}','{$_SESSION['pid']}',NOW())";		if(!$debug){$return=mysqli_query($db,$query);}else{$returnData['queries'].="QUERIES - no processing - $query";}
											break;
				}
			}
		}



		//return data ===== ex: {'OBnuke1777':1777,'OBSnoob0':'95_1','OEupdate1776':{'seconds':385,'notes':'Some new notes'}}	
		if(empty($returnData)){$returnData['FAIL']=$_POST['updates'];}
		$returnjson=json_encode($returnData);
		if(empty($errors)){echo "A|$returnjson";}else{$tempy=implode('|',$errors); echo "X|$errors";}
	}
}	// dafunc


?>