<?php
$page = "dashboard";
$zpbLink = "/ZPB/dashboard";
$dataTarget = "#demo_help_box";
$dataOffset = "10";
$dataSpy = "scroll";
include '../includes/header.php';
include $includeroot.$devprodroot.'/api/ccoi_dbhookup.php';
$id = $_GET['id'];
if(isset($_SESSION['pid']) === false){
    header("Location: https://ccoi-dev.education.ufl.edu/login");
}
else if($_SESSION['currentlyloadedapp'] < 1 || !in_array($_SESSION['currentlyloadedapp'], $_SESSION['myappids'])){
    header("Location: group");
}
$sessions = getSessions(); //defined below
$videos = getVideos(); //defined below
$paths = getPaths(); //defined below
?>
<link rel="stylesheet" href="<?php echo $devprodroot; ?>/css/popup.css">
        <main role="main">
            <div class="container-fluid">
                <div class="container">
                   <div class="row py-3">
<?php if ( count($_SESSION['myappids']) > 1): ?>
                    <div class="col-md-12">
                        <div id="session_go_back" class="row pt-3 pb-4">
                            <div class="col">
                                <a class="underlined-btn" href="group"><span class="oi oi-arrow-thick-left mr-2"></span><span class="btn-text">Select a Different Research Group</span></a>
                            </div>
                        </div>
                    </div>
<?php endif; ?>
                        <div class="col-md-8">
                            <div class="row pr-md-5">
                                <div class="col-md-8 col-12">
                                    <h1 class="red-font" id="pageTitle">Sessions</h1>
                                    <h5 style="text-transform: none;" id="pageDesc">Select a session to view or edit the set</h5>
                                </div>
                                <div class="col-md-4 col-12 pt-2">
                                    <button id="new_session_button" type="button" class="btn btn-gold float-right" data-toggle="tooltip" data-html="true" title="Click here to start" onclick="blurScreen(); showNewSess();">Add Session</button>
                                    <button id="save_session_button" type="button" class="btn btn-blue float-right disabled d-none" data-toggle="tooltip" data-html="true" title="Click here to save your session">Save Session</button>
                                </div>
                            </div>
                            
                            <div class="row pt-3 pr-md-5">
                                <div class="col-12 btn-div">
                                    <div id="research_content">
                                        <h4>Your Sessions</h4>
                                        <ul id="research_session_list" class="mb-4">
<?php foreach ($sessions['research'] as $index => $currentSession): ?>
                                            <li class="session-listing my-2" id="research-<?= $currentSession['id']; ?>" style="display:flex; flex-wrap:no-wrap;">
                                                <div style="width:80%;">
                                                    <a class="btn-link session-edit" href="observation?id=<?= $currentSession['id']; ?>"><?= $currentSession['name'] ?></a>
                                                </div>
                                                <div style="width:18%; display:flex; justify-content:space-between;">
                                                    <a class="btn-link session-edit" href="observation?id=<?= $currentSession['id']; ?>"><span class="oi oi-pencil" title="Edit Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="javascript:void(0)" onclick="deleteSession(<?= $currentSession['id']; ?>)"><span class="oi oi-trash" title="Delete Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="javascript:void(0)"><span class="oi oi-pie-chart" title="View Visualizations" aria-hidden="true"></span></a>
                                                </div>
                                            </li>
<?php endforeach; ?>
                                        </ul> 
                                        <h4>Other's Sessions</h4>
                                        <ul id="others_session_list" class="mb-4">
<?php foreach ($sessions['others'] as $index => $currentSession): ?>
                                            <li class="session-listing my-2" id="others-<?= $currentSession['id']; ?>" style="display:flex; flex-wrap:no-wrap;">
                                            <div style="width:80%;">
                                                    <a class="btn-link session-edit" href="observation?id=<?= $currentSession['id']; ?>"><?= $currentSession['name'] ?></a>
                                                </div>
                                                <div style="width:18%; display:flex; justify-content:flex-end;">
                                                    <a class="btn-link" href="javascript:void(0)"><span class="oi oi-pie-chart" title="View Visualizations" aria-hidden="true"></span></a>
                                                </div>
                                            </li>
<?php endforeach; ?>
                                        </ul> 
                                    </div>
                                    <div id="playground_content" class="d-none">
                                        <h4>Your Playground Sessions (for testing)</h4>
                                        <ul id="playground_session_list" class="mb-4">
