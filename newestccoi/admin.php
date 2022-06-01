<?php
$page = "Admin Panel";
include './includes/header.php';
include $includeroot.$devprodroot.'/api/ccoi_dbhookup.php';
echo "<script language='javascript'>var derServer='https://{$serverroot}{$devprodroot}/';var derDevProd='{$devprodroot}';</script>\n";		// sigh -- needs to be after header, but before JS below

$appid = $_GET['id'];
if( !($_SESSION['roles'][$appid]['admin']) ){
    header("Location: {$devprodroot}");
    //echo "get redirected, idiot";
}
else {
    $_SESSION['currentlyloadedapp'] = $appid;
}

$users = getUsers();
//$videos = getVideos(); //echo "<br>\$videos:"; var_dump($videos);
$paths = getPaths();

//foreach($_POST as $k=>$v){$_SCRUBBED[$k]=mysqli_real_escape_string($db,$v);}

$_SCRUBBED = scrubIt($_SESSION);

function scrubIt($target){
    foreach($target as $k=>$v){
        if(is_array($v)){
            scrubIt($v);
        }
        else{
            $target[$k]=mysqli_real_escape_string($GLOBALS["db"],$v);
        }
    }
    return $target;
}

?>
<script>
    var sessionVars = <?php echo json_encode($_SESSION); ?>;
    if (sessionVars == null) {
        sessionVars = {};
    }
    console.log("sessionVars:");
    console.log(sessionVars);

    var scrubbedVars = <?php echo json_encode($_SCRUBBED); ?>;
    if (scrubbedVars == null) {
        scrubbedVars = {};
    }
    console.log("scrubbedVars:");
    console.log(scrubbedVars);
</script>
<link rel="stylesheet" href="<?php echo $devprodroot; ?>/css/popup.css">
        <main role="main">
            <div class="container-fluid">
                <div class="container">
                    <div id="session_go_back" class="row pt-3 pb-5 d-none">
                        <div class="col">
                            <a class="underlined-btn" href="/newestccoi/"><span class="oi oi-arrow-thick-left mr-2"></span><span class="btn-text">Back to Session Select</span></a>
                        </div>
                    </div>
                    <div class="row py-5" style="min-width: 600px;">
                        <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-8 col-12">
                                <h1 class="red-font">User Management</h1>
                            </div>
                            <div class="col-md-4 col-12 pt-2">
                                <button id="new_user_button" type="button" class="btn btn-gold float-right" data-toggle="tooltip" data-html="true" title="Click here to start" onclick="blurScreen(); showNewUser()">Add User</button>
                            </div>
                        </div>
                            
                        <div class="row pt-3 pr-md-5">
                            <div class="col-12 btn-div">
                                <div class="row">
                                    <div class="col-sm-2">
                                        <p>First Name</p>
                                    </div>
                                    <div class="col-sm-2">
                                        <p>Last Name</p>
                                    </div>
                                    <div class="col-sm-3">
                                        <p>Email</p>
                                    </div>
                                    <div class="col-sm-3">
                                        <p>New Password</p>
                                    </div>
                                    <div class="col-sm-1">
                                        <p>Admin?</p>
                                    </div>
                                    <div class="col-sm-1">
                                        <p>Remove</p>
                                    </div>
                                </div>
                                <ul id="research_session_list" class="mb-4">
