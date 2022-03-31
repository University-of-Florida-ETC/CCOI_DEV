<?php
include($_SERVER['DOCUMENT_ROOT'].'/api/ccoi_session.php');
include($_SERVER['DOCUMENT_ROOT'].'/api/ccoi_dbhookup.php');

if( !empty($_POST['newSession']) ) {

    $requiredValues = ['name'];     //TODO: pathID and research/playground must be specified, currently no interface for that on front-end

    // TEMPORARY, REMOVE LATER
    $_POST['pathid'] = 1;
    $_POST['playground'] = 1;
/*
    // Double check that all required values are present
    foreach ($requiredValues as $currentValue){
        if (!isset($_POST[$currentValue])){
            echo "-1";
            return;

        }
            
    }
*/

    $query="INSERT INTO tbPlaygrounds (pathid,name,createdon) VALUES ('{$_POST['pathid']}','{$_POST['name']}',NOW())";
        $return=mysqli_query($db,$query);
        $lastid=mysqli_insert_id($db);
        $returnData['id'] = $lastid;

    $query="INSERT INTO tbPeopleAppPlaygrounds (personid,appid,sessionid) VALUES ('{$_SESSION['pid']}','{$_SESSION['currentlyloadedapp']}','{$lastid}')";
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
    var_dump($_POST);
    
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
?>