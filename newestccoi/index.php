<?php
$page = "CCOI Tool";
$CCOI_requireslogin = true;
include './includes/header.php';
include $includeroot.$devprodroot.'/api/ccoi_dbhookup.php';
echo "<script language='javascript'>var derServer='https://{$serverroot}{$devprodroot}/';var derDevProd='{$devprodroot}';</script>\n";		// sigh -- needs to be after header, but before JS below
$id = $_GET['id'];

// If a session hasn't been selected, kick user to group selection to pick one
if($_SESSION['currentlyloadedapp'] < 1 || !in_array($_SESSION['currentlyloadedapp'], $_SESSION['myappids'])){
  header("Location: group");
  //print_r($_SESSION);
}

$sessions = getSessions(); //defined below
$videos = getVideos(); //defined below
$paths = getPaths(); //defined below

?>
<script>
    var sessionVars = <?php echo json_encode($_SESSION); ?>;
    if (sessionVars == null) {
        sessionVars = {};
    }
    console.log("sessionVars:");
    console.log(sessionVars);
    console.log("$_SESSION['currentlyloadedapp']");
    console.log(<?= $_SESSION['currentlyloadedapp']; ?>);
    console.log("$_SESSION['roles'][$_SESSION['currentlyloadedapp']]['admin']");
    console.log(<?= $_SESSION['roles'][$_SESSION['currentlyloadedapp']]['admin']; ?>);
</script>
<link rel="stylesheet" href="<?php echo $devprodroot; ?>/css/popup.css">
<link rel="stylesheet" href="popup.css">
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
<?php endif; if ( $_SESSION['roles'][$_SESSION['currentlyloadedapp']]['admin'] == true): ?>
                    <div class="col-md-12">
                        <div id="session_go_back" class="row pt-3 pb-4">
                            <div class="col">
                                <a class="underlined-btn" href="admin?id=<?= $_SESSION['currentlyloadedapp']; ?>"><span class="oi oi-arrow-thick-left mr-2"></span><span class="btn-text">Go to Admin Panel</span></a>
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
                                    <!--<button type="button" onclick="openDialog('dialog1', this)">Add Delivery Address</button>-->
                                    <button id="new_session_button" type="button" class="btn btn-gold float-right" data-toggle="tooltip" data-html="true" title="Click here to start" onclick="openDialog('newSessionDialog', this)">Add Session</button>
                                    <!--<button id="new_session_button" type="button" class="btn btn-gold float-right" data-toggle="tooltip" data-html="true" title="Click here to start" onclick="blurScreen(); showNewSess();">Add Session</button>-->
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
                                                <div style="width:84%;">
                                                    <a class="btn-link session-edit" href="obz?id=<?= $currentSession['id']; ?>"><?= $currentSession['name'] ?></a>
                                                </div>
                                                <div style="width:14%; display:flex; justify-content:space-around;">
                                                    <a class="btn-link session-edit" href="obz?id=<?= $currentSession['id']; ?>"><span class="oi oi-pencil" title="Edit Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="javascript:void(0)" onclick="deleteSession(<?= $currentSession['id']; ?>)"><span class="oi oi-trash" title="Delete Session" aria-hidden="true"></span></a>
                                                    <!--<a class="btn-link" href="/visualizations.php"><span class="oi oi-pie-chart" title="View Visualizations" aria-hidden="true"></span></a>-->
                                                </div>
                                            </li>
<?php endforeach; ?>
                                        </ul> 
                                        <h4>Other's Sessions</h4>
                                        <ul id="others_session_list" class="mb-4">
<?php foreach ($sessions['others'] as $index => $currentSession): ?>
                                            <li class="session-listing my-2" id="others-<?= $currentSession['id']; ?>" style="display:flex; flex-wrap:no-wrap;">
                                            <div style="width:96%;">
                                                    <a class="btn-link session-edit" href="obz?id=<?= $currentSession['id']; ?>"><?= $currentSession['name'] ?></a>
                                                </div>
                                                <!--
                                                <div style="width:18%; display:flex; justify-content:flex-end;">
                                                    <a class="btn-link" href="javascript:void(0)"><span class="oi oi-pie-chart" title="View Visualizations" aria-hidden="true"></span></a>
                                                </div>
-->
                                            </li>
<?php endforeach; ?>
                                        </ul> 
                                    </div>
                                    <div id="playground_content" class="d-none">
                                        <h4>Your Playground Sessions (for testing)</h4>
                                        <ul id="playground_session_list" class="mb-4">
