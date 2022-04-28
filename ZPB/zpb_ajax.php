<?php
include($_SERVER['DOCUMENT_ROOT'].'/api/ccoi_session.php');
include($_SERVER['DOCUMENT_ROOT'].'/api/ccoi_dbhookup.php');
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    var_dump($_POST);
}*/

if( !empty($_POST['newSession']) ) {

    $possibleValues = ['name', 'studentid', 'date', 'video', 'path'];     //TODO: pathID and research/playground must be specified, currently no interface for that on front-end
    $defaultValues = array(
        'name' => 'New Observation Set',
        'studentid'=> NULL,
        'date'=> NULL,
        'video'=> NULL,
        'path'=> 1
    );
    
    // Double check that all required values are present
    foreach ($possibleValues as $currentValue){
        if (!isset($_POST[$currentValue]) || $_POST[$currentValue]==""){
            $_POST[$currentValue] = $defaultValues[$currentValue];
        }
    }

    //var_dump($_POST);

    if(isset($_POST['isPlayground'])){
        $tbName = 'Playground';
    }
    else{
        $tbName = 'Session';
    }

    $query="INSERT INTO tb{$tbName}s (pathid,name,studentid,videoid,placetime,createdon) VALUES ({$_POST['path']},'{$_POST['name']}','{$_POST['studentid']}',{$_POST['video']},'{$_POST['codingDate']}',NOW())";
        $return=mysqli_query($db,$query);
        $lastid=mysqli_insert_id($db);
        $returnData['id'] = $lastid;

    $query="INSERT INTO tbPeopleApp{$tbName}s (personid,appid,sessionid) VALUES ('{$_SESSION['pid']}','{$_SESSION['currentlyloadedapp']}','{$lastid}')";
        $return=mysqli_query($db,$query);
        $lastid=mysqli_insert_id($db);

    $query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('newobs','{$_POST['pathid']}','new','{$lastid}','{$_SESSION['pid']}',NOW())";
        $return=mysqli_query($db,$query);

    echo $returnData['id'];
}

if( !empty($_POST['newApp']) ) {

    $requiredValues = ['name'];     //TODO: pathID and research/playground must be specified, currently no interface for that on front-end

/*
    //Double check that all required values are present
    foreach ($requiredValues as $currentValue){
        if (!isset($_POST[$currentValue])){
            echo "-1";
            return;

        }
            
    }
*/

    //TODO: strip name of bad characters
    $_POST['name'] = substr($_POST['name'], 0, 100);
    $shortName = substr($_POST['name'], 0, 30);

    $query="INSERT INTO tbApps (name,shortname) VALUES ('{$_POST['name']}','{$shortName}')";
        $return=mysqli_query($db,$query);
        $lastid=mysqli_insert_id($db);
        $returnData['id'] = $lastid;

    $query="INSERT INTO tbPersonAppRoles (personid,appid,role) VALUES ('{$_SESSION['pid']}','{$lastid}','admin')";
        $return=mysqli_query($db,$query);

    $_SESSION['myappids'][] = $returnData['id'];
    $_SESSION['myappnames'][] = $_POST['name'];
    echo $returnData['id'];
}

if( !empty($_POST['changeCurrentApp']) ) {
    if( in_array( $_POST['changeTo'], $_SESSION['myappids'] ) ) {   //If this is a valid appID to change to
        $_SESSION['currentlyloadedapp'] = $_POST['changeTo'];       //Change to that appid
        echo "y";                                                   //Yes, that happened
    }
    else{                                                           //If not a valid appid
        echo "n";                                                   //No, that didn't happen
    }    
}