<?php foreach ($users as $currentUser): ?>
                                    <li class="user-listing" id="user-<?= $currentUser['id'] ?>">
                                        <div class="row user pb-1">
                                            <div class="col-sm-2">
                                                <input class="saveOnEdit" type="text" id="first-<?= $currentUser['id'] ?>" name="fname" style="width: 100%;" value="<?= $currentUser['first'] ?>">
                                            </div>
                                            <div class="col-sm-2">
                                                <input class="saveOnEdit" type="text" id="last-<?= $currentUser['id'] ?>" name="lname" style="width: 100%;" value="<?= $currentUser['last'] ?>">
                                            </div>
                                            <div class="col-sm-3">
                                                <input class="saveOnEdit" type="email" id="email-<?= $currentUser['id'] ?>" pattern=".+@globex\.com" style="width: 100%;" value="<?= $currentUser['email'] ?>">
                                            </div>
                                            <div class="col-sm-3">
                                                <input type="password" id="password-<?= $currentUser['id'] ?>" style="width: 100%;" placeholder="New Password" onchange="newPassword('<?= $currentUser['id'] ?>')">
                                            </div>
                                            <div class="col-sm-1">
                                                <input class="saveOnEdit" type="checkbox" id="admin-<?= $currentUser['id'] ?>" name="admin" value="admin"<?php if( in_array('admin', $currentUser['roles']) ) echo " checked"; if( in_array('superadmin', $currentUser['roles']) ) echo " checked disabled"; ?> >
                                            </div>
                                            <div class="col-sm-1">
                                                <a class="btn-link" href="javascript:void(0)" onclick="removeUser(<?= $currentUser['id'] ?>)"><span class="oi oi-trash px-2" title="Delete User" aria-hidden="true"></span></a>
                                            </div>
                                        </div>
                                    </li>
<?php endforeach; ?>
                                </ul> 
                            </div>
                        </div>


                        <div class="row py-5" style="min-width: 600px;">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-8 col-12">
                                        <h1 class="red-font">Videos</h1>
                                    </div>
                                    <div class="col-md-4 col-12 pt-2">
                                        <a href="api/ccoi_videomanager2?id=<?= $appid; ?>"><button id="new_user_button" type="button" class="btn btn-gold float-right" data-toggle="tooltip" data-html="true" title="Go to video manager">Video Manager</button></a>
                                    </div>
                                </div>
<!--
                                <div class="row pt-3 pr-md-5">
                                    <div class="col-12 btn-div">
                                        <div class="row">
                                            <div class="col-sm-10">
                                                <p>Video Name</p>
                                            </div>
                                            <div class="col-sm-1">
                                                <p>Edit</p>
                                            </div>
                                            <div class="col-sm-1">
                                                <p>Delete</p>
                                            </div>
                                        </div>
                                        <ul id="research_session_list" class="mb-4">
<?php //foreach ($videos as $index => $currentVideo): ?>
                                            <li class="video-listing">
                                                <div class="row user pb-1">
                                                    <div class="col-sm-10">
                                                        <input type="text" id="vidname-<?= $index ?>" name="vidname" style="width: 100%;" value="<?= $currentVideo ?>" disabled="true">
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <a class="btn-link session-edit" href="javascript:void(0)"><span class="oi oi-pencil px-2" title="This feature is temporarily disabled" aria-hidden="true" disabled="true"></span></a>
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <a class="btn-link" href="javascript:void(0)"><span class="oi oi-trash px-2" title="This feature is temporarily disabled" aria-hidden="true" disabled="true"></span></a>
                                                    </div>
                                                </div>
                                            </li>
<?php //endforeach; ?>
                                        </ul> 
                                    </div>
                                </div>
-->
                            </div>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-8 col-12">
                                        <h1 class="red-font">Paths</h1>
                                    </div>
                                    <div class="col-md-4 col-12 pt-2">
                                        <button id="new_user_button" type="button" class="btn btn-gold float-right" data-toggle="tooltip" data-html="true" title="This feature is temporarily disabled" disabled="true">Create Path</button>
                                    </div>
                                </div>

                                <div class="row pt-3 pr-md-5">
                                    <div class="col-12 btn-div">
                                        <div class="row">
                                            <div class="col-sm-10">
                                                <p>Path Name</p>
                                            </div>
                                            <div class="col-sm-2">
                                                <!--<p>Delete</p>-->
                                            </div>
                                        </div>
                                        <ul id="research_session_list" class="mb-4">
<?php foreach ($paths as $index => $currentPath): ?>
                                            <li class="path-listing">
                                                <div class="row user pb-1">
                                                    <div class="col-sm-10">
                                                        <input type="text" id="pathname-<?= $index ?>" name="pathname" style="width: 100%;" value="<?= $currentPath ?>" disabled="true">
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <!--<a class="btn-link" href="javascript:void(0)"><span class="oi oi-trash px-2" title="This feature is temporarily disabled" aria-hidden="true" disabled="true"></span></a>-->
                                                    </div>
                                                </div>
                                            </li>
