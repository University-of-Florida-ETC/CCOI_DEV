<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
//phpinfo();  exit;

$db=mysqli_connect("localhost","ccoi_editor","8rW4jpfs67bD",'newccoi');
if(!$db){$err=mysqli_error();		exit ("Error: could not connect to the CCOI Database! -- $access -- $err");}




if(!empty($_GET['uid']) && is_numeric($_GET['uid'])){
	$uid=$_GET['uid'];
	
	$return=mysqli_query($db,"SELECT * FROM tbPeople WHERE id='$uid'");		$persondata=mysqli_fetch_assoc($return);
	
	$return=mysqli_query($db,"SELECT sessionid FROM tbPeopleAppSessions WHERE personid='$uid' AND appid='1'");		
	while($d=mysqli_fetch_assoc($return)){$sessionids[]=$d['sessionid'];}
	$sidstext=implode(',',$sessionids);
	
	$return=mysqli_query($db,"SELECT s.*, v.url FROM tbSessions s, tbVideos v WHERE s.id IN ($sidstext) and s.videoid=v.id");		
	while($d=mysqli_fetch_assoc($return)){$sessions[$d['id']]['s']=$d;}

	$return=mysqli_query($db,"SELECT * FROM tbNodes WHERE 1");		
	while($d=mysqli_fetch_assoc($return)){$nodeData[$d['id']]=$d;}

//	id		sessionid	subsession	sublabel				extra		nodepathid	seconds	notes	pnid	node1	choice	node2	choicegroup		pathtype	nsubgroup
//	887	31			0					some path label	P4		2					65			NuLL	2		1			3			9			NuLL				5				NuLL
	$return=mysqli_query($db,"SELECT SA.*, PN.id as pnid, PN.node1, PN.choice, PN.node2, PN.choicegroup, PN.pathtype, PN.nsubgroup FROM tbSessionActivity SA, tbPathNodes PN WHERE SA.sessionid IN ($sidstext) AND SA.nodepathid=PN.id ORDER BY SA.sessionid, SA.seconds");		
	while($d=mysqli_fetch_assoc($return)){
		$sessions[$d['sessionid']]['a'][$d['subsession']][$d['id']]=$d;		//	echo "here we load subsession {$d['subsession']} with {$d['id']}<br />\n";
		$lasttime[$d['sessionid']]=$d['seconds'];		// gets the highest time value
		if($d['sublabel'] != $oldsub){	//	echo "''$oldsub'' is not ''{$d['sublabel']}'' -- {$d['sublabel']} gets {$subcount[$d['sessionid']]}<br />\n";
			$oldsub=$d['sublabel'];$subcount[$d['sessionid']]++;$pathlabels[$d['sublabel']]=($subcount[$d['sessionid']]-1);
		}		// need to subtract one later
	}
	
	//echo "<br /><br />\n";
	
	foreach($sessions as $derid=>$data){		// $data is an array of sessions
		$output=array();
		$output['_id']=$data['s']['oldid'];
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
				$tempy['node']=$nodeData[$ddd['node1']]['oldid'];
				$tempy['choice']=$nodeData[$ddd['choice']]['oldid'];
				$tempy['minutes']=floor($ddd['seconds'] / 60);
				$tempy['seconds']=$ddd['seconds'] - ($tempy['minutes']*60);
				$tempy['extra']=$ddd['extra'];
				$tempy['notes']=$ddd['notes'];
				$supertempy['steps'][]=$tempy;
			}
			$output['paths'][]=$supertempy;
		}
		$output['prompted']=false;						// ALL sessions are false for this
		$output['sessionNotes']=$data['s']['notes'];
		$output['studentID']=$data['s']['studentid'];
		$output['videoURL']=$data['s']['url'];
		$finaloutput[]=$output;
	}
	
	$final=json_encode($finaloutput);			//echo "<br /><br />\n";
	echo $final;
}

if(!empty($_GET['pid']) && is_numeric($_GET['pid'])){
		$pid=$_GET['pid'];
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

		$jj=json_encode($ajaxOut);
		print_r($jj);
}

