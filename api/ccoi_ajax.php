<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
$CCOI_requireslogin=true;
include('./ccoi_session.php');
//phpinfo();  exit;
include('./ccoi_dbhookup.php');



if(!empty($_GET['uid']) && is_numeric($_GET['uid'])){
	$uid=$_GET['uid']+0;
	if(is_numeric($uid)){
		$return=mysqli_query($db,"SELECT * FROM tbPeople WHERE id='$uid'");		$persondata=mysqli_fetch_assoc($return);
	
		$return=mysqli_query($db,"SELECT sessionid FROM tbPeopleAppSessions WHERE personid='$uid' AND appid='1' AND inactive IS NULL");		
		while($d=mysqli_fetch_assoc($return)){$sessionids[]=$d['sessionid'];}
		$sidstext=implode(',',$sessionids);
		
 				$return=mysqli_query($db,"SELECT sessionid FROM tbPeopleAppPlaygrounds WHERE personid='$uid' AND appid='1' AND inactive IS NULL");		
 				while($d=mysqli_fetch_assoc($return)){$playids[]=$d['sessionid'];}
 				$playidstext=implode(',',$playids);
	
		$return=mysqli_query($db,"SELECT s.*, v.url FROM tbSessions s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id IN ($sidstext) AND s.inactive IS NULL");				// ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
		while($d=mysqli_fetch_assoc($return)){$sessions[$d['id']]['s']=$d;}
		
 				$return=mysqli_query($db,"SELECT s.*, v.url FROM tbPlaygrounds s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id IN ($playidstext) AND s.inactive IS NULL");		// ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
 				while($d=mysqli_fetch_assoc($return)){$playgrounds[$d['id']]['s']=$d;}		//print_r($playgrounds);

		$return=mysqli_query($db,"SELECT * FROM tbNodes WHERE 1");		
		while($d=mysqli_fetch_assoc($return)){$nodeData[$d['id']]=$d;}

	//	id		sessionid	subsession	sublabel				extra		nodepathid	seconds	notes	pnid	node1	choice	node2	choicegroup		pathtype	nsubgroup
	//	887	31			0					some path label	P4		2					65			NuLL	2		1			3			9			NuLL				5				NuLL
		$return=mysqli_query($db,"SELECT SA.*, PN.id as pnid, PN.node1, PN.choice, PN.node2, PN.choicegroup, PN.pathtype, PN.nsubgroup FROM tbSessionActivity SA, tbPathNodes PN WHERE SA.sessionid IN ($sidstext) AND SA.nodepathid=PN.id AND SA.inactive IS NULL ORDER BY SA.sessionid, SA.seconds");		
		while($d=mysqli_fetch_assoc($return)){
			$sessions[$d['sessionid']]['a'][$d['subsession']][$d['id']]=$d;		//	echo "here we load subsession {$d['subsession']} with {$d['id']}<br />\n";
			$lasttime[$d['sessionid']]=$d['seconds'];		// gets the highest time value
			if($d['sublabel'] != $oldsub){	//	echo "''$oldsub'' is not ''{$d['sublabel']}'' -- {$d['sublabel']} gets {$subcount[$d['sessionid']]}<br />\n";
				$oldsub=$d['sublabel'];$subcount[$d['sessionid']]++;$pathlabels[$d['sublabel']]=($subcount[$d['sessionid']]-1);
			}		// need to subtract one later
		}
		
				$oldsub='';
				$return=mysqli_query($db,"SELECT SA.*, PN.id as pnid, PN.node1, PN.choice, PN.node2, PN.choicegroup, PN.pathtype, PN.nsubgroup FROM tbPlaygroundActivity SA, tbPathNodes PN WHERE SA.sessionid IN ($playidstext) AND SA.nodepathid=PN.id AND SA.inactive IS NULL ORDER BY SA.sessionid, SA.seconds");		
				while($d=mysqli_fetch_assoc($return)){
					$playgrounds[$d['sessionid']]['a'][$d['subsession']][$d['id']]=$d;		//	echo "here we load subsession {$d['subsession']} with {$d['id']}<br />\n";
					$plasttime[$d['sessionid']]=$d['seconds'];		// gets the highest time value
					if($d['sublabel'] != $oldsub){	//	echo "''$oldsub'' is not ''{$d['sublabel']}'' -- {$d['sublabel']} gets {$subcount[$d['sessionid']]}<br />\n";
						$oldsub=$d['sublabel'];$subcount[$d['sessionid']]++;$pathlabels[$d['sublabel']]=($subcount[$d['sessionid']]-1);
					}		// need to subtract one later
				}
		
	
		//echo "<br /><br />\n";
	
		foreach($sessions as $derid=>$data){		// $data is an array of sessions
			$output=array();
			$output['_id']=$data['s']['oldid'];
			$output['id']=$derid;
			$output['path']=$data['s']['pathid'];
			$output['observer']=$persondata['oldname'];
			$output['name']=$data['s']['name'];
	//			$output['updated_at']=$data['s']['_____'];
	//			$output['created_at']=$data['s']['_____'];					"created_at":{"$date":{"$numberLong":"1574444989000"}},
			$output['date']=$data['s']['placetime'];
			$output['minutes']=floor($lasttime[$derid] / 60);
			$output['seconds']=$lasttime[$derid] - ($output['minutes']*60);
		
			$output['pathLabels']=array_keys($pathlabels);
			foreach($data['a'] as $subid=>$dd){				//	echo "here is subid $subid<br />\n";										// ==== $dd is a group of rows of activity -- might only be one
				$supertempy=$tempy=array();
				foreach($dd as $dummy=>$ddd){			//	echo "here is actid {$ddd['id']}<br />\n";								// ==== $ddd is a row of activity
					if( !isset($supertempy['label']) ){ if ( isset($pathlabels[$ddd['sublabel']]) ){$supertempy['label']=$pathlabels[$ddd['sublabel']];}else{$supertempy['label']=-1;}}
						$tempy['subsessionid']=$ddd['subsession'];
//GO2						$temp_sub=$ddd['subsession'];
					$tempy['nodeid']=$ddd['node1'];
					$tempy['node']=$nodeData[$ddd['node1']]['oldid'];
					$tempy['choiceid']=$ddd['choice'];
					$tempy['choice']=$nodeData[$ddd['choice']]['oldid'];
					$tempy['minutes']=floor($ddd['seconds'] / 60);
					$tempy['seconds']=$ddd['seconds'] - ($tempy['minutes']*60);
					$tempy['extra']=$ddd['extra'];
					$tempy['notes']=$ddd['notes'];
					$supertempy['steps'][]=$tempy;
				}
				$output['paths'][]=$supertempy;
//GO2				$output['subsessionid']=$temp_sub;
			}
			$output['prompted']=false;						// ALL sessions are false for this
			$output['sessionNotes']=$data['s']['notes'];
			$output['studentID']=$data['s']['studentid'];
			$output['videoURL']=$data['s']['url'];
			$output['videoID']=$data['s']['videoid'];
			$finaloutput[]=$output;				// ============================================== NEED TO ADJUST THIS FOR S and P ==================================================
		}
		
		foreach($playgrounds as $derid=>$data){		// $data is an array of sessions
			$output=array();
			
			$output['isPlayground']=true;
			
			$output['_id']=$data['s']['oldid'];
			$output['id']=$derid;
			$output['path']=$data['s']['pathid'];
			$output['observer']=$persondata['oldname'];
			$output['name']=$data['s']['name'];
	//			$output['updated_at']=$data['s']['_____'];
	//			$output['created_at']=$data['s']['_____'];					"created_at":{"$date":{"$numberLong":"1574444989000"}},
			$output['date']=$data['s']['placetime'];
			$output['minutes']=floor($lasttime[$derid] / 60);
			$output['seconds']=$plasttime[$derid] - ($output['minutes']*60);
		
			$output['pathLabels']=array_keys($pathlabels);
			foreach($data['a'] as $subid=>$dd){				//	echo "here is subid $subid<br />\n";										// ==== $dd is a group of rows of activity -- might only be one
				$supertempy=$tempy=array();
				foreach($dd as $dummy=>$ddd){			//	echo "here is actid {$ddd['id']}<br />\n";								// ==== $ddd is a row of activity
					if( !isset($supertempy['label']) ){ if ( isset($pathlabels[$ddd['sublabel']]) ){$supertempy['label']=$pathlabels[$ddd['sublabel']];}else{$supertempy['label']=-1;}}
						$tempy['subsessionid']=$ddd['subsession'];
 //GO2						$temp_sub=$ddd['subsession'];
					$tempy['nodeid']=$ddd['node1'];
					$tempy['node']=$nodeData[$ddd['node1']]['oldid'];
					$tempy['choiceid']=$ddd['choice'];
					$tempy['choice']=$nodeData[$ddd['choice']]['oldid'];
					$tempy['minutes']=floor($ddd['seconds'] / 60);
					$tempy['seconds']=$ddd['seconds'] - ($tempy['minutes']*60);
					$tempy['extra']=$ddd['extra'];
					$tempy['notes']=$ddd['notes'];
					$supertempy['steps'][]=$tempy;
				}
				$output['paths'][]=$supertempy;
 //GO2				$output['subsessionid']=$temp_sub;
			}
			$output['prompted']=false;						// ALL sessions are false for this
			$output['sessionNotes']=$data['s']['notes'];
			$output['studentID']=$data['s']['studentid'];
			$output['videoURL']=$data['s']['url'];
			$output['videoID']=$data['s']['videoid'];
			$finaloutput[]=$output;						// ============================================== NEED TO ADJUST THIS FOR S and P ==================================================
		}  // THIS SHOULD BE A CLONE OF THE ABOVE
		
		
		
		
		
	
		$final=json_encode($finaloutput);			//echo "<br /><br />\n";
		echo $final;
	}
}