<?php foreach ($sessions['playground'] as $index => $currentSession): ?>
                                            <li class="session-listing my-2" id="playground-<?= $currentSession['id']; ?>">
                                                <div class="row">
                                                    <div class="col-sm-9 col-12">
                                                        <a class="btn-link session-edit" href="observation?id=<?= $currentSession['id']; ?>&isPlayground=1"><?= $currentSession['name'] ?></a>
                                                    </div>
                                                    <div class="col-sm-3 col-12">
                                                        <a class="btn-link session-edit" href="observation?id=<?= $currentSession['id']; ?>&isPlayground=1"><span class="oi oi-pencil px-2" title="Edit Session" aria-hidden="true"></span></a>
                                                        <a class="btn-link" href="javascript:void(0)" onclick="deleteSession(<?= $currentSession['id']; ?>, 1)"><span class="oi oi-trash px-2" title="Delete Session" aria-hidden="true"></span></a>
                                                        <a class="btn-link" href="javascript:void(0)"><span class="oi oi-pie-chart px-2" title="View Visualizations" aria-hidden="true"></span></a>
                                                    </div>
                                                </div>
                                            </li>
<?php endforeach; ?>
                                        </ul> 
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-4 col-12">
                            <div class="row">
                                <div class="col">
                                    <button id="switch_mode_button" class="btn btn-blue btn-full-width my-2" onclick='switchMode()'>Just Testing?</button>
                                    <button id="launch_video_button" class="btn btn-blue btn-full-width my-2 d-none">Open Video <span class="oi oi-external-link px-2" title="Open Session Video"></span></button>
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
            <div><!--popup stuff -->
                <div id="blurOverlay" onclick="closePopups()"></div>

                <div id="newSession" class="popup">
                    <button type="button" class="exitPopup" onclick="closePopups()">✕</button>
                    <h2>New Session</h2>
                    <form name="sessionForm" action="" method="post" id="sessionForm">
                        <label for="name">Session Name</label>
                        <input id= "name" type="text" name='name' placeholder='New Observation'><br>
                        <label for="studentid">Student ID</label>
                        <input id= "studentid" type="text" name='studentid' placeholder='Student ID'><br>
                        <label for="codingDate">Coding Date</label>
                        <input id= "codingDate" type="date" name='codingDate' placeholder='MM/DD/YYYY'><br>
                        <label for="video">Video</label>
                        <select id= "video" name='video'><br>
<?php foreach ($videos as $index => $currentVideo): ?>
                            <option value="<?= $currentVideo['id']; ?>"><?= $currentVideo['name']; ?></option>
<?php endforeach; ?>
                        </select><br>
                        <label for="path">Path</label>
                        <select id= "path" name='path'><br>
<?php foreach ($paths as $index => $currentPath): ?>
                            <option value="<?= $currentPath['id']; ?>"><?= $currentPath['name']; ?></option>
