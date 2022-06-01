<pre>
<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
//$CCOI_requireslogin=true;
//include('./ccoi_session.php');
//phpinfo();  exit;

$newnodeid=1000;
$newpathid=2;
$nodemap[0]=0;		// for self-referencing mega definition entries

$db=mysqli_connect("localhost","ccoi_editor","8rW4jpfs67bD",'newccoi');
if(!$db){$err=mysqli_error();		exit ("Error: could not connect to the CCOI Database! -- $access -- $err");}


if(!empty($_GET['pid']) && is_numeric($_GET['pid'])){
	$pid=$_GET['pid']+0;
	if(is_numeric($pid)){

		$return=mysqli_query($db,"SELECT id FROM tbNodeGroups WHERE 1 ORDER BY id DESC LIMIT 1");
		$d=mysqli_fetch_assoc($return);			$newngid=$d['id']+0;
		
		$return2=mysqli_query($db,"SELECT * FROM tbNodeGroups WHERE pathid='$pid' ORDER BY id");			//echo "SELECT * FROM tbNodes WHERE id IN ($nids) AND inactive IS NULL ORDER BY id\n\n";
		while($dd=mysqli_fetch_assoc($return2)){
			foreach($dd as $k=>$v){$d[$k]=mysqli_real_escape_string($db,$v);}
			$newngid++;
			$query="INSERT INTO tbNodeGroups (appid, pathid, derorder, name, humanname, parent, grouphex, labelpos, fill, hide, chartfill) VALUES ('{$d['appid']}','{$newpathid}','{$d['derorder']}','{$d['name']}','{$d['humanname']}','{$d['parent']}','{$d['grouphex']}','{$d['labelpos']}','{$d['fill']}','{$d['hide']}','{$d['chartfill']}')";
			$query2=str_replace("''",'NULL',$query);
	//		$return=mysqli_query($db,$query2);			$newnodeid=mysqli_insert_id($db);
	//		$debug=" --- {$d['id']} => $newngid";
			echo "$query2; $debug\n";
			$ngmap[$d['id']]=$newngid;
		}
	
		$return=mysqli_query($db,"SELECT id FROM tbChoiceGroups WHERE 1 ORDER BY id DESC LIMIT 1");
		$d=mysqli_fetch_assoc($return);			$newcgid=$d['id']+0;
		
		$return2=mysqli_query($db,"SELECT * FROM tbChoiceGroups WHERE pathid='$pid' ORDER BY id");			//echo "SELECT * FROM tbNodes WHERE id IN ($nids) AND inactive IS NULL ORDER BY id\n\n";
		while($dd=mysqli_fetch_assoc($return2)){
			foreach($dd as $k=>$v){$d[$k]=mysqli_real_escape_string($db,$v);}
			$newcgid++;
			$query="INSERT INTO tbChoiceGroups (appid, pathid, name) VALUES ('{$d['appid']}','{$newpathid}','{$d['name']}')";
			$query2=str_replace("''",'NULL',$query);
	//		$return=mysqli_query($db,$query2);			$newnodeid=mysqli_insert_id($db);
	//		$debug=" --- {$d['id']} => $newcgid";
			echo "$query2; $debug\n";
			$cgmap[$d['id']]=$newcgid;
		}
	
	
		$return=mysqli_query($db,"SELECT id FROM tbNodes WHERE 1 ORDER BY id DESC LIMIT 1");
		$d=mysqli_fetch_assoc($return);			$newnodeid=$d['id']+0;
	
		$return=mysqli_query($db,"SELECT id FROM tbPathNodes WHERE 1 ORDER BY id DESC LIMIT 1");
		$d=mysqli_fetch_assoc($return);			$firstpnid=$d['id']+1;
	
//		$return=mysqli_query($db,"INSERT INTO tbPaths (startpnid,name) VALUES ('$firstpnid','New Path')");
//		$newpathid=mysqli_insert_id($db);
	
		$return=mysqli_query($db,"SELECT * FROM tbPathNodes WHERE pathid='$pid' AND inactive IS NULL ORDER BY node1, choice, node2");
		while($d=mysqli_fetch_assoc($return)){$pndata[$d['id']]=$d; $nodes[$d['node1']]=1; $nodes[$d['choice']]=1; $nodes[$d['node2']]=1;}
		$pnids=implode(',',array_keys($pndata));
		unset($nodes[0]);    unset($nodes['']);		// coice/node2 of zero is valid as it 'self-defines' a mage node -- dont need the info though -- '' is an abberation from old data
		$nids=implode(',',array_keys($nodes));				//		echo "nids = $nids";
	
		$return2=mysqli_query($db,"SELECT * FROM tbNodes WHERE id IN ($nids) AND inactive IS NULL ORDER BY id");			//echo "SELECT * FROM tbNodes WHERE id IN ($nids) AND inactive IS NULL ORDER BY id\n\n";
		while($dd=mysqli_fetch_assoc($return2)){
			foreach($dd as $k=>$v){$d[$k]=mysqli_real_escape_string($db,$v);}
			$newnodeid++;
			$query="INSERT INTO tbNodes (oldid, title, extra, aside, sametime, nodegroup, groupchoices) VALUES ('{$d['oldid']}','{$d['title']}','{$d['extra']}','{$d['aside']}','{$d['sametime']}','{$ngmap[$d['nodegroup']]}','{$d['groupchoices']}')";
			$query2=str_replace("''",'NULL',$query);
	//		$return=mysqli_query($db,$query2);			$newnodeid=mysqli_insert_id($db);
	//		$debug=" --- {$d['id']} => $newnodeid";
			echo "$query2; $debug\n";
			$nodemap[$d['id']]=$newnodeid;
		}
		
		
		foreach($pndata as $id=>$dd){
			foreach($dd as $k=>$v){$d[$k]=mysqli_real_escape_string($db,$v);}
	//		$debug=" --- {$d['id']}";
			$query="INSERT INTO tbPathNodes (pathid, node1, choice, choiceorder, node2, choicegroup, pathtype, nsubgroup) VALUES ('$newpathid','{$nodemap[$d['node1']]}','{$nodemap[$d['choice']]}','{$d['choiceorder']}','{$nodemap[$d['node2']]}','{$cgmap[$d['choicegroup']]}','{$d['pathtype']}','{$d['nsubgroup']}')";
			$query2=str_replace("''",'NULL',$query);
//			$return=mysqli_query($db,$query2);
			echo "$query2; $debug\n";
		}
	}
}
?>