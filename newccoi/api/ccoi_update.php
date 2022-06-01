<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
$CCOI_requireslogin=true;
include('./ccoi_session.php');
//phpinfo();  exit;
include('./ccoi_db_hookup.php');
$debug=true;

if(!empty($_GET['himark'])){$_POST=$_GET;}

//updateDatabase('OE',{1766:{pn:'128',extra:'4',timer:'177',notes:'kid spoke to kid #4'},1777:{nuke:1}});
// ==== F (function) - D (data) to be altered - always an object
/*
		"nuke" is easy enough - nuke the item associated with dafunc (OE) and the target id
		"noob" will want you to create a new item associated with dafunc BUT it will also need attaching -- so a new OE needs attaching to an OB (OB to OBS, etc)
			noob's data bit could simply have the higher ID to attach to -----  [ OE / noob: 415 ] means  add an OE and attach it to OB 415 (return the new OEid - and maybe the new subnum)

*/

//print_r($_POST);	

if(!empty($_POST['dafunc']) && isset($_POST['play'])){		// we should explicitly require play=0 for research data

	$dafunc=$_POST['dafunc'];

	if($_POST['play']===false){$subplay='Playground';}else{$subplay='Session';}

	if(!empty($_POST['updates'])){
		$pupdates=json_decode($_POST['updates'],true);		//print_r($pupdates);
		foreach($pupdates as $t=>$u){		foreach($u as $n=>$d){			//echo "$t / $u / $d\n";
			switch($n){
				case 'noob':		$noobs[]=$d; break;					// should only be one at a time, but hey...
				case 'nuke':		$nukes[]=$t; break;					// can often have many at once when snipping a branch
				case 'swap':		$swaps[$t]=$d; break;					// should only be one at a time, but hey...
				default:				$updates[$t][$n]=mysqli_real_escape_string($db,$d);				// $updates[1766]['pn']=128 (see above line 11 or so)
		}}}
		
		foreach($noobs as $attach){
			$action=array();	// clear it out
			switch($dafunc){		// create a new row for the table and attach to the appopriate things -- for now, lets not auto-populate, but rather use defaults and let the updater work later
				case 'OBS':	$action['q']="INSERT INTO tb{$subplay}s (pathid,name,createdon) VALUES ('{$attach}','New Observation Set',NOW());";			// === NOTE == TODO == this path should not be set here, but in response to data sent in the request
									$action['q']="INSERT INTO tbPeopleApp{$subplay}s (personid,appid,sessionid) VALUES ('{$_SESSION['pid']}','{$_SESSION['currentlyloadedapp']}','<LASTID>');";
									$returnData["{$dafunc}noob0"]=$attach.'_';	// these two work together below - space before noob needed
							//		$_SESSION['sessions'][$data['appid']][$data['sessionid']]=$data['name'];
									break;		// create in tbSessions(Play) and attach to tbPeopleAppSessions(Play)-- should it also pre-create the first subsession -- and OE?
									
				case 'OB':		//list($attach1,$attach2)=explode('|',$attach);				// sessid and subnum to get correct id
									$action['q']="SELECT subnum AS tempybit FROM tbSub{$subplay}s WHERE sessid='{$attach}' AND inactive IS NULL ORDER BY subnum DESC LIMIT 1;";		// here, we're fetching a bit of data -- we'll store it in $lastselect for later use -- NOTE, if this is the first subnum, then there will be no return data
									$action['q']="INSERT INTO tbSub{$subplay}s (sessid,subnum,name) VALUES ('{$attach}','<LASTSELECT+1>','New Observation');";
									break;		// create in tbSubSessions(Play) and attach to tbSessions(Play)
				case 'OE':		// actually -- for a new OE, we're really just sending a choice and seconds -- rest should be inferred -- depending on the reuse OE issue, this may get used more
									list($sid,$ssid,$newtarget,$secs)=explode('_',$attach);		// OE noobs are different as they have multiple attachments and initial data
									$return=mysqli_query($db,"SELECT id, node1 FROM tbPathNodes WHERE node1='{$newtarget}' AND choice='0' LIMIT 1");			$tempy=mysqli_fetch_assoc($return);		$npid=$tempy['id'];		$mega=$tempy['node1'];
									if($secs=='newob'){
										$action['q']="SELECT seconds AS tempybit FROM tb{$subplay}Activity WHERE sessid='{$sid}' AND inactive IS NULL ORDER BY seconds DESC LIMIT 1;";
										$secs='<LASTSELECT>';
// ============ this tells the LASTSELECT operator below to add the requested bit to the return ===== NOTE: action['N'] is also set, so order is important -- SN / SP / SS / N
										$action['SN']="{$dafunc}noob0";
										$returnData["{$dafunc}noob0"]="{$npid}_{$mega}_";
									}else{
										$returnData["{$dafunc}noob0"]="{$npid}_{$mega}_{$secs}_";
									}
									$action['q']="INSERT INTO tb{$subplay}Activity (sessionid,ssid,nodepathid,seconds) VALUES ('{$sid}','{$ssid}','{$npid}','{$secs}');";		// create in tbSessionActivity(PlayAct) and attach to tbSubSessions(Play)
									break;
									// NOTE --- OE could get rid of the initial sessionid in tbSA (since tbSS carries this now)
			}
			$action['N']="{$dafunc}noob0";
			$actions[]=$action;
		}

// $action['q']=$query;
// $action['S']="{$dafunc}noob0";
// $action['N']="{$dafunc}noob0";
// $action['SP']="{$dafunc}noob0";
// $actions[]=$action;

// ======= logging =======
// tbActivityLog -- only logs edits
// 	id / action / onid / field / details / actby / acton ==== oldvalue / newvalue instead of details?  might be too much
// 	$logs[act][onid][field]=details;
// 	$logs['newobs'][path]['new']=newid;		// if we have the same number of action queries as we have logging ones, then we can use $lastselect, etc on the logs
// 		$logs['newob'][sid]['new']=newid;
// 		$logs['newoe'][ssid]['new']=newid;		// should prob switch newid and path/sid/ssid positions
// 		
// 		$logs['new_xx'][relevantid]=0;		later process can trade 0 for the newid and build log query
// 	
// 	$logs['nukeoe'][id]['nuke']=1;
// 	$logs['swapoe'][id]['pnid']=newid;		// or swap id and newid -- OR both -- tells what happened to each (birth, death)
// 	$logs['updateob'][id]['notes']='these are some new notes';
// 
// 	digging through these logs would be a bit hairy though as there are no real connections here -- what OBS was the newOE part of again? -- what happened to OB45 (and its kids)?
// 		a tool could usebits of the db to recreate the ids we need to hunt for in logs...




		foreach($nukes as $id){
			$action=array();	// clear it out
			switch($dafunc){		// nuke an item from the appropriate table - somethings like subnum might need updating (say one removes subnum 2 from 1,2,3,4 -- now its 1,2*,3*)
				case 'OBS':		$action['q']="UPDATE tbPeopleApp{$subplay}s	 SET inactive='1' WHERE personid='{_______}' AND sessionid='{$id}' LIMIT 1;";	
										$action['q']="UPDATE tb{$subplay}s	 SET inactive='1' WHERE sessionid='{$id}' LIMIT 1;";	$returnData["{$dafunc}nuke{$id}"]=$id;	// MAYBE not this one -- for when 3 people share a session, removing it from one keeps with others
										break;
				case 'OB':			$action['q']="UPDATE tbSub{$subplay}s SET inactive='1' WHERE id='{$id}' LIMIT 1;";			$returnData["{$dafunc}nuke{$id}"]=$id;
										break;
				case 'OE':			$action['q']="UPDATE tb{$subplay}Activity SET inactive='1' WHERE id='{$id}' LIMIT 1;";		$returnData["{$dafunc}nuke{$id}"]=$id;
										break;
			}
			$actions[]=$action;
		}
		
		foreach($swaps as $id=>$swap){				// swaps are really only for redirecting an existing OE to a new PNid
			$action=array();	// clear it out
			switch($dafunc){
				case 'OE':			$action['q']="UPDATE tb{$subplay}Activity SET nodepathid='{$swap}' WHERE id='{$id}' LIMIT 1;";
										$return=mysqli_query($db,"SELECT node1 FROM tbPathNodes WHERE id='{$swap}' LIMIT 1");			$tempy=mysqli_fetch_assoc($return);		$newmega=$tempy['node1'];   if(empty($newmega)){$newmega=0;}
										$returnData["{$dafunc}swap{$id}"]="{$swap}_{$newmega}_0";		//edits to OEs shoulre return 3 bits
										break;
			}
			$actions[]=$action;
		}

		foreach($updates as $id=>$bits2update){
			foreach($bits2update as $name=>$data){
				$action=array();	// clear it out
				switch($dafunc){		// update bits in existing items -- PREWASHED above
					case 'OBS':		$action['q']="UPDATE tb{$subplay}s SET {$name}='{$data}' WHERE id='{$id}' LIMIT 1;";				$returnData["{$dafunc}update{$id}"][$name]=$data;	// this is repeated as we dont want it set if no query (dafunc=catfood)
											break;
					case 'OB':			$action['q']="UPDATE tbSub{$subplay}s SET {$name}='{$data}' WHERE id='{$id}' LIMIT 1;";			$returnData["{$dafunc}update{$id}"][$name]=$data;
											break;
					case 'OE':			$action['q']="UPDATE tb{$subplay}Activity SET {$name}='{$data}' WHERE id='{$id}' LIMIT 1;";	$returnData["{$dafunc}update{$id}"][$name]=$data;	// OE reuse just changes the nodepath of the existing SAid -- nuke and create will need more info
											break;
				}
				$actions[]=$action;
			}
		}
		
		//return data ===== ex: {'OBnuke1777':1777,'OBSnoob0':'95_1','OEupdate1776':{'seconds':385,'notes':'Some new notes'}}	
		foreach($actions as $a){
		
				if(strpos($a['q'],'<LASTID>')){$a['q']=str_replace('<LASTID>',$lastid,$a['q']);}	// need to save the id for the next line on the next loop
				if(strpos($a['q'],'<LASTSELECT>')){$a['q']=str_replace('<LASTSELECT>',$lastselect,$a['q']);}
				if(strpos($a['q'],'<LASTSELECT+1>')){$a['q']=str_replace('<LASTSELECT+1>',$lastselect+1,$a['q']);}
		//	echo '@';
				if(!$debug){
					$return=mysqli_query($db,$a['q']);							$lastselect=$lastid='';			$fart=mysqli_error($db);
					if( !empty($fart) ){
						if(mysqli_affected_rows($db)>0 || mysqli_num_rows($db)>0){
							$type=substr($a['q'],0,1);
							switch($type){
								case 'S':	$tempy=mysqli_fetch_assoc($return); $lastselect=$tempy['tempybit']; break;
								case 'I':		$lastid=mysqli_insert_id($db);  break;
								// nothing for U and we should never see D
							}
						}
					}else{		// got a SQL error somewhere
						$errors[]=$fart;
					}
				}else{ 
					$type=substr($a['q'],0,1);	//	echo '#'.$type;
					switch($type){
						case 'S':	$return=mysqli_query($db,$a['q']);  $tempy=mysqli_fetch_assoc($return);
										if(!empty($tempy['tempybit'])){$lastselect=$tempy['tempybit'];}else{$lastselect=8888;}
										break;		// run the selects when debugging
						case 'I':		$lastid=rand(9900,9999);	
										break;
					}
					$returnData['queries'].="QUERIES - no processing - {$a['q']}\n";		// reuse var to show query data
				}
				
				// secondary data loading
				if(!empty($a['SN'])){if(empty($lastselect)){$lastselect=0;}$returnData[$a['SN']].="{$lastselect}_";}		// number
				if(!empty($a['SP'])){$lastselect+=1;$returnData[$a['SP']].="{$lastselect}_";}										// should always be a number
				if(!empty($a['SS'])){if(empty($lastselect)){$lastselect='';}$returnData[$a['SN']].="{$lastselect}_";}			// string
				if(!empty($a['N'])){$returnData[$a['N']].=$lastid;}		// newly created id
				
		}
		$returnjson=json_encode($returnData);
		if(empty($errors)){echo "A|$returnjson";}else{$tempy=implode('|',$errors); echo "X|$errors";}
	}
}	// dafunc


?>