<?php
$page = "dashboard";
$zpbLink = "/ZPB/dashboard";
$dataTarget = "#demo_help_box";
$dataOffset = "10";
$dataSpy = "scroll";
include '../includes/header.php';
//include $includeroot.$devprodroot.'/api/ccoi_dbhookup.php';
$siteAdmins = [
    42,     //Zack
    46,     //Brandon
    32,     //Mark
];
//$apps = getSessions(); //defined below
/*
echo "myappids: "; var_dump($_SESSION['myappids']);
echo "<br>myappnames: "; var_dump($_SESSION['myappnames']);
echo "<br>currentlyloadedapp:";
var_dump($_SESSION['currentlyloadedapp']);
*/
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
<?php if (in_array($_SESSION['pid'], $siteAdmins)): ?>
                                    <button id="new_session_button" type="button" class="btn btn-gold float-right" data-toggle="tooltip" data-html="true" title="Click here to start" onclick="createNewApp()">Add Group</button>
<?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row pt-3 pr-md-5">
                                <div class="col-12 btn-div">
                                    <h4>Research Groups</h4>
                                    <ul id="research_session_list">
<?php $numApps = count($_SESSION['myappids']); for ($i = 0; $i < $numApps; $i++): ?>
                                        <li class="session-listing my-2">
                                            <div class="row">
                                                <div class="col-sm-11 col-12">
                                                    <a class="btn-link session-edit" href="javascript:void(0)" onclick="changeCurrentSession(<?= $_SESSION['myappids'][$i]; ?>)"><?= $_SESSION['myappnames'][$i]; ?></a>
                                                </div>
                                                <div class="col-sm-1 col-12">
<?php if ($_SESSION['roles'][$_SESSION['myappids'][$i]]['admin']==true): ?>
                                                    <a class="btn-link session-edit" href="admin?id=<?= $_SESSION['myappids'][$i]; ?>"><span class="oi oi-pencil px-2" title="Edit Research Group" aria-hidden="true"></span></a>
<?php endif; ?>
                                                </div>
                                            </div>
                                        </li>
<?php endfor; ?>
                                    </ul> 
                                </div>
                            </div>

                        </div>
                        <div class="col-md-4 col-12 d-none">
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
            var derServer='https://ccoi-dev.education.ufl.edu/';

            function GetAjaxReturnObject(mimetype){
                var xmlHttp=null;
                if (window.XMLHttpRequest) { // Mozilla, Safari, ...
                    xmlHttp = new XMLHttpRequest();
                    if (xmlHttp.overrideMimeType) {xmlHttp.overrideMimeType(mimetype);}
                } else if (window.ActiveXObject) { // IE
                    try {xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");}catch (e) {try {xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");}catch (e) {}}
                }
                return xmlHttp;
            }

            function getHTML(httpRequest) {
                if (httpRequest.readyState===4) {
                    if (httpRequest.status === 200) {			// if buggy, check logs for firefox / OPTIONS instead of POST -- need same domain
                        return httpRequest.responseText;
                    }
                }
            }

            //Function to create new app
            function createNewApp() {
                let name = prompt("Enter the name of the new group:");
                
                var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
                xmlHttp.onreadystatechange = function() {
                    var data=getHTML(xmlHttp);
                    if(data){
                        let returnedInt = parseInt(data);
                        if (returnedInt == -1) {
                            console.error("Missing required data");
                        }
                        else {
                            let newEntry = document.createElement("li");
                            newEntry.setAttribute("class", "session-listing my-2");
                            newEntry.innerHTML = `<div class="row">
                                                <div class="col-sm-9 col-12">
                                                    <a class="btn-link session-edit" href="dashboard">${name}</a>
                                                </div>
                                                <div class="col-sm-3 col-12">
                                                    <a class="btn-link session-edit" href="dashboard"><span class="oi oi-pencil px-2" title="Edit Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="#"><span class="oi oi-trash px-2" title="Delete Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="#"><span class="oi oi-pie-chart px-2" title="View Visualizations" aria-hidden="true"></span></a>
                                                </div>
                                            </div>`;
                            let playgroundList = document.getElementById("research_session_list");
                            playgroundList.appendChild(newEntry);
                        }
                    }
                }
                sendStr='newApp=1&name='+name;
                var url =  encodeURI(derServer+'ZPB/zpb_ajax.php?'+sendStr);			console.log(url);
                xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
            }

            //Function to change current app to different app
            function changeCurrentSession(newSessionID) {
                var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
                xmlHttp.onreadystatechange = function() {
                    var data=getHTML(xmlHttp);
                    if(data){
                        if(data=="y"){
                            window.location.href = '/ZPB/dashboard';
                        }
                        else {
                            console.error("invalid new app id");
                            alert("Could not verify that new group is valid. Please refresh the page.");
                        }
                    }
                }
                sendStr='changeCurrentApp=1&changeTo='+newSessionID;
                var url =  encodeURI(derServer+'ZPB/zpb_ajax.php?'+sendStr);			console.log(url);
                xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
            }
        </script>
    </body> 
</html>