<?php
$page = "Session";
$CCOI_requireslogin = true;
include './includes/header.php';
include './api/ccoi_dbhookup.php';
echo "<script language='javascript'>var derServer='https://{$serverroot}{$devprodroot}/';var derDevProd='{$devprodroot}';</script>\n";        // sigh -- needs to be after header, but before JS below

$id = $_GET['id'] + 0;
if (isset($_GET['isPlayground'])) {
    $tbName = 'Playground';
    echo "<script>const isPlayground = true;</script>";
} else {
    $tbName = 'Session';
    echo "<script>const isPlayground = false;</script>";
}

//Check that they are allowed in here
//=====================================
$editor = false;
//If you are an editor for this session
$return = mysqli_query($db, "SELECT * FROM tbPeopleApp{$tbName}s WHERE sessionid = $id AND personid = {$_SESSION['pid']} AND inactive IS NULL LIMIT 1");
if( mysqli_fetch_assoc($return) ){
    //echo "<br>You are the owner";
    $editor = true;
}
//If you are NOT an editor for this session
else {
    //Check if the user is in the same app as this session
    //Get this session's app
    $return = mysqli_query($db, "SELECT appid FROM tbPeopleApp{$tbName}s WHERE sessionid = $id AND inactive IS NULL LIMIT 1");
    $d = mysqli_fetch_assoc($return);

    //If they are not in the session's app
    if( !in_array($d['appid'], $_SESSION['myappids']) ){
        header("Location: /newestccoi");//Get redirected, idiot
    }
    else{
        $_SESSION['currentlyloadedapp'] = $d['appid'];
    }
}


$session = getSessionInfo($id); //defined below     //echo "<br>session: "; var_dump($session);
$appVideos = getAppVideos($id); //Defined below     //print_r($appVideos);
$videoInfo = getVideoInfo($id); //echo "<br>videoInfo: "; var_dump($videoInfo);
$choiceGroups = getChoiceGroups($session['pathid']);

//Grabbing 3 data structures:
//observations:     object containing this session's observations and all associated info (obsEls),                     keys are the observation IDs
//pathNodes:        object containing all of the info for every pathnode so we can use it as a pnID dictionary,         keys are the pathnodeIDs
//questionNodes:    object containing question's text and pnID of the user's choices at this question,                  keys are the question's nodeID

//FIRST QUERY: get all of the active observation elements for this session
//Wrote it this way because there wasn't an inactive column in SubSessions, so if you used the list of observations from that table you would pull inactive/deleted observations and then have to retroactively not use them
//Now that column exists so this way is less intuitive but functions identically and runs in the same amount of time
//c'est la vie
$return = mysqli_query($db, "SELECT ssid, extra, nodepathid, seconds, notes FROM tb{$tbName}Activity WHERE sessionid = $id AND inactive IS NULL");
while ($d = mysqli_fetch_assoc($return)) {
    //SSID corresponds to the specific Observation that these OEs belong to. 
    $observations[$d['ssid']]['nodes'][] = $d;      //put the observation element's info in observations[observationID]['nodes'][observationElementIndex]
    $listOfSSID[$d['ssid']] = 1;                    //create a list of active observations
}
$ssidListText = implode(',', array_keys($listOfSSID));  //format the list of active in a way we can query it

//SECOND QUERY: get the info at the observation level that we couldnt grab with the first query
//Basically, we couldn't get the observation name and notes efficiently in the first query, so just run another that's pretty damn short and quick to get them
$return = mysqli_query($db, "SELECT name, notes, id FROM tbSub{$tbName}s WHERE id IN ({$ssidListText})");
while ($d = mysqli_fetch_assoc($return)) { /*$observations[$d['ssid']][d['id']]=$d;*/
    
    $observations[$d['id']]['name'] = $d['name'];
    $observations[$d['id']]['notes'] = $d['notes'];
}

//THIRD QUERY: get the nodeID and question text for each question
//Fun quirk of the DB, we have an extraneous row for each question in each pathDiagram where node1 = the question's nodeID, choice = 0, and node2 = 0 or null or something
//We have to look out for it in the next query, which sucks, but it gives us an easy way to grab all the question info we need pretty quickly
$return = mysqli_query($db, "SELECT PN.node1, N.title FROM tbPathNodes PN LEFT JOIN tbNodes N ON PN.node1 = N.id WHERE PN.pathid = '{$session['pathid']}' AND PN.choice = 0 AND PN.inactive IS NULL AND N.inactive IS NULL");
while ($d = mysqli_fetch_assoc($return)) {
    $questionNodes[$d['node1']]['title'] = $d['title'];
}

//FOURTH QUERY: get the nodeID and question text for each question
//Imagine storing the same pathnode info in every observation element that uses that pathnode LOCALLY, and then dropping it when you send it to the server
//Couldn't be me
$return = mysqli_query($db, "SELECT PN.id as pnid, PN.node1, PN.choice, PN.choiceorder, PN.choicegroup, PN.node2, N.code, N.title, N.extra, N.aside FROM tbPathNodes PN LEFT JOIN tbNodes N ON PN.choice = N.id WHERE PN.pathid = '{$session['pathid']}' AND PN.choice != 0 AND PN.inactive IS NULL AND N.inactive IS NULL");
while ($d = mysqli_fetch_assoc($return)) {
    $questionNodes[$d['node1']]['choices'][$d['choiceorder']] = $d['pnid'];
    $pathNodes[$d['pnid']] = $d;
}

//Hide the interactive elements when you are not an editor
//TODO: also remove the JS to edit stuff, just for an extra layer of security
if($editor == false){
    echo<<<HIDESTUFF
	<style>
		#save_session_button {display: none!important}
		#add_path_button {display: none!important}
        .path-edit-icon {display: none!important}
        .path-delete-icon {display: none!important}
        #save_session_button {display: none!important}
	</style>
HIDESTUFF;
}
?>
<script>
    var sessionID = <?php echo $id; ?>;
    var observations = <?php echo json_encode($observations); ?>;
    if (observations == null) {
        observations = {};
    }
    console.log("observations:");
    console.log(observations);
    var questionNodes = <?php echo json_encode($questionNodes); ?>;
    console.log("questionNodes:");
    console.log(questionNodes);
    var pathNodes = <?php echo json_encode($pathNodes); ?>;
    console.log("pathNodes:");
    console.log(pathNodes);
    var choiceGroups = <?php echo json_encode($choiceGroups); ?>;
    console.log("choiceGroups:");
    console.log(choiceGroups);
