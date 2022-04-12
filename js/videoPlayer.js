function closePage () {
    window.parent.toggleVideoContainers();
    window.close();
}

/*
function initializeListeners(slider, output) {
    let video = document.getElementById('ccoi_video');
    console.log(window.opener);
    window.opener.document.getElementById('vid_speed_1x').click(function() {
        video.playbackRate = 1;
        slider.value = 1;
        output = 1.0;
    });
    window.opener.document.getElementById('vid_speed_1_5x').click(function() {
        video.playbackRate = 1.5;
        slider.value = 1.5;
        output = 1.5;
    });
    window.opener.document.getElementById('vid_speed_2x').click(function() {
        video.playbackRate = 2;
        slider.value = 2;
        output = 2.0;
    });
}*/

function changeSpeed(rate) {
    let video = document.getElementById('ccoi_video');
    let slider = document.getElementById("video_slider");
    let output = document.getElementById("video_speed");
    video.playbackRate = slider.value = output.innerHTML = rate;
}

function fetchScramble(url) {
    $.ajax({
        // TODO MAKE THIS AJAX CALL WORK REGARDLESS OF BASE URL
        url: 'https://ccoi-dev.education.ufl.edu/ZPB/zpb_ajax.php',
        data: {
            action: 'fetchScramble',
            baseURL: url
        },
        type: 'post',
        success: function(data) {
            console.log(output);
        }
    })
}

// Initialize video controls upon page load
function initializeVideoControls() {
    let slider = document.getElementById("video_slider");
    let output = document.getElementById("video_speed");
    output.innerHTML = parseFloat(slider.value).toPrecision(2); // Display the default slider value

    // Update the current slider value (each time you drag the slider handle)
    slider.oninput = function() {
        let videoSpeed = parseFloat(this.value).toFixed(1);
        output.innerHTML = videoSpeed;
        document.getElementById('ccoi_video').playbackRate = parseFloat(videoSpeed).toPrecision(2);
    }
}

$().ready(function () {
    fetchScramble(window.src);
    console.log(window.src);
    let vid = `<video controls id="ccoi_video" height="100%" width="100%">
   //   <source src="${window.parent.src}" type="video/mp4">
		<source src="/ccoivids/${window.src}"  type="video/mp4">
    </video>`;
    $('#video_player_container').html(vid);
    window.parent.video = $('video')[0];

    $('#video_title').text(derVideoTitle);

    initializeVideoControls();
});