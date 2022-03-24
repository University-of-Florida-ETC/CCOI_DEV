<?php
$page = "dashboard";
$dataTarget = "#demo_help_box";
$dataOffset = "10";
$dataSpy = "scroll";
include '../includes/header.php';
include '../api/ccoi_dbhookup.php';
$id=$_GET['id']+0;

//TODO: check that they are allowed in here
$session = getSessionInfo($id); //defined below
echo "<br>session: "; print_r($session);

$return = mysqli_query($db, "SELECT * FROM tbPaths WHERE id = '{$session['pathid']}'");
while ($d = mysqli_fetch_assoc($return)) {
    $currentPathStartsAt = $d['startpnid'];
}
echo "<br>current path starts ats nodeid: ". $currentPathStartsAt;
$endid = intval($session['pathid']) + 1;
echo "<br>endids: ". $endid;
echo "<br>find ending pnid query: ". "SELECT * FROM tbPaths WHERE id = '{$endid}'";
$return = mysqli_query($db, "SELECT * FROM tbPaths WHERE id = '{$endid}'");
while ($d = mysqli_fetch_assoc($return)) {
    $currentPathEndsAt = $d['startpnid'];
}
if( isset($currentPathEndsAt) ){
    echo "<br>current path ends at nodeid: ". $currentPathEndsAt;
    $nodequery = "SELECT * FROM tbNodes WHERE id >= {$currentPathStartsAt} AND id < {$currentPathEndsAt}";
}
else {
    echo "<br>current path is last by id";
    $nodequery = "SELECT * FROM tbNodes WHERE id >= {$currentPathStartsAt}";
}

// Get pathnode data
$return = mysqli_query($db, $nodequery);
while ($d = mysqli_fetch_assoc($return)) {
    $nodeData[$d['id']] = $d;
}
//echo "<br>nodeData: "; print_r($nodeData);

//If in playgrounds, query playgrounds DB
if (isset($_GET['isPlayground'])){
    echo "It's a playground";
    $return = mysqli_query($db, "SELECT SA.*, SS.id as ssid, SS.subnum, SS.name as ssname, SS.notes as ssnotes, PN.id as pnid, PN.node1, PN.choice, PN.node2, PN.choicegroup, PN.pathtype, PN.nsubgroup FROM tbPlaygroundActivity SA, tbPathNodes PN, tbSubPlaygrounds SS WHERE SA.sessionid = $id AND SA.nodepathid=PN.id AND SA.ssid=SS.id ORDER BY SA.sessionid, SA.seconds");
}
//Otherwise, query research
else
    $return = mysqli_query($db, "SELECT SA.*, SS.id as ssid, SS.subnum, SS.name as ssname, SS.notes as ssnotes, PN.id as pnid, PN.node1, PN.choice, PN.node2, PN.choicegroup, PN.pathtype, PN.nsubgroup FROM tbSessionActivity SA, tbPathNodes PN, tbSubSessions SS WHERE SA.sessionid = $id AND SA.nodepathid=PN.id AND SA.ssid=SS.id ORDER BY SA.sessionid, SA.seconds");
//Regardless, populate with observation info
while ($d = mysqli_fetch_assoc($return)) { /*$subsessions[$d['ssid']][d['id']]=$d;*/
    $subsessions[$d['ssid']][] = $d;
}
//echo "subsessions: ";
//print_r($subsessions);
//TODO: stuff to make old node editor work with new backend

