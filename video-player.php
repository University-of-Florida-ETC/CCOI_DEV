<?php
$page = "";
include 'includes/header.php';
?>
<main role="main">
    <div class="container-fluid">
        <div class="container">
            <div class="row pb-5">
                <div class="col-md-12">
                    <h1 id="video_title" class="py-3"></h1>
                    <div id="video_player_container">

                    </div>
                    <div id="video_speed_container" class="pt-3">
                        <input type="range" min="0.1" max="2" value="1" step="0.1" class="slider-color" id="video_slider">
                        <div id="video_speed_output">Playback Speed: <span id="video_speed">1.0</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
<script src="./js/jquery-3.4.1.min.js"></script>
<script src="./js/videoPlayer.js"></script>
<script src="./js/bootstrap.min.js"></script>
</body>
</html>