if(!empty($_GET['pid']) && is_numeric($_GET['pid'])){
		$pid=$_GET['pid']+0;
		if(is_numeric($pid)){
			$query="SELECT p.startpnid, n.oldid FROM tbPaths p, tbNodes n WHERE p.id='$pid' AND p.startpnid=n.id";		$return=mysqli_query($db,$query);		$d=mysqli_fetch_assoc($return);
			$firstNode=$d['startpnid'];

			$ajaxOut['version']='20181705';
			$ajaxOut['firstNodeID']=$d['oldid'];			// need to populate this right

			$return=mysqli_query($db,"SELECT * FROM tbNodeGroups WHERE pathid='$pid'");
			while ($d=mysqli_fetch_assoc($return)){
				$nodeGroups[$d['id']]=$d;
				if(empty($d['humanname'])){$d['humanname']='';}
				$tempy=array();
					$tempy['machine_name']=$d['name'];
					$tempy['label']=$d['humanname'];
					if($d['id']==8){$tempy['labelArray']=array("Non-computing","Interaction");}	// horrible hack for one off shit in old json - dont want this shit in db
					$tempy['labelPosition']=$d['labelpos'];
					if(!empty($d['parent'])){$tempy['parent']=$d['parent'];}
					$tempy['fill']=$d['fill'];
					if(!empty($d['hide'])){$tempy['hide_from_graph']=$d['hide'];}			// there is also labelArray which only happens with #8 ["Non-computing", "Interaction"]
					if(!empty($d['chartfill'])){$tempy['chart_fill']=$d['chartfill'];}
				$ajaxOut['nodeGroups'][]=$tempy;
			}

			$return=mysqli_query($db,"SELECT id, name FROM tbChoiceGroups WHERE pathid='$pid'");
			while ($d=mysqli_fetch_assoc($return)){$choiceGroups[$d['id']]=$d['name'];}

			$globalcount=0;
			$finaloutput=array();

					fetchNodes($firstNode,false);
					$ajaxOut['nodes']=$finaloutput;

			$jj=json_encode($ajaxOut);				$_SESSION['currentlyloadedpath']=$pid;		//$_SESSION['sessions'][$data['appid']][$data['sessionid']]=$data['name'];
			print_r($jj);
		}
}

if(!empty($_GET['tpid']) && is_numeric($_GET['tpid'])){
		$pid=$_GET['tpid']+0;
		if(is_numeric($pid)){
			$query="SELECT p.startpnid, p.name, n.oldid FROM tbPaths p, tbNodes n WHERE p.id='$pid' AND p.startpnid=n.id";		$return=mysqli_query($db,$query);		$d=mysqli_fetch_assoc($return);
			$firstNode=$d['startpnid'];
			$ajaxOut['path']=$d['name'];

			$return=mysqli_query($db,"SELECT * FROM tbNodeGroups WHERE pathid='$pid'");
			while ($d=mysqli_fetch_assoc($return)){
				$nodeGroups[$d['id']]=$d;
				if(empty($d['humanname'])){$d['humanname']='';}
				$tempy=array();
				//	$tempy['machine_name']=$d['name'];
					$tempy['t_id']=$d['id'];
					$tempy['t_label']=$d['humanname'];
					if($d['id']==8){$tempy['labelArray']=array("Non-computing","Interaction");}	// horrible hack for one off shit in old json - dont want this shit in db
					if(!empty($d['parent'])){$tempy['t_parent']=$d['parent'];}
				$ajaxOut['nodeGroups'][]=$tempy;
			}

			$return=mysqli_query($db,"SELECT id, name FROM tbChoiceGroups WHERE pathid='$pid'");
			while ($d=mysqli_fetch_assoc($return)){
				$tempy=array();
				$tempy['t_id']=$d['id'];
				$tempy['t_name']=$d['name'];
				$ajaxOut['choiceGroups'][]=$tempy;
			}

			$linkedmegas=array();
			$globalcount=0;			// prob dont need this for trailblazer
			$finaloutput=array();
			$alreadyfetched=array();

					fetchNodes($firstNode,true);
					$ajaxOut['nodes']=$finaloutput;
			
			// === here we're going back looking for MEGAs attached to the path, but with no other connections from the firstNode -- exists, but unlinked
			$linkedmegastext=implode(',',$linkedmegas);
			$query="SELECT node1 FROM tbPathNodes WHERE pathid='$pid' AND choice='0' AND node1 NOT IN ($linkedmegastext) AND inactive IS NULL";		//echo $query;
			$return=mysqli_query($db,$query);
			$finaloutput=array();		// we're re-using this variable -- referenced in the function GLOBAL
			while ($d=mysqli_fetch_assoc($return)){			//echo "--{$d['node1']}-- ";
				if( !$alreadyfetched[$d['node1']] ){		// if there is a loop in the unlinked data, then it will process again... sigh.
					fetchNodes($d['node1'],true);
	//				$reallyfinals[]=$finaloutput;		// each "finaloutput" will be small as the unlinked items wont usually have sub-node choices (could though) -- tiny trees
				}
			}
			if(!empty($finaloutput)){
				$ajaxOut['unlinkedmegas']=$finaloutput;			//echo '<pre>';	print_r($finaloutput);
			}

			if($_GET['mark']==1){
				echo '<pre>';  print_r($ajaxOut);
			}else{
				$jj=json_encode($ajaxOut);
				echo "$jj";
			}
		}
}

if(!empty($_GET['vuid']) && is_numeric($_GET['vuid'])){
	$uid=$_GET['vuid']+0;
	if(is_numeric($uid)){
		$return=mysqli_query($db,"SELECT sessionid FROM tbPeopleAppSessions WHERE personid='$uid' AND appid='1'");		
		while($d=mysqli_fetch_assoc($return)){$sessionids[]=$d['sessionid'];}
		$sidstext=implode(',',$sessionids);
	
		$return=mysqli_query($db,"SELECT s.id, v.* FROM tbSessions s, tbVideos v WHERE s.id IN ($sidstext) and s.videoid=v.id order by v.name");		
		while($d=mysqli_fetch_assoc($return)){$vids[$d['name']]=$d['url'];}

		$final=json_encode($vids);			//echo "<br /><br />\n";
		echo $final;
	}
}

if(!empty($_GET['vids4app']) && is_numeric($_GET['vids4app'])){
	$aid=$_GET['vids4app']+0;
	if(is_numeric($aid)){
		$return=mysqli_query($db,"SELECT v.* FROM tbVideos v WHERE v.appid='1' order by v.name");		
		while($d=mysqli_fetch_assoc($return)){$vids[$d['name']]=$d['id'];}
	
		$final=json_encode($vids);			//echo "<br /><br />\n";
		echo $final;
	}
}