$jsonReplacement['firstNodeID']=$currentPathStartsAt;
$return=mysqli_query($db,"SELECT * FROM tbNodeGroups WHERE pathid = '{$session['pathid']}'");		
while($d=mysqli_fetch_assoc($return)){
    //$jsonReplacement['nodeGroups'][intval($d['derorder'])] = [
    $jsonReplacement['nodeGroups'][] = [
        "machine_name" => $d['name'],
        "label" => $d['humanname'],
        "labelposition" =>  $d['labelpos'],
        "fill" =>  $d['fill']
    ];
}
//echo "<br>jsonReplacement['nodeGroups']: "; print_r($jsonReplacement['nodeGroups']);
$nodeids = [];
$return=mysqli_query($db,"SELECT * FROM tbPathNodes WHERE pathid = '{$session['pathid']}' AND inactive IS NULL");		
while($d=mysqli_fetch_assoc($return)){
    if($d['choice']==0){
        continue;
    }
    $nodeIndex = array_search($d['node1'], $nodeids);
    if( $nodeIndex === false ){
        $nodeIndex = array_push($nodeids, $d['node1'])-1;
        $jsonReplacement['nodes'][] = [
            "id" => $nodeIndex,
            "node_id" => $d['node1'],
            "nodeid" => $nodeData[$d['node1']]['oldid'],
            "title" => $nodeData[$d['node1']]['title']
            //"groups" => $jsonReplacement['nodeGroups'][$nodeData[$d['node1']]['nodegroup']],
        ];
    }
    $toInsert = [
        "description" => $nodeData[$d['choice']]['title'],
        "next" => $nodeData[$d['node2']]['oldid'],
        "next_id" => $d['node2'],
        "branch_id" => $d['choice'],
        "branch_new_id" => $nodeData[$d['choice']]['oldid']
    ];
    if( isset($nodeData[$d['choice']]['extra']) ){
        $toInsert['extra'] = $nodeData[$d['choice']]['extra'];
    }
    if( isset($d['pathtype']) ){
        $toInsert['path_type'] = $jsonReplacement[$d['pathtype']]['label'];
    }
    if( isset($nodeData[$d['choice']]['aside']) ){
        $toInsert['aside'] = $nodeData[$d['choice']]['aside'];
    }
    $jsonReplacement['nodes'][$nodeIndex]['branches'][0][] = $toInsert;
}
//echo "<br>jsonReplacement['nodes']: "; print_r($jsonReplacement['nodes']);
?>
<script>
    var sessionID = <?php echo $id; ?>;
    var jsonReplacement = `<?php echo json_encode(getJSONData(), JSON_PRETTY_PRINT); ?>`;
    console.log(jsonReplacement);
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
                            <h5 style="text-transform: none;">Select an observation to view or edit its responses</h5>
                        </div>
                        <div class="col-md-4 col-12 pt-2">
                            <button id="save_session_button" type="button" class="btn btn-blue float-right disabled" data-toggle="tooltip" data-html="true" title="Click here to save your session">Save Session</button>
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
                            <button id="add_path_button" type="button" class="btn btn-darkblue" data-toggle="tooltip" data-html="true" title="Click here to add a Observation">Add Observation</button>
                        </div>

                        <div id="path_listing" class="col-12 pt-4 pr-md-5">
                            <div id="path_list" class="draggable-container">
                                <?php $count = 1;
                                foreach ($subsessions as $key => $currentSub) : ?>
                                    <div class="path-listing-container">
                                        <h5 data-index="<?= $count; ?>" class="path-listing-header">Observation #<?= $count; ?>: <?= $currentSub[0]['ssname']; ?>
                                            <a class="btn-link path-edit-icon" href="#" data-index="<?= $count; ?>"><span class="oi oi-pencil px-3" title="Edit Path" aria-hidden="true"></span></a>
                                            <a class="btn-link path-delete-icon" href="#" data-index="<?= $count; ?>"><span class="oi oi-trash" title="Delete Path" aria-hidden="true"></span></a>
                                            <button class="btn-link float-right path-dropdown-btn" data-toggle="collapse" data-target="#path_drop_<?= $count; ?>" aria-expanded="true"><span class="oi oi-chevron-top" title="Show Path Steps" aria-hidden="true"></span></button>
                                        </h5>
                                        <ol class="collapse" id="path_drop_<?= $count; ?>" style="">
                                            <?php foreach ($currentSub as $index => $currentOE) :
                                                $currentSeconds = (int)$currentOE['seconds'];
                                                $currentNode = $nodeData[(int)$currentOE['choice']];
                                            ?>
                                                <li><?php echo sprintf("(%02d:%02d) %s: %s", $currentSeconds / 60, $currentSeconds % 60, $currentNode['code'], $currentNode['title'], $currentNode['title']);
                                                    if (isset($currentOE['notes'])) echo sprintf(" [%s]", $currentOE['notes']); ?></li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                <?php $count++;
                                endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div id="path_input" class="col-12 pt-3 pr-md-5 d-none">
                        <h2 id="path_title"></h2>
                        <div class="row">
                            <form method="post" action="javascript:void(0)" id="branch_form" class="col s9" name="branch_form">
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
                                            <input class="form-control" id="timestamp_input_minutes" type="number" min="0" max="9999" step="1" value="0">
                                            <label class="text-center" for="timestamp_input_minutes">minutes</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-12">
                                        <div class="form-group">
                                            <input class="form-control" id="timestamp_input_seconds" type="number" min="0" max="59" step="1" value="0">
                                            <label class="text-center" for="timestamp_input_seconds">seconds</label>
                                        </div>
                                    </div>
                                    <div class="col-md-5 col-12">
                                        <button id="proceed_button" class="btn btn-sm btn-outline-darkblue mb-2">Proceed</button>
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

                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="notes_input">Notes:</label>
                                            <textarea class="form-control" id="notes_input" name="notes_input" placeholder="Insert path notes here" rows="5"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <button id="path_go_back" class="btn btn-blue" type="button">Go Back</button>
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="row">
                        <div class="col">
                            <button id="launch_video_button" class="btn btn-blue btn-full-width my-2">Open Video <span class="oi oi-external-link px-2" title="Open Session Video"></span></button>
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
<script src="/js/zpbccoi.js"></script>
<script src="/js/ccoi.js"></script>
<script src="/js/observation.js"></script>
<script src="/js/ccoi-data-model.js"></script>
<script src="/js/observe.js"></script> 
<script>

    /*
    console.log("In");
    try{
        if(typeof(jsUserVars) != 'undefined'){
            console.log("In2");
            userid=jsUserVars['pid'];
            //setTimeout(function(){ fetchUserObSets2(userid);},500);
            setTimeout(function(){ fetchUserObSets3(userid);},50);
            //fetchUserObSets2(userid);
        }
        console.log("In3");
    }
    catch(error){
        console.log("In4");
        error(error);
    }
    */
