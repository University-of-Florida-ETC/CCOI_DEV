<?php
$page = "dashboard";
$zpbLink = "/dashboard";
$dataTarget = "#demo_help_box";
$dataOffset = "10";
$dataSpy = "scroll";
include 'includes/header.php';
?>
<main>
    <div id="research_session_list"">
    </div>
    <div id="playgrounds_session_list"">
    </div>
</main>
        <?php include 'includes/footer.php'; ?>
        <!--<script src="showObsSet.js"></script>-->
        <script src="js/zpbccoi.js"></script>
        <script>
            try{
                if(typeof(jsUserVars) != 'undefined'){
                    userid=jsUserVars['pid'];
                    //setTimeout(function(){ fetchUserObSets2(userid);},500);
                    setTimeout(function(){
                        fetchUserObSets4(userid);
                        /*
                        setTimeout(function(){
                            console.log("fetchDaPath:")
                            fetchDaPath(userid);
                            console.log("fetchDaCodes:")
                            fetchDaCodes(userid);
                        }
                        ,50);
                        */
                    }
                    ,10);
                    
                    //fetchUserObSets2(userid);
                }
            }
            catch(error){
                error(error);
            }
        </script>
    </body> 
</html>