if( !empty($_POST['updateObsEl']) ) {
    //TODO: Verify that it's an observation that you can modify
    //PeopleAppSess if row exists with person id and session id then its good
    //var_dump($_POST);
    
    echo "\r\n\r\nUpdating session with ID: " . $_POST['id'];
    /*foreach ($_POST['paths'] as $currentObservation){
        echo "\r\n\r\n  Observation #{$currentObservation['id']}";

        if( $currentObservation['label'] != "-1" )
        {
            echo " '{$currentObservation['label']}'";
        }
        else {
            echo " with no label";
        }

        if( isset($currentObservation['isNew']) )
        {
            echo " is new";
        }
        if( isset($currentObservation['isEdited']) )
        {
            echo " is edited";
        }

        foreach ($currentObservation['steps'] as $index => $currentNode){
            echo "\r\n    Node at index {$index} has id {$currentNode['choice']}";
            if( isset($currentNode['isNew']) )
            {
                echo " and is new";
            }
            if( isset($currentNode['isEdited']) )
            {
                echo " and is edited";
            }
        }
    }
    */

    if(isset($_POST['isPlayground'])){
        $tbName = 'Playground';
        echo " in playground";
    }
    else{
        $tbName = 'Session';
        echo " in research";
    }
    

    //UPDATE tbSessionActivity SET ssid='{$newids[$d['sessionid']][$d['subsession']]}' WHERE id='{$d['id']}' LIMIT 1;\n

    foreach ($_POST['observations'] as $ssid => $currentObservation){
        //echo "\r\nchecking ssid:"; var_dump($ssid);
        //echo "\r\nchecking currentObservation:"; var_dump($currentObservation);
        // Grab existing nodes
        if ($ssid > 0){
            echo "\r\n\r\n Updating observation with ssID: " . $ssid;
            $currentSubsession = [];
            $return=mysqli_query($db,"SELECT * FROM tb{$tbName}Activity WHERE ssid={$ssid}");
            while($d=mysqli_fetch_assoc($return)){
                $currentSubsession[]= $d;
            }

            $numExistingNodes = count($currentSubsession);

            foreach ($currentObservation['nodes'] as $nodeIndex => $currentNode){
                if($nodeIndex < $numExistingNodes){
                    echo "\r\n  Updating node with index: " . $nodeIndex;
                    //echo "\r\n  currentSubsession[nodeIndex]: "; var_dump($currentSubsession[$nodeIndex]);
                    $query = "UPDATE tb{$tbName}Activity SET nodepathid = {$currentNode['nodepathid']}, seconds = {$currentNode['seconds']}, notes='{$currentNode['notes']}', extra='{$currentNode['extra']}', inactive=NULL WHERE id={$currentSubsession[$nodeIndex]['id']}";
                    //echo "\r\n\r\nquery: "; var_dump($query);
                    $return=mysqli_query($db,$query);
    //				$error="$query\n";
                    //if(mysqli_affected_rows($db)>0){echo "A|$megaid";break;}else{$error=mysqli_error($db);}
                }
                else{
                    echo "\r\n  Creating node with index: " . $nodeIndex;
                    $query = "INSERT INTO tb{$tbName}Activity (extra,nodepathid,notes,seconds,ssid,sessionid) VALUES ('{$currentNode['extra']}',{$currentNode['nodepathid']},'{$currentNode['notes']}',{$currentNode['seconds']},{$currentNode['ssid']},{$_POST['id']})";
                    //echo "\r\n\r\nquery: "; var_dump($query);
                    $return=mysqli_query($db,$query);
                }
            }
            echo "\r\n  Last node index: " . $nodeIndex;

            if( ($nodeIndex+1) < $numExistingNodes) {
                for ($i = $nodeIndex+1; $i < $numExistingNodes; $i++){
                    echo "\r\n  Turning off node with index: " . $i;
                    $return=mysqli_query($db,"UPDATE tb{$tbName}Activity SET inactive = 1 WHERE id={$currentSubsession[$i]['id']}");
                }
                
            }
            
        }
        else {

            echo "\r\n\r\n Creating observation for ssID: " . $ssid;
            
            $query = "INSERT INTO tbSub{$tbName}s (sessid,name) VALUES ({$_POST['id']},'{$currentObservation['name']}')";
            echo "\r\n\r\nquery: "; var_dump($query);

            $return=mysqli_query($db,$query);
            if(mysqli_affected_rows($db)>0){
                $newObsID=mysqli_insert_id($db);
            }
            

            echo "\r\n\r\n Actual ssID for this new observation: " . $newObsID;
            
            
            
            
            foreach ($currentObservation['nodes'] as $nodeIndex => $currentNode){
                echo "\r\n  Creating node with index: " . $nodeIndex;
                $query = "INSERT INTO tb{$tbName}Activity (extra,nodepathid,notes,seconds,ssid,sessionid) VALUES ('{$currentNode['extra']}','{$currentNode['nodepathid']}','{$currentNode['notes']}','{$currentNode['seconds']}',{$newObsID},{$_POST['id']})";
                echo "\r\n\r\nquery: "; var_dump($query);
                $return=mysqli_query($db,$query);
            }
        }
        
    }
    
    //echo "<br>Create a";
    /*
    //TODO: grab ssids of session
    $return=mysqli_query($db,"SELECT * FROM tbSubPlaygrounds WHERE sessid='{$_POST['id']}'");
    while($d=mysqli_fetch_assoc($return)){
        $observationids= $d['id'];
        $observations[$d['id']]=$d;
        //TODO: update observation name if that's been changed
    }
 	$observationidstext=implode(',',$observationids);

    $return=mysqli_query($db,"SELECT * FROM tbPlaygroundActivity WHERE ssid IN ($observationidstext) AND inactive IS NULL");
    while($d=mysqli_fetch_assoc($return)){
        $steps[] = $d;
        $stepids[] = $d['id'];
    }
 	$stepidstext=implode(',',$stepids);

    foreach ($_POST['paths'] as $currentObservation) {
        //Read in current observation
        	
        //TODO: If any non-node values have changed, update them

        

        foreach ($currentObservation['steps'] as $currentNode) {
            if( isset($currentNode['isNew']) ){
                //TODO: Check that this node doesn't exist
                //TODO: Add this node to db
            }
            if( isset($currentNode['isEdited']) ){
                //TODO: Check that this node exists
                //TODO: Edit node in db
            }    
        }
    }
    */
}