</script>
</body>

</html>

<?php

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

function getJSONData(){
    return `{
        "version": "20181705",
        "firstNodeID": 1,
        "nodeGroups": [
          {
            "machine_name": "start",
            "label": "Starting Node",
            "labelPosition": "top",
            "fill": "fff"
          },
          {
            "machine_name": "independent",
            "label": "Independent",
            "labelPosition": "bottom",
            "fill": "ddd"
          },
          {
            "machine_name": "independent_computing",
            "label": "Independent Problem Solving",
            "labelPosition": "bottom",
            "fill": "fff",
            "hide_from_graph": "1",
            "chart_fill": "2196f3"
          },
          {
            "machine_name": "independent_non_computing",
            "label": "Non-Computing Independent",
            "labelPosition": "bottom",
            "fill": "fff",
            "hide_from_graph": "1",
            "chart_fill": "009688"
          },
          {
            "machine_name": "interactive",
            "label": "Non-Computing Interaction",
            "labelPosition": "top",
            "fill": "ddd"
          },
          {
            "machine_name": "problem_solving",
            "label": "Collaborative Problem Solving",
            "labelPosition": "top",
            "parent": "5",
            "fill": "ccc",
            "chart_fill": "ff5722"
          },
          {
            "machine_name": "computing_communication",
            "label": "Computing Discussion (Non-Problem Solving)",
            "labelPosition": "bottom",
            "parent": "5",
            "fill": "ccc",
            "chart_fill": "ffc107"
          },
          {
            "machine_name": "non_computing_communication",
            "label": "Non-Computing Interaction",
            "labelArray": [
              "Non-computing",
              "Interaction"
            ],
            "labelPosition": "bottom",
            "parent": "5",
            "fill": "ccc",
            "chart_fill": "8bc34a"
          }
        ],
        "nodes": [
          {
            "id": 0,
            "node_id": 1,
            "nodeid": "2789f0",
            "title": "How does the event begin?",
            "groups": [
              "start"
            ],
            "group_hex": [
              "06bf1b"
            ],
            "should_group_choices": false,
            "branches": [
              [
                {
                  "description": "Student addresses Peer",
                  "next": "effa06",
                  "next_id": "9",
                  "extra": "Peer ID",
                  "path_type": "Interactive",
                  "branch_id": "52731b",
                  "branch_new_id": "2"
                },
                {
                  "description": "Peer addresses Student",
                  "next": "effa06",
                  "next_id": "9",
                  "extra": "Peer ID",
                  "path_type": "Interactive",
                  "branch_id": "21b330",
                  "branch_new_id": "3"
                },
                {
                  "description": "Student addresses Adult",
                  "next": "effa06",
                  "next_id": "9",
                  "extra": "Adult ID",
                  "path_type": "Interactive",
                  "branch_id": "8e716f",
                  "branch_new_id": "4"
                },
                {
                  "description": "Adult addresses Student",
                  "next": "effa06",
                  "next_id": "9",
                  "extra": "Adult ID",
                  "path_type": "Interactive",
                  "branch_id": "87dfa4",
                  "branch_new_id": "5"
                },
                {
                  "description": "Student works independently on a computing related task",
                  "next": "9591c0",
                  "next_id": "128",
                  "path_type": "Independent",
                  "branch_id": "c83b4e",
                  "branch_new_id": "6",
                  "node_sub_group": "independent_computing"
                },
                {
                  "description": "Student works independently on a non computing task",
                  "next": "9591c0",
                  "next_id": "128",
                  "path_type": "Independent",
                  "branch_id": "666ca8",
                  "branch_new_id": "7",
                  "node_sub_group": "independent_non_computing"
                },
                {
                  "description": "Student joins peer conversation",
                  "next": "effa06",
                  "next_id": "9",
                  "path_type": "Interactive",
                  "branch_id": "b31dcc",
                  "branch_new_id": "8"
                }
              ]
            ]
          },
          {
            "id": 1,
            "node_id": 9,
            "nodeid": "effa06",
            "title": "How does the interaction with the peer or adult begin or continue?",
            "groups": [
              "interactive"
            ],
            "group_hex": [
              "aaaaaa"
            ],
            "should_group_choices": true,
            "branches": [
              [
                {
                  "description": "Student clearly expresses how he or she needs help with a difficulty or problem",
                  "next": "73ae21",
                  "next_id": "38",
                  "branch_id": "da6dbb",
                  "branch_new_id": "10"
                },
                {
                  "description": "Student expresses a need for help, but is not explicit to the difficulty or problem ",
                  "next": "73ae21",
                  "next_id": "38",
                  "branch_id": "2e2236",
                  "branch_new_id": "11"
                },
                {
                  "description": "Student discusses computing (not problem solving)",
                  "next": "3710bb",
                  "next_id": "97",
                  "branch_id": "4d4acd",
                  "branch_new_id": "12"
                },
                {
                  "description": "Student engages in non-computing conversation",
                  "next": "9da550",
                  "next_id": "117",
                  "branch_id": "262e59",
                  "branch_new_id": "13"
                },
                {
                  "description": "Student offers support to peer (the peer did not specifically ask for help)",
                  "next": "645409",
                  "next_id": "46",
                  "branch_id": "f11803",
                  "branch_new_id": "14"
                },
                {
                  "description": "Student said something that is unclear or inaudible",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "58c900",
                  "branch_new_id": "15"
                },
                {
                  "description": "Student verbally addresses a person without expressing the offer or need for help, curiosity, excitement, accomplishment or non-computing conversation (e.g., \"Hey you...\" or \"Mrs. S...\" or \"Stop that!\")",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "541093",
                  "branch_new_id": "16"
                }
              ],
              [
                {
                  "description": "Peer offers self-regulation support to student",
                  "aside": "Describe the support in the field notes.",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "aeb05b",
                  "branch_new_id": "17"
                },
                {
                  "description": "Peer offers support to student who was working collaboratively on a problem or topic",
                  "next": "73ae21",
                  "next_id": "38",
                  "branch_id": "5ba6f5",
                  "branch_new_id": "18"
                },
                {
                  "description": "Peer offers support to student who was working independently on a problem or topic",
                  "next": "73ae21",
                  "next_id": "38",
                  "branch_id": "f93287",
                  "branch_new_id": "19"
                },
                {
                  "description": "Peer discusses computing (not problem solving)",
                  "next": "3710bb",
                  "next_id": "97",
                  "branch_id": "fa2db2",
                  "branch_new_id": "20"
                },
                {
                  "description": "Peer engages student in non-computing conversation (heard on student's computer)",
                  "next": "9da550",
                  "next_id": "117",
                  "branch_id": "712191",
                  "branch_new_id": "21"
                },
                {
                  "description": "Peer asks student for help",
                  "next": "645409",
                  "next_id": "46",
                  "branch_id": "5df510",
                  "branch_new_id": "22"
                },
                {
                  "description": "Peer said something that is unclear or inaudible",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "1722a1",
                  "branch_new_id": "23"
                },
                {
                  "description": "Peer verbally addresses the student without expressing the need\/offer for help, curiosity, excitement, accomplishment or non-computing conversation (e.g. \"Hey you...\" or \"Can you stop that!\")",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "b649d2",
                  "branch_new_id": "24"
                }
              ],
              [
                {
                  "description": "Adult offers self-regulation support to student",
                  "aside": "Describe the support in the field notes.",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "668767",
                  "branch_new_id": "25"
                },
                {
                  "description": "Adult offers support to student who was working collaboratively on a problem or topic",
                  "next": "73ae21",
                  "next_id": "38",
                  "branch_id": "f77c01",
                  "branch_new_id": "26"
                },
                {
                  "description": "Adult offers support to student who was working independently on a problem or topic",
                  "next": "73ae21",
                  "next_id": "38",
                  "branch_id": "3f9c83",
                  "branch_new_id": "27"
                },
                {
                  "description": "Adult talks to student about a non-computing task (e.g., student has to finish a math problem, check recording, go to another room)",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "a863a2",
                  "branch_new_id": "28"
                },
                {
                  "description": "Adult verbally comments on student's work [heard on student's computer]",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "8851be",
                  "branch_new_id": "29"
                },
                {
                  "description": "Adult engages in non-computing conversation [heard on student's computer]",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "cdc9a8",
                  "branch_new_id": "30"
                },
                {
                  "description": "Adult said something that is unclear or inaudible",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "ec7320",
                  "branch_new_id": "31"
                },
                {
                  "description": "Adult verbally addresses the student without expressing the offer for help, curiosity, excitement, accomplishment or non-computing conversation (e.g., \"Can you please stop that!\")",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "5ac2f4",
                  "branch_new_id": "32"
                },
                {
                  "description": "Adult directs student to computing task",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "0b4e2a",
                  "branch_new_id": "33"
                }
              ],
              [
                {
                  "description": "Student's cursor stopped moving for more than 30 seconds, or the student leaves station (> 30 seconds) and returns to independently work [end path]",
                  "branch_id": "585692",
                  "branch_new_id": "34"
                },
                {
                  "description": "Interaction terminates [end path]",
                  "branch_id": "d92d7d",
                  "branch_new_id": "35"
                },
                {
                  "description": "The video record ends [end path]",
                  "branch_id": "ef8f7e",
                  "branch_new_id": "36"
                }
              ]
            ],
            "branch_group_names": [
              "Student Driven",
              "Peer Driven",
              "Adult Driven",
              "General"
            ]
          },
          {
            "id": 2,
            "node_id": 38,
            "nodeid": "73ae21",
            "title": "What was the problem? (This is referring to the difficulty not the subtask)",
            "groups": [
              "problem_solving"
            ],
            "group_hex": [
              "0676be"
            ],
            "should_group_choices": false,
            "branches": [
              [
                {
                  "description": "Difficulty or problem or topic is related to computing\/programming",
                  "next": "645409",
                  "next_id": "46",
                  "branch_id": "bcc9dd",
                  "branch_new_id": "39"
                },
                {
                  "description": "Difficulty or problem or topic is related to academic content",
                  "next": "645409",
                  "next_id": "46",
                  "branch_id": "81b16e",
                  "branch_new_id": "40"
                },
                {
                  "description": "Difficulty or problem or topic is related to navigating software (e.g. logging in, changing levels)",
                  "next": "645409",
                  "next_id": "46",
                  "branch_id": "2926b7",
                  "branch_new_id": "41"
                },
                {
                  "description": "Difficulty or problem or topic is related to asset creation and management (e.g. creating\/modifying\/resizing images, recording\/editing audio)",
                  "next": "645409",
                  "next_id": "46",
                  "branch_id": "eb0e22",
                  "branch_new_id": "42"
                },
                {
                  "description": "Difficulty or problem or topic is related to general computer technology (e.g., mouse, monitor, adapter)",
                  "next": "645409",
                  "next_id": "46",
                  "branch_id": "0f1899",
                  "branch_new_id": "43"
                },
                {
                  "description": "Difficulty or problem or topic is related to multiple of the above categories",
                  "aside": "Indicate the relevant categories in the field notes using the following keywords: \"computing\/programming\", \"academic content\", \"navigating software\", \"asset creation\/management\", \"general computer technology\", \"other\"",
                  "next": "645409",
                  "next_id": "46",
                  "branch_id": "59bef4",
                  "branch_new_id": "44"
                },
                {
                  "description": "Other (Please add addtional notes)",
                  "next": "645409",
                  "next_id": "46",
                  "branch_id": "0dd1f9",
                  "branch_new_id": "45"
                }
              ]
            ]
          },
          {
            "id": 3,
            "node_id": 46,
            "nodeid": "645409",
            "title": "What did the interaction between the Peer or Adult and the student look like?",
            "groups": [
              "problem_solving"
            ],
            "group_hex": [
              "0676be"
            ],
            "should_group_choices": true,
            "branches": [
              [
                {
                  "description": "Peer and student discuss the difficulty or problem",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "70374a",
                  "branch_new_id": "47"
                },
                {
                  "description": "Peer and student discuss the difficulty or problem, and another person(s) joins the discussion",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "4190ff",
                  "branch_new_id": "48"
                },
                {
                  "description": "Peer recites all steps of the solution",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "b99510",
                  "branch_new_id": "49"
                },
                {
                  "description": "Peer recites all steps of the solution, and another person(s) joins",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "dc3aea",
                  "branch_new_id": "50"
                },
                {
                  "description": "Peer physically shows by taking over the student's computer, and no discussions are occurring",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "1b7cb8",
                  "branch_new_id": "51"
                },
                {
                  "description": "Peer physically shows by taking over the student's computer, no discussions are occurring, and another person(s) joins",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "a3c5c4",
                  "branch_new_id": "52"
                },
                {
                  "description": "Peer physically shows by taking over the student's computer, and discussions are occurring",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "ec1587",
                  "branch_new_id": "53"
                },
                {
                  "description": "Peer physically shows by taking over the student's computer, discussions are occurring, and another person (s) joins",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "ac5964",
                  "branch_new_id": "54"
                },
                {
                  "description": "Peer and student discuss the difficulty or problem, and then Peer directs student to another peer or adult",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "ddae53",
                  "branch_new_id": "55"
                }
              ],
              [
                {
                  "description": "Adult and student discuss the difficulty or problem",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "109ab7",
                  "branch_new_id": "56"
                },
                {
                  "description": "Adult and student discuss the difficulty or problem, and another person(s) joins the discussion",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "2b3163",
                  "branch_new_id": "57"
                },
                {
                  "description": "Adult recites all steps of the solution",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "084bba",
                  "branch_new_id": "58"
                },
                {
                  "description": "Adult recites all steps of the solution, and another person(s) joins",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "822843",
                  "branch_new_id": "59"
                },
                {
                  "description": "Adult physically shows by taking over the student's computer, and no discussions are occurring",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "8e0204",
                  "branch_new_id": "60"
                },
                {
                  "description": "Adult physically shows by taking over the student's computer, no discussions are occurring, and another person(s) joins",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "f95e7d",
                  "branch_new_id": "61"
                },
                {
                  "description": "Adult physically shows by taking over the student's computer, and discussions are occurring",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "f40ad7",
                  "branch_new_id": "62"
                },
                {
                  "description": "Adult physically shows by taking over the student's computer, discussions are occurring, and another person (s) joins",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "3846e2",
                  "branch_new_id": "63"
                },
                {
                  "description": "Adult and student discuss the difficulty or problem, and then Adult directs student to another peer or adult",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "16f275",
                  "branch_new_id": "64"
                }
              ],
              [
                {
                  "description": "Peer directs student to talk to another peer or adult",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "a5c984",
                  "branch_new_id": "65"
                },
                {
                  "description": "Peer clearly states that he or she does not know how to help",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "0ae378",
                  "branch_new_id": "66"
                },
                {
                  "description": "Peer clearly states that he or she does not want to help",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "69f908",
                  "branch_new_id": "67"
                }
              ],
              [
                {
                  "description": "Adult directs student to talk to another peer or adult",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "1c2726",
                  "branch_new_id": "68"
                },
                {
                  "description": "Adult clearly states that he or she does not know how to help",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "098f26",
                  "branch_new_id": "69"
                },
                {
                  "description": "Adult is unwilling to help",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "1960c3",
                  "branch_new_id": "70"
                },
                {
                  "description": "Adult verbally comments on student's work",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "53256d",
                  "branch_new_id": "71"
                }
              ],
              [
                {
                  "description": "Student dismisses his or her attempt for interacting (e.g. student does not call the person again) [end path]",
                  "branch_id": "90fa95",
                  "branch_new_id": "72"
                },
                {
                  "description": "Peer dismisses his or her attempt for interacting (e.g. peer does not call the student again) [end path]",
                  "branch_id": "7d8292",
                  "branch_new_id": "73"
                },
                {
                  "description": "Adult dismisses his or her attempt for interacting (e.g. adult does not call the student again) [end path]",
                  "branch_id": "0e599e",
                  "branch_new_id": "74"
                },
                {
                  "description": "Student ignored or cannot hear or code student's response to peer",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "b8645c",
                  "branch_new_id": "75"
                },
                {
                  "description": "Student ignored or cannot hear or code student's response to adult",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "29bc47",
                  "branch_new_id": "76"
                },
                {
                  "description": "Peer ignored or cannot hear or code peer's response to student",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "cfcfc7",
                  "branch_new_id": "77"
                },
                {
                  "description": "Adult ignored or cannot hear or code adult's response to student",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "c88493",
                  "branch_new_id": "78"
                },
                {
                  "description": "Can't code interaction [end path]",
                  "branch_id": "bc7f6c",
                  "branch_new_id": "79"
                }
              ],
              [
                {
                  "description": "Student and peer discuss a difficulty or problem",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "0eb295",
                  "branch_new_id": "80"
                },
                {
                  "description": "Student and peer discuss a difficulty or problem, and another person(s) joins the discussion",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "09d441",
                  "branch_new_id": "81"
                },
                {
                  "description": "Student recites all steps of the solution",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "a29d87",
                  "branch_new_id": "82"
                },
                {
                  "description": "Student recites all steps of the solution, and another person(s) joins",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "685c02",
                  "branch_new_id": "83"
                },
                {
                  "description": "Student physically shows by taking over the peer's computer, and no discussions are occurring",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "bf68f9",
                  "branch_new_id": "84"
                },
                {
                  "description": "Student physically shows by taking over the peer's computer, no discussions are occurring, and another person(s) joins",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "393f1b",
                  "branch_new_id": "85"
                },
                {
                  "description": "Student physically shows by taking over the peer's computer, and discussions are occurring",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "ec1187",
                  "branch_new_id": "86"
                },
                {
                  "description": "Student physically shows by taking over the peer's computer, discussions are occurring, and another person (s) joins",
                  "next": "125cd7",
                  "next_id": "94",
                  "branch_id": "9d4e4e",
                  "branch_new_id": "87"
                },
                {
                  "description": "Student and peer discuss a difficulty or problem, and then student directs peer to another peer or adult",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "81d8cf",
                  "branch_new_id": "88"
                }
              ],
              [
                {
                  "description": "Student directs peer to talk to another peer or adult",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "c1225a",
                  "branch_new_id": "89"
                },
                {
                  "description": "Student clearly states that he or she does not know how to help",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "55c726",
                  "branch_new_id": "90"
                },
                {
                  "description": "Student clearly states that he or she does not want to help",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "1f25e9",
                  "branch_new_id": "91"
                }
              ],
              [
                {
                  "description": "Student's cursor stopped moving for more than 30 seconds, or the student leaves station (> 30 seconds) and returns to independently work [end path]",
                  "branch_id": "20377b",
                  "branch_new_id": "92"
                },
                {
                  "description": "Interaction terminates [end path]",
                  "branch_id": "6c93ea",
                  "branch_new_id": "93"
                }
              ]
            ],
            "branch_group_names": [
              "Peer - Problem Discussed",
              "Adult - Problem Discussed",
              "Peer - Unable or Unwilling to Help",
              "Adult - Unable or Unwilling to Help",
              "Ignored",
              "Student - Problem Discussed",
              "Student - Unable or Unwilling to Help",
              "General"
            ]
          },
          {
            "id": 4,
            "node_id": 94,
            "nodeid": "125cd7",
            "title": "Was the problem solved or not solved? (Was the difficulty solved?)",
            "groups": [
              "problem_solving"
            ],
            "group_hex": [
              "0676be"
            ],
            "should_group_choices": false,
            "branches": [
              [
                {
                  "description": "Problem was not solved",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "ecb144",
                  "branch_new_id": "95"
                },
                {
                  "description": "Problem was solved",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "990d2d",
                  "branch_new_id": "96"
                }
              ]
            ]
          },
          {
            "id": 5,
            "node_id": 97,
            "nodeid": "3710bb",
            "title": "What is the nature of the student's computing-related communication with the peer or adult?",
            "groups": [
              "computing_communication"
            ],
            "group_hex": [
              "db8409"
            ],
            "assumes_previous_timestamp": "1",
            "should_group_choices": true,
            "branches": [
              [
                {
                  "description": "Student questions peer about something associated with peer's work",
                  "next": "e7d1ec",
                  "next_id": "108",
                  "branch_id": "41cade",
                  "branch_new_id": "98"
                },
                {
                  "description": "Student verbally comments about his\/her own work, e.g. accomplishment",
                  "next": "e7d1ec",
                  "next_id": "108",
                  "branch_id": "6f1a57",
                  "branch_new_id": "99"
                },
                {
                  "description": "Student verbally comments about something associated with peer's work",
                  "next": "e7d1ec",
                  "next_id": "108",
                  "branch_id": "9ec392",
                  "branch_new_id": "100"
                },
                {
                  "description": "Student expresses dissatisfaction or frustration",
                  "next": "e7d1ec",
                  "next_id": "108",
                  "branch_id": "4d95d2",
                  "branch_new_id": "101"
                },
                {
                  "description": "Student said something that is unclear or inaudible",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "7c3097",
                  "branch_new_id": "102"
                }
              ],
              [
                {
                  "description": "Peer questions student about something associated with student's work",
                  "next": "e7d1ec",
                  "next_id": "108",
                  "branch_id": "d518e6",
                  "branch_new_id": "103"
                },
                {
                  "description": "Peer verbally comments about something associated with his\/her own work, e.g. accomplishment",
                  "next": "e7d1ec",
                  "next_id": "108",
                  "branch_id": "2ad3e4",
                  "branch_new_id": "104"
                },
                {
                  "description": "Peer verbally comments about something associated with student's work",
                  "next": "e7d1ec",
                  "next_id": "108",
                  "branch_id": "3e74d1",
                  "branch_new_id": "105"
                },
                {
                  "description": "Peer expresses dissatisfaction or frustration",
                  "next": "e7d1ec",
                  "next_id": "108",
                  "branch_id": "be878a",
                  "branch_new_id": "106"
                },
                {
                  "description": "Peer said something that is unclear or inaudible",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "53e98d",
                  "branch_new_id": "107"
                }
              ]
            ],
            "branch_group_names": [
              "Student Driven",
              "Peer Driven"
            ]
          },
          {
            "id": 6,
            "node_id": 108,
            "nodeid": "e7d1ec",
            "title": "What is the student\/peer\/adult's response to the initial computing-related communication?",
            "groups": [
              "computing_communication"
            ],
            "group_hex": [
              "db8409"
            ],
            "assumes_previous_timestamp": "1",
            "should_group_choices": false,
            "branches": [
              [
                {
                  "description": "Peer verbally responds to the student's curiosity, excitement, or frustration",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "242880",
                  "branch_new_id": "109"
                },
                {
                  "description": "Adult verbally responds to the student's curiosity, excitement, or frustration",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "629874",
                  "branch_new_id": "110"
                },
                {
                  "description": "Interaction terminates [end path]",
                  "branch_id": "1fb40a",
                  "branch_new_id": "111"
                },
                {
                  "description": "Student\/peer\/adult ignored or cannot hear or code the response",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "947465",
                  "branch_new_id": "112"
                },
                {
                  "description": "Student\/peer\/adult said something that is unclear or inaudible",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "1ded37",
                  "branch_new_id": "113"
                },
                {
                  "description": "Student verbally responds to the peer's curiosity, excitement, or frustration",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "35cacd",
                  "branch_new_id": "114"
                },
                {
                  "description": "Student offers support to peer (the peer did not specifically ask for help)",
                  "next": "645409",
                  "next_id": "46",
                  "branch_id": "8c2517",
                  "branch_new_id": "115"
                }
              ]
            ]
          },
          {
            "id": 7,
            "node_id": 117,
            "nodeid": "9da550",
            "title": "How did the student\/peer respond to the non-computing conversation?",
            "groups": [
              "non_computing_communication"
            ],
            "group_hex": [
              "320ead"
            ],
            "assumes_previous_timestamp": "1",
            "should_group_choices": false,
            "branches": [
              [
                {
                  "description": "Peer verbally responds to student's non-computing conversation",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "ed0549",
                  "branch_new_id": "118"
                },
                {
                  "description": "Adult verbally responds to student's non-computing conversation",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "434981",
                  "branch_new_id": "119"
                },
                {
                  "description": "Interaction terminates [end path]",
                  "branch_id": "7b114c",
                  "branch_new_id": "120"
                },
                {
                  "description": "Student\/peer\/adult ignored or cannot hear or code response",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "798662",
                  "branch_new_id": "121"
                },
                {
                  "description": "Peer or adult redirects student to the computing task",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "6b445c",
                  "branch_new_id": "122"
                },
                {
                  "description": "Student\/peer\/adult said something that is unclear or inaudible",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "360fc7",
                  "branch_new_id": "123"
                },
                {
                  "description": "Student verbally responds to peer's non-computing conversation",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "ebcb13",
                  "branch_new_id": "124"
                },
                {
                  "description": "Student redirects peer to the computing task",
                  "next": "effa06",
                  "next_id": "9",
                  "branch_id": "0885ff",
                  "branch_new_id": "125"
                }
              ]
            ]
          },
          {
            "id": 8,
            "node_id": 128,
            "nodeid": "9591c0",
            "title": "How does the event end or continue? (Independent Path)",
            "groups": [
              "independent"
            ],
            "group_hex": [
              "c4414f"
            ],
            "should_group_choices": false,
            "branches": [
              [
                {
                  "description": "Student's cursor stopped moving for more than 30 seconds, or the student leaves station (> 30 seconds) and returns to independently work [end path]",
                  "aside": "Timestamp this code at the point where the student leaves the station or the student's cursor stops moving. Begin the next path at the point where the student returns to their station or the cursor begins moving.",
                  "branch_id": "08cfb9",
                  "branch_new_id": "129"
                },
                {
                  "description": "The video record ends [end path]",
                  "branch_id": "ea409a",
                  "branch_new_id": "130"
                },
                {
                  "description": "Student solves a problem previously discussed during an interaction [end path]",
                  "branch_id": "3fcc28",
                  "branch_new_id": "131"
                },
                {
                  "description": "Interaction begins [end path]",
                  "aside": "Timestamp this code when the interaction begins. Create a new path for the interaction and start it ",
                  "branch_id": "e75b96",
                  "branch_new_id": "132"
                },
                {
                  "description": "Student switches to a computing related task while working independently [self loop]",
                  "next": "9591c0",
                  "next_id": "128",
                  "branch_id": "90973c",
                  "branch_new_id": "133",
                  "node_sub_group": "independent_computing"
                },
                {
                  "description": "Student switches to a non computing task while working independently [self loop]",
                  "next": "9591c0",
                  "next_id": "128",
                  "branch_id": "5c3968",
                  "branch_new_id": "134",
                  "node_sub_group": "independent_non_computing"
                }
              ]
            ]
          }
        ]
      }`;
}
?>