<?php endforeach; ?>
                                        </ul> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <iframe name="dummyframe" id="dummyframe" style="display: none;"></iframe>

            <div><!--popup stuff -->
                <div id="blurOverlay" onclick="closePopups()"></div>

                <div id="newUser" class="popup">
                    <button type="button" class="exitPopup" onclick="closePopups()">✕</button>
                    <h2>New User</h2>
                    <form name="userForm" id="userForm">
                        <input type='hidden' name='yahoo' value='1' />
                        <input type='hidden' name='appid' value=<?= $appid; ?> />
                        <label for="first">First Name</label>
                        <input id= "first" type="text" name='first' placeholder='First Name' required><br>
                        <label for="last">Last Name</label>
                        <input id= "last" type="text" name='last' placeholder='Last Name' required><br>
                        <label for="email">Email Address</label>
                        <input id= "email" type="email" name='email' placeholder='user@email.com' required><br>
                        <label for="newpass">Password</label>
                        <input id= "newpass" type="password" name='newpass' required><br>
                        <input id="userSubmit" style="margin-top: 2rem;" type='submit' value='Add New User'>
                    </form>
                </div><!--popup-->
                <div id="userResponse" class="popup">
                    <button type="button" class="exitPopup" onclick="closePopups()">✕</button>
                    <p id="userResponseText"></p>
                </div>
            </div>
        </main>
        
        <?php include '../includes/footer.php'; ?>
        <script src="/js/jquery-3.4.1.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>
        <script>
            const blur = document.getElementById('blurOverlay');
            const newUserWin = document.getElementById('newUser');
            function blurScreen() {
                blur.classList.toggle('blurred');
            }
            function showNewUser() {
                newUserWin.classList.toggle('popped');
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

            function createNewUser() {
                const formData = new FormData(document.getElementById("userForm"));
                formData.append('newUser', 1);

                let sendStr = '';
                formData.forEach(function(value, key){
                    if(value == ""){}
                    else {
                        sendStr += `&${key}=${value}`;
                    }
                });
                sendStr = sendStr.substring(1);
                
                var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
                xmlHttp.onreadystatechange = function() {
                    var data=getHTML(xmlHttp);
                    if(data){
                        console.log("data returned: ");
                        console.log(data);
                        let returnedInt = parseInt(data);

                        newUserWin.classList.remove('popped');
                        let responseWindow = document.getElementById('sessionResponse');
                        responseWindow.classList.add('popped');
                        let responseText = document.getElementById('sessionResponseText');

                        if (returnedInt == -1) {
                            responseText.innerText = "There was an error creating that user.<br>Please refresh the page and try again.<br><br>If the problem persists, please contact an administrator.";
                            //console.error("Missing required data");
                        }
                        else {
                            responseText.innerText = "User has been created successfully.<br><br>They have been added to the bottom of your the user list.";
                            let newEntry = document.createElement("li");
                            newEntry.setAttribute("class", "session-listing my-2");
                            newEntry.setAttribute("id", tbName+"-"+returnedInt);
                            newEntry.setAttribute("style", "display:flex; flex-wrap:no-wrap;");
                            newEntry.innerHTML = `  <div style="width:80%;">
                                                        <a class="btn-link session-edit" href="obz?id=${returnedInt}${extraText}">${name}</a>
                                                    </div>
                                                    <div style="width:18%; display:flex; justify-content:space-between;">
                                                        <a class="btn-link session-edit" href="obz?id=${returnedInt}${extraText}"><span class="oi oi-pencil" title="Edit Session" aria-hidden="true"></span></a>
                                                        <a class="btn-link" href="javascript:void(0)" onclick="deleteSession(${returnedInt})"><span class="oi oi-trash" title="Delete Session" aria-hidden="true"></span></a>
                                                        <a class="btn-link" href="javascript:void(0)"><span class="oi oi-pie-chart" title="View Visualizations" aria-hidden="true"></span></a>
                                                    </div>`;
                            let playgroundList = document.getElementById(tbName+"_session_list");
                            playgroundList.appendChild(newEntry);
                        }
                    }
                }
                console.log("sendStr: "+sendStr);
                
                var url =  encodeURI(derServer+'/zpb_ajax.php?'+sendStr);			console.log(url);
                xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
            }

            $(function() {
                $("#userForm").on("submit", function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: "api/ccoi_users.php",
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(data) {
                            /*
                            console.log("data: ");
                            console.log(data);
                            newUserWin.classList.remove('popped');
                            $("#userResponse").addClass('popped');
                            $("#userResponseText").text(data);
                            */
                            document.location.reload();
                        },
                        error: function(data) {
                            /*
                            console.log("data: ");
                            console.log(data);
                            newUserWin.classList.remove('popped');
                            $("#userResponse").addClass('popped');
                            $("#userResponseText").text(data);
                            */
                            document.location.reload();
                        }
                    });
                });
            });

            function createUser() {
                /*
                const formData = new FormData(document.getElementById("userForm"));
                console.log("formData:");
                console.log(formData);
                $("#research_session_list").append(`<li class="user-listing" id="user">
                                        <div class="row user pb-1">
                                            <div class="col-sm-2">
                                                <input class="saveOnEdit" type="text" id="first" name="fname" style="width: 100%;" value="${formData.first}">
                                            </div>
                                            <div class="col-sm-2">
                                                <input class="saveOnEdit" type="text" id="last" name="lname" style="width: 100%;" value="${formData.last}">
                                            </div>
                                            <div class="col-sm-3">
                                                <input class="saveOnEdit" type="email" id="email" pattern=".+@globex\.com" style="width: 100%;" value="${formData.email}">
                                            </div>
                                            <div class="col-sm-3">
                                                <input type="password" id="password-1" style="width: 100%;" placeholder="New Password" onchange="newPassword('1')">
                                            </div>
                                            <div class="col-sm-1">
                                                <input class="saveOnEdit" type="checkbox" id="admin-1" name="admin" value="admin">
                                            </div>
                                            <div class="col-sm-1">
                                                <a class="btn-link" href="javascript:void(0)" onclick=""><span class="oi oi-trash px-2" title="Delete User" aria-hidden="true"></span></a>
                                            </div>
                                        </div>
                                    </li>`);
                                    */
            }

            function removeUser(id){
                if (confirm('Are you sure you want to remove this user?')) {
                    var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
                    xmlHttp.onreadystatechange = function() {
                        var data=getHTML(xmlHttp);
                        if(data){
                            console.log(data);
                            if(data == "y") {
                                //Hide the element
                                var targetElement = document.getElementById('user-'+id);
                                targetElement.classList.add('d-none');
                            }
                            else if(data == "n") {
                                alert("Session could not be deleted! Please refresh and try again. If the problem persists, please contact an administrator.");
                            }
                        }
                    }
                    sendStr='removeUser=1&appid=<?=$appid;?>&userid='+id;
                    var url =  encodeURI(derServer+'zpb_ajax.php?'+sendStr);			console.log(url);
                    xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
                } else {
                    // Do nothing!
                    console.log('Removing user was canceled.');
                }
            }
        </script>
        <script language='javascript'>
            function doUpdate(e){
                var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
                xmlHttp.onreadystatechange = function() {var data=getHTML(xmlHttp);
                    if(data){
                        console.log("data:");
                        console.log(data);
                    }
                }
                let components = e.target.id.split("-");
                var bit;		if(e.target.type=='checkbox'){bit=e.target.checked;}else{bit=encodeURIComponent(e.target.value);}
                var sendStr = 'updateUser=1&appid=<?= $appid ?>&userid='+components[1]+'&toChange='+components[0]+'&newValue='+bit;
                console.log("sendStr = "+sendStr);
                var url = derServer+'zpb_ajax.php?'+sendStr;					console.log(url);
                xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
            }

            function newPassword(e){
                if (confirm("Are you sure you want to change this user's password? \r\nThis cannot be undone! \r\nPlease make sure the user knows their password!")) {
                    var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
                    xmlHttp.onreadystatechange = function() {var data=getHTML(xmlHttp);
                        if(data){
                            console.log("data:");
                            console.log(data);
                        }
                    }
                    
                    console.log("e:");
                    console.log(e);
                    console.log("getting password from:");
                    console.log("#password-"+e);
                    let newPass = $("#password-"+e).val();
                    var sendStr = 'updateUser=1&appid=<?= $appid ?>&userid='+e+'&toChange=password&newValue='+newPass;
                    console.log("sendStr = "+sendStr);
                    var url = derServer+'zpb_ajax.php?'+sendStr;					console.log(url);
                    xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
                }
            }

            var elementsThatSave=document.getElementsByClassName('saveOnEdit');
            for (var i = 0; i < elementsThatSave.length; i++) {
                elementsThatSave[i].addEventListener('change',function(e){doUpdate(e);e.stopPropagation();},true);
            }
        </script>
    </body> 
