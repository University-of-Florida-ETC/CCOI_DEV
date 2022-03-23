<?php
$page = "dashboard";
$zpbLink = "/ZPB/dashboard";
$dataTarget = "#demo_help_box";
$dataOffset = "10";
$dataSpy = "scroll";
include '../includes/header.php';
include $includeroot.$devprodroot.'/api/ccoi_dbhookup.php';
$appid = $_GET['id'];
if( !in_array($appid, $_SESSION['myappids'])){
    header("Location: group");
}
$users = getUsers();
$videos = getVideos();
//echo "<br>\$videos:"; var_dump($videos);
//$paths = getPaths();
?>

        <main role="main">
            <div class="container-fluid">
                <div class="container">
                    <div id="session_go_back" class="row pt-3 pb-5 d-none">
                        <div class="col">
                            <a class="underlined-btn" href=<?php echo $zpbLink; ?>><span class="oi oi-arrow-thick-left mr-2"></span><span class="btn-text">Back to Session Select</span></a>
                        </div>
                    </div>
                    <div class="row py-5" style="min-width: 600px;">
                        <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-8 col-12">
                                <h1 class="red-font">User Management</h1>
                            </div>
                            <div class="col-md-4 col-12 pt-2">
                                <button id="new_user_button" type="button" class="btn btn-gold float-right" data-toggle="tooltip" data-html="true" title="Click here to start">Add User</button>
                            </div>
                        </div>
                            
                        <div class="row pt-3 pr-md-5">
                            <div class="col-12 btn-div">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p>First Name</p>
                                    </div>
                                    <div class="col-sm-3">
                                        <p>Last Name</p>
                                    </div>
                                    <div class="col-sm-4">
                                        <p>Email</p>
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
                                    <li class="user-listing">
                                        <div class="row user pb-1">
                                            <div class="col-sm-3">
                                                <input class="saveOnEdit" type="text" id="first-<?= $currentUser['id'] ?>" name="fname" style="width: 100%;" value="<?= $currentUser['first'] ?>">
                                            </div>
                                            <div class="col-sm-3">
                                                <input class="saveOnEdit" type="text" id="last-<?= $currentUser['id'] ?>" name="lname" style="width: 100%;" value="<?= $currentUser['last'] ?>">
                                            </div>
                                            <div class="col-sm-4">
                                                <input class="saveOnEdit" type="email" id="email-<?= $currentUser['id'] ?>" pattern=".+@globex\.com" style="width: 100%;" value="<?= $currentUser['email'] ?>">
                                            </div>
                                            <div class="col-sm-1">
                                                <input class="saveOnEdit" type="checkbox" id="admin-<?= $currentUser['id'] ?>" name="admin" value="admin"<?php if( in_array('admin', $currentUser['roles']) ) echo " checked"; ?><?php if( in_array('superadmin', $currentUser['roles']) ) echo " disabled"; ?> >
                                            </div>
                                            <div class="col-sm-1">
                                                <a class="btn-link" href="javascript:void(0)"><span class="oi oi-trash px-2" title="Delete User" aria-hidden="true"></span></a>
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
                                        <button id="new_user_button" type="button" class="btn btn-gold float-right" data-toggle="tooltip" data-html="true" title="Click here to start">Add Video</button>
                                    </div>
                                </div>

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
<?php foreach ($videos as $index => $currentVideo): ?>
                                            <li class="video-listing">
                                                <div class="row user pb-1">
                                                    <div class="col-sm-10">
                                                        <input type="text" id="vidname-<?= $index ?>" name="vidname" style="width: 100%;" value="<?= $currentVideo ?>">
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <a class="btn-link session-edit" href="javascript:void(0)"><span class="oi oi-pencil px-2" title="Edit Video Name" aria-hidden="true"></span></a>
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <a class="btn-link" href="javascript:void(0)"><span class="oi oi-trash px-2" title="Delete User" aria-hidden="true"></span></a>
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
        </main>
        <?php include 'includes/footer.php'; ?>
        <script src="/js/jquery-3.4.1.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>
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

            console.log(`<?php var_dump($sessions) ?>`);

            function createNewSession() {
                let name = prompt("Enter the name of the new session:");
                
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
                                                    <a class="btn-link session-edit" href="observation?id=${returnedInt}&isPlayground=1">${name}</a>
                                                </div>
                                                <div class="col-sm-3 col-12">
                                                    <a class="btn-link session-edit" href="observation?id=${returnedInt}&isPlayground=1"><span class="oi oi-pencil px-2" title="Edit Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="#"><span class="oi oi-trash px-2" title="Delete Session" aria-hidden="true"></span></a>
                                                    <a class="btn-link" href="#"><span class="oi oi-pie-chart px-2" title="View Visualizations" aria-hidden="true"></span></a>
                                                </div>
                                            </div>`;
                            let playgroundList = document.getElementById("playgrounds_session_list");
                            playgroundList.appendChild(newEntry);
                        }
                    }
                }
                sendStr='newSession=1&name='+name;
                var url =  encodeURI(derServer+'ZPB/zpb_ajax.php?'+sendStr);			console.log(url);
                xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
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
                var url = derServer+'ZPB/zpb_ajax.php?'+sendStr;					console.log(url);
                xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
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
        $return=mysqli_query($db,"SELECT id, first, last, email FROM tbPeople WHERE id IN ($useridstext)");		
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
?>