function fetchNodes($id,$trail){		// only called by PID and TPID
			$GLOBALS['alreadyfetched'][$id]=true;
			$nodestofetch[]=$id;
			$return=mysqli_query($GLOBALS['db'],"SELECT * FROM tbPathNodes WHERE node1='$id' AND inactive IS NULL order by choiceorder");				// =================== PRIMARY QUERY ===============
			while ($d=mysqli_fetch_assoc($return)){
				if(!empty($d['choice'])){$nodestofetch[]=$d['choice'];}else{$GLOBALS['linkedmegas'][]=$d['node1'];}		// if choice=zero than this is a meganode definer
				if(!empty($d['node2'])){$nodestofetch[]=$d['node2'];}				// we have to check for this because when a path terminates, mode2 is null
				if(!empty($d['choicegroup'])){
					$should_group_choices=true;
					$bgns[$d['choicegroup']]=$GLOBALS['choiceGroups'][$d['choicegroup']];
				}
			}
			$nodestofetchtext=implode(',',$nodestofetch);
	
			$query="SELECT * FROM tbNodes WHERE id IN ($nodestofetchtext)";		//echo "$query\n";
			$return2=mysqli_query($GLOBALS['db'],$query);
			while ($d=mysqli_fetch_assoc($return2)){$nodeData[$d['id']]=$d;}			/// ===================== HERE is where nodeData is set =====================
	
			$output['id']=$GLOBALS['globalcount'];					// for some reason this is not the id -- I suspect it means nothing in the end, but a 'count' of array elements
			$output['node_id']=$id;
			$output['nodeid']=$nodeData[$id]['oldid'];
			$output['title']=$nodeData[$id]['title'];
			// ===== trailblazer stuff
			if($trail){
				$output['t_megaid']=$id;
				$output['ng']=$nodeData[$id]['nodegroup'];
				$GLOBALS['processedNodes'][$id]=1;
				if(!empty($nodeData[$id]['code'])){$output['t_codegroup']=$nodeData[$id]['code'];}else{$output['t_codegroup']='';}		// all megas SHOULD have a code group (1-0, 4-0, etc), but might not
			}			
			if(!empty($nodeData[$id]['nodegroup'])){
				$output['groups'][]=$GLOBALS['nodeGroups'][$nodeData[$id]['nodegroup']]['name'];
				$output['group_hex'][]=$GLOBALS['nodeGroups'][$nodeData[$id]['nodegroup']]['grouphex'];
//GO			$output['group_id'][]=$GLOBALS['nodeGroups'][$nodeData[$id]['nodegroup']]['id'];
			}
			if(!empty($nodeData[$id]['sametime'])){$output['assumes_previous_timestamp']=$nodeData[$id]['sametime'];}
			if($should_group_choices){
				$output['should_group_choices']=true;
			}else{
				$output['should_group_choices']=false;
			}
			
			//{"description":"Adult offers support to student who was working collaboratively on a problem or topic","next":"73ae21","branch_id":"f77c01"},

			$nextvictims=array();
			mysqli_data_seek($return,0);
			while ($d=mysqli_fetch_assoc($return)){		// === remember this is the pathNodes query ====
				if(!empty($d['choice'])){		// a loose mega with no choices can exist -- we also need to filter out the defining row that attaches the mega to the path (1,megaid,0,0,0)
						if(empty($d['choicegroup'])){$d['choicegroup']=0;}
						if(empty($d['choicegroup']) && $should_group_choices){
						//	echo "ERROR: either we group choices or we dont -- make up your mind\n";			// NOTE ===== V1 - if choices are grouped, ALL choices must be grouped -- prob let this go
						}

						if($d['choicegroup'] != $oldchoicegroup){
							$oldchoicegroup=$d['choicegroup'];
							if(!empty($supertempy)){$branches[]=$supertempy;}		// catches the first 'null'
							$supertempy=array();
						}
		
						$tempy=array();
							$tempy['description']=$nodeData[$d['choice']]['title'];
							if(!empty($nodeData[$d['choice']]['aside'])){$tempy['aside']=$nodeData[$d['choice']]['aside'];}
							if(!empty($nodeData[$d['node2']]['oldid'])){$tempy['next']=$nodeData[$d['node2']]['oldid'];}		// terminators dont explicitly terminate, just no next...
							if(!empty($nodeData[$d['node2']]['id'])){$tempy['next_id']=$d['node2'];}

							if(!empty($nodeData[$d['choice']]['extra'])){$tempy['extra']=$nodeData[$d['choice']]['extra'];}
							if(!empty($d['pathtype'])){$tempy['path_type']=$GLOBALS['nodeGroups'][$d['pathtype']]['humanname'];}

							$tempy['branch_id']=$nodeData[$d['choice']]['oldid'];
							$tempy['branch_new_id']=$d['choice'];
							// for some reason, branch id is listed between these...
							if(!empty($d['nsubgroup'])){$tempy['node_sub_group']=$GLOBALS['nodeGroups'][$d['nsubgroup']]['name'];}
							
							// ===== trailblazer stuff
							if($trail){
								$tempy['t_parentid']=$d['node1'];
								$tempy['t_thisid']=$d['choice'];
								if(!empty($d['node2'])){$tempy['t_nextid']=$d['node2'];}else{$tempy['t_nextid']='X';}
								$tempy['t_choicegroup']=$d['choicegroup'];
								if(!empty($nodeData[$d['choice']]['inactive'])){$tempy['t_inactive']=true;}		// for now, we should let TB know if an inactive node is still linked somewhere
								if(!empty($nodeData[$d['node2']]['inactive'])){$tempy['t_target_inactive']=true;}		// for now, we should let TB know if an inactive node is still linked somewhere
					//			$GLOBALS['processedNodes'][$d['choice']]=1;			// dont really ned this one -- a subnode cant exist without a parent mega
								if(!empty($nodeData[$d['choice']]['code'])){$tempy['t_code']=$nodeData[$d['choice']]['code'];}else{$tempy['t_code']='';}		// all choices SHOULD have a code, but might not
							}
		
						$supertempy[]=$tempy;
						if(!empty($d['node2'])){$nextvictims[$d['node2']]=1;}
				} // end valid choice detector -- if zero, then is an empty mega
			}		// ==== end db while ====
	
			if(!empty($supertempy)){$branches[]=$supertempy;}			$supertempy=array();			// grab the last block
	
			if(!empty($branches)){$output['branches']=$branches;}
			if($should_group_choices){ $output['branch_group_names']=array_values($bgns); }
			$GLOBALS['globalcount']++;

			$GLOBALS['finaloutput'][]=$output;
			$GLOBALS['alreadyfetched'][$id]=true;

			// go fetch the linked nodes
			if(!empty($nextvictims)){foreach($nextvictims as $node=>$dummy){if( !$GLOBALS['alreadyfetched'][$node] ){fetchNodes($node,$trail);}}}
}		// ==== end function ====  // only called by PID and TPID

// more below
















































// =======================================================================================================================
//			New versions of uid and pid fetching for the new Observe Tool -- eventually switch these with the originals
// =======================================================================================================================

if(!empty($_GET['ping'])){echo 'pong';}

if(!empty($_GET['vids4app2']) && is_numeric($_GET['vids4app2'])){
	$id=$_GET['vids4app2']+0;
	if(is_numeric($id)){
		$return=mysqli_query($db,"SELECT v.* FROM tbVideos v WHERE v.appid='{$id}' AND inactive IS NULL ORDER BY v.name");		
		while($d=mysqli_fetch_assoc($return)){$vids[$d['id']]=$d['url'];}
	
		$final=json_encode($vids);			//echo "<br /><br />\n";
		echo $final;
	}
}

if(!empty($_GET['paths4app2']) && is_numeric($_GET['paths4app2'])){
	$id=$_GET['paths4app2']+0;
	if(is_numeric($id)){
		$return=mysqli_query($db,"SELECT p.* FROM tbPaths p, tbAppPaths ap WHERE ap.appid='{$id}' AND ap.pathid=p.id AND p.invalid IS NULL and ap.invalid IS NULL order by p.name");		
		while($d=mysqli_fetch_assoc($return)){$derpaths[$d['id']]=$d;}
		$final=json_encode($derpaths);			//echo "<br /><br />\n";
		echo $final;
	}
}