if( !empty($_POST['updateUser']) ) {
    //var_dump($_POST);

    if ($_SESSION['roles'][$_POST['appid']]['admin']==true){
        echo "i'm in";
        if($_POST['toChange']=='admin'){
            echo "change admin status";
            $return=mysqli_query($db,"SELECT role FROM PersonAppRoles WHERE personid='{$_POST['userid']}' AND appid='{$_POST['appid']}'");
            while($d=mysqli_fetch_assoc($return)){
                echo "\n\rd: "; var_dump($d);
            }
        }
        else{
            echo " change other thing";
            
            $query="UPDATE tbPeople SET {$_POST['toChange']} = {$_POST['newValue']} WHERE id='{$_POST['userid']}' LIMIT 1";
            $return=mysqli_query($db,$query);
            if(mysqli_affected_rows($db)==1){
                echo "y";
            }
            else{
                echo 'n';
            }
            //echo "$post||$ischeckbox||$query";
            /*
            echo "query: "."SELECT {$_POST['toChange']} FROM tbPeople WHERE id='{$_POST['userid']}'";
            $return=mysqli_query($db,"SELECT {$_POST['toChange']} FROM tbPeople WHERE id='{$_POST['userid']}'");
            while($d=mysqli_fetch_assoc($return)){
                echo " d: "; var_dump($d);
            }
            */
        }
    }
    else {
        echo "Error: Please log in.";
    }
}

if( !empty($_POST['deleteSession']) ) {

    $requiredValues = ['sessionid'];     //TODO: pathID and research/playground must be specified, currently no interface for that on front-end

    // Double check that all required values are present
    foreach ($requiredValues as $currentValue){
        if (!isset($_POST[$currentValue])){
            echo "-1";
            return;

        }
            
    }

    if(isset($_POST['isPlayground'])){
        $tbName = 'Playground';
    }
    else{
        $tbName = 'Session';
    }

    // Verify that user is allowed to delete this session
    $return=mysqli_query($db,"SELECT sessionid FROM tbPeopleApp{$tbName}s WHERE personid='{$_SESSION['pid']}' AND appid='{$_SESSION['currentlyloadedapp']}' AND inactive IS NULL");		
        while($d=mysqli_fetch_assoc($return)){$sessionids[]=$d['sessionid'];}

    if( in_array($_POST['sessionid'], $sessionids) ){
        $query="UPDATE tb{$tbName}s SET inactive = 1 WHERE id='{$_POST['sessionid']}' LIMIT 1";
            $return=mysqli_query($db,$query);
            if(mysqli_affected_rows($db)==1){
                echo "y";
            }
            else{
                echo 'n';
            }
    }
    else {
        echo "f";
    }
}


if(isset($_POST['action']) && !empty($_POST['action'] && isset($_POST['baseURL']) && !empty($_POST['baseURL']))) {
    $action = $_POST['action'];
    $baseURL = $_POST['baseURL'];
    switch($action) { 
        case 'fetchScramble' : fetchScramble($baseURL, $db);break;
        default: echo('Lol');
    }
}

//AYO BRANDON CHECK THIS BAD BOY OUT
//ZACK'S SUPER-FAMOUS CODE-OUT
if( !empty($_GET['updateMeta']) ) {
    
    //TODO Verify if this is even necessary
    // For now, ignore videoid, and path.
    // 'videoid', 'path',
    $possibleValues = ['name', 'studentid', 'placetime', 'notes'];     
    $receivedValues = [];
    // Double check that all required values are present
    foreach ($possibleValues as $index => $currentValue){
        if(isset($_POST[$currentValue])){
            $receivedValues[] = $_POST[$currentValue];
        }
        else {
            unset($possibleValues[$index]);
        }
    }
    $columnsToUpdate = implode(',',$possibleValues);
    $newValues = implode(',', $receivedValues);

    //var_dump($_POST);

    if(isset($_POST['isPlayground'])){
        $tbName = 'Playground';
    }
    else{
        $tbName = 'Session';
    }

    $query="UPDATE tb{$tbName}s SET name='".$_POST['name']."', studentid = {$_POST['studentid']}, placetime = '".$_POST['placetime']."', notes = '".$_POST['notes']."' WHERE id = {$_GET['id']}";
    $return=mysqli_query($db,$query);
    echo "UPDATE tb{$tbName}s SET name='{$_POST['name']}', studentid = {$_POST['studentid']}, placetime = '{$_POST['placetime']}', notes = '{$_POST['notes']}' WHERE id = {$_GET['id']}";
    echo $_GET['id'];

}

function fetchScramble($baseURL, $db) {

    $query = "SELECT scramble FROM tbVideos WHERE url={$baseURL} AND inactive IS NULL";
    $return=mysqli_query($db,$query);
    $return=mysqli_fetch_assoc($return);
    return $return;
}

?>