<?php endforeach; ?>
                        </select>
                        <input id="sessionSubmit" style="margin-top: 2rem;" type='button' value='Create New Session' onclick="createNewSession()">
                    </form>
                </div><!--popup-->
                <div id="sessionResponse" class="popup">
                    <button type="button" class="exitPopup" onclick="closePopups()">✕</button>
                    <p id="emailResponseText"></p>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
        <script src="/js/jquery-3.4.1.min.js"></script>
        <script src="/js/utility.js"></script>
        <!--
        <script src="./js/draggable.js"></script>
        <script src="./js/demo.js"></script> -->
        <script src="/js/observation.js"></script>
        <script src="/js/bootstrap.min.js"></script>
        <!--cript src="/js/zpbdash.js"></script>-->
        <script src="/js/ccoi.js"></script>
        <script src="/js/ccoi-data-model.js"></script>
        <!--<script src="./js/zpbccoi.js"></script>-->
        <!--
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
                else {
                    console.log("Need to login!!");
                }
                
            }
            catch(error){
                console.log("In4");
                error(error);
            }
        </script>
        -->
        <script>
            var isPlayground = false;
            var derServer='https://ccoi-dev.education.ufl.edu/';

            const blur = document.getElementById('blurOverlay');
            const newSessWin = document.getElementById('newSession');
            function blurScreen() {
                blur.classList.toggle('blurred');
            }
            function showNewSess() {
                newSessWin.classList.toggle('popped');
            }
            function closePopups() {
                if (blur.classList.contains('blurred')) {
                    blurScreen();
                    const currentPopup = document.getElementsByClassName("popped")[0];
                    currentPopup.classList.toggle("popped");
                }
            }

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

            console.log(`<?php var_dump($sessions) ?>`);

            function deleteSession(id, isPlayground = false){
                if (confirm('Are you sure you want to delete this session? This cannot be undone!')) {
                    let extraText = '';
                    if(isPlayground)
                        extraText = '&isPlayground=1';

                    var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
                    xmlHttp.onreadystatechange = function() {
                        var data=getHTML(xmlHttp);
                        if(data){
                            console.log(data);
                            if(data == "y") {
                                //Hide the element
                                var targetElement;
                                if (isPlayground){
                                    targetElement = document.getElementById('playground-'+id);
                                    console.log('targeting element with ID: '+'playground-'+id);
                                    console.log('targetElement in conditional: '+targetElement);
                                }
                                else{
                                    targetElement = document.getElementById('research-'+id);
                                    console.log('targeting element with ID: '+'research-'+id);
                                    console.log('targetElement in conditional: '+targetElement);
                                }
                                console.log('targetElement out of conditional: '+targetElement);
                                targetElement.classList.add('d-none');
                            }
                            else if(data == "n") {
                                alert("Session could not be deleted! Please login.");
                            }
                            else if(data == "n") {
                                alert("Session could not be deleted! Contact your head researcher for support.");
                            }
                        }
                    }
                    sendStr='deleteSession=1'+extraText+'&sessionid='+id;
                    var url =  encodeURI(derServer+'ZPB/zpb_ajax.php?'+sendStr);			console.log(url);
                    xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
                } else {
                    // Do nothing!
                    console.log('Deleting session was canceled.');
                }
            }

            function switchMode(){
                let currentElement;
                if(isPlayground){
                    currentElement = document.getElementById("playground_content");
                    currentElement.classList.add('d-none');

                    currentElement = document.getElementById("research_content");
                    currentElement.classList.remove('d-none');

                    currentElement = document.getElementById("switch_mode_button");
                    currentElement.innerText = 'Just Testing?';

                    currentElement = document.getElementById("pageTitle");
                    currentElement.innerText = 'Sessions';

                    currentElement = document.getElementById("pageDesc");
                    currentElement.innerText = 'Select a session to view or edit the set';

                    isPlayground = false;
                }
                else{
                    currentElement = document.getElementById("playground_content");
                    currentElement.classList.remove('d-none');

                    currentElement = document.getElementById("research_content");
                    currentElement.classList.add('d-none');
                    
                    currentElement = document.getElementById("switch_mode_button");
                    currentElement.innerText = 'Enter Real Data';

                    currentElement = document.getElementById("pageTitle");
                    currentElement.innerText = 'Playgrounds';

                    currentElement = document.getElementById("pageDesc");
                    currentElement.innerText = 'Select a test session to view or edit the set';

                    isPlayground = true;
                }
            }

            function createNewSession() {
                //let name = prompt("Enter the name of the new session:");
                const formData = new FormData(document.getElementById("sessionForm"));
                var dataObject = {'newSession': 1};
                formData.forEach(function(value, key){
                    dataObject[key] = value;
                });
                /*
                if (name == NULL || name == ""){
                    name = "New Observation";
                }*/

                if (dataObject['name'] == null || dataObject['name'] == ""){
                    dataObject['name'] = "New Observation Set";
                }

                for (const [key, value] of Object.entries(dataObject)) {
                    if( value == "" ){
                        delete dataObject[key];
                    }
                }

                let tbName = 'research';
                //let extraText = '';
                if(isPlayground){
                    tbName = 'playground';
                    //formData.set('isPlayground', "1");
                    dataObject['isPlayground'] = 1;
                    //extraText = '&isPlayground=1';
                }
                
                var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
                xmlHttp.onreadystatechange = function() {
                    var data=getHTML(xmlHttp);
                    if(data){
                        console.log("data returned: ");
                        console.log(data);
                        let returnedInt = parseInt(data);
                        if (returnedInt == -1) {
                            console.error("Missing required data");
                        }
                        else {
                            let newEntry = document.createElement("li");
                            newEntry.setAttribute("class", "session-listing my-2");
                            newEntry.setAttribute("id", tbName+"-"+returnedInt);
                            newEntry.innerHTML = `<div class="row">
                                                <div class="col-sm-9 col-12">
                                                    <a class="btn-link session-edit" href="observation?id=${returnedInt}${extraText}">${name}</a>
                                                </div>
                                                <div class="col-sm-3 col-12">
                                                    <a class="btn-link session-edit" href="observation?id=${returnedInt}${extraText}"><span class="oi oi-pencil px-2" title="Edit Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="javascript:void(0)"><span class="oi oi-trash px-2" title="Delete Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="javascript:void(0)"><span class="oi oi-pie-chart px-2" title="View Visualizations" aria-hidden="true"></span></a>
                                                </div>
                                            </div>`;
                            let playgroundList = document.getElementById(tbName+"_session_list");
                            playgroundList.appendChild(newEntry);
                        }
                    }
                }
                //sendStr='newSession=1'+extraText+'&name='+name;
                sendStr=JSON.stringify(dataObject);
                console.log("sendStr: "+sendStr);
                
                var url =  encodeURI(derServer+'ZPB/zpb_ajax.php?'+sendStr);			console.log(url);
                xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
            }

            /*
            function formatSessionLists() {
                if (document.getElementById('session-3') !== null){
                    let researchList = document.getElementById('research_session_list');
                    for(let i = 3; i > 0; i++){
                        try{
                            currentElement = document.getElementById('session-'+i);
                            currentElement.classList.add('d-none');
                        }
                        catch(error){
                            break;
                        }
                    }
                }
                //Hide extra sessions
                
            }
            */
        </script>
    </body> 
