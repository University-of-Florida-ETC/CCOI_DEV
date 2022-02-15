<?php
$page = "dashboard";
$zpbLink = "/dashboard";
$dataTarget = "#demo_help_box";
$dataOffset = "10";
$dataSpy = "scroll";
include 'includes/header.php';
?>
<main>
    <div id="obsList">
    </div>
</main>
        <?php include 'includes/footer.php'; ?>
        <script src="showObsSet.js"></script>
        <script>
            try{
                if(typeof(jsUserVars) != 'undefined'){
                    userid=jsUserVars['pid'];
                    //setTimeout(function(){ fetchUserObSets2(userid);},500);
                    setTimeout(function(){
                        fetchUserObSets(userid);
                        setTimeout(function(){
                            console.log("fetchDaPath:")
                            fetchDaPath(userid);
                            console.log("fetchDaCodes:")
                            fetchDaCodes(userid);
                        }
                        ,50);
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