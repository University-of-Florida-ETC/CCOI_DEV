<pre><?php

$db=mysqli_connect("localhost","ccoi_editor","8rW4jpfs67bD",'newccoi');
if(!$db){$err=mysqli_error();		exit ("Error: could not connect to the CCOI Database! -- $access -- $err");}

// $blahs=array(1,2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0);
// srand((float)microtime() * 66);				shuffle($blahs);
// srand((float)microtime() * 140);				shuffle($blahs);
// $blahtext=implode('',$blahs);		echo "$blahtext\n\n\n";
// 
// 
// for($e=1;$e<53;$e++){
// 	$bill=makeScramble($e);
// 	echo "UPDATE tbVideos SET scramble='{$bill}' WHERE id='$e' LIMIT 1;\n";
// 
// }
// 
// function makeScramble($blah){
// 			$scramblearray=array();srand();
// 			for($e=0;$e<8;$e++){array_push($scramblearray,chr(rand(97,122)));}
// 			for($e=0;$e<8;$e++){array_push($scramblearray,chr(rand(49,57)));}
// 			for($e=0;$e<8;$e++){array_push($scramblearray,chr(rand(65,90)));}
// 			srand((float)microtime() * 1430);			shuffle($scramblearray);
// 			srand((float)microtime() * 141);				shuffle($scramblearray);
// 			srand((float)microtime() * 52);				shuffle($scramblearray);
// 			$scramblename=implode('',$scramblearray);
// 			$firstbit=$GLOBALS['blahs'][$blah];
// 			$scramblename=$firstbit.$scramblename;		// make sure starts with a number
// 			return $scramblename;
// }


echo "INSERT INTO tbSubSessions (sessid, subnum, name) VALUES \n";
$currentsubid=0;

$return=mysqli_query($db,"SELECT id, sessionid, subsession, sublabel FROM tbSessionActivity WHERE 1 order by sessionid, subsession, id");		
while($d=mysqli_fetch_assoc($return)){
	if(empty($newids[$d['sessionid']][$d['subsession']])){
		if(!empty($d['sublabel'])){$subby="'{$d['sublabel']}'";}else{$subby='NuLL';}
		echo "('{$d['sessionid']}','{$d['subsession']}',$subby),\n";
		$currentsubid++;
		$newids[$d['sessionid']][$d['subsession']]=$currentsubid;
		$updates.="UPDATE tbSessionActivity SET ssid='$currentsubid' WHERE id='{$d['id']}' LIMIT 1;\n";
	}else{
		$updates.="UPDATE tbSessionActivity SET ssid='{$newids[$d['sessionid']][$d['subsession']]}' WHERE id='{$d['id']}' LIMIT 1;\n";
	}
}

echo "\n\n\n\n\n\n$updates\n";

?>