<?php
$page = "dashboard";
$dataTarget = "#demo_help_box";
$dataOffset = "10";
$dataSpy = "scroll";
include '../includes/header.php';
include '../api/ccoi_dbhookup.php';
$id=$_GET['id']+0;

//TODO: check that they are allowed in here
echo "getting session info";
$session = getSessionInfo($id); //defined below
print_r($session);
//echo "session: "; print_r($session);

// Get node data
$return = mysqli_query($db, "SELECT * FROM tbNodes WHERE 1");
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

?>
<script>let sessionID = <?php echo $id; ?></script>
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

?>