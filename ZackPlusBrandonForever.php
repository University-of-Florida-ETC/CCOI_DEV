<?php
$page = "zpb";
$zpbLink = "/ZackPlusBrandonForever";
$dataTarget = "#demo_help_box";
$dataOffset = "10";
$dataSpy = "scroll";
include 'includes/header.php';
?>
        <main role="main">
            <div class="container-fluid">
                <div class="container">
                    <div id="session_go_back" class="row pt-3 pb-5 d-none">
                        <div class="col">
                            <a class="underlined-btn" href=<?php echo $zpbLink; ?>><span class="oi oi-arrow-thick-left mr-2"></span><span class="btn-text">Back to Session Select</span></a>
                        </div>
                    </div>
                   <div class="row py-5">
                        <div class="col-md-8">
                            <div class="row pr-md-5">
                                <div class="col-md-8 col-12">
                                    <h1 class="red-font">Demo Test</h1>
                                    <h5>Demo Session</h5>
                                </div>
                                <div class="col-md-4 col-12 pt-2">
                                    <button id="new_session_button" type="button" class="btn btn-gold float-right d-none" data-toggle="tooltip" data-html="true" title="Click here to start">Add Session</button>
                                    <button id="save_session_button" type="button" class="btn btn-blue float-right disabled d-none" data-toggle="tooltip" data-html="true" title="Click here to save your session">Save Session</button>
                                </div>
                            </div>
                            
                            <div class="row pt-3 pr-md-5">
                              <div class="col-12 btn-div">
                                <ul id="session_list" class="d-none">
                                </ul> 
                              </div>
                            </div>


                            <div id="dom_group_1" class="row pt-3 d-none">
                                <div id="path_start" class="col-12 pt-3 pr-md-5 accordion">
                                    <div class="card">
                                        <div class="card-header" id="session_meta_collapse_heading">
                                            <h5 class="mb-0">
                                                <button id="session_meta_title" class="btn btn-link" data-toggle="collapse" data-target="#session_meta_collapse" aria-expanded="true" aria-controls="session_meta_collapse">
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
                                                            <input placeholder="Session Name" id="session_title" name="name" type="text" class="form-control">
                                                        </div>
                                                        <div class="form-group col">
                                                            <label for="session_student">Student ID</label>
                                                            <input placeholder="Student ID" id="session_student" name="studentID" type="text" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col">
                                                            <label for="session_date">Coding Date</label>
                                                            <input id="session_date" name="date" type="date" class="datepicker">
                                                        </div>
                                                        <div class="form-group col">
                                                            <label for='session_video_title'>Video</label>
                                                            <input type="text" id="session_video_title" name="session_video_title" value="Demo 2020-5-15 C01" class='fakeInput' placeholder='Demo Video' disabled>
                                                            <input type="hidden" id="session_video_url" name="session_video_url" value="Demo_2020-5-15_C01.webm">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="block" for="session_notes" id="session_notes_label">Session notes</label>
                                                        <textarea id="session_notes" name="sessionNotes" class="form-control"></textarea>
                                                    </div>
                                                </form>
                                                <button type="button" class="btn btn-outline-blue btn-sm" data-toggle="collapse" data-target="#session_meta_collapse" aria-expanded="true" aria-controls="session_meta_collapse">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 py-2 pr-md-5">
                                    <button id="add_path_button" type="button" class="btn btn-darkblue" data-toggle="tooltip" data-html="true" title="Click here to add a path">Add Path</button>
                                    <button id="reorder_paths_button" type="button" class="btn btn-outline-darkblue" data-toggle="tooltip" data-html="true" title="Click here to reorder paths">Reorder Paths</button>
                                    <button id="finish_reorder_button" type="button" class="btn btn-outline-darkblue d-none" data-toggle="tooltip" data-html="true" title="Click here when you are done reordering paths">End Reorder</button>
                                    <div class="alert alert-warning path-order-disclaimer mb-0 mt-3 d-none"><small>Note: Save session to complete path reorder process. Path numbers will reflect old path order until the page has been refreshed.</small></div>
                                </div>

                                <div id="path_listing" class="col-12 pt-4 pr-md-5">
                                    <div id="path_list" class="draggable-container">

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
                                                    <input type="text" class="form-control" id="path_label" name="path_label" placeholder="Example Label" >
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-12">
                                                <button id="path_label_button" class="btn btn-blue" type="button">Set Label</button>
                                            </div>
                                        </div>
                                        <div class="row pb-3">
                                            <div class="col-md-2 col-12">
                                                <div class="form-group">
                                                    <input class="form-control" id="timestamp_input_minutes" type="number" min="0" max="9999" step="1" value="0" />
                                                    <label class="text-center" for="timestamp_input_minutes" >minutes</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 col-12">
                                                <div class="form-group">
                                                    <input class="form-control" id="timestamp_input_seconds" type="number" min="0" max="59" step="1" value="0" />
                                                    <label class="text-center" for="timestamp_input_seconds" >seconds</label>
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
                                    <button id="irr_button" class="btn btn-gold btn-full-width my-2 d-none">Inter-Rater Reliability <span class="oi oi-people px-2" title="Inter-Rater Reliability Demo"></span></button>
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
        <?php include 'includes/footer.php'; ?>
        <script src="./js/jquery-3.4.1.min.js"></script>
        <script src="./js/utility.js"></script>
        <script src="./js/ccoi.js"></script>
        <script src="./js/ccoi-data-model.js"></script>
        <script src="./js/draggable.js"></script>
        <script src="./js/observation.js"></script>
        <script src="./js/demo.js"></script>
        <script src="./js/bootstrap.min.js"></script>
        <script src="./js/zpbccoi.js"></script>
        <script>
            console.log("In");
            try{
                if(typeof(jsUserVars) != 'undefined'){
                    console.log("In2");
                    userid=jsUserVars['pid'];
                    //setTimeout(function(){ fetchUserObSets2(userid);},500);
                    setTimeout(function(){ fetchUserObSets2(userid);},50);
                    //fetchUserObSets2(userid);
                }
                console.log("In3");
            }
            catch(error){
                console.log("In4");
                error(error);
            }
        </script>
    </body> 
</html>