if(!empty($_GET['uid2']) && is_numeric($_GET['uid2'])){
	$uid=$_GET['uid2']+0;
	if(is_numeric($uid)){
		$return=mysqli_query($db,"SELECT * FROM tbPeople WHERE id='$uid'");		$persondata=mysqli_fetch_assoc($return);
	
		$return=mysqli_query($db,"SELECT sessionid FROM tbPeopleAppSessions WHERE personid='$uid' AND appid='1' AND inactive IS NULL");		
		while($d=mysqli_fetch_assoc($return)){$sessionids[]=$d['sessionid'];}
		$sidstext=implode(',',$sessionids);
		
 				$return=mysqli_query($db,"SELECT sessionid FROM tbPeopleAppPlaygrounds WHERE personid='$uid' AND appid='1' AND inactive IS NULL");		
 				while($d=mysqli_fetch_assoc($return)){$playids[]=$d['sessionid'];}
 				$playidstext=implode(',',$playids);
	
		$return=mysqli_query($db,"SELECT s.*, v.url FROM tbSessions s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id IN ($sidstext) AND s.inactive IS NULL");				// ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
		while($d=mysqli_fetch_assoc($return)){$sessions[$d['id']]['s']=$d;}
		
 				$return=mysqli_query($db,"SELECT s.*, v.url FROM tbPlaygrounds s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id IN ($playidstext) AND s.inactive IS NULL");		// ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
 				while($d=mysqli_fetch_assoc($return)){$playgrounds[$d['id']]['s']=$d;}		//print_r($playgrounds);

		$return=mysqli_query($db,"SELECT * FROM tbNodes WHERE 1");		
		while($d=mysqli_fetch_assoc($return)){$nodeData[$d['id']]=$d;}

	//	id		sessionid	subsession	sublabel				extra		nodepathid	seconds	notes	pnid	node1	choice	node2	choicegroup		pathtype	nsubgroup
	//	887	31			0					some path label	P4		2					65			NuLL	2		1			3			9			NuLL				5				NuLL
		$return=mysqli_query($db,"SELECT SA.*, SS.id as ssid, SS.subnum, SS.name as ssname, SS.notes as ssnotes, PN.id as pnid, PN.node1, PN.choice, PN.node2, PN.choicegroup, PN.pathtype, PN.nsubgroup FROM tbSessionActivity SA, tbPathNodes PN, tbSubSessions SS WHERE SA.sessionid IN ($sidstext) AND SA.nodepathid=PN.id AND SA.ssid=SS.id ORDER BY SA.sessionid, SA.seconds");		
		while($d=mysqli_fetch_assoc($return)){
			$sessions[$d['sessionid']]['a'][$d['ssid']][$d['id']]=$d;		//	echo "here we load subsession {$d['subsession']} with {$d['id']}<br />\n";
			$lasttime[$d['sessionid']]=$d['seconds'];		// gets the highest time value
// 			if($d['sublabel'] != $oldsub){	//	echo "''$oldsub'' is not ''{$d['sublabel']}'' -- {$d['sublabel']} gets {$subcount[$d['sessionid']]}<br />\n";
// 				$oldsub=$d['sublabel'];		$subcount[$d['sessionid']]++;
// 				$pathlabels[$d['sublabel']]=($subcount[$d['sessionid']]-1);
// 			}		// need to subtract one later
		}
		
				$oldsub='';
				$return=mysqli_query($db,"SELECT SA.*, SS.id as ssid, SS.subnum, SS.name as ssname, SS.notes as ssnotes, PN.id as pnid, PN.node1, PN.choice, PN.node2, PN.choicegroup, PN.pathtype, PN.nsubgroup FROM tbPlaygroundActivity SA, tbPathNodes PN, tbSubPlaygrounds SS WHERE SA.sessionid IN ($playidstext) AND SA.nodepathid=PN.id AND SA.ssid=SS.id ORDER BY SA.sessionid, SA.seconds");		
				while($d=mysqli_fetch_assoc($return)){
					$playgrounds[$d['sessionid']]['a'][$d['ssid']][$d['id']]=$d;		//	echo "here we load subsession {$d['subsession']} with {$d['id']}<br />\n";
					$plasttime[$d['sessionid']]=$d['seconds'];		// gets the highest time value
// 					if($d['sublabel'] != $oldsub){	//	echo "''$oldsub'' is not ''{$d['sublabel']}'' -- {$d['sublabel']} gets {$subcount[$d['sessionid']]}<br />\n";
// 						$oldsub=$d['sublabel'];$subcount[$d['sessionid']]++;$pathlabels[$d['sublabel']]=($subcount[$d['sessionid']]-1);
// 					}		// need to subtract one later
				}
		
		foreach($sessions as $derid=>$data){		// $data is an array of sessions
			$output=array();
			$output['isPlayground']=0;
			$output['path']=$data['s']['pathid'];
			$output['observer']=$uid;
			if(!empty($data['s']['name'])){$output['name']=$data['s']['name'];}else{$output['name']="ObSet Title #{$derid}";}
			$output['placetime']=$data['s']['placetime'];
			if(!empty($data['s']['notes'])){$output['notes']=$data['s']['notes'];}
			$output['studentid']=$data['s']['studentid'];
			$output['videoURL']=$data['s']['url'];
			$output['videoID']=$data['s']['videoid'];
			$output['observations']=array();		// if someone has an old session with =NO= data, then wew still need to create an empty 'obs' area or the JS will fart
			foreach($data['a'] as $subid=>$dd){				//	echo "here is subid $subid<br />\n";										// ==== $dd is a group of rows of activity -- might only be one
				$supertempy=array();		//$defsublabels[$subid]=0;
				foreach($dd as $dummy=>$ddd){			//	echo "here is actid {$ddd['id']}<br />\n";								// ==== $ddd is a row of activity
					$tempy=array();		//$defsublabels[$subid]++;
//					if( !isset($supertempy['label']) ){ if ( isset($pathlabels[$ddd['sublabel']]) ){$supertempy['label']=$pathlabels[$ddd['sublabel']];}else{$supertempy['label']=-1;}}
			//		if(!empty($ddd['ssname'])){$tempy['sublabel']=$ddd['ssname'];}//else{$tempy['sublabel']='Observation Node '.$defsublabels[$subid];}
					$tempy['SAid']=$ddd['id'];
					$tempy['PNid']=$ddd['pnid'];
					$tempy['megaid']=$ddd['node1'];
					$tempy['choiceid']=$ddd['choice'];
					$tempy['seconds']=$ddd['seconds'];
					if(!empty($ddd['extra'])){$tempy['extra']=$ddd['extra'];}
					if(!empty($ddd['notes'])){$tempy['notes']=$ddd['notes'];}
					$supertempy['ObResp'][]=$tempy;
				}
				$output['observations'][$subid]=$supertempy;
				$output['observations'][$subid]['ssid']=$ddd['ssid'];
				if(empty($ddd['ssname'])){$ddd['ssname']="Observation Title #{$ddd['ssid']}";}
				$output['observations'][$subid]['name']=$ddd['ssname'];
				if(!empty($ddd['ssnotes'])){$output['observations'][$subid]['notes']=$ddd['ssnotes'];}
			}

			$finaloutput[$derid]=$output;				// ============================================== NEED TO ADJUST THIS FOR S and P ==================================================
		}
		
		foreach($playgrounds as $derid=>$data){		// $data is an array of sessions
			$output=array();
			$output['isPlayground']=1;
			$output['id']=$derid;
			$output['path']=$data['s']['pathid'];
			$output['observer']=$persondata['oldname'];
			if(!empty($data['s']['name'])){$output['name']=$data['s']['name'];}else{$output['name']="ObSet Title #{$derid}";}
			$output['placetime']=$data['s']['placetime'];
			if(!empty($data['s']['notes'])){$output['notes']=$data['s']['notes'];}
			$output['studentID']=$data['s']['studentid'];
			$output['videoURL']=$data['s']['url'];
			$output['videoID']=$data['s']['videoid'];
			$output['observations']=array();		// if someone has an old session with =NO= data, then wew still need to create an empty 'obs' area or the JS will fart
			foreach($data['a'] as $subid=>$dd){				//	echo "here is subid $subid<br />\n";										// ==== $dd is a group of rows of activity -- might only be one
				$supertempy=$tempy=array();
				foreach($dd as $dummy=>$ddd){			//	echo "here is actid {$ddd['id']}<br />\n";								// ==== $ddd is a row of activity
			//		if( !isset($supertempy['label']) ){ if ( isset($pathlabels[$ddd['sublabel']]) ){$supertempy['label']=$pathlabels[$ddd['sublabel']];}else{$supertempy['label']=-1;}}
					$tempy['SAid']=$ddd['id'];
					$tempy['PNid']=$ddd['pnid'];
					$tempy['megaid']=$ddd['node1'];
					$tempy['choiceid']=$ddd['choice'];
					$tempy['seconds']=$ddd['seconds'];
					if(!empty($ddd['extra'])){$tempy['extra']=$ddd['extra'];}
					if(!empty($ddd['notes'])){$tempy['notes']=$ddd['notes'];}
					$supertempy['ObResp'][]=$tempy;
				}
				$output['observations'][$subid]=$supertempy;
				$output['observations'][$subid]['ssid']=$ddd['ssid'];
				if(empty($ddd['ssname'])){$ddd['ssname']="Observation Title #{$ddd['ssid']}";}
				$output['observations'][$subid]['name']=$ddd['ssname'];
				if(!empty($ddd['ssnotes'])){$output['observations'][$subid]['notes']=$ddd['ssnotes'];}
			}
			$finaloutput[$derid]=$output;						// ============================================== NEED TO ADJUST THIS FOR S and P ==================================================
		}  // THIS SHOULD BE A CLONE OF THE ABOVE
		
		
		if($_GET['mark']==1){
			echo '<pre>';  print_r($finaloutput);	
		}else{
			$final=json_encode($finaloutput);			echo $final;
		}
	}
}

if(!empty($_GET['pid2']) && is_numeric($_GET['pid2'])){
		$pid=$_GET['pid2']+0;
		if(is_numeric($pid)){
			$query="SELECT p.startpnid FROM tbPaths p WHERE p.id='$pid'";		$return=mysqli_query($db,$query);		$d=mysqli_fetch_assoc($return);
			$firstNode=$d['startpnid'];

			$return=mysqli_query($db,"SELECT id, name FROM tbChoiceGroups WHERE pathid='$pid'");
			while ($d=mysqli_fetch_assoc($return)){$choiceGroups[$d['id']]=$d['name'];}
			
			$return=mysqli_query($GLOBALS['db'],"SELECT * FROM tbPathNodes WHERE pathid='$pid' AND inactive IS NULL order by node1, choicegroup, choiceorder");				// =================== PRIMARY QUERY ===============
			while ($d=mysqli_fetch_assoc($return)){
				if($d['node1']>0){$nodestofetch[$d['node1']]=1;}
				if($d['choice']>0){$nodestofetch[$d['choice']]=1;}
				if($d['node2']>0){$nodestofetch[$d['node2']]=1;}
			}
			
			$nodestofetchtext=implode(',',array_keys($nodestofetch));
			$query="SELECT * FROM tbNodes WHERE id IN ($nodestofetchtext)";		//echo "$query\n";
			$return2=mysqli_query($GLOBALS['db'],$query);
			while ($d=mysqli_fetch_assoc($return2)){$nodeData[$d['id']]=$d;}
			
			
			mysqli_data_seek($return,0);
			while ($d=mysqli_fetch_assoc($return)){		// === remember this is the pathNodes query ====
				if(empty($d['choicegroup'])){$d['choicegroup']=0;}

				// here we're detailing a choice possibility and sorting it under its mega - incuding the mega (cg=0, co=0, node2=0)
				$tempy=array();
				if(!empty($d['choice'])){
					$tempy['pnid']=$d['id']; // this is the tbPN id - used for highlighting selected items later
					$tempy['choiceid']=$d['choice'];
					$tempy['title']=$nodeData[$d['choice']]['title'];
					$tempy['code']=$nodeData[$d['choice']]['code'];
					if(!empty($nodeData[$d['choice']]['codedesc'])){$tempy['codedesc']=$nodeData[$d['choice']]['codedesc'];}else{$tempy['codedesc']='There is no decription for this choice';}
				}else{
					$tempy['title']=$nodeData[$d['node1']]['title'];		// if this is a mega, show its title info here
					$tempy['codegroup']=$nodeData[$d['node1']]['code'];		// megas have codes now (1-0, 4-0, etc) to indicate the grouping it should use for dropdowns
				}
				if(!empty($d['node2'])){$tempy['target']=$d['node2'];}else{	
					if(!empty($d['choiceorder'])){
						$tempy['target']=$GLOBALS['firstNode'];			// no target?  send em back to first node (implicit termination)
					}else{}		// this is a mega - no target
				}
				if(!empty($nodeData[$d['choice']]['aside'])){$tempy['aside']=$nodeData[$d['choice']]['aside'];}
				if(!empty($nodeData[$d['choice']]['extra'])){$tempy['extra']=$nodeData[$d['choice']]['extra'];}
//				if(!empty($d['pathtype'])){$tempy['path_type']=$GLOBALS['nodeGroups'][$d['pathtype']]['humanname'];}

				$supertempy[$d['node1']][$d['choicegroup']][$d['choiceorder']]=$tempy;
			}		// ==== end db while ====

			
			if($_GET['mark']==1){
				echo '<pre>';  print_r($choiceGroups);  print_r($supertempy);
			}else{
				$jj=json_encode($supertempy);
				$cG=json_encode($choiceGroups);
				echo "$cG|X|$jj|X|$firstNode";
			}
		}
}

