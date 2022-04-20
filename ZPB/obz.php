<?php
$page = "Session";
$dataTarget = "#demo_help_box";
$dataOffset = "10";
$dataSpy = "scroll";
include '../includes/header.php';
include '../api/ccoi_dbhookup.php';
$id = $_GET['id'] + 0;

//TODO: check that they are allowed in here
$session = getSessionInfo($id); //defined below
$appVideos = getAppVideos($id); //Defined below
$videoInfo = getVideoInfo($id);
//print_r($appVideos);
//print_r($session);
//echo "<br>session: "; print_r($session);

if (isset($_GET['isPlayground'])) {
    $tbName = 'Playground';
}
else {
    $tbName = 'Session';
}

//$return = mysqli_query($db, "SELECT SA.*, SS.id as ssid, SS.subnum, SS.name as ssname, SS.notes as ssnotes, PN.id as pnid, PN.node1, PN.choice, PN.node2, PN.choicegroup, PN.pathtype, PN.nsubgroup FROM tbSessionActivity SA, tbPathNodes PN, tbSubSessions SS WHERE SA.sessionid = $id AND SA.nodepathid=PN.id AND SA.ssid=SS.id ORDER BY SA.sessionid, SA.seconds");
$return = mysqli_query($db, "SELECT ssid, extra, nodepathid, seconds, notes FROM tb{$tbName}Activity WHERE sessionid = $id");
while ($d = mysqli_fetch_assoc($return)) {
    $subsessions[$d['ssid']]['nodes'][] = $d;
    $listOfSSID[$d['ssid']] = 1;
}
$ssidListText = implode(',', array_keys($listOfSSID));
$return = mysqli_query($db, "SELECT name, notes, id FROM tbSub{$tbName}s WHERE id IN ({$ssidListText})");
while ($d = mysqli_fetch_assoc($return)) { /*$subsessions[$d['ssid']][d['id']]=$d;*/
    $subsessions[$d['id']]['name'] = $d['name'];
    $subsessions[$d['id']]['notes'] = $d['notes'];
}
//echo "<br><br>subsessions: "; var_dump($subsessions);

$return = mysqli_query($db, "SELECT PN.node1, N.title FROM tbPathNodes PN LEFT JOIN tbNodes N ON PN.node1 = N.id WHERE PN.pathid = '{$session['pathid']}' AND PN.choice = 0 AND PN.inactive IS NULL AND N.inactive IS NULL");
while ($d = mysqli_fetch_assoc($return)) {
    $questionNodes[$d['node1']]['title']= $d['title'];
}

$return = mysqli_query($db, "SELECT PN.id as pnid, PN.node1, PN.choice, PN.choiceorder, PN.node2, N.code, N.title, N.extra, N.aside FROM tbPathNodes PN LEFT JOIN tbNodes N ON PN.choice = N.id WHERE PN.pathid = '{$session['pathid']}' AND PN.choice != 0 AND PN.inactive IS NULL AND N.inactive IS NULL");
while ($d = mysqli_fetch_assoc($return)) {
    $questionNodes[$d['node1']]['choices'][$d['choiceorder']] = $d['pnid'];
    $pathNodes[$d['pnid']]=$d;
}
?>
<script>
    var sessionID = <?php echo $id; ?>;
    var subsessions = <?php echo json_encode($subsessions); ?>;
    console.log("subsessions:"); console.log(subsessions);
    var questionNodes = <?php echo json_encode($questionNodes); ?>;
    console.log("questionNodes:"); console.log(questionNodes);
    var pathNodes = <?php echo json_encode($pathNodes); ?>;
    console.log("pathNodes:"); console.log(pathNodes);
