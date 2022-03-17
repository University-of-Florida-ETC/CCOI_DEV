<?php
include($_SERVER['DOCUMENT_ROOT'].'/api/ccoi_session.php');
include($_SERVER['DOCUMENT_ROOT'].'/api/ccoi_dbhookup.php');
if( !empty($_POST['newSession']) ) {
    /*
    $_POST['pathid'] = 1;
    $_POST['playground'] = 1;

    $query="INSERT INTO tbPlaygrounds (pathid,name,createdon) VALUES ('{$_POST['pathid']}','{$_POST['name']}',NOW())";
    echo "Query 1: ".$query."\n";
    $query="INSERT INTO tbPeopleAppPlaygrounds (personid,appid,sessionid) VALUES ('{$_SESSION['pid']}','{$_SESSION['currentlyloadedapp']}','{$lastid}')";
    echo "Query 2: ".$query."\n";
    $query="INSERT INTO tbActivityLog (action, onid, field, details, actby, acton) VALUES ('newobs','{$_POST['pathid']}','new','{$lastid}','{$_SESSION['pid']}',NOW())";
    echo "Query 3: ".$query."\n";
    */

    
    $requiredValues = ['name'];     //TODO: pathID and research/playground must be specified, currently no interface for that on front-end

    // TEMPORARY, REMOVE LATER
    $_POST['pathid'] = 1;
    $_POST['playground'] = 1;
/*
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
    foreach ($requiredValues as $currentValue){
        if (!isset($_POST[$currentValue])){
            echo "-1";
            return;

        }
            
    }
*/
    $_POST['name'] = substr($_POST['name'], 0, 100);
    $shortName = substr($_POST['name'], 0, 30);

    $query="INSERT INTO tbApps (name,shortname) VALUES ('{$_POST['name']}','{$shortName}')";
        $return=mysqli_query($db,$query);
        $lastid=mysqli_insert_id($db);
        $returnData['id'] = $lastid;

    $query="INSERT INTO tbPersonAppRoles (personid,appid,role) VALUES ('{$_SESSION['pid']}','{$lastid}','admin')";
        $return=mysqli_query($db,$query);

    echo $returnData['id'];
    
}
?>