if(!empty($_GET['sid2']) && is_numeric($_GET['sid2'])){
	$sid=$_GET['sid2']+0;
	if(is_numeric($sid)){	
	
		$return=mysqli_query($db,"SELECT s.*, v.url FROM tbSessions s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id='$sid'");				// ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
		while($d=mysqli_fetch_assoc($return)){$sessions[$d['id']]['s']=$d;}
		
 	//			$return=mysqli_query($db,"SELECT s.*, v.url FROM tbPlaygrounds s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id IN ($playidstext) AND s.inactive IS NULL");		// ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
 	//			while($d=mysqli_fetch_assoc($return)){$playgrounds[$d['id']]['s']=$d;}		//print_r($playgrounds);


// =============== make this only grab nodes in PATH X ================

		$return=mysqli_query($db,"SELECT * FROM tbNodes WHERE 1");		
		while($d=mysqli_fetch_assoc($return)){$nodeData[$d['id']]=$d;}



		$return=mysqli_query($db,"SELECT * FROM tbChoiceGroups WHERE 1");		
		while($d=mysqli_fetch_assoc($return)){$cgData[$d['id']]=$d;}
		
		$return=mysqli_query($db,"SELECT * FROM tbNodeGroups WHERE 1");		
		while($d=mysqli_fetch_assoc($return)){$ngData[$d['id']]=$d;}
		
		$return=mysqli_query($db,"SELECT id, nodegroup FROM tbNodes WHERE nodegroup is not NULL");		
		while($d=mysqli_fetch_assoc($return)){$ngMegas[$d['id']]=$d['nodegroup'];}

	//	id		sessionid	subsession	sublabel				extra		nodepathid	seconds	notes	pnid	node1	choice	node2	choicegroup		pathtype	nsubgroup
	//	887	31			0					some path label	P4		2					65			NuLL	2		1			3			9			NuLL				5				NuLL
		$return=mysqli_query($db,"SELECT SA.*, SS.id as ssid, SS.subnum, SS.name as ssname, SS.notes as ssnotes, PN.id as pnid, PN.node1, PN.choice, PN.node2, PN.choicegroup, PN.pathtype, PN.nsubgroup FROM tbSessionActivity SA, tbPathNodes PN, tbSubSessions SS WHERE SA.sessionid='$sid' AND SA.nodepathid=PN.id AND SA.ssid=SS.id ORDER BY SA.sessionid, SA.seconds");		
		while($d=mysqli_fetch_assoc($return)){
			$sessions[$d['sessionid']]['a'][$d['ssid']][$d['id']]=$d;		//	echo "here we load subsession {$d['subsession']} with {$d['id']}<br />\n";
			$lasttime[$d['sessionid']]=$d['seconds'];		// gets the highest time value
// 			if($d['sublabel'] != $oldsub){	//	echo "''$oldsub'' is not ''{$d['sublabel']}'' -- {$d['sublabel']} gets {$subcount[$d['sessionid']]}<br />\n";
// 				$oldsub=$d['sublabel'];		$subcount[$d['sessionid']]++;
// 				$pathlabels[$d['sublabel']]=($subcount[$d['sessionid']]-1);
// 			}		// need to subtract one later
		}
		
//				$oldsub='';
//				$return=mysqli_query($db,"SELECT SA.*, SS.id as ssid, SS.subnum, SS.name as ssname, SS.notes as ssnotes, PN.id as pnid, PN.node1, PN.choice, PN.node2, PN.choicegroup, PN.pathtype, PN.nsubgroup FROM tbPlaygroundActivity SA, tbPathNodes PN, tbSubPlaygrounds SS WHERE SA.sessionid IN ($playidstext) AND SA.nodepathid=PN.id AND SA.ssid=SS.id ORDER BY SA.sessionid, SA.seconds");		
//				while($d=mysqli_fetch_assoc($return)){
//					$playgrounds[$d['sessionid']]['a'][$d['ssid']][$d['id']]=$d;		//	echo "here we load subsession {$d['subsession']} with {$d['id']}<br />\n";
//					$plasttime[$d['sessionid']]=$d['seconds'];		// gets the highest time value
	// 					if($d['sublabel'] != $oldsub){	//	echo "''$oldsub'' is not ''{$d['sublabel']}'' -- {$d['sublabel']} gets {$subcount[$d['sessionid']]}<br />\n";
	// 						$oldsub=$d['sublabel'];$subcount[$d['sessionid']]++;$pathlabels[$d['sublabel']]=($subcount[$d['sessionid']]-1);
	// 					}		// need to subtract one later
//				}
		
		foreach($sessions as $derid=>$data){		// only one session in this version --- $data is an array of sessions
			$output=array();
		//	$output['isPlayground']=0;
			$output['path']=$data['s']['pathid'];
			$output['observer']=$uid;
			if(!empty($data['s']['name'])){$output['name']=$data['s']['name'];}else{$output['name']="ObSet Title #{$derid}";}
			$output['placetime']=$data['s']['placetime'];
	//		if(!empty($data['s']['notes'])){$output['notes']=$data['s']['notes'];}
			$output['studentid']=$data['s']['studentid'];
			$output['videoURL']=$data['s']['url'];
			$output['observations']=array();		// if someone has an old session with =NO= data, then wew still need to create an empty 'obs' area or the JS will fart
			$notsubid=0;
			foreach($data['a'] as $subid=>$dd){				//	echo "here is subid $subid<br />\n";										// ==== $dd is a group of rows of activity -- might only be one
				$supertempy=array();		//$defsublabels[$subid]=0;
				foreach($dd as $dummy=>$ddd){			//	echo "here is actid {$ddd['id']}<br />\n";								// ==== $ddd is a row of activity
					$tempy=array();		//$defsublabels[$subid]++;
//					if( !isset($supertempy['label']) ){ if ( isset($pathlabels[$ddd['sublabel']]) ){$supertempy['label']=$pathlabels[$ddd['sublabel']];}else{$supertempy['label']=-1;}}
			//		if(!empty($ddd['ssname'])){$tempy['sublabel']=$ddd['ssname'];}//else{$tempy['sublabel']='Observation Node '.$defsublabels[$subid];}
					$tempy['pnid']=$ddd['pnid'];
					$tempy['megaid']=$ddd['node1'];
						$tempy['ng']=$ngData[$ngMegas[$ddd['node1']]]['humanname'];
					$tempy['choiceid']=$ddd['choice'];
					if(!empty($ddd['choicegroup'])){$tempy['choicegroup']=$cgData[$ddd['choicegroup']]['name'];}
					$tempy['seconds']=$ddd['seconds'];
					$supertempy['ObResp'][]=$tempy;
				}
				$output['observations'][$notsubid]=$supertempy;
				if(empty($ddd['ssname'])){$ddd['ssname']="Observation Title #{$ddd['ssid']}";}
				$output['observations'][$notsubid]['name']=$ddd['ssname'];
				$notsubid++;
//				if(!empty($ddd['ssnotes'])){$output['observations'][$subid]['notes']=$ddd['ssnotes'];}
			}

			$finaloutput[$derid]=$output;				// ============================================== NEED TO ADJUST THIS FOR S and P ==================================================
		}
		
// 		foreach($playgrounds as $derid=>$data){		// $data is an array of sessions
// 			$output=array();
// 			$output['isPlayground']=1;
// 			$output['id']=$derid;
// 			$output['path']=$data['s']['pathid'];
// 			$output['observer']=$persondata['oldname'];
// 			if(!empty($data['s']['name'])){$output['name']=$data['s']['name'];}else{$output['name']="ObSet Title #{$derid}";}
// 			$output['placetime']=$data['s']['placetime'];
// 			if(!empty($data['s']['notes'])){$output['notes']=$data['s']['notes'];}
// 			$output['studentID']=$data['s']['studentid'];
// 			$output['videoURL']=$data['s']['url'];
// 			$output['videoID']=$data['s']['videoid'];
// 			foreach($data['a'] as $subid=>$dd){				//	echo "here is subid $subid<br />\n";										// ==== $dd is a group of rows of activity -- might only be one
// 				$supertempy=$tempy=array();
// 				foreach($dd as $dummy=>$ddd){			//	echo "here is actid {$ddd['id']}<br />\n";								// ==== $ddd is a row of activity
// 			//		if( !isset($supertempy['label']) ){ if ( isset($pathlabels[$ddd['sublabel']]) ){$supertempy['label']=$pathlabels[$ddd['sublabel']];}else{$supertempy['label']=-1;}}
// 					$tempy['SAid']=$ddd['id'];
// 					$tempy['PNid']=$ddd['pnid'];
// 					$tempy['megaid']=$ddd['node1'];
// 					$tempy['choiceid']=$ddd['choice'];
// 					$tempy['seconds']=$ddd['seconds'];
// 					if(!empty($ddd['extra'])){$tempy['extra']=$ddd['extra'];}
// 					if(!empty($ddd['notes'])){$tempy['notes']=$ddd['notes'];}
// 					$supertempy['ObResp'][]=$tempy;
// 				}
// 				$output['observations'][$subid]=$supertempy;
// 				$output['observations'][$subid]['ssid']=$ddd['ssid'];
// 				if(empty($ddd['ssname'])){$ddd['ssname']="Observation Title #{$ddd['ssid']}";}
// 				$output['observations'][$subid]['name']=$ddd['ssname'];
// 				if(!empty($ddd['ssnotes'])){$output['observations'][$subid]['notes']=$ddd['ssnotes'];}
// 			}
// 			$finaloutput[]=$output;						// ============================================== NEED TO ADJUST THIS FOR S and P ==================================================
// 		}  // THIS SHOULD BE A CLONE OF THE ABOVE
		
		
		if($_GET['mark']==1){
			echo '<pre>';  print_r($finaloutput);	
		}else{
			$final=json_encode($finaloutput);			echo $final;
		}
	}
}		// fetch a session's information for the visualizer

