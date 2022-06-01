<?php
$CCOI_requireslogin=true;
include 'includes/header.php';
if(!empty($_GET['vid']) && is_numeric($_GET['vid'])){
	include('./api/ccoi_dbhookup.php');
	$vid=mysqli_real_escape_string($db,$_GET['vid']);													//		$vid=53;		// ================ FOR DEV - ONLY VIDEO IS 53 ============
	$query="SELECT * FROM tbVideos WHERE id='$vid' LIMIT 1";
	$return=mysqli_query($db,$query);$v=mysqli_fetch_assoc($return); 
	$jsVidInfo="    var derVideoUrl='/ccoivids/{$v['scramble']}_{$v['url']}';    var derVideoTitle='{$v['name']}';";
}
?>
<script language="javascript">
<?php
	echo $jsVidInfo;
?>
</script>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500&display=swap" rel="stylesheet">
<style>
	body {font-family: 'Rubik', sans-serif; font-weight: 300;}
</style>

<main role="main"><div class="container-fluid"><div class="container">



			<div class="row pb-5">
                <div class="col-md-12">
                    <h1 id="video_title" class="py-3"></h1><!-- <p>For the Dev Server, the only video is the demo video</p> -->
                    <div id="video_player_container">

                    </div>
                    <div id="video_speed_container" class="pt-3">
                        <input type="range" min="0.1" max="2" value="1" step="0.1" class="slider-color" id="video_slider">
                        <div id="video_speed_output">Playback Speed: <span id="video_speed">1.0</span></div>
                    </div>
                </div>
            </div>





</div> <!-- end container --></div> <!-- end cont-fluid --></main>
<?php 
	include 'includes/footer.php'; 
/*	== from the JS below ==
 let vid = `<video controls id="ccoi_video" height="100%" width="100%">
      <source src="${window.parent.src}" type="video/mp4">
    </video>`;
    $('#video_player_container').html(vid);
    window.parent.video = $('video')[0];

    $('#video_title').text(window.parent.videoTitle);
*/
?>

<script src="./js/jquery-3.4.1.min.js"></script>
<script src="./js/videoPlayer.js"></script>
<script src="./js/bootstrap.min.js"></script>

</body> 
</html>