</script>
<main role="main">
    <div class="container-fluid">
        <div class="container">
            <div id="session_go_back" class="row pt-3">
                <div class="col">
                    <a class="underlined-btn" href="dashboard"><span class="oi oi-arrow-thick-left mr-2"></span><span class="btn-text">Back to Session Select</span></a>
                </div>
            </div>
            <div class="row py-5">
                <div class="col-md-8">
                    <div class="row pr-md-5">
                        <div class="col-md-8 col-12">
                            <h1 class="red-font" id="sessionTitle"><?php echo $session['name']; ?></h1>
                            <h5 style="text-transform: none;">Select an observation to view or edit its responsess</h5>
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
                                            Edit Session Meta Data
                                        </button>
                                    </h5>
                                </div>

                                <div id="session_meta_collapse" class="collapse" aria-labelledby="session_meta_collapse_heading" data-parent="#path_start">
                                    <div class="col py-3">
                                        <form id="session_meta_form" method="post" action="javascript:void(0)">
                                            <div class="row">
                                                <div class="form-group col">
                                                    <label for="session_title">Session Name</label>
                                                    <input placeholder="Session Name" id="session_title" name="session_title" type="text" class="form-control" value="<?php echo $session['name'] ?>">
                                                </div>
                                                <div class="form-group col">
                                                    <label for="studentID">Student ID</label>
                                                    <input placeholder="Student ID" id="studentID" name="studentID" type="text" class="form-control" value="<?php echo $session['studentid'] ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col">
                                                    <label for="session_date">Coding Date</label>
                                                    <input id="session_date" name="date" type="date" class="datepicker" value="<?php echo $session['placetime'] ?>">
                                                </div>
                                                <div class="form-group col">
                                                    <label for="session_video_title">Video</label>
                                                    <input type="text" id="session_video_title" name="session_video_title" value="Demo 2020-5-15 C01" class="fakeInput" placeholder="Demo Video" disabled=""> <!-- TODO: GET VIDEO NAME FROM ID -->
                                                    <input type="hidden" id="session_video_url" name="session_video_url" value="<?php echo $session['url'] ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="block" for="session_notes" id="session_notes_label">Session Notes</label>
                                                <textarea id="session_notes" name="session_notes" class="form-control"><?php echo $session['notes'] ?></textarea>
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
                                            <input type="text" class="form-control" id="path_label" name="path_label" placeholder="Example Label">
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
                                            <textarea class="form-control" id="notes_input" name="notes_input" placeholder="Insert path notes here" rows="5"></textarea>
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
                            <button id="irr_button" class="btn btn-gold btn-full-width my-2">Inter-Rater Reliability <span class="oi oi-people px-2" title="Inter-Rater Reliability Demo"></span></button>
                        </div>
                    </div>
                    <div class="sticky-top">
                        <div id="demo_help_box" class="row pt-3">
                            <div class="col">
                                <div class="md-boxed-content light-blue-background">
                                    <h4>C-COI Demo Instructions</h4>
                                    <ol id="demo_help_ol">
                                        <li>Click Add Session button to begin</li>
                                        <li>Click the Pencil Icon to edit the session</li>
                                        <li>Open video above and begin observing</li>
                                    </ol>
                                    <em>Note:</em> If you need further information on how to use the instrument, visit the <a href="/about#learn">CCOI Help Center</a> section or our <a target="_blank" href="/assets/files/CCOI_Code_Book.pdf">code book</a>.
                                </div>
                                <div class="embed-responsive embed-responsive-16by9" id="videoFrameContainer">

                                </div>
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
<!--
<script src="/js/zpbccoi.js"></script>
<script src="/js/ccoi.js"></script>
<script src="/js/observation.js"></script>
<script src="/js/ccoi-data-model.js"></script>
<script src="/js/observe.js"></script>
                            -->
</body>

<!-- THIS IS THE SCRIPT BLOCK WITH THE AJAX STUFF -->
<script>
    var derServer = 'https://ccoi-dev.education.ufl.edu/';

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
</script><!-- THIS IS THE SCRIPT BLOCK WITH THE AJAX STUFF -->