</html>



<?php
function getSessions(){
    if( !empty($_SESSION['pid']) && is_numeric($_SESSION['pid']) ){
        $db = $GLOBALS["db"];
        $uid=$_SESSION['pid']+0;

        if(is_numeric($uid)){    
            //Get session IDs of research sessions
            $return=mysqli_query($db,"SELECT sessionid FROM tbPeopleAppSessions WHERE personid='$uid' AND appid='{$_SESSION['currentlyloadedapp']}' AND inactive IS NULL");		
            while($d=mysqli_fetch_assoc($return)){$sessionids[]=$d['sessionid'];}
            $sidstext=implode(',',$sessionids);

            //Get session IDs of research sessions
            $return=mysqli_query($db,"SELECT sessionid FROM tbPeopleAppSessions WHERE personid!='$uid' AND appid='{$_SESSION['currentlyloadedapp']}' AND inactive IS NULL AND id != '132'");		
            while($d=mysqli_fetch_assoc($return)){$othersessionids[]=$d['sessionid'];}
            $othersidstext=implode(',',$othersessionids);

            //Get session IDs of playground sessions
            $return=mysqli_query($db,"SELECT sessionid FROM tbPeopleAppPlaygrounds WHERE personid='$uid' AND appid='{$_SESSION['currentlyloadedapp']}' AND inactive IS NULL");		
            while($d=mysqli_fetch_assoc($return)){$playids[]=$d['sessionid'];}		//echo "d: "; var_dump($d);
            $playidstext=implode(',',$playids);

            //Get info (title) of research sessions
            $return=mysqli_query($db,"SELECT s.*, v.url FROM tbSessions s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id IN ($sidstext) AND s.inactive IS NULL");				// ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
            while($d=mysqli_fetch_assoc($return)){$allSessions['research'][]=$d; }		//echo "<br>session: "; var_dump($d);

            //Get info (title) of other people's research sessions
            $return=mysqli_query($db,"SELECT s.*, v.url FROM tbSessions s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id IN ($othersidstext) AND s.inactive IS NULL");				// ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
            while($d=mysqli_fetch_assoc($return)){
                $allSessions['others'][]=$d;
            }		//echo "<br>session: "; var_dump($d);
            
            //Get info (title) of research sessions
            $return=mysqli_query($db,"SELECT s.*, v.url FROM tbPlaygrounds s LEFT JOIN tbVideos v ON s.videoid=v.id WHERE s.id IN ($playidstext) AND s.inactive IS NULL");		// ====== NOTE NOTE NOTE if there are no videos, this might return fewer results
            while($d=mysqli_fetch_assoc($return)){$allSessions['playground'][]=$d;}		//print_r($playgrounds);		//echo "<br>playground: "; var_dump($d);

            return $allSessions;
        }
        else{
            return "<br>UID isn't numberic :(";
        }
    }
    else
        return "<br>Session isn't valid :(";
}

function getVideos(){
    if( !empty($_SESSION['pid']) && is_numeric($_SESSION['pid']) ){
        $db = $GLOBALS["db"];
        $uid=$_SESSION['pid']+0;

        if(is_numeric($uid)){    
            $return=mysqli_query($db,"SELECT id, name FROM tbVideos WHERE appid='{$_SESSION['currentlyloadedapp']}' AND inactive IS NULL");		
            while($d=mysqli_fetch_assoc($return)){$videos[]=$d;}

            return $videos;
        }
        else{
            return "<br>UID isn't numberic :(";
        }
    }
    else
        return "<br>Session isn't valid :(";
}

function getPaths(){
    if( !empty($_SESSION['pid']) && is_numeric($_SESSION['pid']) ){
        $db = $GLOBALS["db"];
        $uid=$_SESSION['pid']+0;

        if(is_numeric($uid)){    
            $return=mysqli_query($db,"SELECT pathid FROM tbAppPaths WHERE appid={$_SESSION['currentlyloadedapp']} AND invalid IS NULL");		
            while($d=mysqli_fetch_assoc($return)){$pathids[]=$d['pathid'];}
            $pathidstext=implode(',',$pathids);
            echo "pathids: "; var_dump($pathids);
            echo "<br>pathidstext: "; var_dump($pathidstext);

            $return=mysqli_query($db,"SELECT id, name FROM tbPaths WHERE id IN ($pathidstext) AND invalid IS NULL");		
            while($d=mysqli_fetch_assoc($return)){$paths[]=$d;}
            echo "<br>paths: "; var_dump($paths);

            return $paths;
        }
        else{
            return "<br>UID isn't numberic :(";
        }
    }
    else
        return "<br>Session isn't valid :(";
}
?>