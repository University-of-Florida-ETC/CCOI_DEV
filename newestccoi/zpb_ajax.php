<?php
include($_SERVER['DOCUMENT_ROOT'].'/newestccoi/api/ccoi_session.php');
include($_SERVER['DOCUMENT_ROOT'].'/newestccoi/api/ccoi_dbhookup.php');

/*if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    var_dump($_POST);
}*/
foreach($_POST as $k=>$v){$_SCRUBBED[$k]=mysqli_real_escape_string($db,$v);}

function scrubIt($target){
    foreach($target as $k=>$v){
        if(is_array($v)){
            $target[$k] = scrubIt($v);
        }
        else{
            $target[$k]=mysqli_real_escape_string($GLOBALS["db"],$v);
        }
    }
    return $target;
}

//TODO Scrub $_POST and $_GET with Mark's one liner and start pulling from $_SCRUBEBD within queries.
if( !empty($_POST['newSession']) ) {
    $possibleValues = ['name', 'studentid', 'codingDate', 'video', 'path'];     //TODO: pathID and research/playground must be specified, currently no interface for that on front-end
    $defaultValues = array(
        'name' => 'New Observation Set',
        'studentid'=> '',
        'date'=> '',
        'video'=> '',
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

    $query="INSERT INTO tb{$tbName}s (pathid,name,studentid,videoid,placetime,createdon) VALUES ({$_SCRUBBED['path']},'{$_SCRUBBED['name']}','{$_SCRUBBED['studentid']}',{$_SCRUBBED['video']},'{$_SCRUBBED['codingDate']}',NOW())";
        $return=mysqli_query($db,$query);
        $lastid=mysqli_insert_id($db);
        //echo "lastid: ". $lastid;
        $returnData['id'] = $lastid;

    $query="INSERT INTO tbPeopleApp{$tbName}s (personid,appid,sessionid) VALUES ('{$_SESSION['pid']}','{$_SESSION['currentlyloadedapp']}','{$lastid}')";
        $return=mysqli_query($db,$query);
        $lastid=mysqli_insert_id($db);

    $query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('newobs','{$_SCRUBBED['pathid']}','new','{$lastid}','{$_SESSION['pid']}',NOW())";
        $return=mysqli_query($db,$query);

    echo $returnData['id'];
}


if( !empty($_POST['newApp']) ) {
    $_POST['name'] = substr($_POST['name'], 0, 100);
    $shortName = substr($_POST['name'], 0, 30);

    $query="INSERT INTO tbApps (name,shortname) VALUES ('{$_SCRUBBED['name']}','{$shortName}')";
        $return=mysqli_query($db,$query);
        $lastid=mysqli_insert_id($db);
        $newAppID = $lastid;

    $query="INSERT INTO tbPersonAppRoles (personid,appid,role) VALUES ('{$_SESSION['pid']}','{$lastid}','superadmin')";
        $return=mysqli_query($db,$query);

    $_SESSION['myappids'][] = $newAppID;
    $_SESSION['myappnames'][] = $_POST['name'];
    $_SESSION['roles'][$newAppID]['admin']=true;
    echo $newAppID;
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
    $_POST = scrubIt($_POST);
    //TODO: Verify that it's an observation that you can modify
    //PeopleAppSess if row exists with person id and session id then its good
    //var_dump($_POST);
    
    //echo "\r\n\r\nUpdating session with ID: " . $_POST['id'];

    if(isset($_POST['isPlayground'])){
        $tbName = 'Playground';
        //echo " in playground";
    }
    else{
        $tbName = 'Session';
        //echo " in research";
    }
    
    $mappingNewObs = [];

    //UPDATE tbSessionActivity SET ssid='{$newids[$d['sessionid']][$d['observation']]}' WHERE id='{$d['id']}' LIMIT 1;\n
    $query ="SELECT id FROM tbSub{$tbName}s WHERE sessid = {$_POST['id']} AND inactive is NULL";
    $return = mysqli_query($db, $query);
    //echo "inactive session query: ".$query;
    while ($d = mysqli_fetch_assoc($return)) {
        $existingSSIDs[$d['id']] = 1;
    }
    

    foreach ($_POST['observations'] as $ssid => $currentObservation){
        //echo "\r\nchecking ssid:"; var_dump($ssid);
        //echo "\r\nchecking currentObservation:"; var_dump($currentObservation);
        // Grab existing nodes
        if ($ssid > 0){
            //echo "\r\n\r\n Updating observation with ssID: " . $ssid;
            unset($existingSSIDs[$ssid]);

            //$currentObservation['name'] = mysqli_real_escape_string($db,$currentObservation['name']);
            $query = "UPDATE tbSub{$tbName}s SET name = '{$currentObservation['name']}' WHERE id={$ssid}";
            //echo "\r\nquery: "; var_dump($query);
            $return=mysqli_query($db,$query);


            $currentSubsession = [];
            $return=mysqli_query($db,"SELECT * FROM tb{$tbName}Activity WHERE ssid={$ssid}");
            while($d=mysqli_fetch_assoc($return)){
                $currentSubsession[]= $d;
            }

            $numExistingNodes = count($currentSubsession);

            foreach ($currentObservation['nodes'] as $nodeIndex => $currentNode){
                if($nodeIndex < $numExistingNodes){
                    //echo "\r\n  Updating node with index: " . $nodeIndex;
                    //echo "\r\n  currentSubsession[nodeIndex]: "; var_dump($currentSubsession[$nodeIndex]);
                    $query = "UPDATE tb{$tbName}Activity SET nodepathid = {$currentNode['nodepathid']}, seconds = {$currentNode['seconds']}, notes='{$currentNode['notes']}', extra='{$currentNode['extra']}', inactive=NULL WHERE id={$currentSubsession[$nodeIndex]['id']}";
                    //echo "\r\n\r\nquery: "; var_dump($query);
                    $return=mysqli_query($db,$query);
    //				$error="$query\n";
                    //if(mysqli_affected_rows($db)>0){echo "A|$megaid";break;}else{$error=mysqli_error($db);}
                }
                else{
                    //echo "\r\n  Creating node with index: " . $nodeIndex;
                    $query = "INSERT INTO tb{$tbName}Activity (extra,nodepathid,notes,seconds,ssid,sessionid) VALUES ('{$currentNode['extra']}',{$currentNode['nodepathid']},'{$currentNode['notes']}',{$currentNode['seconds']},{$currentNode['ssid']},{$_SCRUBBED['id']})";
                    //echo "\r\n\r\nquery: "; var_dump($query);
                    $return=mysqli_query($db,$query);
                }
            }
            //echo "\r\n  Last node index: " . $nodeIndex;

            if( ($nodeIndex+1) < $numExistingNodes) {
                for ($i = $nodeIndex+1; $i < $numExistingNodes; $i++){
                    //echo "\r\n  Turning off node with index: " . $i;
                    $return=mysqli_query($db,"UPDATE tb{$tbName}Activity SET inactive = 1 WHERE id={$currentSubsession[$i]['id']}");
                }
                
            }
            
        }
        else {

            //echo "\r\n\r\n Creating observation for ssID: " . $ssid;
            
            $query = "INSERT INTO tbSub{$tbName}s (sessid,name,subnum) VALUES ({$_SCRUBBED['id']},'{$currentObservation['name']}','1')";
            //echo "\r\n\r\nquery: "; var_dump($query);

            $return=mysqli_query($db,$query);
            if(mysqli_affected_rows($db)>0){
                $newObsID=mysqli_insert_id($db);
                $mappingNewObs[$ssid] = $newObsID;
            }
            

            //echo "\r\n\r\n Actual ssID for this new observation: " . $newObsID;
            
            
            
            
            foreach ($currentObservation['nodes'] as $nodeIndex => $currentNode){
                //echo "\r\n  Creating node with index: " . $nodeIndex;
                $query = "INSERT INTO tb{$tbName}Activity (extra,nodepathid,notes,seconds,ssid,sessionid) VALUES ('{$currentNode['extra']}','{$currentNode['nodepathid']}','{$currentNode['notes']}','{$currentNode['seconds']}',{$newObsID},{$_SCRUBBED['id']})";
                //echo "\r\n\r\nquery: "; var_dump($query);
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

    $deletedSessions = implode(',',array_keys($existingSSIDs));
    if (strlen($deletedSessions) > 0){
        $query = "UPDATE tbSub{$tbName}s SET inactive = 1 WHERE id IN ({$deletedSessions})";
        //echo "\r\ninactive session query 1: ".$query;
        $return=mysqli_query($db,$query);
        $query = "UPDATE tb{$tbName}Activity SET inactive = 1 WHERE ssid IN ({$deletedSessions})";
        //echo "\r\ninactive session query 2: ".$query;
        $return=mysqli_query($db,$query);
    }

    echo json_encode($mappingNewObs);
}

if( !empty($_POST['updateUser']) ) {
    //var_dump($_POST);

    if ($_SESSION['roles'][$_POST['appid']]['admin']==true){
        if($_POST['toChange']=='admin'){
            if($_POST['newValue'] == "true") { $newRole = 'admin'; }
            else { $newRole = 'coder'; }

            $query="UPDATE tbPersonAppRoles SET role = '{$newRole}' WHERE personid='{$_SCRUBBED['userid']}' AND appid = '{$_SCRUBBED['appid']}'";
            $return=mysqli_query($db,$query);
            if(mysqli_affected_rows($db)==1){
                echo "y";
            }
            else{
                echo 'n';
            }
        }
        elseif($_POST['toChange']=='password'){
            //echo "oh no he's changing the password to " . $_POST['newValue'];
            $passhash=password_hash($_POST['newValue'],PASSWORD_BCRYPT);
            $query="UPDATE tbPeople SET passhash = '$passhash' WHERE id='{$_SCRUBBED['userid']}' LIMIT 1";
            $return=mysqli_query($db,$query);
            if(mysqli_affected_rows($db)==1){
                echo "y";
            }
            else{
                echo 'n';
            }
        }
        else{
            echo "       change other thing       ";
            
            $query="UPDATE tbPeople SET {$_SCRUBBED['toChange']} = '{$_SCRUBBED['newValue']}' WHERE id='{$_SCRUBBED['userid']}' LIMIT 1";
            echo "       query: ". $query ."       ";
            $return=mysqli_query($db,$query);
            if(mysqli_affected_rows($db)==1){
                echo "y";
            }
            else{
                echo 'n';
            }
        }
    }
    else {
        echo "Error: Please log in.";
    }
}

if( !empty($_POST['removeUser']) ) {
    //var_dump($_POST);

    if ($_SESSION['roles'][$_POST['appid']]['admin'] == true){
        $query="DELETE FROM tbPersonAppRoles WHERE personid='{$_SCRUBBED['userid']}' AND appid = '{$_SCRUBBED['appid']}'";
        $return=mysqli_query($db,$query);
        if(mysqli_affected_rows($db)>=1){
            echo "y";
        }
        else{
            echo $query;
        }
        $return = mysqli_query($db, "SELECT * FROM tbPersonAppRoles WHERE personid = {$_SCRUBBED['userid']} LIMIT 1");
        if( mysqli_fetch_assoc($return) ){
            //They still are in a research group somewhere
        }
        //If you are NOT in any research groups
        else {
            $query="UPDATE tbPeople SET inactive = '1' WHERE id='{$_SCRUBBED['userid']}' LIMIT 1";
            $return=mysqli_query($db,$query);
            if(mysqli_affected_rows($db)==1){
                //echo "y";
            }
            else{
                //echo 'n';
            }
        }
    }
    else {
        echo "Error: Please log in.";
    }
}

if( !empty($_POST['deleteSession']) ) {
    foreach($_POST as $k=>$v){$_SCRUBBED[$k]=mysqli_real_escape_string($db,$v);}
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
        $query="UPDATE tb{$tbName}s SET inactive = 1 WHERE id='{$_SCRUBBED['sessionid']}' LIMIT 1";
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
    var_dump($_POST);
    foreach($_POST as $k=>$v){$_SCRUBBED[$k]=mysqli_real_escape_string($db,$v);}
    // Grab our POST data, sanitize.
    // $id = (int)$_GET('id');
    // $name = $db->real_escape_string($_POST['name']);
    // $studentid = (int)$_POST['studentid']; //Cast to int to prevent injection
    // $placetime = $db->real_escape_string($_POST['placetime']);
    // $notes = $db->real_escape_string($_POST['notes']);

    //TODO Verify if this is even necessary
    // For now, ignore videoid, and path.
    // 'videoid', 'path',
    // $possibleValues = ['name', 'studentid', 'placetime', 'notes'];     
    // $receivedValues = [];
    // // Double check that all required values are present
    // foreach ($possibleValues as $index => $currentValue){
    //     if(isset($_POST[$currentValue])){
    //         $receivedValues[] = $_POST[$currentValue];
    //     }
    //     else {
    //         unset($possibleValues[$index]);
    //     }
    // }
    // $columnsToUpdate = implode(',',$possibleValues);
    // $newValues = implode(',', $receivedValues);



    if(isset($_POST['isPlayground'])){
        $tbName = 'Playground';
    }
    else{
        $tbName = 'Session';
    }

    $query="UPDATE tb{$tbName}s SET name='".$_SCRUBBED['name']."', studentid = {$_SCRUBBED['studentid']}, placetime = '".$_SCRUBBED['placetime']."', notes = '".$_SCRUBBED['notes']."' WHERE id = {$_GET['id']}";
    print_r($query);
    $return=mysqli_query($db,$query);
    echo $query;

}

function fetchScramble($baseURL, $db) {

    $query = "SELECT scramble FROM tbVideos WHERE url={$baseURL} AND inactive IS NULL";
    $return=mysqli_query($db,$query);
    $return=mysqli_fetch_assoc($return);
    return $return;
}

?>