<?php foreach ($sessions['playground'] as $index => $currentSession): ?>
                                            <li class="session-listing my-2" id="playground-<?= $currentSession['id']; ?>" style="display:flex; flex-wrap:no-wrap;">
                                                <div style="width:84%;">
                                                    <a class="btn-link session-edit" href="obz?id=<?= $currentSession['id']; ?>&isPlayground=1"><?= $currentSession['name'] ?></a>
                                                </div>
                                                <div style="width:14%; display:flex; justify-content:space-around;">
                                                    <a class="btn-link session-edit" href="obz?id=<?= $currentSession['id']; ?>&isPlayground=1"><span class="oi oi-pencil" title="Edit Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="javascript:void(0)" onclick="deleteSession(<?= $currentSession['id']; ?>, true)"><span class="oi oi-trash" title="Delete Session" aria-hidden="true"></span></a>
                                                    <!--<a class="btn-link" href="/visualizations.php"><span class="oi oi-pie-chart" title="View Visualizations" aria-hidden="true"></span></a>-->
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
                                    <button id="irr_button" class="btn btn-gold btn-full-width my-2" onclick="location.href='https://ccoi.education.ufl.edu/newestccoi/api/ccoi_irr_viewer'">Inter-Rater Reliability <span class="oi oi-people px-2" title="Inter-Rater Reliability"></span></button>
                                </div>
                            </div>
                            <div class="sticky-top">
                                <div id="demo_help_box" class="row pt-3">
                                    <div class="col">
                                        <div class="md-boxed-content light-blue-background">
                                            <h4>C-COI Dashboard Instructions</h4>
                                            <ol id="demo_help_ol">
                                                <li>Create a new session if necessary, be sure to fill in pertinent info.</li>
                                                <li>Once created, session list will update.</li>
                                                <li><strong>Note: You can toggle between playground and research by clicking the button.</strong></li>
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

            <!-- ACCESSIBLE POPUP SHENANIGANS -->
            <div id="dialog_layer" class="dialogs">

                <div role="dialog" id="newSessionDialog" aria-labelledby="newSessionDialog_label" aria-modal="true" class="hidden">
                    <h2 id="newSessionDialog_label" class="dialog_label pb-2 mb-4">Create New Session</h2>
                    <div class="dialog_form">
                        <form name="sessionForm" action="" method="post" id="sessionForm">
                        <div class="dialog_form_item">
                            <label>
                            <span class="label_text">Session Name:</span>
                            <input type="text" class="wide_input" name="name" placeholder="New Observation">
                            </label>
                        </div>
                        <div class="dialog_form_item">
                            <label>
                            <span class="label_text">Student ID:</span>
                            <input type="text" class="wide_input" name='studentid' placeholder='12345678'>
                            </label>
                        </div>
                        <div class="dialog_form_item">
                            <label>
                            <span class="label_text">Coding Date:</span>
                            <input type="date" class="wide_input" name='codingDate' placeholder='MM/DD/YYYY'>
                            </label>
                        </div>
                        <div class="dialog_form_item">
                            <label>
                            <span class="label_text">Video:</span>
                            <select class="wide_input" id= "video" name='video'>
<?php foreach ($videos as $index => $currentVideo): ?>
                                <option value="<?= $currentVideo['id']; ?>"><?= $currentVideo['name']; ?></option>
<?php endforeach; ?>
                            </select>
                            </label>
                        </div>
                        <div class="dialog_form_item">
                            <label>
                            <span class="label_text">Path:</span>
                            <select class="wide_input" id= "path" name='path'>
<?php foreach ($paths as $index => $currentPath): ?>
                            <option value="<?= $currentPath['id']; ?>"><?= $currentPath['name']; ?></option>