if(!empty($_GET['pid3']) && is_numeric($_GET['pid3'])){
		$pid=$_GET['pid3']+0;
		if(is_numeric($pid)){
			$return=mysqli_query($GLOBALS['db'],"SELECT * FROM tbPathNodes WHERE pathid='$pid' AND inactive IS NULL order by node1, choicegroup, choiceorder");				// =================== PRIMARY QUERY ===============
			while ($d=mysqli_fetch_assoc($return)){
				if($d['node1']>0){$nodestofetch[$d['node1']]=1;}
				if($d['choice']>0){$nodestofetch[$d['choice']]=1;}
				if($d['node2']>0){$nodestofetch[$d['node2']]=1;}
			}
			
			$nodestofetchtext=implode(',',array_keys($nodestofetch));
			$query="SELECT code, codedesc, codeexp, coderules FROM tbNodes WHERE id IN ($nodestofetchtext) AND code LIKE '%-%' AND code IS NOT NULL";		//echo "$query\n";
			$return2=mysqli_query($GLOBALS['db'],$query);
			while ($d=mysqli_fetch_assoc($return2)){
				$tempy=array();
				if(!empty($d['codedesc'])){$tempy['codedesc']=$d['codedesc'];}else{$tempy['codedesc']='There is no decription for this code';}
				if(!empty($d['codeexp'])){$tempy['codeexp']=str_replace("\r",'<br />',$d['codeexp']);}else{$tempy['codeexp']='There is no explanation for this code';}
				if(!empty($d['coderules'])){$tempy['coderules']=str_replace("\r",'<br />',$d['coderules']);}else{$tempy['coderules']='There are no rules defined for this code';}
				$supertempy[$d['code']]=$tempy;
			}
			if($_GET['mark']==1){
				echo '<pre>';  print_r($supertempy);
			}else{
				$jj=json_encode($supertempy);
				echo "$jj";
			}
		}
}		// fetch all the code information for a pathid


































// =========================== THIS AREA IS FOR CATCHING EDITED DATA FROM TRAILBLAZER ===============

if(!empty($_POST['op']) && is_numeric($_POST['op'])){
	if(!empty($_POST['loadedpid']) && is_numeric($_POST['loadedpid'])){
		$derpid=$_POST['loadedpid']+0;
		$errors='';   $needhits=$gothits=0;		$querys=$q1s=$q2s=array();
		switch($_POST['op']){
			case '1':	// ======== OP 1 is a drag reorder within a mega			op=1&mega=128&node130=4&node131=2&node132=3
							if(!empty($_POST['mega']) && is_numeric($_POST['mega'])){
								$megpid=$_POST['mega']+0;
								foreach($_POST as $k=>$v){
									if(substr($k,0,4)=='node'){
										$nodeid=substr($k,4);  $nodeid+=0;
										$querys[]="UPDATE tbPathNodes SET choiceorder='$v' WHERE node1='$megaid' AND choice='$nodeid' AND pathid='$derpid' LIMIT 1";
									}
								}
							}
							if(!empty($querys)){
								foreach($querys as $q){
									$needhits++;
									$return=mysqli_query($db,$q);
									if(mysqli_affected_rows($db)>0){$gothits++;}else{$errors.=mysqli_error($db);}
//									$errors.="$q\n";
								}
								if($gothits>=$needhits){echo 'A';break;}
							}
							echo 'X|'.$errors;
							break;
			case '2':	// ======== OP 2 is an update for a choice node    op=2&node=62&mega=46&ot=Adult... occurring&exv=asd&exi=rtert&cg=8&dest=mega_1
							if(!empty($_POST['node']) && is_numeric($_POST['node'])){
								$validitems['ot']='title';    $validitems['exv']='extra';    $validitems['exi']='aside';		$validitems['cd']='code';		// NOTE - code just changes the code -- the secondary bits are not changed and should prob be moved to a "universal" table to feed the code editor
								$nodeid=$_POST['node']+0;
								$megaid=$_POST['mega']+0;
								foreach($_POST as $k=>$v){
									if(!empty($validitems[$k])){
										if(!empty($v)){$v2='\''.mysqli_real_escape_string($db,$v).'\'';}else{$v2='NULL';}    $q1s[]="{$validitems[$k]}=$v2";
									}
								}
								if(!empty($q1s)){$q1=implode(',',$q1s);    $querys[]="UPDATE tbNodes SET $q1 WHERE id='$nodeid' LIMIT 1";}

								if(isset($_POST['cg'])){if(!empty($_POST['cg'])){$cg=$_POST['cg']+0;$q2s[]="choicegroup='{$cg}'";}else{$q2s[]='choicegroup=NULL';}}
								if(!empty($_POST['dest'])){$dnum=substr($_POST['dest'],(strpos($_POST['dest'],'_')+1)); $dest=$dnum+0; if($dnum>0){$q2s[]="node2='{$dest}'";}}		// dest is never null
								if(!empty($q2s)){$q2=implode(',',$q2s);    $querys[]="UPDATE tbPathNodes SET $q2 WHERE node1='{$megaid}' AND choice='{$nodeid}' AND pathid='$derpid' LIMIT 1";}
							}
							if(!empty($querys)){
								foreach($querys as $q){
									$needhits++;
									$return=mysqli_query($db,$q);
									if(mysqli_affected_rows($db)>0){$gothits++;}else{$errors.=mysqli_error($db);}
//									$errors.="$q\n";
								}
								if($gothits>=$needhits){echo 'A|1';break;}else{$errors.=" --- $gothits $needhits";}		// the one in A|1 doesnt mean anything
							}
							echo 'X|'.$errors;
							break;
			case '3':	// ======== OP 3 is an update for a mega node    op=3&mega=46&ot=Adult... occurring&ng=4
							if(!empty($_POST['mega']) && is_numeric($_POST['mega'])){
								$validitems['ot']='title';    $validitems['ng']='nodegroup';
								$megaid=$_POST['mega']+0;
								foreach($_POST as $k=>$v){
									if(!empty($validitems[$k])){
										if(!empty($v)){$v2='\''.mysqli_real_escape_string($db,$v).'\'';}else{$v2='NULL';}    $q1s[]="{$validitems[$k]}=$v2";
									}
								}
								if(!empty($q1s)){$q1=implode(',',$q1s);    $query="UPDATE tbNodes SET $q1 WHERE id='$megaid' LIMIT 1";}		//$error="<br /><br />Not really an error...<br /><br />\n$query\n";	
								if(!empty($query)){
									$return=mysqli_query($db,$query);
									if(mysqli_affected_rows($db)>0){echo 'A|1';break;}else{$error=mysqli_error($db);}		// the one in A|1 doesnt mean anything
//									$error="X|$query\n";
								}
							}
							echo 'X|'.$error;
							break;
			case '4':	// ======== OP 4 is a new node within an existing megaop=4&mega=42
							if(!empty($_POST['mega']) && is_numeric($_POST['mega'])){
								$megaid=$_POST['mega']+0;
								$query="SELECT choiceorder FROM tbPathNodes WHERE node1='{$megaid}' AND pathid='$derpid' ORDER BY choiceorder DESC LIMIT 1";
									$return=mysqli_query($db,$query);		$d=mysqli_fetch_assoc($return);		$order=$d['choiceorder']+1;
								$query="INSERT INTO tbNodes (title) VALUES ('New Node')";
								$return=mysqli_query($db,$query);
//								$error="<br /><br />Not really an error...<br /><br />\n$query\n";					$newnodeid=99999;
								if(mysqli_affected_rows($db)>0){
									$newnodeid=mysqli_insert_id($db);
									$query="INSERT INTO tbPathNodes (pathid,node1,choice,choiceorder,node2) VALUES ('$derpid','{$megaid}','{$newnodeid}','{$order}',NULL)";		// NULL for node 2 defaults to "path terminates"
									$return=mysqli_query($db,$query);
//									$error.="$query\n";
									if(mysqli_affected_rows($db)>0){echo "A|{$newnodeid}";break;}else{$error=mysqli_error($db);}
								}
								echo 'X|'.$error;
							}
							break;
			case '5':	// ======== OP 5 is a new mega
							// ========== DIFFERENT --- new megas dont have pathnode rows and there's really nothing to connect to -- need system  (newmegaid,0,0,0,0)? perhaps?
								$query="INSERT INTO tbNodes (title) VALUES ('New Mega Node')";
								$return=mysqli_query($db,$query);
//								$error="<br /><br />Not really an error...<br /><br />\n$query\n";					$newnodeid=99999;
								if(mysqli_affected_rows($db)>0){
									$newnodeid=mysqli_insert_id($db);
									$query="INSERT INTO tbPathNodes (pathid,node1,choice,choiceorder,node2) VALUES ('$derpid','{$newnodeid}','0','0','0')";		// zero zero zero is needed because the mega nodes need SOMETHING to attach to, so we use this to attach to the path
									$return=mysqli_query($db,$query);
//									$error.="       $query\n";
									if(mysqli_affected_rows($db)>0){echo "A|$newnodeid";break;}else{$error='(2) '.mysqli_error($db);}
								}else{$error='(1) '.mysqli_error($db);}
								echo 'X|'.$error;
							break;
			case '6':	// ======== OP 6 is for nuking nodes (can probably be used for megas as well)
							if(!empty($_POST['mega']) && is_numeric($_POST['mega'])){$megaid=$_POST['mega']+0;}
							if(!empty($_POST['victim']) && is_numeric($_POST['victim'])){
								$victimid=$_POST['victim']+0;
								if(empty($megaid)){
									$query="UPDATE tbNodes SET inactive='1' WHERE id='{$victimid}' LIMIT 1";		// ======= WE MAY NOT WANT TO DO THIS IF THE NODE CAN BE USED ELSEWHERE -- we could use this for nuking megas and the lower for removing links FROM megas ===========
									$return=mysqli_query($db,$query);
//									$error="$query\n";
									if(mysqli_affected_rows($db)>0){echo "A|$megaid";break;}else{$error=mysqli_error($db);}
								}else{
									$query="UPDATE tbPathNodes SET inactive='1' WHERE choice='{$victimid}' AND node1='{$megaid}' AND pathid='$derpid' LIMIT 1";		// zero zero zero is needed because the mega nodes need SOMETHING to attach to, so we use this to attach to the path
									$return=mysqli_query($db,$query);
//									$error.="       $query\n";
									if(mysqli_affected_rows($db)>0){echo "A|$victimid_$megaid";break;}else{$error=mysqli_error($db);}
								}
							}else{
								$error='no victim';
							}
								echo 'X|'.$error;
							break;
			case '7':	// ======== OP 7 is for nuking MEGAs
							if(!empty($_POST['mega']) && is_numeric($_POST['mega'])){
								$megaid=$_POST['mega']+0;
								if(!empty($megaid)){
									$query="UPDATE tbNodes SET inactive='1' WHERE id='{$megaid}' LIMIT 1";		// ======= WE MAY NOT WANT TO DO THIS IF THE NODE CAN BE USED ELSEWHERE -- we could use this for nuking megas and the lower for removing links FROM megas ===========
									$return=mysqli_query($db,$query);
//									$error="$query\n";
									if(mysqli_affected_rows($db)>0){echo "A|$megaid";break;}else{$error=mysqli_error($db);}

									// == here we also invalidate the mega's connections === TECHNICALLY we dont need to do this, but best if done ???  what if restore?  what if mega had nodes normally deleted?  how to separate?
									$return=mysqli_query($db,"SELECT node2 FROM tbPathNodes WHERE node1='$megaid' AND appid='1' AND pathid='$derpid' AND inactive IS NULL");		
									while($d=mysqli_fetch_assoc($return)){$victims[]=$d['node2'];}
									$victimstext=implode(',',$victims);
//									$error.="       $megaid_$victimstext";
								
									$query="UPDATE tbPathNodes SET inactive='1' WHERE node1='$megaid' AND appid='1' AND pathid='$derpid' AND inactive IS NULL";		// zero zero zero is needed because the mega nodes need SOMETHING to attach to, so we use this to attach to the path
									$return=mysqli_query($db,$query);
//									$error.="       $query\n";
									if(mysqli_affected_rows($db)>0){echo "A|$megaid_$victimstext";break;}else{$error=mysqli_error($db);}
								}
							}else{
								$error='no victim';
							}
								echo 'X|'.$error;
							break;
			default: echo 'no op set';

		}
	} // end pid check

} // end op check -- EID is for trailblazer edited items -- modifying a path, rather than a session