<!-- THIS IS THE SCRIPT BLOCK WITH THE NODE EDITING STUFF -->
<script>
    // Global variables: stuff thats so generally applicable and needs to be accessed in a bunch of places
    let currentQuestionID = -1;     // This is the nodeID of the question node that is currently loaded
    let currentObs = 0;         // This is the ID of the observation that is currently being edited
    let nodeInObsIndex = 0;     // This is the index of the node currently being edited within its observation
    let newObsID = -1;         // This is the ID of new subsessions created during this user's session. To guarantee it is unique from IDs on the table (and it is recognizable as new), it counts down from -1

    function populateObsList() {
        //Empty out the observation list
        $("#path_list").empty();

        //For each observation, add a dropdown
        Object.entries(subsessions).forEach((currentObs, obsIndex) => {
            $("#path_list").append(`
            <div id="observation-list-${obsIndex}" class="path-listing-container">
                <h5 data-index="${obsIndex}" class="path-listing-header">Observation #${obsIndex+1}: ${currentObs[1]['name']}
                    <a class="btn-link path-edit-icon" href="javascript:void(0)" data-index="${obsIndex}" onclick="editObservation(${currentObs[0]})"><span class="oi oi-pencil px-3" title="Edit Path" aria-hidden="true"></span></a>
                    <a class="btn-link path-delete-icon" href="javascript:void(0)" data-index="${obsIndex}"><span class="oi oi-trash" title="Delete Path" aria-hidden="true"></span></a>
                    <button class="btn-link float-right path-dropdown-btn" data-toggle="collapse" data-target="#path_drop_${obsIndex}" aria-expanded="false"><span class="oi oi-chevron-top" title="Show Path Steps" aria-hidden="true"></span></button>
                </h5>
                <ol class="collapse" id="path_drop_${obsIndex}" style=""></ol>
            </div>`);

            //For each node in an observation, add a listing
            Object.entries(currentObs[1]['nodes']).forEach((currentNode, nodeIndex) => {
                //Grab time to print
                let currentSeconds = parseInt(currentNode[1]['seconds']);
                let minutesToPrint = (Math.floor(currentSeconds / 60)).toString();  minutesToPrint = minutesToPrint.padStart(2, '0');
                let secondsToPrint = (currentSeconds % 60).toString();              secondsToPrint = secondsToPrint.padStart(2, '0');

                //Grab notes to print
                let notesText = '';
                if (currentNode[1]['notes'] != null) {
                    notesText = `[${currentNode['notes']}]`;
                }

                if (currentNode[1]['nodepathid'] == "0") {
                    $("#path_drop_" + obsIndex).append(`<li>(${minutesToPrint}:${secondsToPrint}) ${notesText}</li>`);
                }
                else {
                    let currentNodeData = pathNodes[parseInt(currentNode[1]['nodepathid'])];
                    if (currentNodeData == undefined) {
                        $("#path_drop_" + obsIndex).append(`<li>(${minutesToPrint}:${secondsToPrint}) ${notesText}</li>`);
                    }
                    else {
                        $("#path_drop_" + obsIndex).append(`<li>(${minutesToPrint}:${secondsToPrint}) ${currentNodeData['code']}: ${currentNodeData['title']} ${notesText}</li>`);
                    }
                }

            });
        });
    }

    populateObsList();

    function goBack() {
        if (nodeInObsIndex == 0) {
            hideNodeEditor();
        } else {
            nodeInObsIndex -= 1;
            setupNodeInfo(subsessions[currentObs]['nodes'][nodeInObsIndex]['node1']);
        }
    }

    function selectRadio(selectedNum) {
        let selectionValue = $("#branch_radio_form").find('input[name="choiceRadio"]:checked').val();
        // Check if changing answer changes next node: if so, get rid of rest of data

        //Log what the node was before the change
        console.log("subsessions[currentObs]['nodes'][nodeInObsIndex] before: ");
        console.log(subsessions[currentObs]['nodes'][nodeInObsIndex]);

        // Store the values that need to be brought over
        let nodeSecs = subsessions[currentObs]['nodes'][nodeInObsIndex]['seconds'];

        // Give node the info of the pathnode
        subsessions[currentObs]['nodes'][nodeInObsIndex] = pathNodes[selectedNum];
        subsessions[currentObs]['nodes'][nodeInObsIndex]['seconds'] = nodeSecs;

        console.log("subsessions[currentObs]['nodes'][nodeInObsIndex] after: ");
        console.log(subsessions[currentObs]['nodes'][nodeInObsIndex]);
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

        subsessions[currentObs][nodeInObsIndex]['seconds'] = totalSeconds;
    }

    function autoFill() {
        console.log(currentObs);
        console.log("currentObs");
        console.log(nodeInObsIndex);
        console.log("nodeInObsIndex");
        try {
            console.log(subsessions[currentObs]['nodes'][nodeInObsIndex]['choice']);
            console.log("subsessions[currentObs]['nodes'][nodeInObsIndex]['choice']");
            let existingChoiceID = subsessions[currentObs]['nodes'][nodeInObsIndex]['choice'];
            $("#choiceRadio" + existingChoiceID).prop("checked", true);
        } catch {

        }
    }

    function hideNodeEditor() {
        if (!DOM.path_input.classList.contains('d-none')) {
            DOM.path_input.classList.add('d-none');
        }
        populateObsList();
        DOM.dom_group_1.classList.remove('d-none');
    }

    function startEditingNodes() {
        if (!DOM.dom_group_1.classList.contains('d-none')) {
            DOM.dom_group_1.classList.add('d-none');
        }
        DOM.path_input.classList.remove('d-none');
    }

    function addObservation() {
        //console.log("subsessions before:"); console.log(subsessions); 
        //console.log("insertId:"); console.log(insertId);
        subsessions[(newObsID).toString()] = {
            name: "New Observation",
            notes: null,
            nodes: []
        };
        currentObs = newObsID;
        newObsID -= 1;
        console.log("subsessions after: ");
        console.log(subsessions);
        nodeInObsIndex = 0;
        startEditingNodes();
        setupNodeInfo(Object.keys(questionNodes)[0]);
    }

    function editObservation(ssID) {
        startEditingNodes();
        currentObs = ssID;
        nodeInObsIndex = 0;
        setupNodeInfo(Object.keys(questionNodes)[0]);
    }

    function setupNodeInfo(structIndex) {
        //console.log("structIndex: "+structIndex);
        //console.log("structure[structIndex]:"); console.log(structure[structIndex]);
        //console.log("nodeData[structIndex]:"); console.log(nodeData[structIndex]);

        //DOM.path_title.innerText = nodeData[structIndex]['title'];
        $("#path_title").text(questionNodes[structIndex]['title']);

        if (subsessions[currentObs]['nodes'][nodeInObsIndex] != undefined) {
            $("#timestamp_input_minutes").val(Math.floor(parseInt(subsessions[currentObs]['nodes'][nodeInObsIndex]['seconds']) / 60));
            $("#timestamp_input_seconds").val(parseInt(subsessions[currentObs]['nodes'][nodeInObsIndex]['seconds']) % 60);
        }

        $("#branch_container").empty();
        $("#branch_container").append('<form id="branch_radio_form" class="col-12 pt-3" action="javascript:void(0)"></form>');

        currentQuestionID = structIndex;

        Object.entries(questionNodes[structIndex]['choices']).forEach((value, index) => {
            //console.log("index: "+index);
            //console.log("value:");
            //console.log(value);
            if (value[0] == "0") {
                console.log("when value[0] == 0, value[1] ==");
                console.log(value[1]);
            } else {

                $("#branch_radio_form").append(`
                <p onclick="selectRadio(${value[1]});">
                    <input type="radio" name="choiceRadio" id="choiceRadio${value[1]}" value="${value[1]}">
                    <label for="choiceRadio${value[1]}" class="choiceOfList">(${index}) ${pathNodes[value[1]]['title']}</label>
                </p>`);

            }

        });
        /*
                structure[structIndex].forEach((index, value) => {
                    console.log("index: "+index);
                    console.log("value:");
                    console.log(value);
                });
                */

        //TODO: autofill
        autoFill();
    }

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
            echo (json_encode($videoInfop['name']))
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
        let videoSRC = "/ccoivids/" + scramble +"_" + url;
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

    function proceed() {
        //check if extra data is needed for choice
        //if necessary, ask for extra data

        //get index of choice
        let selectionValue = $("#branch_radio_form").find('input[name="choiceRadio"]:checked').val();
        //console.log("proceed retrieved value: "); console.log(selectionValue);
        //get pnid
        //let selectedPN = newwQuery[currentQuestionID]['choices'][selectionValue];
        //console.log("selectedPN: "); console.log(selectedPN);
        //let selectedPNID = selectedPN['choice'];
        //console.log("selectedPNID: "); console.log(selectedPNID);
        let nextQuestionNode = pathNodes[selectionValue]['node2'];
        nodeInObsIndex += 1;
        //console.log("nextQuestionNode: "); console.log(nextQuestionNode);
        //store info in data struct
        //load next node or return to observation viewer (depending on if path terminates)
        if (nextQuestionNode == null) {
            subsessions[currentObs]['nodes'].length = nodeInObsIndex;
            hideNodeEditor();
        } else {
            setupNodeInfo(nextQuestionNode);
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
                console.log("AJAX returns this:");
                console.log(data);
            }
        };
        var sendStr = "updateObsEl=1&id=" + sessionID + "&" + $.param(subsessions);
        console.log("sendStr:");
        console.log(sendStr);
        var url = encodeURI(derServer + "ZPB/zpb_ajax.php?" + sendStr);
        console.log(url);
        xmlHttp.open("POST", url, true);
        xmlHttp.setRequestHeader(
            "Content-Type",
            "application/x-www-form-urlencoded"
        );
        xmlHttp.send(sendStr);
    }


    var DOM = {};
    var popoutWindow;
    var IDs = [
        'launch_video_button',
        'go_to_session_select',
        'save_session_button',
        'session_list',
        'session_meta_form',
        'session_video_url',
        'session_notes',
        'new_session_button',
        'session_submit_button',
        'add_path_button',
        'reorder_paths_button',
        'finish_reorder_button',
        'node_preview_list',
        'path_start',
        'path_choices',
        'path_select',
        'path_input',
        'path_list',
        'path_listing',
        'path_preview',
        'path_preview_list',
        'path_preview_heading',
        'path_title',
        'path_label',
        'path_label_button',
        'proceed_button',
        'proceed_and_play_button',
        'branch_form',
        'notes_input',
        'timestamp_input_minutes',
        'timestamp_input_seconds',
        'irr_button',
        'dom_group_1',
        'path_go_back',
        'session_go_back',
        'visualizations',
        'viz_container',
        'viz_refresh',
        'viz_session_select',
        'viz_chart_select_form',
        'viz_session_select_ul',
        'viz_chart_select_ul',
        'demo_no_sesh',
        'viz_select_btn',
        'session_facts',
        'sankey_container',
        'csvImportShow',
        'exportHumanReadable',
        'exportCSV',
        'prepareGVall',
        'goToMainMenu',
        'pathSelectTitle',
        'pathSelectList',
        'sessionLabel',
        'sessionTitle',
        'sessionDate',
        'sessionStudent',
        'sessionPrompted',
        'sessionSelect',
        'timestampInputMinutes',
        'timestampInputSeconds',
        'notesInputLabel',
        'csvImportDialog',
        'csvImportFileInput',
        'gvExportDialog',
        'returnFromGV',
        'gvForm',
        'gvSelectGraphType',
        'gvSelectEdgeType',
        'gvShowEnd',
        'gvAcyclic',
        'gvSelectSessions',
        'rawOutput',
        'exportTitle',
        'returnFromExport',
        'exportDownload',
        'exportOut',
        'branch_container',
        'branch_radio_form',
        'observation-list'
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
                print_r($d);
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
?>