<?php endforeach; ?>
                            </select>
                            </label>
                        </div>
                        </form>
                    </div>
                    <div class="dialog_form_actions">
                        <button type="button" onclick="createNewSession(); closeDialog(this)" id="sessionSubmit" style="margin-top: 2rem;">Create Session</button>
                        <button type="button" onclick="closeDialog(this)" style="height: fit-content;">Cancel</button>
                    </div>
                </div>

                <div role="dialog" id="dialog1" aria-labelledby="dialog1_label" aria-modal="true" class="hidden">
                    <h2 id="dialog1_label" class="dialog_label">Add Delivery Address</h2>
                    <div class="dialog_form">
                    <div class="dialog_form_item">
                        <label>
                        <span class="label_text">Street:</span>
                        <input type="text" class="wide_input">
                        </label>
                    </div>
                    <div class="dialog_form_item">
                        <label>
                        <span class="label_text">City:</span>
                        <input type="text" class="city_input">
                        </label>
                    </div>
                    <div class="dialog_form_item">
                        <label>
                        <span class="label_text">State:</span>
                        <input type="text" class="state_input">
                        </label>
                    </div>
                    <div class="dialog_form_item">
                        <label>
                        <span class="label_text">Zip:</span>
                        <input type="text" class="zip_input">
                        </label>
                    </div>

                    <div class="dialog_form_item">
                        <label for="special_instructions">
                        <span class="label_text">Special instructions:</span>
                        </label>
                        <input id="special_instructions" type="text" aria-describedby="special_instructions_desc" class="wide_input">
                        <div class="label_info" id="special_instructions_desc">
                        For example, gate code or other information to help the driver find you
                        </div>
                    </div>
                    </div>
                    <div class="dialog_form_actions">
                        <button type="button" onclick="openDialog('dialog2', this, 'dialog2_para1')">Verify Address</button>
                        <button type="button" onclick="replaceDialog('dialog3', undefined, 'dialog3_close_btn')">Add</button>
                        <button type="button" onclick="closeDialog(this)">Cancel</button>
                    </div>
                </div>

                
                <div id="dialog2" role="dialog" aria-labelledby="dialog2_label" aria-describedby="dialog2_desc" aria-modal="true" class="hidden">
                    <h2 id="dialog2_label" class="dialog_label">Verification Result</h2>
                    <div id="dialog2_desc" class="dialog_desc">
                    <p tabindex="-1" id="dialog2_para1">This is just a demonstration. If it were a real application, it would
                        provide a message telling whether the entered address is valid.</p>
                    <p>
                        For demonstration purposes, this dialog has a lot of text. It demonstrates a
                        scenario where:
                    </p>
                    <ul>
                        <li>The first interactive element, the help link, is at the bottom of the dialog.</li>
                        <li>If focus is placed on the first interactive element when the dialog opens, the
                        validation message may not be visible.</li>
                        <li>If the validation message is visible and the focus is on the help link, then
                        the focus may not be visible.</li>
                        <li>
                        When the dialog opens, it is important that both:
                        <ul>
                            <li>The beginning of the text is visible so users do not have to scroll back to
                            start reading.</li>
                            <li>The keyboard focus always remains visible.</li>
                        </ul>
                        </li>
                    </ul>
                    <p>There are several ways to resolve this issue:</p>
                    <ul>
                        <li>Place an interactive element at the top of the dialog, e.g., a button or link.</li>
                        <li>Make a static element focusable, e.g., the dialog title or the first block of
                        text.</li>
                    </ul>
                    <p>
                        Please <em>DO NOT </em> make the element with role dialog focusable!
                    </p>
                    <ul>
                        <li>The larger a focusable element is, the more difficult it is to visually
                        identify the location of focus, especially for users with a narrow field of view.</li>
                        <li>The dialog has a visual border, so creating a clear visual indicator of focus
                        when the entire dialog has focus is not very feasible.</li>
                        <li>Screen readers read the label and content of focusable elements. The dialog
                        contains its label and a lot of content! If a dialog like this one has focus, the
                        actual focus is difficult to comprehend.</li>
                    </ul>
                    <p>
                        In this dialog, the first paragraph has <code>tabindex=<q>-1</q></code>. The first
                        paragraph is also contained inside the element that provides the dialog description, i.e., the element that is referenced
                        by <code>aria-describedby</code>. With some screen readers, this may have one negative
                        but relatively insignificant side effect when the dialog opens -- the first paragraph
                        may be announced twice. Nonetheless, making the first paragraph focusable and setting
                        the initial focus on it is the most broadly accessible option.
                    </p>
                    </div>
                    <div class="dialog_form_actions">
                    <a href="#" onclick="openDialog('dialog4', this)">link to help</a>
                    <button type="button" onclick="openDialog('dialog4', this)">accepting an alternative form</button>
                    <button type="button" onclick="closeDialog(this)">Close</button>
                    </div>
                </div>

                
                <div id="dialog3" role="dialog" aria-labelledby="dialog3_label" aria-describedby="dialog3_desc" aria-modal="true" class="hidden">
                    <h2 id="dialog3_label" class="dialog_label">Address Added</h2>
                    <p id="dialog3_desc" class="dialog_desc">
                    The address you provided has been added to your list of delivery addresses. It is ready
                    for immediate use. If you wish to remove it, you can do so from
                    <a href="#" onclick="openDialog('dialog4', this)">your profile.</a>
                    </p>
                    <div class="dialog_form_actions">
                    <button type="button" id="dialog3_close_btn" onclick="closeDialog(this)">OK</button>
                    </div>
                </div>

                <div id="dialog4" role="dialog" aria-labelledby="dialog4_label" aria-describedby="dialog4_desc" class="hidden" aria-modal="true">
                    <h2 id="dialog4_label" class="dialog_label">End of the Road!</h2>
                    <p id="dialog4_desc" class="dialog_desc">
                    You activated a fake link or button that goes nowhere!
                    The link or button is present for demonstration purposes only.
                    </p>
                    <div class="dialog_form_actions">
                    <button type="button" id="dialog4_close_btn" onclick="closeDialog(this)">Close</button>
                    </div>
                </div>
            </div>
            <!--
            <div>
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
                </div>
                <div id="sessionResponse" class="popup">
                    <button type="button" class="exitPopup" onclick="closePopups()">✕</button>
                    <p id="sessionResponseText"></p>
                </div>
            </div>