</script>
<main role="main">
    <div class="container-fluid">
        <div class="container">
            <div id="session_go_back" class="row pt-3">
                <div class="col">
                    <?php
                    if(isset($_GET['isPlayground'])) {
                        echo '<a class="underlined-btn" href="/newestccoi?isPlayground=1"><span class="oi oi-arrow-thick-left mr-2"></span><span class="btn-text">Back to Session Select</span></a>';
                    }

                    else{
                        echo '<a class="underlined-btn" href="/newestccoi/"><span class="oi oi-arrow-thick-left mr-2"></span><span class="btn-text">Back to Session Select</span></a>';
                    }
                     ?>
                </div>
            </div>
            <div class="row py-5">
                <div class="col-md-8">
                    <div class="row pr-md-5">
                        <div class="col-md-8 col-12">
                            <h1 class="red-font" id="sessionTitle"><?php echo $session['name']; ?></h1>
                            <?php
                            if($editor == true) {
                               echo '<h5 style="text-transform: none;">Select an observation to view or edit its responses</h5>';
                            }

                            else{
                                echo '<h5 style="text-transform: none;">Select an observation to view its responses. You are not an editor of this session.</h5>';
                            }
                            ?>
                            <!-- <h5 style="text-transform: none;">Select an observation to view or edit its responses</h5>! -->
                        </div>
                        <div class="col-md-4 col-12 pt-2">
                            <button id="save_session_button" type="button" class="btn btn-blue float-right" data-toggle="tooltip" data-html="true" title="Click here to save your session" onclick="ajaxIt()">Save Session</button>
                        </div>
                    </div>

                    <div id="dom_group_1" class="row pt-3">
                        <div id="path_start" class="col-12 pt-3 pr-md-5 accordion">
                            <div class="card">
                                <div class="card-header" id="session_meta_collapse_heading">
                                    <h5 class="mb-0">
                                        <button id="session_meta_title" class="btn btn-link collapsed" data-toggle="collapse" data-target="#session_meta_collapse" aria-expanded="true" aria-controls="session_meta_collapse">
                                            <?php echo ($editor ? 'Edit' : 'View'); ?> Session Meta Data
                                        </button>
                                    </h5>
                                </div>

                                <div id="session_meta_collapse" class="collapse" aria-labelledby="session_meta_collapse_heading" data-parent="#path_start">
                                    <div class="col py-3">
                                        <form id="session_meta_form" method="post" action="javascript:void(0)">
                                            <div class="row">
                                                <div class="form-group col">
                                                    <label for="session_title">Session Name</label>
                                                    <input placeholder="Session Name" id="session_title" name="session_title" type="text" class="form-control" onchange="fetchMetaFields()" value="<?php echo $session['name'] ?>" <?php echo ($editor ? '' : "disabled = \"true\""); ?>>
                                                </div>
                                                <div class="form-group col">
                                                    <label for="studentID">Student ID</label>
                                                    <input placeholder="Student ID" id="studentID" name="studentID" type="text" class="form-control" onchange="fetchMetaFields()" value="<?php echo $session['studentid'] ?>" <?php echo ($editor ? '' : "disabled = \"true\""); ?>>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col">
                                                    <label for="session_date">Coding Date</label>
                                                    <input id="session_date" name="date" type="date" class="datepicker" onchange="fetchMetaFields()" value="<?php echo $session['placetime'] ?>" <?php echo ($editor ? '' : "disabled = \"true\""); ?>>
                                                </div>
                                                <div class="form-group col">
                                                    <label for="session_video_title">Video</label>
                                                    <input type="text" id="session_video_title" name="session_video_title" value="<?= $videoInfo['name'] ?>" class="fakeInput" placeholder="Demo Video" disabled=""> <!-- TODO: GET VIDEO NAME FROM ID -->
                                                    <input type="hidden" id="session_video_url" name="session_video_url" value="<?php echo $session['url'] ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="block" for="session_notes" id="session_notes_label">Session Notes</label>
                                                <textarea id="session_notes" name="session_notes" class="form-control" onchange="fetchMetaFields()" <?php echo ($editor ? '' : "disabled = \"true\""); ?>><?php echo $session['notes'] ?></textarea>
                                            </div>
                                        </form>
                                        <button type="button" class="btn btn-outline-blue btn-sm" data-toggle="collapse" data-target="#session_meta_collapse" aria-expanded="true" aria-controls="session_meta_collapse">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 py-2 pr-md-5">
                            <button id="add_path_button" type="button" class="btn btn-darkblue" data-toggle="tooltip" data-html="true" title="Click here to add a Observation" onclick="addObservation()">Add Observation</button>
                        </div>

                        <div id="path_listing" class="col-12 pt-4 pr-md-5">

                            <div id="path_list" class="draggable-container">
                            </div>
                        </div>
                    </div>
                    <div id="path_input" class="col-12 pt-3 pr-md-5 d-none">
                        <h2 id="path_title"></h2>
                        <div class="row">
                            <form method="post" action="javascript:void(0)" id="branch_form" class="col-12" name="branch_form">
                                <div class="row py-3">
                                    <div class="col-md-9 col-12">
                                        <div class="form-group">
                                            <label for="path_label">Choose a path label</label>
                                            <input type="text" class="form-control" id="path_label" name="path_label" placeholder="Example Label" onchange="changeObsLabel()">
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <button id="path_label_button" class="btn btn-blue" type="button">Set Label</button>
                                    </div>
                                </div>
                                <div class="row pb-3">
                                    <div class="col-md-2 col-12">
                                        <div class="form-group">
                                            <input class="form-control" id="timestamp_input_minutes" type="number" min="0" max="9999" step="1" value="0" onchange="changeTime()">
                                            <label class="text-center" for="timestamp_input_minutes">minutes</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-12">
                                        <div class="form-group">
                                            <input class="form-control" id="timestamp_input_seconds" type="number" min="0" max="59" step="1" value="0" onchange="changeTime()">
                                            <label class="text-center" for="timestamp_input_seconds">seconds</label>
                                        </div>
                                    </div>
                                    <div class="col-md-5 col-12">
                                        <button id="proceed_button" class="btn btn-sm btn-outline-darkblue mb-2" onclick="proceed()">Proceed</button>
                                        <button id="proceed_and_play_button" class="btn btn-sm btn-outline-darkblue mb-2">Proceed and Play</button>
                                    </div>
                                    <div class="col-md-3 col-12 video-speed-box">
                                        <h5 class="text-center">Playback Speed</h5>
                                        <div class="row">
                                            <div class="col-4">
                                                <button id="vid_speed_1x" class="btn btn-mini btn-blue playback-speed" type="button">1x</button>
                                            </div>
                                            <div class="col-4">
                                                <button id="vid_speed_1_5x" class="btn btn-mini btn-blue playback-speed" type="button">1.5x</button>
                                            </div>
                                            <div class="col-4">
                                                <button id="vid_speed_2x" class="btn btn-mini btn-blue playback-speed" type="button">2x</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="branch_container" class="row">
                                    <form id="branch_radio_form" class="col-12 pt-3" action="javascript:void(0)"></form>
                                </div>
                                <div class="row col-12">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="notes_input">Notes:</label>
                                            <textarea class="form-control" id="notes_input" name="notes_input" placeholder="Insert path notes here" rows="5" onchange="changeNotes()"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <button id="path_go_back" class="btn btn-blue" type="button" onclick="goBack()">Go Back</button>
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="row">
                        <div class="col">
                            <button id="launch_video_button" class="btn btn-blue btn-full-width my-2" onclick="launchVideoFromSession()"> Open Video <span class="oi oi-external-link px-2" title="Open Session Video" span></button>
                            <button id="viz_button" class="btn btn-gold btn-full-width my-2 d-none">Inter-Rater Reliability <span class="oi oi-people px-2" title="Inter-Rater Reliability Demo"></span></button>
                            <button id="irr_button" class="btn btn-gold btn-full-width my-2" style="white-space: normal; word-wrap:break-word;" onclick="location.href='https://ccoi.education.ufl.edu/newestccoi/api/ccoi_irr_viewer'">Inter-Rater Reliability <span class="oi oi-people px-2" title="Inter-Rater Reliability Demo"></span></button>
                        </div>
                    </div>
                    <div class="sticky-top">
                        <div id="demo_help_box" class="row pt-3">
                            <div class="col">
                                <div class="md-boxed-content light-blue-background">
                                    <h4>C-COI Observation Instructions</h4>
                                    <ol id="demo_help_ol">
                                        <li>Open the video</li>
                                        <li>Create observations, be sure to record time and relevant notes</li>
                                        <li>Use IRR viewer to compare session observations for accuracy.</li>
                                    </ol>
                                    <em>Note:</em> If you need further information on how to use the instrument, visit the <a href="/about#learn">CCOI Help Center</a> section or our <a target="_blank" href="/assets/files/CCOI_Code_Book.pdf">code book</a>.
                                </div>
                                <!-- <div class="embed-responsive embed-responsive-16by9" id="videoFrameContainer">
                                    Old div for the on page video container. Scrapped. 
                                </div> -->
                            </div>
                        </div>
                        <div class="row">
                            <div id="path_preview" class="col pt-3 pr-md-5 d-none">
                                <h4 id="path_preview_heading"></h4>
                                <ol id="path_preview_list"></ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