</html>



<?php
function getUsers(){
    $appid = $GLOBALS["appid"];
    //echo "<br>getting users for app with id: ".$appid;
    if( !empty($appid) && is_numeric($appid) ){
        $db = $GLOBALS["db"];

        //echo "<br>first query statement: "."SELECT personid, role FROM tbPersonAppRoles WHERE appid='$appid'";
        $return=mysqli_query($db,"SELECT personid, role FROM tbPersonAppRoles WHERE appid='$appid'");		
        while($d=mysqli_fetch_assoc($return)){
            $userData[$d['personid']][]=$d['role'];
        }
        //echo "userData: "; var_dump($userData);
        $useridsarray = array_keys($userData);
        $useridstext=implode(',',$useridsarray);
        //echo "<br>first query returned: ".$useridstext;

        //echo "<br>second query statement: "."SELECT first, last, email FROM tbPeople WHERE id IN ($appid)";
        $return=mysqli_query($db,"SELECT id, first, last, email FROM tbPeople WHERE id IN ($useridstext) AND inactive IS NULL");		
        while($d=mysqli_fetch_assoc($return)){
            $d['roles'] = $userData[$d['id']];
            $returnData[]=$d;
        }
        //echo "<br>second query returned: "; var_dump($returnData);

        return $returnData;
    }
    else
        return NULL;
}