-->

        </main>
        <?php include 'includes/footer.php'; ?>
        <script src="/js/jquery-3.4.1.min.js"></script>
        <script src="/js/utility.js"></script>
        <!--
        <script src="./js/draggable.js"></script>
        <script src="./js/demo.js"></script>
        <script src="/js/observation.js"></script>
        <script src="/js/bootstrap.min.js"></script>
        script src="/js/zpbdash.js"></script>
        <script src="/js/ccoi.js"></script>
        <script src="/js/ccoi-data-model.js"></script>
        <script src="./js/zpbccoi.js"></script>-->
        <script src="popup.js"></script>
        <script>
            var isPlayground = false;

            const blur = document.getElementById('blurOverlay');
            const newSessWin = document.getElementById('newSession');
            function blurScreen() {
                blur.classList.toggle('blurred');
            }
            function showNewSess() {
                newSessWin.classList.toggle('popped');
            }
            function closePopups() {
                blur.classList.remove('blurred');
                const currentPopup = document.getElementsByClassName("popped")[0];
                currentPopup.classList.toggle("popped");
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
                                    //console.log('targeting element with ID: '+'playground-'+id);
                                    //console.log('targetElement in conditional: '+targetElement);
                                }
                                else{
                                    targetElement = document.getElementById('research-'+id);
                                    //console.log('targeting element with ID: '+'research-'+id);
                                    //console.log('targetElement in conditional: '+targetElement);
                                }
                                //console.log('targetElement out of conditional: '+targetElement);
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
                    var url =  encodeURI(derServer+'zpb_ajax.php?'+sendStr);			console.log(url);
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
                let formData = new FormData(document.getElementById("sessionForm"));
                
                formData.append('newSession', 1);
                if (formData.get('name') == ""){
                    formData.set('name', 'New Observation Set');
                }
                let sendStr = '';
                let name = formData.get('name');
                console.log("formData:");
                formData.forEach(function(value, key){
                    if(value == ""){}
                    else {
                        console.log(`${key} = ${value}`);
                        sendStr += `&${key}=${value}`;
                    }
                });
                sendStr = sendStr.substring(1);

                
                let tbName = 'research';
                let extraText = '';
                if(isPlayground){
                    tbName = 'playground';
                    //formData.set('isPlayground', "1");
                    //dataObject['isPlayground'] = 1;
                    extraText = '&isPlayground=1';
                    sendStr += '&isPlayground=1';
                }
                
                var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
                xmlHttp.onreadystatechange = function() {
                    var data=getHTML(xmlHttp);
                    if(data){
                        console.log("data returned: ");
                        console.log(data);
                        let returnedInt = parseInt(data);

                        if(false){ //OLD BEHAVIOR QUARANTINE
                            newSessWin.classList.remove('popped');
                            let responseWindow = document.getElementById('sessionResponse');
                            responseWindow.classList.add('popped');
                            let responseText = document.getElementById('sessionResponseText');

                            if (returnedInt == -1) {
                                responseText.innerText = "There was an error creating that session. <br> Please refresh the page and try again. <br> <br> If the problem persists, please contact an administrator.";
                                //console.error("Missing required data");
                            }
                            else {
                                responseText.innerHTML = "Session with name '"+name+"' has been created successfully. <br><br> It has been added to the bottom of your "+tbName+" session list.";
                                let newEntry = document.createElement("li");
                                newEntry.setAttribute("class", "session-listing my-2");
                                newEntry.setAttribute("id", tbName+"-"+returnedInt);
                                newEntry.setAttribute("style", "display:flex; flex-wrap:no-wrap;");
                                /*
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
                                                */
                                newEntry.innerHTML = `  <div style="width:84%;">
                                                            <a class="btn-link session-edit" href="obz?id=${returnedInt}${extraText}">${name}</a>
                                                        </div>
                                                        <div style="width:14%; display:flex; justify-content:space-around;">
                                                            <a class="btn-link session-edit" href="obz?id=${returnedInt}${extraText}"><span class="oi oi-pencil" title="Edit Session" aria-hidden="true"></span></a>
                                                            <a class="btn-link" href="javascript:void(0)" onclick="deleteSession(${returnedInt})"><span class="oi oi-trash" title="Delete Session" aria-hidden="true"></span></a>
                                                            <!--<a class="btn-link" href="javascript:void(0)"><span class="oi oi-pie-chart" title="View Visualizations" aria-hidden="true"></span></a>-->
                                                        </div>`;
                                let playgroundList = document.getElementById(tbName+"_session_list");
                                playgroundList.appendChild(newEntry);
                            }
                        }
                        if(true){
                            if (returnedInt == -1) {
                                alert("There was an error creating that session, please refresh the page and try again. If the problem persists, please contact an administrator.");
                                //console.error("Missing required data");
                            }
                            else {
                                //responseText.innerHTML = "Session with name '"+name+"' has been created successfully. <br><br> It has been added to the bottom of your "+tbName+" session list.";
                                let newEntry = document.createElement("li");
                                newEntry.setAttribute("class", "session-listing my-2");
                                newEntry.setAttribute("id", tbName+"-"+returnedInt);
                                newEntry.setAttribute("style", "display:flex; flex-wrap:no-wrap;");
                                /*
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
                                                */
                                newEntry.innerHTML = `  <div style="width:84%;">
                                                            <a class="btn-link session-edit" href="obz?id=${returnedInt}${extraText}">${name}</a>
                                                        </div>
                                                        <div style="width:14%; display:flex; justify-content:space-around;">
                                                            <a class="btn-link session-edit" href="obz?id=${returnedInt}${extraText}"><span class="oi oi-pencil" title="Edit Session" aria-hidden="true"></span></a>
                                                            <a class="btn-link" href="javascript:void(0)" onclick="deleteSession(${returnedInt})"><span class="oi oi-trash" title="Delete Session" aria-hidden="true"></span></a>
                                                            <!--<a class="btn-link" href="javascript:void(0)"><span class="oi oi-pie-chart" title="View Visualizations" aria-hidden="true"></span></a>-->
                                                        </div>`;
                                let playgroundList = document.getElementById(tbName+"_session_list");
                                playgroundList.appendChild(newEntry);
                            }
                        }
                    }
                }
                //sendStr='newSession=1'+extraText+'&name='+name;
                //sendStr=JSON.stringify(dataObject);
                console.log("sendStr: "+sendStr);
                
                var url =  encodeURI(derServer+'zpb_ajax.php?'+sendStr);			console.log(url);
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

            const queryString = window.location.search;
            console.log(queryString);
            const urlParams = new URLSearchParams(queryString);
            console.log(urlParams);
            if( urlParams.get('isPlayground') ){
                switchMode();
            }
        </script>
    </body> 
</html>



<?php
include '../includes/footer.php';

function getSessions(){
    if( !empty($_SESSION['pid']) && is_numeric($_SESSION['pid']) ){
        $db = $GLOBALS["db"];
        $uid=$_SESSION['pid']+0;

        if(is_numeric($uid)){    
            //Get session IDs of research sessions
            $return=mysqli_query($db,"SELECT sessionid FROM tbPeopleAppSessions WHERE personid='$uid' AND appid='{$_SESSION['currentlyloadedapp']}' AND inactive IS NULL");		
            while($d=mysqli_fetch_assoc($return)){$sessionids[]=$d['sessionid'];}
            $sidstext=implode(',',$sessionids);
            var_dump($sessionids);

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

            $return=mysqli_query($db,"SELECT id, name FROM tbPaths WHERE id IN ($pathidstext) AND invalid IS NULL");		
            while($d=mysqli_fetch_assoc($return)){$paths[]=$d;}

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