if(!empty($_GET['tpid']) && is_numeric($_GET['tpid'])){
		$pid=$_GET['tpid'];
		$query="SELECT p.startpnid, n.oldid FROM tbPaths p, tbNodes n WHERE p.id='$pid' AND p.startpnid=n.id";		$return=mysqli_query($db,$query);		$d=mysqli_fetch_assoc($return);
		$firstNode=$d['startpnid'];

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

		$globalcount=0;
		$finaloutput=array();

				fetchNodes($firstNode,true);
				$ajaxOut['nodes']=$finaloutput;

		$jj=json_encode($ajaxOut);
		print_r($jj);
}












function fetchNodes($id,$trail){
			$nodestofetch[]=$id;
			$return=mysqli_query($GLOBALS['db'],"SELECT * FROM tbPathNodes WHERE node1='$id' order by choiceorder");				// =================== PRIMARY QUERY ===============
			while ($d=mysqli_fetch_assoc($return)){
				if(!empty($d['choice'])){$nodestofetch[]=$d['choice'];}
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
			$output['nodeid']=$nodeData[$id]['oldid'];
			$output['title']=$nodeData[$id]['title'];
			// ===== trailblazer stuff
			if($trail){
				$output['t_megaid']=$id;
			}			
			if(!empty($nodeData[$id]['nodegroup'])){
				$output['groups'][]=$GLOBALS['nodeGroups'][$nodeData[$id]['nodegroup']]['name'];
				$output['group_hex'][]=$GLOBALS['nodeGroups'][$nodeData[$id]['nodegroup']]['grouphex'];
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
						if(empty($d['choicegroup'])){$d['choicegroup']=0;}
						if(empty($d['choicegroup']) && $should_group_choices){echo "ERROR: either we group choices or we dont -- make up your mind\n";}

						if($d['choicegroup'] != $oldchoicegroup){
							$oldchoicegroup=$d['choicegroup'];
							if(!empty($supertempy)){$branches[]=$supertempy;}		// catches the first 'null'
							$supertempy=array();
						}
		
						$tempy=array();
							$tempy['description']=$nodeData[$d['choice']]['title'];
							if(!empty($nodeData[$d['choice']]['aside'])){$tempy['aside']=$nodeData[$d['choice']]['aside'];}
							if(!empty($nodeData[$d['node2']]['oldid'])){$tempy['next']=$nodeData[$d['node2']]['oldid'];}		// terminators dont explicitly terminate, just no next...

							if(!empty($nodeData[$d['choice']]['extra'])){$tempy['extra']=$nodeData[$d['choice']]['extra'];}
							if(!empty($d['pathtype'])){$tempy['path_type']=$GLOBALS['nodeGroups'][$d['pathtype']]['humanname'];}

							$tempy['branch_id']=$nodeData[$d['choice']]['oldid'];
							// for some reason, branch id is listed between these...
							if(!empty($d['nsubgroup'])){$tempy['node_sub_group']=$GLOBALS['nodeGroups'][$d['nsubgroup']]['name'];}
							
							// ===== trailblazer stuff
							if($trail){
								$tempy['t_parentid']=$d['node1'];
								$tempy['t_thisid']=$d['choice'];
								if(!empty($d['node2'])){$tempy['t_nextid']=$d['node2'];}else{$tempy['t_nextid']='terminate';}
								$tempy['t_choicegroup']=$d['choicegroup'];
							}
		
						$supertempy[]=$tempy;
						if(!empty($d['node2'])){$nextvictims[$d['node2']]=1;}
		
			}		// ==== end db while ====
	
			$branches[]=$supertempy;			$supertempy=array();			// grab the last block
	
			$output['branches']=$branches;
			if($should_group_choices){ $output['branch_group_names']=array_values($bgns); }
			$GLOBALS['globalcount']++;

			$GLOBALS['finaloutput'][]=$output;
			$GLOBALS['alreadyfetched'][$id]=true;

			// go fetch the linked nodes
			if(!empty($nextvictims)){foreach($nextvictims as $node=>$dummy){if( !$GLOBALS['alreadyfetched'][$node] ){fetchNodes($node,$trail);}}}
}		// ==== end function ====



?>