// had some tabs after the question mark bracket below that resulted in them returning as part of the AJAX return data -- ugh








































// ================================== IRR functions ================================
if( (!empty($_GET['irrA']) && is_numeric($_GET['irrA']))  &&  (!empty($_GET['irrB']) && is_numeric($_GET['irrB']))){
	$irrA=$_GET['irrA']+0;		$irrB=$_GET['irrB']+0;
	if(is_numeric($irrA) && is_numeric($irrA) && $irrA != $irrB){	

	if($_GET['mark']==1){echo<<<ENDECHOX
	<style>
		table.ObTable {padding: 20px; border: 1px solid black; font-size: 14px; text-align:center; border-collapse: collapse;border-spacing: 0;}
		table.ObTable tr.pathhead {font-size: 18px; font-weight: bold;}
		table.ObTable tr.pathcats {font-size: 16px; font-weight: bold;}
		table.ObTable td {padding: 6px 12px; border: 1px solid #888; text-align:center;}
		table.ObTable tr.check {background-color:none;}
		table.ObTable tr.ack {background-color:#fdd;}
		table.ObTable tr.bigack {background-color:#fdd;}
		div#irr_errors {border: 1px solid red; background-color: #fdd; padding: 20px;}
	</style>
ENDECHOX;
}

		$data[$irrA]=irrDataFetcher($irrA);
		$data[$irrB]=irrDataFetcher($irrB);
		
		$dataA=$data[$irrA]['observations'];			$obsCountA=count($dataA);
		$dataB=$data[$irrB]['observations'];			$obsCountB=count($dataB);			if($obsCountA>=$obsCountB){$moreAB=$obsCountA;}else{$moreAB=$obsCountB;}
		
		// ================ Obs Count check ==================
		if($obsCountA != $obsCountB){
			$errors['obscount'][]="Obs Count Fail: There are $obsCountA observations in {$data[$irrA]['name']} ($irrA) and $obsCountB in {$data[$irrB]['name']} ($irrB)";
		}
		if($obsCountA==0){$errors['obscount'][]="Obs Count Fail: There are no observations in {$data[$irrA]['name']} (id: $irrA)";}
		if($obsCountB==0){$errors['obscount'][]="Obs Count Fail: There are no observations in {$data[$irrB]['name']} (id: $irrB)";}
		
		
		// ================ time check ==================
		$_TIMEFLEX=5;
		
		for($e=0;$e<$moreAB;$e++){
			$ee=$e+1;
			$finaltables[$e]='<table class="ObTable"><tr class="pathhead"><td colspan="5">Observation (Path)  '.$ee.'</td></tr><tr class="pathcats"><td colspan="2">Reviewer 1</td><td colspan="2">Reviewer 2</td><td>&nbsp;</td></tr><tr class="pathcats"><td>Time</td><td>Code</td><td>Code</td><td>Time</td><td>&nbsp;</td></tr>';
		
			if( abs($dataA[$e]['ObResp'][0]['seconds'] - $dataB[$e]['ObResp'][0]['seconds']) > $_TIMEFLEX ){
				$errors['timing'][]="Timing check fail: Start too far apart on Observation $ee ({$dataA[$e]['ObResp'][0]['seconds']} - {$dataB[$e]['ObResp'][0]['seconds']})";
			}
			
		$oeNumA=count($dataA[$e]['ObResp'])-1;
		$oeNumB=count($dataB[$e]['ObResp'])-1;
			if( abs($dataA[$e]['ObResp'][$oeNumA]['seconds'] - $dataB[$e]['ObResp'][$oeNumB]['seconds']) > $_TIMEFLEX ){
				$errors['timing'][]="Timing check fail: End too far apart on Observation $ee ({$dataA[$e]['ObResp'][$oeNumA]['seconds']} - {$dataB[$e]['ObResp'][$oeNumB]['seconds']})";
			}

		}

		$dataAcopy=$dataA; $dataBcopy=$dataB;			// to keep track of what's left
		foreach($dataA as $numA=>$obsA){					// $obsA == obResp array and name
			$agreements[$numA]=0;
			$decisions[$numA]=0;
			foreach($obsA['ObResp'] as $enumA=>$oeA){		// 
				$keyA="A-{$numA}-{$enumA}";
				
		//		echo "Testing $keyA ({$dataA[$numA]['name']}) ({$oeA['seconds']})\n";
				$yay=false;	$meh=false;
				
		//		foreach($dataB as $numB=>$obsB){
					foreach($dataB[$numA]['ObResp'] as $enumB=>$oeB){
						$keyB="B-{$numA}-{$enumB}";						
		//				echo " &nbsp; &nbsp; Testing $keyB ({$dataB[$numB]['name']}) ({$oeB['seconds']})\n";
						
						if( !$alreadymatched[$keyB] ){
							if(abs($oeA['seconds'] - $oeB['seconds']) <= $_TIMEFLEX){
								if( $oeA['pnid']==$oeB['pnid'] ){		// got a match -- same pnid at same time?
									$matched[$keyA]=$keyB;
									$alreadymatched[$keyB]=true;
									unset($dataAcopy[$numA]['ObResp'][$enumA]);		unset($dataBcopy[$numA]['ObResp'][$enumB]);
					//				$errors['matches'][]="Element {$numA}/{$enumA} in {$dataA[$numA]['name']} and element {$numB}/{$enumB} in {$dataB[$numB]['name']} MATCH ({$oeA['pnid']} == {$oeB['pnid']}) ({$oeA['seconds']} and {$oeB['seconds']})";
									$tables[$numA][$oeA['seconds']][]="<tr class='check'><td>{$oeA['seconds']}</td><td>{$oeA['pnid']}</td><td>{$oeB['pnid']}</td><td>{$oeB['seconds']}</td><td>CHECK</td></tr>";
									$yay=true;
									$agreements[$numA]++;
									$decisions[$numA]++;
				//					echo " &nbsp; &nbsp; &nbsp; &nbsp; GOT ONE!\n";
									continue(2);
								}else{
									// ================ the times match, but the choices differ
							//		$errors['matches'][]="IRR semi-fail: Element {$numA}/{$enumA} in {$dataA[$numA]['name']} and element {$numB}/{$enumB} in {$dataB[$numB]['name']} have a similar time ({$oeA['seconds']} and {$oeB['seconds']}), but different choices";
			//				echo "IRR semi-fail: Element {$numA}/{$enumA} in {$dataA[$numA]['name']} and element {$numB}/{$enumB} in {$dataB[$numB]['name']} have a similar time ({$oeA['seconds']} and {$oeB['seconds']}), but different choices<br />";
									unset($dataAcopy[$numA]['ObResp'][$enumA]);		unset($dataBcopy[$numA]['ObResp'][$enumB]);
									$tables[$numA][$oeA['seconds']][]="<tr class='ack'><td>{$oeA['seconds']}</td><td>{$oeA['pnid']}</td><td>{$oeB['pnid']}</td><td>{$oeB['seconds']}</td><td>ACK</td></tr>";
									$meh=true;
									$decisions[$numA]++;
				//					echo " &nbsp; &nbsp; &nbsp; &nbsp; got one!\n";
									continue(2);
								}
							}
						}
					}
			//	}
				if(!$yay && !$meh){
	//				$errors['NOmatches'][]="No love in B for $keyA";
	//				unset($dataAcopy[$numA]['ObResp'][$enumA]);	
					$nottables[$numA][$oeA['seconds']][]="<tr class='bigack'><td>{$oeA['seconds']}</td><td>{$oeA['pnid']}</td><td>&nbsp;---&nbsp;</td><td>&nbsp;---&nbsp;</td><td>BIGACK</td></tr>";
					$decisions[$numA]++;
				}
			}
		}
		
	//	echo "\n\n\n\n";
	
//	print_r($dataBcopy); echo '<br /><br />';
//	print_r($dataAcopy);
		
		foreach($dataBcopy as $numB=>$obsB){			// lets go backward through the unmatched Bs and compare them to As instead
			foreach($obsB['ObResp'] as $enumB=>$oeB){
				$keyB="B-{$numB}-{$enumB}";
		//			echo " Testing $keyB again ({$dataBcopy[$numB]['name']}) ({$oeB['seconds']})\n";
				$yay=false;	$meh=false;
	//			foreach($dataAcopy as $numA=>$obsA){		// now lets go through the unmatched bits and see what we can match
					foreach($dataAcopy[$numB]['ObResp'] as $enumA=>$oeA){
						$keyA="A-{$numB}-{$enumA}";
		//					echo "&nbsp; &nbsp; Testing $keyA again ({$dataAcopy[$numA]['name']}) ({$oeA['seconds']})\n";
						if( !$alreadymatched[$keyA] ){
							if(abs($oeA['seconds'] - $oeB['seconds']) <= $_TIMEFLEX){
					//			$errors['matches'][]="IRR semi-fail 2: Element {$numA}/{$enumA} in {$dataA[$numA]['name']} and element {$numB}/{$enumB} in {$dataB[$numB]['name']} have a similar time, but different choices";
								unset($dataAcopy[$numB]['ObResp'][$enumA]);		unset($dataBcopy[$numB]['ObResp'][$enumB]);
								$tables[$numB][$oeB['seconds']][]="<tr class='ack'><td>{$oeA['seconds']}</td><td>{$oeA['pnid']}</td><td>{$oeB['pnid']}</td><td>{$oeB['seconds']}</td><td>ACK2</td></tr>";
								$meh=true;
								$decisions[$numA]++;
								$alreadymatched[$keyA]=true; $yay=true; 
			//					echo " &nbsp; &nbsp; &nbsp; &nbsp; sort of got one!\n";
								continue(2);
							}
						}				
					}
	//			}
				if(!$meh){
	//				$errors['NOmatches'][]="No love in A for $keyB";
	//					echo "No love in A for $keyB ---- {$oeA['seconds']} - {$oeA['pnid']} ------ {$oeB['seconds']} - {$oeB['pnid']}<br />";
	//				unset($dataBcopy[$numB]['ObResp'][$enumB]);
					$nottables[$numB][$oeB['seconds']][]="<tr class='bigack'><td>&nbsp;---&nbsp;</td><td>&nbsp;---&nbsp;</td><td>{$oeB['pnid']}</td><td>{$oeB['seconds']}</td><td>BIGACK2</td></tr>";
					$decisions[$numB]++;
				}
			}
		}
		
		foreach($nottables as $numX=>$d){	
			foreach($d as $sec=>$dd){
				foreach($dd as $ddd){
					$tables[$numX][$sec][]=$ddd;
				}
			}
		}


		foreach($tables as $numX=>$d){
			foreach($d as $sec=>$dd){
				foreach($dd as $ddd){
					$finaltables[$numX].=$ddd;
				}
			}
			$finaltables[$numX].="<tr class='pathcats'><td colspan='4'>Agreements:</td><td>{$agreements[$numX]}</td></tr>";
			$finaltables[$numX].="<tr class='pathcats'><td colspan='4'>Decisions:</td><td>{$decisions[$numX]}</td></tr>";
			$pct[$numX]=round(($agreements[$numX]/$decisions[$numX])*10000)/100;  $pcttotal+=$pct[$numX];
				$pcttotal2+=$agreements[$numX];		$pctcount2+=$decisions[$numX];
			$finaltables[$numX].="<tr class='pathcats'><td colspan='4'>Percent Agreement:</td><td>{$pct[$numX]}</td></tr></table>";
		}
		
		if(!empty($pct)){
			$output="<h3>Observation (Path) Agreement:</h3><ul>";			foreach($pct as $n=>$p){$n1=$n+1;$output.="<li>Observation (Path) {$n1}: {$p}%</li>";}		
//			$pctcount=count($pct);		$bigpct=round(($pcttotal/$pctcount));		
			$bigpct2=round(($pcttotal2/$pctcount2)*10000)/100;
			$output.="</ul><p>Average agreement across Observation (Path) nodes: {$bigpct2}%</p>";
		
			$output.="<table id='tableoftables' cellpadding='20'><tr valign='top'>";		$trflip=0;
			for($e=0;$e<$moreAB;$e++){
				$output.="<td>{$finaltables[$e]}</td>";
				if($trflip==1){$output.='</tr><tr valign="top">';$trflip=0;}else{$trflip=1;}
			}
			if($trflip==1){$output.='</tr></table>';}else{$output.='<td>&nbsp;</td></tr></table>';}
		}		


		
//		if($_GET['mark']==1){
		//	print_r($data);		
//			print_r($errors);	
//		}else{
			//$final=json_encode($finaloutput);			echo $final;
			if(!empty($errors)){
				echo '<div id="irr_errors"><p>Errors:</p><ul>';
				foreach($errors as $t=>$e){
					foreach($e as $ee){
						echo "<li>$ee</li>";
					}
				}
				echo '</ul></div>';
			}
			echo $output;
//		}
		
	}
}

function irrDataFetcher($sid){
		global $db;
		
		$return=mysqli_query($db,"SELECT s.*, v.url FROM tbSessions s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id='$sid'");				// ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
		while($d=mysqli_fetch_assoc($return)){$sessions[$d['id']]['s']=$d;}
		
		$return=mysqli_query($db,"SELECT SA.*, SS.id as ssid, SS.subnum, SS.name as ssname, SS.notes as ssnotes, PN.id as pnid, PN.node1, PN.choice, PN.node2, PN.choicegroup, PN.pathtype, PN.nsubgroup FROM tbSessionActivity SA, tbPathNodes PN, tbSubSessions SS WHERE SA.sessionid='$sid' AND SA.nodepathid=PN.id AND SA.ssid=SS.id ORDER BY SA.sessionid, SA.seconds");		
		while($d=mysqli_fetch_assoc($return)){
			$sessions[$d['sessionid']]['a'][$d['ssid']][$d['id']]=$d;		//	echo "here we load subsession {$d['subsession']} with {$d['id']}<br />\n";
			$lasttime[$d['sessionid']]=$d['seconds'];	
		}

		foreach($sessions as $derid=>$data){		// only one session in this version --- $data is an array of sessions
			$output=array();
			$output['path']=$data['s']['pathid'];
			$output['observer']=$uid;
			if(!empty($data['s']['name'])){$output['name']=$data['s']['name'];}else{$output['name']="ObSet Title #{$derid}";}
			$output['placetime']=$data['s']['placetime'];
	//		if(!empty($data['s']['notes'])){$output['notes']=$data['s']['notes'];}
			$output['studentid']=$data['s']['studentid'];
			$output['videoURL']=$data['s']['url'];
			$output['observations']=array();		// if someone has an old session with =NO= data, then wew still need to create an empty 'obs' area or the JS will fart
			$notsubid=0;
			foreach($data['a'] as $subid=>$dd){				//	echo "here is subid $subid<br />\n";										// ==== $dd is a group of rows of activity -- might only be one
				$supertempy=array();		//$defsublabels[$subid]=0;
				foreach($dd as $dummy=>$ddd){			//	echo "here is actid {$ddd['id']}<br />\n";								// ==== $ddd is a row of activity
					$tempy=array();		//$defsublabels[$subid]++;
					$tempy['pnid']=$ddd['pnid'];
					$tempy['seconds']=$ddd['seconds'];
					$supertempy['ObResp'][]=$tempy;
				}
				$output['observations'][$notsubid]=$supertempy;
				if(empty($ddd['ssname'])){$ddd['ssname']="Observation Title #{$ddd['ssid']}";}
				$output['observations'][$notsubid]['name']=$ddd['ssname'];
				$notsubid++;
//				if(!empty($ddd['ssnotes'])){$output['observations'][$subid]['notes']=$ddd['ssnotes'];}
			}

			return $output;
		}
}

if(!empty($_GET['irrvid']) && is_numeric($_GET['irrvid'])){
	$vid=$_GET['irrvid']+0;	
	$return=mysqli_query($db,"SELECT s.id, s.name, p.first, p.last FROM tbSessions s, tbPeople p, tbPeopleAppSessions pas WHERE s.videoid='$vid' AND s.id=pas.sessionid AND pas.personid=p.id");				// ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
		while($d=mysqli_fetch_assoc($return)){
			$output.="<option value='{$d['id']}'>{$d['name']} (id #{$d['id']} - {$d['first']} {$d['last']})</option>";
		}
	echo $output;
}



?>