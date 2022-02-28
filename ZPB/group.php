<?php
$page = "dashboard";
$zpbLink = "/ZPB/dashboard";
$dataTarget = "#demo_help_box";
$dataOffset = "10";
$dataSpy = "scroll";
include '../includes/header.php';
include $includeroot.$devprodroot.'/api/ccoi_dbhookup.php';
$siteAdmins = [
    42,     //Zack
    46,     //Brandon
    32,     //Mark
];
$apps = getSessions(); //defined below
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
                                    <h1 class="red-font">Research Groups</h1>
                                    <h5 style="text-transform: none;">Select a research group to enter its dashboard</h5>
                                </div>
                                <div class="col-md-4 col-12 pt-2">
                                    <button id="new_session_button" type="button" class="btn btn-gold float-right d-none" data-toggle="tooltip" data-html="true" title="Click here to start">Add Session</button>
                                    <button id="save_session_button" type="button" class="btn btn-blue float-right disabled d-none" data-toggle="tooltip" data-html="true" title="Click here to save your session">Save Session</button>
                                </div>
                            </div>
                            
                            <div class="row pt-3 pr-md-5">
                                <div class="col-12 btn-div">
                                    <h4>Research Groups</h4>
                                    <ul id="research_session_list">
<?php foreach ($apps as $currentApp): ?>
                                        <li class="session-listing">
                                            <div class="row">
                                                <div class="col-sm-9 col-12">
                                                    <a class="btn-link session-edit" href="dashboard?id=<?= $currentApp['id']; ?>"><?= $currentApp['name']; ?></a>
                                                </div>
                                                <div class="col-sm-3 col-12">
                                                    <a class="btn-link session-edit" href="dashboard?id=<?= $currentApp['shortname']; ?>"><span class="oi oi-pencil px-2" title="Edit Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="#"><span class="oi oi-trash px-2" title="Delete Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="#"><span class="oi oi-pie-chart px-2" title="View Visualizations" aria-hidden="true"></span></a>
                                                </div>
                                            </div>
                                        </li>
<?php endforeach; ?>
                                    </ul> 
                                </div>
                            </div>

                        </div>
                        <div class="col-md-4 col-12">
                            <div class="row">
                                <div class="col">
                                    <button id="launch_video_button" class="btn btn-blue btn-full-width my-2">Open Video <span class="oi oi-external-link px-2" title="Open Session Video"></span></button>
                                    <button id="viz_button" class="btn btn-gold btn-full-width my-2 d-none">Inter-Rater Reliability <span class="oi oi-people px-2" title="Inter-Rater Reliability Demo"></span></button>
                                    <button id="irr_button" class="btn btn-gold btn-full-width my-2">Inter-Rater Reliability <span class="oi oi-people px-2" title="Inter-Rater Reliability"></span></button>
                                </div>
                            </div>
                            <div class="sticky-top">
                                <div id="demo_help_box" class="row pt-3">
                                    <div class="col">
                                        <div class="md-boxed-content light-blue-background">
                                            <h4>C-COI Instructions</h4>
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
        <script src="/js/jquery-3.4.1.min.js"></script>
        <script src="/js/utility.js"></script>
        <!--<script src="./js/ccoi.js"></script>
        <script src="./js/ccoi-data-model.js"></script>
        <script src="./js/draggable.js"></script>
        <script src="./js/observation.js"></script>
        <script src="./js/demo.js"></script>-->
        <script src="/js/bootstrap.min.js"></script>
        <!--<script src="./js/zpbccoi.js"></script>-->
        <script>
            console.log(`<?php var_dump($sessions) ?>`);
        </script>
    </body> 
</html>



<?php

function getSessions(){
    if( !empty($_SESSION['pid']) && is_numeric($_SESSION['pid']) ){
        $db = $GLOBALS["db"];
        $uid=$_SESSION['pid']+0;

        if(is_numeric($uid)){    
            //Get all appIDs user is in
            $return=mysqli_query($db,"SELECT appid FROM tbPersonAppRoles WHERE personid='$uid'");		
            while($d=mysqli_fetch_assoc($return)){$appids[]=$d['appid'];}
            $appidstext=implode(',',$appids);

            //Get names of all apps
            $return=mysqli_query($db,"SELECT * FROM tbApps WHERE id IN ($appidstext) ");		
            while($d=mysqli_fetch_assoc($return)){$appList[]=$d;}		//echo "d: "; var_dump($d);

            return $appList;
        }
        else{
            return "<br>UID isn't numberic :(";
        }
    }
    else
        return "<br>Session isn't valid :(";
}

?>