<script src="/js/jquery-3.4.1.min.js"></script>
<script src="/js/utility.js"></script>
<script src="/js/bootstrap.min.js"></script>
</body>

<!-- THIS IS THE SCRIPT BLOCK WITH THE AJAX STUFF -->
<script>
    
    let observationsObj = <?php echo json_encode($observations) ?>;
    let ssidObj = <?php echo json_encode($listOfSSID) ?>;
    let questionNodesObj = <?php echo json_encode($questionNodes) ?>;
    let pathNodesObj = <?php echo json_encode($pathNodes) ?>;
    //DANGGG pp
    console.log("Observations Object: \n")
    console.log(observationsObj);
    console.log("SSID Obj: \n");
    console.log(ssidObj);
    console.log("Question node obj: \n");
    console.log(questionNodesObj);
    console.log("Path Node Obj:\n")
    console.log(pathNodesObj);
    
    function GetAjaxReturnObject(mimetype) {
        var xmlHttp = null;
        if (window.XMLHttpRequest) { // Mozilla, Safari, ...
            xmlHttp = new XMLHttpRequest();
            if (xmlHttp.overrideMimeType) {
                xmlHttp.overrideMimeType(mimetype);
            }
        } else if (window.ActiveXObject) { // IE
            try {
                xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try {
                    xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {}
            }
        }
        return xmlHttp;
    }

    function getHTML(httpRequest) {
        if (httpRequest.readyState === 4) {
            if (httpRequest.status === 200) { // if buggy, check logs for firefox / OPTIONS instead of POST -- need same domain
                return httpRequest.responseText;
            }
        }
    }

    function ajaxIt() {
        var xmlHttp = GetAjaxReturnObject("text/html");
        if (xmlHttp == null) {
            alert("Your browser does not support AJAX!");
            return;
        }

        xmlHttp.onreadystatechange = function() {
            var data = getHTML(xmlHttp);
            if (data) {
                data = JSON.parse(data);
                console.log("AJAX returns this:");
                console.log(data);
                console.log("observations before:");
                console.log(observations);
                Object.entries(data).forEach(ssids => {
                    let oldSSID = ssids[0];
                    let newSSID = ssids[1];

                    observations[newSSID] = observations[oldSSID];
                    delete observations[oldSSID];
                });
                console.log("observations after:");
                console.log(observations);
                populateObsListOrdered();
            }
        };
        let extraText = '';
        if(isPlayground){
            extraText = '&isPlayground=1';
        }
        var sendStr = "updateObsEl=1"+extraText+"&id=" + sessionID + "&" + $.param({
            "observations": observations
        });

        //If this evaluates to true, we know that our sessionMeta has changes that need to be saved.
        if (sessionMeta.length !== 0) {
            console.log(sessionMeta);
            $.ajax({
                url: derServer + 'zpb_ajax.php?updateMeta=1&id=' + sessionID,
                type: 'POST',
                data: sessionMeta,
                //contentType: 'application/json; charset=utf-8',
                //dataType: 'json',
                async: false,
                success: function(msg) {
                    console.log("success");
                    alert("Session saved successfully.");
                    console.log($("#sessionTitle"))
                    $("#sessionTitle").text(sessionMeta.name);
                },
                error: function(err) {
                    console.log("Session failed to save. Please report this occurrence.");
                    //alert(err);
                },
            });
        }

        console.log("sendStr:");
        console.log(sendStr);
        var url = encodeURI(derServer + "zpb_ajax.php?" + sendStr);
        console.log(url);
        xmlHttp.open("POST", url, true);
        xmlHttp.setRequestHeader(
            "Content-Type",
            "application/x-www-form-urlencoded"
        );
        xmlHttp.send(sendStr);
    }
</script><!-- THIS IS THE SCRIPT BLOCK WITH THE AJAX STUFF -->







<!-- THIS IS THE SCRIPT BLOCK WITH THE NODE EDITING STUFF -->
<script>
    //onload
    $('button[data-toggle=collapse]').each(function() {
        console.log(this);
        var chevron = $(this).parent().next('div');
        if (chevron.hasClass('collapsed')) {
            $(this).children('span').removeClass('oi-chevron-down').addClass('oi-chevron-right');
        } else {
            $(this).children('span').removeClass('oi-chevron-right').addClass('oi-chevron-down');
        }
    });

    // click event
    $('span[data-toggle=collapse]').click(function() {
        // swap chevron
        $(this).children('span').toggleClass('oi-chevron-down oi-chevron-up');
    });
    // Global variables: stuff thats so generally applicable and needs to be accessed in a bunch of places
    // ================================================================================================
    let currentQuestionID = -1; // This is the nodeID of the question node that is currently loaded
    let currentObs = 0; // This is the ID of the observation that is currently being edited
    let nodeInObsIndex = 0; // This is the index of the node currently being edited within its observation
    let newObsID = -1; // This is the ID of new observations created during this user's session. To guarantee it is unique from IDs on the table (and it is recognizable as new), it counts down from -1
    let editedInfo = {}; //  Object that contains all of the information that needs to be sent in AJAX
    //let startSessionMeta = fetchMetaFields(); // Object that contains all of the meta information present at the start of a session load. Will be used for comparison to end of session !sessionMeta prior to start an AJAX request, because why bother?
    var sessionMeta = {}; //Current session meta information object, this will be updated as the meta gets updated (if at all), and later used for comparison to check if a save is necessary. 

    // SECTION FOR CODE THAT CREATES THE OBSERVATION LIST
    // ================================================================================================
    function populateObsList() {
        //Empty out the observation list
        $("#path_list").empty();

        //For each observation, add a dropdown

        Object.entries(observations).forEach((currentObs, obsIndex) => {
            $("#path_list").append(`
            <div id="observation-list-${obsIndex}" class="path-listing-container">
                <h5 data-index="${obsIndex}" class="path-listing-header">Observation #${obsIndex+1}: ${currentObs[1]['name']}
                    <a class="btn-link path-edit-icon" href="javascript:void(0)" data-index="${obsIndex}" onclick="editObservation(${currentObs[0]})"><span class="oi oi-pencil px-3" title="Edit Path" aria-hidden="true"></span></a>
                    <a class="btn-link path-delete-icon" href="javascript:void(0)" data-index="${obsIndex}" onclick="removeObservation(${currentObs[0]})"><span class="oi oi-trash" title="Delete Path" aria-hidden="true"></span></a>
                    <button class="btn-link float-right path-dropdown-btn" data-toggle="collapse" data-target="#path_drop_${obsIndex}" aria-expanded="false"><span class="oi oi-chevron-bottom" title="Show Path Steps" aria-hidden="true"></span></button>
                </h5>
                <ol class="collapse" id="path_drop_${obsIndex}" style=""></ol>
            </div>`);

            //For each node in an observation, add a listing
            Object.entries(currentObs[1]['nodes']).forEach((currentNode, nodeIndex) => {
                //Grab time to print
                let currentSeconds = parseInt(currentNode[1]['seconds']);
                let minutesToPrint = (Math.floor(currentSeconds / 60)).toString();
                minutesToPrint = minutesToPrint.padStart(2, '0');
                let secondsToPrint = (currentSeconds % 60).toString();
                secondsToPrint = secondsToPrint.padStart(2, '0');

                //Grab notes to print
                let notesText = '';
                if (currentNode[1]['notes'] != null) {
                    if (currentNode[1]['notes'] != "") {
                        notesText = `[${currentNode[1]['notes']}]`;
                    }
                }

                //Include node data in print if possible
                if (currentNode[1]['nodepathid'] == "0") { //If no node data set yet, don't print node data
                    $("#path_drop_" + obsIndex).append(`<li>(${minutesToPrint}:${secondsToPrint}) ${notesText}</li>`);
                } else {
                    let currentNodeData = pathNodes[parseInt(currentNode[1]['nodepathid'])];
                    if (currentNodeData == undefined) { //If there is error grabbing node data, don't print node data
                        $("#path_drop_" + obsIndex).append(`<li>(${minutesToPrint}:${secondsToPrint}) ${notesText}</li>`);
                    } else { //If there is no error grabbing node data, print node data (LETS GOOOOOOO)
                        $("#path_drop_" + obsIndex).append(`<li>(${minutesToPrint}:${secondsToPrint}) ${currentNodeData['code']}: ${currentNodeData['title']} ${notesText}</li>`);
                    }
                }

            });
        });
    }

    function populateObsListOrdered() {
        //Empty out the observation list
        $("#path_list").empty();

        //Find order of ssids based on time of first node
        keysSorted = Object.keys(observations).sort(function(a,b){return parseInt(observations[a]['nodes'][0]['seconds'])-parseInt(observations[b]['nodes'][0]['seconds'])});
        //console.log("keys sorted by time:"); console.log(keysSorted);

        //For each observation, add a dropdown
        for(let obsIndex = 0; obsIndex < keysSorted.length; obsIndex++){
        //Object.entries(observations).forEach((currentObs, obsIndex) => {
            currentObs = [keysSorted[obsIndex], observations[keysSorted[obsIndex]]];

            $("#path_list").append(`
            <div id="observation-list-${obsIndex}" class="path-listing-container">
                <h5 data-index="${obsIndex}" class="path-listing-header">Observation #${obsIndex+1}: ${currentObs[1]['name']}
                    <a class="btn-link path-edit-icon" href="javascript:void(0)" data-index="${obsIndex}" onclick="editObservation(${currentObs[0]})"><span class="oi oi-pencil px-3" title="Edit Path" aria-hidden="true"></span></a>
                    <a class="btn-link path-delete-icon" href="javascript:void(0)" data-index="${obsIndex}" onclick="removeObservation(${currentObs[0]})"><span class="oi oi-trash" title="Delete Path" aria-hidden="true"></span></a>
                    <button class="btn-link float-right path-dropdown-btn" data-toggle="collapse" data-target="#path_drop_${obsIndex}" aria-expanded="false"><span class="oi oi-chevron-bottom" title="Show Path Steps" aria-hidden="true"></span></button>
                </h5>
                <ol class="collapse" id="path_drop_${obsIndex}" style=""></ol>
            </div>`);

            //For each node in an observation, add a listing
            Object.entries(currentObs[1]['nodes']).forEach((currentNode, nodeIndex) => {
                //Grab time to print
                let currentSeconds = parseInt(currentNode[1]['seconds']);
                let minutesToPrint = (Math.floor(currentSeconds / 60)).toString();
                minutesToPrint = minutesToPrint.padStart(2, '0');
                let secondsToPrint = (currentSeconds % 60).toString();
                secondsToPrint = secondsToPrint.padStart(2, '0');

                //Grab notes to print
                let notesText = '';
                if (currentNode[1]['notes'] != null) {
                    if (currentNode[1]['notes'] != "") {
                        notesText = `[${currentNode[1]['notes']}]`;
                    }
                }

                //Include node data in print if possible
                if (currentNode[1]['nodepathid'] == "0") { //If no node data set yet, don't print node data
                    $("#path_drop_" + obsIndex).append(`<li>(${minutesToPrint}:${secondsToPrint}) ${notesText}</li>`);
                } else {
                    let currentNodeData = pathNodes[parseInt(currentNode[1]['nodepathid'])];
                    if (currentNodeData == undefined) { //If there is error grabbing node data, don't print node data
                        $("#path_drop_" + obsIndex).append(`<li>(${minutesToPrint}:${secondsToPrint}) ${notesText}</li>`);
                    } else { //If there is no error grabbing node data, print node data (LETS GOOOOOOO)
                        let code = currentNodeData['code'];
                        if (code == null){
                            code = "M-D";
                        }
                        $("#path_drop_" + obsIndex).append(`<li>(${minutesToPrint}:${secondsToPrint}) ${code}: ${currentNodeData['title']} <br>${notesText}</li>`);
                    }
                }

            });
        }
        
    }

    //populateObsList();
    populateObsListOrdered();


    // SECTION FOR CODE THAT OPENS THE NODE EDITOR
    // ================================================================================================
    function addObservation() {
        //console.log("observations before:"); console.log(observations); 
        //console.log("insertId:"); console.log(insertId);

        //Create a new observation in the local data with a unique ssid and filler info
        console.log(observations);
        observations[(newObsID).toString()] = {
            name: "New Observation",
            notes: null,
            nodes: []
        };

        //Setup node editor to start editing this new observation
        currentObs = newObsID;
        nodeInObsIndex = 0;

        //Decrement global unique obsID so we don't use it again
        newObsID -= 1;

        //Start editing this new observation
        startEditingNodes();
        setupNodeInfoWithGroups(Object.keys(questionNodes)[0]);
    }

    function removeObservation(ssid) {
        if(confirm("Are you sure you want to delete this observation?")){
            delete observations[ssid];
        }
        populateObsListOrdered();
    }

    function editObservation(ssID) {
        currentObs = ssID;
        nodeInObsIndex = 0;
        startEditingNodes();
        setupNodeInfoWithGroups(Object.keys(questionNodes)[0]);
        clearPathPreview();
        initializePathPreview();
        fillPathPreview();
    }

    function setupNodeInfo(structIndex) {
        //Set the obs label
        $("#path_label").val(observations[currentObs]['name']);

        //Set the question title
        $("#path_title").text(questionNodes[structIndex]['title']);

        //Empty the list of choices
        $("#branch_container").empty();
        $("#branch_container").append('<form id="branch_radio_form" class="col-12 pt-3" action="javascript:void(0)"></form>');

        //Set global identifier of current question node ID
        currentQuestionID = structIndex;

        //Add all of the answers for the associated question
        Object.entries(questionNodes[structIndex]['choices']).forEach(value => {
            //console.log("index: "+index);
            //console.log("value:");
            //console.log(value);
            if (value[0] == "0") { //Error case, log it
                console.log("when value[0] == 0, value[1] ==");
                console.log(value[1]);
            } else {
                $("#branch_radio_form").append(`
                <p onclick="selectRadio(${value[1]});">
                    <input type="radio" name="choiceRadio" id="choiceRadio${value[1]}" value="${value[1]}">
                    <label for="choiceRadio${value[1]}" class="choiceOfList">(${value[0]}) ${pathNodes[value[1]]['title']}</label>
                </p>`);
            }
        });

        //Attempt to autofill the existing data
        autoFill();
    }

    function setupNodeInfoWithGroups(structIndex) {
        let nodesInGroups = {};

        //console.log("iterating through question nodes");
        Object.entries(questionNodes[structIndex]['choices']).forEach(value => {
            //console.log("questionNodes[structIndex]['choices'] current value");
            //console.log(value[1]);
            if(pathNodes[value[1]]['choicegroup'] == null){
                if(nodesInGroups[Number.MAX_SAFE_INTEGER]){
                    nodesInGroups[Number.MAX_SAFE_INTEGER].push(value[1]);
                }
                else{
                    nodesInGroups[Number.MAX_SAFE_INTEGER] = [value[1]];
                }
            }
            else {
                if(nodesInGroups[pathNodes[value[1]]['choicegroup']]){
                    nodesInGroups[pathNodes[value[1]]['choicegroup']].push(value[1]);
                }
                else{
                    nodesInGroups[pathNodes[value[1]]['choicegroup']] = [value[1]];
                }
            }
        });
        //console.log("nodesInGroups:");
        //console.log(nodesInGroups);
        //console.log("nodesInGroups.length");
        //console.log(Object.keys(nodesInGroups).length);

        if(Object.keys(nodesInGroups).length == 1){
            console.log("no choice groups");
            setupNodeInfo(structIndex);
            return;
        }
        console.log("there are choice groups");

        //Set the obs label
        $("#path_label").val(observations[currentObs]['name']);

        //Set the question title
        $("#path_title").text(questionNodes[structIndex]['title']);

        //Empty the list of choices
        $("#branch_container").empty();
        $("#branch_container").append('<form id="branch_radio_form" class="col-12 pt-3" action="javascript:void(0)">   <div class="accordion" id="branch_group_accordion"></div>   </form>');

        //Set global identifier of current question node ID
        currentQuestionID = structIndex;
        let currentChoiceNum = 1;

        //console.log("iterating through node groups");
        Object.entries(nodesInGroups).forEach( (value, index) => {
            /*
            console.log("value:");
            console.log(value);
            console.log("index:");
            console.log(index);
            */
            if(value[0] == Number.MAX_SAFE_INTEGER){
                //console.log("not in a node group");
                value[1].forEach( value2 => {
                    //console.log("value2:");
                    //console.log(value2);
                    $("#branch_radio_form").append(`
                    <p onclick="selectRadio(${value2});">
                        <input type="radio" name="choiceRadio" id="choiceRadio${value2}" value="${value2}">
                        <label for="choiceRadio${value2}" class="choiceOfList">(${currentChoiceNum}) ${pathNodes[value2]['title']}</label>
                    </p>`);
                    currentChoiceNum++;
                });
            }
            else{
                //console.log("in a node group");
                $("#branch_group_accordion").append(`<div class="card mb-3">
                    <div class="card-header" id="heading_${value[0]}">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_${value[0]}" aria-expanded="false" aria-controls="collapse_${value[0]}">
                                ${choiceGroups[value[0]]}
                            </button>
                        </h5>
                    </div>
                    <div id="collapse_${value[0]}" class="collapse" aria-labelledby="heading_${value[0]}" data-parent="#branch_group_accordion" style="">
                        <div id="collapse_${value[0]}_body" class="card-body"></div>
                    </div>
                </div>`);
                let targetCollapse = `#collapse_${value[0]}_body`;
                //console.log("targetCollapse:");
                //console.log(targetCollapse);
                value[1].forEach( value2 => {
                    //console.log("value2:");
                    //console.log(value2);
                    $(targetCollapse).append(`
                    <p onclick="selectRadio(${value2});">
                        <input type="radio" name="choiceRadio" id="choiceRadio${value2}" value="${value2}">
                        <label for="choiceRadio${value2}" class="choiceOfList">(${currentChoiceNum}) ${pathNodes[value2]['title']}</label>
                    </p>`);
                    currentChoiceNum++;
                });
            }
        });
        
        //Attempt to autofill the existing data
        autoFill();
    }

    function autoFill() {
        //Debug info
        /*
        console.log("currentObs");
        console.log(currentObs);
        console.log("nodeInObsIndex");
        console.log(nodeInObsIndex);
        */
        //Try to load current choice
        if (observations[currentObs]['nodes'][nodeInObsIndex] == undefined) {
            let secondsForNewNode = 0;
            if (observations[currentObs]['nodes'][nodeInObsIndex-1]) {
                secondsForNewNode = observations[currentObs]['nodes'][nodeInObsIndex-1]['seconds'];
            }
            observations[currentObs]['nodes'][nodeInObsIndex] = {
                nodepathid: "0",
                seconds: secondsForNewNode,
                notes: null
            };
        }
        try {
            let existingChoiceID = observations[currentObs]['nodes'][nodeInObsIndex]['nodepathid'];
            //console.log("pnid of current choice");
            //console.log(existingChoiceID);
            $("#choiceRadio" + existingChoiceID).prop("checked", true);
        } catch {}
        //Try to load current timestamp
        try {
            let existingSeconds = parseInt(observations[currentObs]['nodes'][nodeInObsIndex]['seconds']);
            $("#timestamp_input_minutes").val(Math.floor(existingSeconds / 60));
            $("#timestamp_input_seconds").val(existingSeconds % 60);
        } catch {}
        //Try to load current notes
        try {
            let existingNotes = observations[currentObs]['nodes'][nodeInObsIndex]['notes'];
            $("#notes_input").val(existingNotes);
        } catch (error) {
            $("#notes_input").val("");
        }
    }
    //TODO : Finish handling this jazz cigar.
    function fetchMetaFields() {
        sessionMeta = {
            'name': $("#session_title").val(),
            'studentid': $("#studentID").val(),
            'placetime': $("#session_date").val(),
            'notes': $("#session_notes").val(),
        };


        console.log(sessionMeta);
    }

    // SECTION FOR CODE THAT NAVIGATES THE NODE EDITOR
    // ================================================================================================
    function startEditingNodes() {
        if (!DOM.dom_group_1.classList.contains('d-none')) {
            DOM.dom_group_1.classList.add('d-none');
        }
        DOM.path_input.classList.remove('d-none');

        // initializePathPreview();
        //Commenting out until ready to test.


    }

    function hideNodeEditor() {
        if (!DOM.path_input.classList.contains('d-none')) {
            DOM.path_input.classList.add('d-none');
        }
        populateObsListOrdered();
        DOM.dom_group_1.classList.remove('d-none');
    }
    //unhides the path preview 

    // observations array contains a full list of the observations, with currentobs pointing to the currently selected observation. EX: observations[currentObs]['nodepathid']
    // The information stored within this data structure is sparse. However, it does provide a path node id, which enables us to 
    // find the necessary information about the node in the 'pathNodes' data structure. We can use pnid to isolate the information we're wanting:
    // EX: pathNodes[pnid]['title'] will give us the text associated with the option. 
    function initializePathPreview() {
        if($("#path_preview").hasClass('d-none')) {$("#path_preview").removeClass('d-none')};
    }

    function clearPathPreview() {
        $("#path_preview_list").empty();
        $("#path_preview").addClass('d-none');
    }
    // Fills in the path preview with an observations already existing OEs
    function fillPathPreview() {
        Object.entries(observations[currentObs]['nodes']).forEach(entry => {
            console.log(entry);
            let pnid = entry[1]['nodepathid'];
            let title = pathNodes[pnid]['title'];
            let code = pathNodes[pnid]['code'];
            console.log(title);
            $('#path_preview_list').append(
                '<li>' +'(' + code +') - ' + title + '</li>'
            )
        })
    }

    function appendPathPreview(selectedValue) {
        let title = pathNodes[selectedValue]['code'];
        let code = pathNode[pnid]['code'];

        console.log(title);
        console.log(code);

        $('#path_preview_list').append(
            '<li>' + '(' + code + ') - ' + title + '</li>'
        )

    }

    function proceed() {
        let selectionValue = $("#branch_radio_form").find('input[name="choiceRadio"]:checked').val();
       
        if(selectionValue === null || selectionValue === undefined) {
            alert("Please select an option before proceeding.")
            return;
        }
        
        clearPathPreview();
        initializePathPreview();
        fillPathPreview();
        
        // * Path Preview Logic
        
        // $("#path_preview_list").append(
        //    '<li>' + selectionValue + '</li>'
        // );
            // Commenting out until ready to test. 

        //$("#path_preview_list").append(
          //  <li> selectionValue </li>
        //);
        //TODO: Check if extra data is needed for choice
        //TODO: If necessary, ask for extra data

        //Load info for next question
        let nextQuestionNode = pathNodes[selectionValue]['node2'];

        //Increment the index of the node within the observation we're viewing
        nodeInObsIndex += 1;

        //load next node or return to observation viewer (depending on if path terminates)
        if (nextQuestionNode == null) {
            console.log("Here is the observation object as it exists currently: \n")
            console.log(observations[currentObs]);
            observations[currentObs]['nodes'].length = nodeInObsIndex;
            hideNodeEditor();
            //If the path is terminated after the current selection, we need to clear the path preview list and hide it.
            // clearPathPreview();
            //Commenting out until ready to test. 
        } else {
            setupNodeInfoWithGroups(nextQuestionNode);
        }
    }

    function goBack() {
        if (nodeInObsIndex == 0) {
            hideNodeEditor();
        } else {
            nodeInObsIndex -= 1;
            /*
            console.log("goBack() VARIABLE DUMP");
            console.log("nodeInObsIndex");
            console.log(nodeInObsIndex);
            console.log("currentObs");
            console.log(currentObs);
            console.log("observations[currentObs]['nodes'][nodeInObsIndex]['nodepathid]");
            console.log(observations[currentObs]['nodes'][nodeInObsIndex]['nodepathid']);
            console.log("pathNodes[observations[currentObs]['nodes'][nodeInObsIndex]['nodepathid']]['node1']");
            console.log(pathNodes[observations[currentObs]['nodes'][nodeInObsIndex]['nodepathid']]['node1']);
            */
            setupNodeInfoWithGroups(pathNodes[observations[currentObs]['nodes'][nodeInObsIndex]['nodepathid']]['node1']);
        }
    }

    // SECTION FOR CODE THAT SAVES STUFF THAT HAPPENS IN THE NODE EDITOR
    // ================================================================================================
    //! BRANDON THIS IS IMPORTANT TO WHAT YOU'RE DOING
    function selectRadio(selectedNum) {
        //Get pnID of newly selected choice
        let selectionValue = $("#branch_radio_form").find('input[name="choiceRadio"]:checked').val();

        //TODO: Check if changing answer changes next node
        //  If yes, confirm with user that choice will erase all data
        //      If they confirm, erase all later data, then continue with this function
        //      If they don't, reselect old choice and break
        //  If no, continue with this function
        if(observations[currentObs]['nodes'][nodeInObsIndex+1]){
            let oldValue = observations[currentObs]['nodes'][nodeInObsIndex+1]['nodepathid'];
            console.log("oldValue: "+oldValue)
            if(pathNodes[selectionValue]['node2'] != pathNodes[selectionValue]['node1'])
                if( confirm(`This will change the path of the current observation. As a result, all later nodes will be deleted. \r\n\r\nAre you sure you want to record this observation element?`) ){
                    //This is where the deleting will happen
                    observations[currentObs]['nodes'].length = (nodeInObsIndex+1);
                    console.log("we in");
                }
                else{
                    $("#choiceRadio"+oldValue).prop("checked", true);
                    return;
                }
        }

        //Log what the node was before the change
        console.log("observations[currentObs]['nodes'][nodeInObsIndex] before: ");
        console.log(observations[currentObs]['nodes'][nodeInObsIndex]);
        if (observations[currentObs]['nodes'][nodeInObsIndex] == undefined) {
            observations[currentObs]['nodes'][nodeInObsIndex] = {};
        }

        //Old way
        /*
        // Store the values that need to be brought over
        let nodeSecs = observations[currentObs]['nodes'][nodeInObsIndex]['seconds'];

        // Give node the info of the pathnode
        observations[currentObs]['nodes'][nodeInObsIndex] = pathNodes[selectedNum];
        // Restore information
        observations[currentObs]['nodes'][nodeInObsIndex]['seconds'] = nodeSecs;
        */

        //New way
        observations[currentObs]['nodes'][nodeInObsIndex]['nodepathid'] = selectionValue;
        try {
            delete observations[currentObs]['nodes'][nodeInObsIndex]['extra'];
        } catch {
            //There was no extra to delete
        }

        if(pathNodes[selectionValue]['extra']){
            let extraData = prompt("Please enter this observation element\'s extra data:\r\n\r\n"+pathNodes[selectionValue]['extra']);
            if(extraData != "" && extraData != null){
                observations[currentObs]['nodes'][nodeInObsIndex]['extra'] = extraData;
            }
        }

        //Log what the node is after the change
        console.log("observations[currentObs]['nodes'][nodeInObsIndex] after: ");
        console.log(observations[currentObs]['nodes'][nodeInObsIndex]);
    }

    function changeTime() {
        let minutes = parseInt($("#timestamp_input_minutes").val());
        if (minutes == NaN) {
            minutes = 0;
        }
        let seconds = parseInt($("#timestamp_input_seconds").val());
        if (seconds == NaN) {
            seconds = 0;
        }
        let totalSeconds = seconds + (60 * minutes);

        observations[currentObs]['nodes'][nodeInObsIndex]['seconds'] = totalSeconds;
    }

    function changeObsLabel() {
        let newLabel = $("#path_label").val();
        observations[currentObs]['name'] = newLabel;
    }

    function changeNotes() {
        let newNotes = $("#notes_input").val();
        console.log("newNotes = "+newNotes);
        if (newNotes == "") {
            newNotes = null;
        }
        observations[currentObs]['nodes'][nodeInObsIndex]['notes'] = newNotes;
        console.log("observations[currentObs]['nodes'][nodeInObsIndex] after: ");
        console.log(observations[currentObs]['nodes'][nodeInObsIndex]);
    }

    // SECTION FOR VIDEO CODE
    // ================================================================================================

    function launchVideoFromSession() {
        let videoID = $("#session_video_url").val();
        //TODO: add back isDemo() functionality for presentation purposes. 
        //Using JSON_encode here in order to ensure the string is formatted correctly for JS. 
        let scramble =
            <?php
            echo (json_encode($videoInfo['scramble']));
            ?>;

        let url =
            <?php
            echo (json_encode($videoInfo['url']))
            ?>;

        let title =
            <?php
            echo (json_encode($videoInfo['name']))
            ?>

        console.log(url);
        popoutWindow = window.open("/video-player"); // to avoid browser pop up blockers, we have to load the pop up window directly in the on click, not in the ajax call.
        // Add click event to proceed and play button now that we have a video
        //! The way this function was originally written assumes that the user would be clicking 'Open Video' AFTER clicking add observation.
        //! Need to remedy this: DOMContentLoaded() needs to be used in order to ensure the button is already loaded. Break this out to its own function
        //! Once path editing works normally. 
        /*$(DOM.proceed_and_play_button).click(function() {
            submitBranch();
            popoutWindow.video.play();
        });*/
        popoutListeners();
        console.log(url);
        console.log(scramble);
        console.log(title);
        let videoSRC = "/ccoivids/" + scramble + "_" + url;
        popoutWindow.src = videoSRC;
        popoutWindow.videoTitle = title;

    }

    function popoutListeners() {
        $("#vid_speed_1x").click(function() {
            popoutWindow.changeSpeed(1.0);
        });
        $("#vid_speed_1_5x").click(function() {
            popoutWindow.changeSpeed(1.5);
        });
        $("#vid_speed_2x").click(function() {
            popoutWindow.changeSpeed(2.0);
        });
    }

    // SECTION FOR DOM CODE
    // ================================================================================================

    var DOM = {};
    var popoutWindow;
    var IDs = [
        'path_input',
        'dom_group_1'
    ];

    var numIDs = IDs.length;
    for (var i = 0; i < numIDs; ++i) {
        var ID = IDs[i];
        DOM[ID] = document.getElementById(ID);
    }
</script>

</html>

<?php
function getAppVideos($id)
{
    if (!empty($id) && is_numeric($id)) {
        $db = $GLOBALS["db"];
        if (!empty($_GET['isPlayground'])) {
            $return = mysqli_query($db, "SELECT v.id, v.name, v.url FROM tbVideos v LEFT JOIN tbPeopleAppPlaygrounds pg ON pg.appid = v.appid WHERE pg.id = 1 AND pg.inactive IS NULL");
            while ($d = mysqli_fetch_assoc($return)) {
                //print_r($d);
                $appVideos = $d;
            }
        } else {
            return;
        }

        return $appVideos;
    }
}
function getSessionInfo($id)
{
    if (!empty($id) && is_numeric($id)) {
        $db = $GLOBALS["db"];

        // If playground observation, pull from playground stuff
        if (!empty($_GET['isPlayground'])) {
            $return = mysqli_query($db, "SELECT s.*, v.url FROM tbPlaygrounds s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id = $id AND s.inactive IS NULL");        // ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
            while ($d = mysqli_fetch_assoc($return)) {
                $session = $d;
            }        //print_r($playgrounds);		//echo "<br>playground: "; var_dump($d);
        }

        //If not playgroung observation, pull from research stuff
        else {
            $return = mysqli_query($db, "SELECT s.*, v.url FROM tbSessions s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id = $id AND s.inactive IS NULL");                // ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
            while ($d = mysqli_fetch_assoc($return)) {
                $session = $d;
            }        //echo "<br>session: "; var_dump($d);
        }
        return $session;
    } else
        return "<br>Session isn't valid :(";
}

function getVideoInfo($id)
{
    if (!empty($id) && is_numeric($id)) {
        $db = $GLOBALS["db"];
        $query = "SELECT url, scramble, name FROM tbVideos WHERE id=(SELECT videoid FROM tbSessions WHERE id=$id)";
        $return = mysqli_query($db, $query);
        $return = mysqli_fetch_assoc($return);

        return $return;
    }
}

function getChoiceGroups($pathid)
{
    if (!empty($pathid) && is_numeric($pathid)) {
        $db = $GLOBALS["db"];
        $query = "SELECT id, name FROM tbChoiceGroups WHERE pathid=$pathid";
        $return = mysqli_query($db, $query);
        while ($d = mysqli_fetch_assoc($return)) {
            $choiceGroups[$d['id']] = $d['name'];
        }        //echo "<br>session: "; var_dump($d);
        return $choiceGroups;
    }
}
?>