function getVideos(){
    $appid = $GLOBALS["appid"];
    //echo "<br>appid: ".$appid;
    //echo "<br>getting users for app with id: ".$appid;
    if( !empty($appid) && is_numeric($appid) ){
        $db = $GLOBALS["db"];

        //echo "<br>first query statement: "."SELECT id, name FROM tbVideos WHERE appid='$appid'";
        $return=mysqli_query($db,"SELECT id, name FROM tbVideos WHERE appid='$appid'");		
        while($d=mysqli_fetch_assoc($return)){
            $returnData[$d['id']]=$d['name'];
        }
        //echo "<br>returnData: "; var_dump($returnData);

        return $returnData;
    }
    else
        return NULL;
}

function getPaths(){
    $appid = $GLOBALS["appid"];
    //echo "<br>getting paths for app with id: ".$appid;
    if( !empty($appid) && is_numeric($appid) ){
        $db = $GLOBALS["db"];

        //echo "<br>first query statement: "."SELECT pathid FROM tbAppPaths WHERE appid='$appid'";
        $return=mysqli_query($db,"SELECT pathid FROM tbAppPaths WHERE appid='$appid'");		
        while($d=mysqli_fetch_assoc($return)){
            $pathids[] = $d['pathid'];
        }
        $pathidstext=implode(',',$pathids);
        //echo "<br>first query returned: ".$pathidstext;

        //echo "<br>second query statement: "."SELECT first, last, email FROM tbPeople WHERE id IN ($appid)";
        $return=mysqli_query($db,"SELECT id, name FROM tbPaths WHERE id IN ($pathidstext)");		
        while($d=mysqli_fetch_assoc($return)){
            $returnData[$d['id']]=$d['name'];
        }
        //echo "<br>second query returned: "; var_dump($returnData);

        return $returnData;
    }
    else
        return NULL;
}
?>