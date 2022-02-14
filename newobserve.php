<?php
$CCOI_requireslogin=true;
include 'includes/header.php';
echo "<script language='javascript'>var derServer='https://{$serverroot}{$devprodroot}/';var derDevProd='{$devprodroot}';</script>\n";		// sigh -- needs to be after header, but before JS below
?>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500&display=swap" rel="stylesheet">
<script src="./js/newccoi.js"></script>
<style>
	body {font-family: 'Rubik', sans-serif; font-weight: 300;}
	div#Hheaders h4#usersets {color: #1C4E9D; font-size: 14px; margin-bottom:0;}
		div#Hheaders h4#usersets:hover {color: #e8a50c !important; cursor:pointer;}
	div#Hheaders h1#loadedobsset {color: #1C4E9D; font-size: 22px; margin-bottom:0; margin-left: 40px; text-indent: -40px;}
		div#Hheaders h1#loadedobsset:hover {color: #e8a50c !important; cursor:pointer;}
		div#Hheaders h1#loadedobsset a#dertext {font-family:inherit; color:inherit;}
	div#Hheaders h1#loadedobs {color: #1C4E9D; font-size: 22px; margin-bottom:0; margin-left: 40px; text-indent: -40px;}
		div#Hheaders h1#loadedobs a#dertext {font-family:inherit; color:inherit;}
		
		div#Hheaders h1#loadedobsset button {border: none; background: none; color: inherit; font-size: 16px;}
		div#Hheaders h1#loadedobsset button:hover {color: #e8a50c;}
		div#Hheaders h1#loadedobs button {border: none; background: none; color: inherit; font-size: 20px;}
		div#Hheaders h1#loadedobs button:hover {color: #e8a50c;}
		
	div#Hheaders .orange {color: #e8a50c !important; font-size: 28px !important;}
	div#Hheaders .orange2 {color: #e8a50c !important;}
	div#Hheaders p.sessionxofx {color: #1C4E9D; font-size: 16px; font-weight: 500; margin: 0;}
	div#Hheaders p#highlevelnotes {color: #1C4E9D; font-size: 16px; font-weight: 500; ma_rgin-top: -0.8em; display:none;}
		div#Hheaders p#highlevelnotes:hover {color: #e8a50c !important; cursor:pointer;}
		div#Hheaders p.tiny {font-size: 14px !important; line-height: 16px;}
	div#Hheaders p#obsetmeta {color: #1C4E9D; font-size: 16px; font-weight: 500; ma_rgin-top: -0.8em; display:none;}
		div#Hheaders p#obsetmeta span.editspan:hover {color: #e8a50c !important; cursor:pointer;}

		div#Hheaders p#obsetmeta button {border: none; background: none; color: #1C4E9D;}
		div#Hheaders p#obsetmeta button:hover {color: #e8a50c;}
		
	div#Hheaders p#maybevideolink {display:none; position: relative;}
	
	div#Hheaders span.oi2 {text-indent: 0; padding-left: 12px; font-size: 14px; top: -1px;}
	 div.minimega span.oi3{position: absolute;  top: 12px; left: -18px; color:red;}

	div#leftside {opacity:0;}
	
	div#leftInnerContainer h3 {display:none;}
	div#rightInnerContainer h3 {color: #e8a50c;}
	div#rightInnerContainer h3 span.hiddenHeaderSpan {font-size: 12px;}
	div#rightInnerContainer .oi {display:none;}
	div#rightInnerContainer .oiplus {display:none;}
	div#rightInnerContainer div.subsessionselect {font-size: 16px;}
	
	div#demo_help_box {font-size:12px;}
	div#demo_help_box div.md-boxed-content {padding: 1rem;}
	
	.past {background-color: #5e7290;}
	span#playstate {font-family: 'Rubik', sans-serif; font-weight: 500;}		/* inconsulata has no bold */
		span#playstate:hover {color:#e8a50c; cursor:pointer;}
	.playground {background-image: url(/assets/images/junglegym.png); background-repeat: no-repeat; background-position: 360px 30px; background-size: 80px;}
	
	div#overlayOuter {background-color:rgba(0,0,0,.3);position:fixed; top:0; left:0; width:100%;height:100%;z-index:602;display:none;}
	div#overlayInner {width: 350px;  height: 450px; position:absolute; top:50%; left:50%; margin-top:-225px; margin-left: -175px; border: 1px solid #666; border-radius: 8px; background-color:#f8f8f8; box-shadow: 0px 2px 48px #444;}		/* top:50%; margin-top: -225px; left:50%; margin-left: -175px;  */
	div#overlayInner div#overlayHead {height: 26px; padding: 1px 0 0 10px; font-weight: 400; border-radius: 6px 6px 0 0; background: -webkit-gradient(linear, left top, left bottom, from(#3672a7), to(#1a3c71)); background: -moz-linear-gradient(top, #3672a7, #1a3c71); color: white;}
	div#overlayInner div#overlayBody {padding: 8px 10px; font-size: 12px;}
	div#overlayInner div#overlayBody p {font-size: 14px; line-height: 18px;}
	div#overlayInner button#closeoverlay {position: absolute; top: -17px; right: -19px; height: 34px; color: #cc4c00; border: 1px solid #444; border-radius: 6px; background-color: #ffbeb0; font-size: 18px;}
		div#overlayInner button#closeoverlay:hover {cursor: pointer;}
	div#overlayInner img.subm_____itmulti {position: absolute; bottom: -14px; right: -14px;}
	div#overlayInner button.submitmulti {position: absolute; bottom: -17px; right: -19px; height: 34px; color: green; top: initial; border: 1px solid #444; border-radius: 6px; background-color: #c4eaa7; font-size: 18px;}
		div#overlayInner button.submitmulti:hover {cursor: pointer;}
	
	div.observationselect {background-color: #2b528e; border-radius: 24px; pad_ding: 10px; font-size: 18px; line-height: 20px; color: white; margin-bottom: 6px; position: relative;}
		div.observationselect:hover {background-color:#e8a50c; cursor:pointer;}
		div.observationselect button.title {text-align: left; border: none; background: none; color: inherit; padding: 10px 10px 10px 25px;font-weight: 300;}
	div.subsessionselect {background-color: #2b528e; border-radius: 24px; pad_ding: 10px; font-size: 18px; line-height: 20px; color: white; margin-bottom: 6px; position: relative;}
		div.subsessionselect:hover {background-color:#e8a50c; cursor:pointer;}
		div.subsessionselect div.time {font-size: 16px; font-weight: 400; width: 60px; height: 100%; border-radius: 24px 0 0 24px; text-align:center; position:absolute; top:0; left:0; background-color:#89a1c3;}
			div.subsessionselect div.timetext {width: 66px; position:absolute; top:50%; left:0; margin-top: -9px;}
		div.subsessionselect button.title {text-align: left; border: none; background: none; color: inherit; margin-left: 60px; padding: 10px;font-weight: 300;}
		div.subsessionselect div.notes {font-size: 14px; line-height: 16px; padding-left:8px; margin-top:4px; margin-left: 46px; display:none;}

	div.mega {}
		div.mega div.minimega {background-color: #2b528e; border-radius: 24px; padding:10px 10px 10px 20px; font-size: 18px; line-height: 20px; color: white; margin-bottom: 6px; position: relative;}
			div.mega div.minimega:hover {background-color:#e8a50c; cursor:pointer;}
		div.mega div.minimega div.title {font-size: 18px; line-height: 20px; margin-left: 16px; padding-right: 10px;}
		div.mega div.minimega div.time {font-size: 16px; font-weight: 400; width: 40px; height: 100%; border-radius: 24px 0 0 24px; text-align:center; position:absolute; top:0; left:0; background-color:#89a1c3;}
			div.mega div.minimega div.timetext {width: 46px; position:absolute; top:50%; left:0; margin-top: -50%;}
		div.mega div.minimega div.megatext {font-size: 12px; margin-left: 16px; margin-top: -5px;}
		div.mega div.minimega div.notes {font-size: 12px; display:none; margin-left: 16px; margin-top: 5px; line-height: 14px;}
	div.mega div.maximega {margin-top:20px; margin-bottom: 40px; padding-top:20px; padding-bottom: 20px; border-top:2px solid #2b528e; border-bottom: 2px solid #2b528e; position:relative;}
	div.mega div.maximega button.closemega {width:23px; height: 23px; position:absolute; top:10px; right: 10px; padding: 1px 1px 2px 2px;; color:white; background-color:#c51b00; border: none; border-radius:4px;}
	div.mega div.maximega button.closemega:hover {color:#c51b00; background-color:pink; cursor:pointer;}
	div.mega div.maximega div.title {font-size: 32px; line-height: 32px; margin: 10px 0 0 0; color: #e8a50c; font-weight: 500;}
		div.mega div.maximega div.mega_option {background-color: #2b528e; border-radius: 24px; padding: 10px 16px; font-size: 18px; line-height: 20px; color: white; margin-bottom: 6px; position: relative;}
		div.mega div.maximega div.mega_option:hover {background-color:#e8a50c; cursor:pointer;}
		div.mega div.maximega div.mega_option p {font-family:'Rubik', sans-serif; font-weight: 300; font-size: 12px; margin-left: 60px; line-height: 10px; margin-top:4px; margin-bottom:0; color:white;}
		div.mega div.maximega div.mega_option div.notes {font-size: 12px; line-height: 16px;margin-top:4px; m_argin-left: 46px;}
	div.mega div.maximega div.mega_drop_controller {background-color: #2b528e; border-radius: 30px; padding: 10px 10px 10px 16px; font-size: 18px; line-height: 20px; color: white; margin-bottom: 6px;}
	div.mega div.maximega div.mega_drop_controller:hover {background-color:#e8a50c; cursor:pointer;}
		div.mega div.maximega div.mega_drop_option {background-color: #2b528e; border-radius: 24px; padding: 10px 10px 10px 16px; font-size: 18px; line-height: 20px; color: white; margin-bottom: 6px;}
		div.mega div.maximega div.mega_drop_option div.notes {font-size: 12px; line-height: 16px; margin-top:4px; m_argin-left: 46px;}
		div.mega div.maximega div.mega_drop_option:hover {background-color:#e8a50c; cursor:pointer;}
		div.mega div.maximega div.mega_drop_option p {font-family:'Rubik', sans-serif; font-weight: 300; font-size: 12px; margin-left: 60px; line-height: 10px; margin-top:4px; margin-bottom:0; color:white;}
	div.mega div.maximega div.mega_drop_container {}
	div.mega div.maximega div.mega_drop_container div.mega_drop_option {margin-left: 8%;}
	div.mega div.maximega div.timerblock {color: #1C4E9D; margin-bottom:24px; margin-top: -8px;}
	div.mega div.maximega div.timerblock div.clock {width: 50px; display:inline-block; margin-right: 20px;}
	div.mega div.maximega div.timerblock div.clock img {margin-top:-10px;}
	div.mega div.maximega div.timerblock div.timer {width: 120px; display:inline-block; margin-right: 20px; font-size: 42px; transform: translate(0,7px);}
	div.mega div.maximega div.timerblock div.incrementer {width: 290px; display:inline-block; margin-right: 20px; color: white; background-color: #1C4E9D; border-radius:8px; font-size: 20px;}
/*
		div.mega div.maximega div.timerblock div.incrementer span {border-left: 1px solid white; padding: 4px 10px;}
			div.mega div.maximega div.timerblock div.incrementer span:hover {background-color:#e8a50c; cursor:pointer;}
		div.mega div.maximega div.timerblock div.incrementer span.incr_beg {border-top-left-radius: 8px; border-bottom-left-radius: 8px; border-left: none;}
		div.mega div.maximega div.timerblock div.incrementer span.incr_end {border-top-right-radius: 8px; border-bottom-right-radius: 8px;}
*/
		div.mega div.maximega div.timerblock div.incrementer button {border: none; border-left: 1px solid white; padding: 4px 10px; color: white; background-color: #1C4E9D; font-weight: 300;}
			div.mega div.maximega div.timerblock div.incrementer button:hover {background-color:#e8a50c; cursor:pointer;}
		div.mega div.maximega div.timerblock div.incrementer button.incr_beg {border-top-left-radius: 8px; border-bottom-left-radius: 8px; border-left: none;}
		div.mega div.maximega div.timerblock div.incrementer button.incr_end {border-top-right-radius: 8px; border-bottom-right-radius: 8px;}

	div#platonicMegaX {display:none;}

	div.mega div.maximega div.timerblock div.timerinst {display:inline-block; font-size: 12px; line-height: 14px; vertical-align: sub;}
	
	a#launch_video_button {background-color: #2b528e; width: 300px; border-radius: 24px; font-size: 16px; text-align:left; color: white; padding: 8px 20px; margin-bottom: 20px;}
	a#launch_video_button span.obvid {font-size: 14px; line-height: 10px;}
	a#launch_video_button span.oi {float:right; top: 4px;}
	a#launch_video_button:hover {background-color:#e8a50c; cursor:pointer;}
	div#playsp__eeds {display:block; position:absolute; top:-5px; left: 330px; font-size: 14px; margin-top:10px; text-align: center;}
	div#playsp__eeds div {height: 30px; width: 50px; display: inline-block; background-color: #2b528e; line-height: 32px; border-radius: 15px; font-size: 14px; color: white; padding: 0 5px; text-align: center;}
		
		div#platonicObsSet {display:none;}
		div#platonicObs {display:none;}
		div#platonicMega {display:none;}
		div#platonicMegaDropController {display:none;}
		div#platonicMegaDropItem {display:none;}
		div#platonicMegaItem {display:none;}
		a#platonicVideoButton {display:none;}
		button#platonicSubmitMulti {display:none;}

	input.singleline {border: 1px solid #ccc; border-radius: 6px; line-height: 28px; font-size: 22px; width: 580px; font-weight: 300;}
		input.singleline:focus {outline: none;}
	textarea.multiline {border: 1px solid #ccc; border-radius: 6px; line-height: 28px; font-size: 22px; width: 580px; height: 320px; font-weight: 300;}
		textarea.multiline:focus {outline: none;}
	select.dropdowner {border: 1px solid #ccc; border-radius: 6px; line-height: 28px; font-size: 22px; width: 580px; font-weight: 300;}
		select.dropdowner:focus {outline: none;}
		select.dropdowner option {}
	
	div.selectoricons {position: absolute; top: 10px; padding: 0; right: 10px; width: 130px; text-align: right;}
		div.selectoricons span.oiplus {padding-right:0;}
	div.selectoricons button {border: none; background: none; color: white;}
	div.selectoricons2 {position: absolute; top: 10px; padding: 0; right: 10px; width: 130px; text-align: right;}
	div.selectoricons2 button {border: none; background: none; color: white; font-size: 16px;}

	button:hover {cursor:pointer;}
	
	
	@media all and (max-width: 1200px) {
		div.selectoricons span {padding-right:0 !important;}
		div#righ___tside div#playspeeds div {width: 36px;}
		div#right___side div#launch_video_button {font-size: 14px; padding: 14px 10px 10px 10px;}
		div#righ___tside div.subsessionselect div.time {width: 50px;}
		div#right___side div.subsessionselect div.timetext {width: 50px;}
		div#righ___tside div.subsessionselect div.title {font-size: 14px;}
	}
	@media all and (max-width: 1000px) {
		span.oiplus {margin-right: -4px;}
		div#rig___htside div#playspeeds {width: 160px;}
		div#righ___tside div.subsessionselect div.time {width: 50px;}
		div#righ___tside div.subsessionselect div.timetext {width: 50px; font-size: 14px;}
		div#rig___htside div.subsessionselect div.title {margin-left: 32px; max-width: 90%;}
	}
	
</style>
<main role="main"><div class="container-fluid"><div class="container">

<div id="go_back" class="row pt-3 pb-5 d-none">
	<div class="col"><a class="underlined-btn" href="/demo"><span class="oi oi-arrow-thick-left mr-2"></span><span class="btn-text">Back to Session Select</span></a></div>
</div>

<div id="leftandright" class="row pb-5">
	<div id="leftside" class="col-md-12">
		<div class="row">
<!--			<div class="col-12"><h4 id="loadedpath" class="observationtitle">CCOI Path #0</h4><h1 id="loadedobsset" class="observationtitle">Demo ObservationSet</h1><h3 id="loadedobs" class="observationtitle">Demo Observation</h3><p id="loadedobsxofx" class="sessionxofx">Event #x of y</p></div>  -->
			<div id="Hheaders" class="col-12">
				<h4 id="usersets"></h4>
				<h1 id="loadedobsset"></h1>
				<h1 id="loadedobs"></h1>
				<p id="obsetmeta"></p>
				<p id="maybevideolink"></p>
				<p id="highlevelnotes" title="_"></p>
				<p id="loadedobsxofx" class="sessionxofx">Select an observation to view or edit its elements</p>
			</div>
		</div>
		<div id="leftOuterContainer" class="row pt-3">
			<div id="leftInnerContainer" class="col-12 btn-div">&nbsp;</div>
		</div>
	</div>


<!--
	<div id="rightside" class="col-md-4 col-12">
		<div class="row"><div class="col">
			<div id="launch_video_button">Open Video <span class="oi oi-external-link px-2" title="Open Session Video"></span></div>
			<div id="playspeeds">PLAYBACK SPEED &nbsp;  &nbsp; <div id="play10x">1X</div> &nbsp; <div id="play15x">1.5X</div> &nbsp; <div id="play20x">2X</div></div>
		</div></div>

		<div id="demo_help_box" class="row pt-3"><div class="col">
		<div class="md-boxed-content light-blue-background">
			<h4>C-COI Demo Instructions</h4>
			<ol id="demo_help_ol"><li>Click Add Session button to begin</li><li>Click the Pencil Icon to edit the session</li><li>Open video above and begin observing</li></ol>
			<em>Note:</em> If you need further information on how to use the instrument, visit the <a href="/about#learn">CCOI Help Center</a> section or our <a target="_blank" href="/assets/files/CCOI_Code_Book.pdf">code book</a>.
		</div>
		</div></div>	
			

			<div class="row"><div id="rightInnerContainer" class="col pt-3 pr-md-5"></div></div>				<span class="oiplus">+</span><span class="oi oi-pencil" title="Add/Edit Note"></span>
	</div> <!- end rightside ->
-->
</div> <!-- end leftandright --></div> <!-- end container --></div> <!-- end cont-fluid --></main>

<div id="overlayOuter"><div id="overlayInner"><button id="closeoverlay" class="oi oi-x" title="close overlay"></button><div id="overlayHead">Header</div><div id="overlayBody">Body</div></div></div>		<!--  <img src="./assets/images/redx2.png" id="closeoverlay" />  -->

<!-- USEFUL OI-icons:  tag, pencil, reload, task, warning, wrench, x, star, share-boxed, plus, pin, pie-chart, chart, person, people, map-marker, lock-locked, magnifying-glass, link-intact, key, home, heart, external-link, envelope-closed, flag, folder, file, comment-square, cog, clipboard, circle-x, cloud, cloud-download, chevron-left, check, chat, caret-left, bookmark, bar-chart, ban, arrow-left, arrow-thick-left, arrow-circle-left   -->
<div id="platonicObsSet" class="observationselect"><button class="col-sm-10 col-12 title">Platonic ObservationSet</button><div class="col-sm-3 col-12 selectoricons"><button class="oi oi-tag" title="Rename Observation Set" /> &nbsp; <button class="oi oi-trash" title="Delete Observation Set" /> &nbsp; <button class="oi oi-pie-chart" title="View Visualizations" /></div></div>
	<!-- <div id="platonicObsSet" class="observationselect"><div class="col-sm-10 col-12 title">Platonic ObservationSet</div><div class="col-sm-3 col-12 selectoricons"><button class="oi oi-tag" title="Rename Observation Set" /> &nbsp; <button class="oi oi-trash" title="Delete Observation Set" /> &nbsp; <button class="oi oi-pie-chart" title="View Visualizations" /></div></div> -->
<div id="platonicObs" class="subsessionselect"><div class="time"><div class="timetext"></div></div><button class="col-sm-10 col-12 title">Platonic Observation</button><div class="col-sm-1 col-12 selectoricons"><button class="oi oi-tag" title="Rename Observation" /></div><div class="col-sm-11 col-12 notes"></div></div>
	<!-- <div id="platonicObs" class="subsessionselect"><div class="time"><div class="timetext"></div></div><div class="col-sm-10 col-12 title">Platonic Observation</div><div class="col-sm-1 col-12 selectoricons"><button class="oi oi-tag" title="Rename Observation" /></div><div class="col-sm-11 col-12 notes"></div></div> -->
<div id="platonicMegaX" class="megaX" mega="0"><div class="col-sm-12 col-12 minimega"><div class="time"><div class="timetext"></div></div><div class="col-sm-11 col-12 megatext"></div><div class="col-sm-11 col-12 title"></div><div class="col-sm-1 col-12 selectoricons"></div><div class="col-sm-11 col-12 notes"></div></div><div class="col-sm-12 col-12 maximega" style="display:none;"><div class="col-sm-12 col-12 title">MegaTitle</div><img src="./assets/images/redx2.png" class="closemega" /><div class="col-sm-12 col-12 timerblock"><div class="clock"><img src="./assets/images/clock.png" /></div><div class="timer" storedtime="0">00:00</div><div class="incrementer"><span class="incr_beg" style="margin-left: 1px;">-5</span><span>-1</span><span>+1</span><span>+5</span><span>+30</span><span class="incr_end" style="margin-right: 1px;">+5m</span></div><div class="timerinst">minutes will automatically<br />roll over with increment</div></div></div></div>
<div id="platonicMega" class="mega" mega="0"><div class="col-sm-12 col-12 minimega"><div class="time"><div class="timetext"></div></div><div class="col-sm-11 col-12 megatext"></div><div class="col-sm-11 col-12 title"></div><div class="col-sm-1 col-12 selectoricons"></div><div class="col-sm-11 col-12 notes"></div></div><div class="col-sm-12 col-12 maximega" style="display:none;"><div class="col-sm-12 col-12 title">MegaTitle</div><button class="closemega oi oi-x" title="close"></button><div class="col-sm-12 col-12 timerblock"><div class="clock"><img src="./assets/images/clock.png" /></div><div class="timer" storedtime="0">00:00</div>
	<div class="incrementer"><button class="incr_beg" style="margin-left: 1px;">-5</button><button>-1</button><button>+1</button><button>+5</button><button>+30</button><button class="incr_end" style="margin-right: 1px;">+5m</button></div><div class="timerinst">minutes will automatically<br />roll over with increment</div></div></div></div>
<div id="platonicMegaDropController" class="col-sm-12 col-12 mega_drop_controller" onClick="flipContainer(this);">GroupName</div><div class="col-sm-12 col-12 mega_drop_container" id="platonicMegaDropContainer"></div>
<div id="platonicMegaDropItem" class="col-sm-11 col-12 mega_drop_option" choicetarget="0" pnid="0">Platonic Mega Dropdown Option</div>
<div id="platonicMegaItem" class="col-sm-12 col-12 mega_option" choicetarget="0" pnid="0">Platonic Mega Item</div>
<a href="#" target="ccoi_vid_popout" id="platonicVideoButton"><span>Open Video </span><span class="oi oi-external-link px-2" title="Open Session Video"></span></a>
<!-- <img id="platonicSubmitMulti" src="./assets/images/greencheck.png" class="submitmulti" /> -->
<button id="platonicSubmitMulti" class="submitmulti oi oi-check" title="submit"></button>

<?php 
	include 'includes/footer.php'; 
	
	
	
?>
<script language="javascript">
	var userid=0;
	if(typeof(jsUserVars) != 'undefined'){
		userid=jsUserVars['pid'];
		fetchAppPaths(1)		// this is the application id -- need to make variable later
		fetchAppVids(1);		// this is the application id -- need to make variable later
		setTimeout(function(){ fetchUserObSets(userid);},500);
	}
	
	var leftAndRight=document.getElementById('leftandright');			var origLeftAndRightClass='row py-5';
	var leftSide=document.getElementById('leftside');
	var leftInnerContainer=document.getElementById('leftInnerContainer');
//	var rightInnerContainer=document.getElementById('rightInnerContainer');
	
	var launchVideoButton=document.getElementById('launch_video_button');
	var playSpeeds=document.getElementById('playspeeds');
	
	var overlayOuter=document.getElementById('overlayOuter');
	//	overlayOuter.addEventListener('click',function(e){overlayOuter.style.display='none';e.stopPropagation();},false);		// false allows inneritems to work
	var overlayInner=document.getElementById('overlayInner');
	var overlayHead=document.getElementById('overlayHead');
	var overlayBody=document.getElementById('overlayBody');		
	var closeOverlay=document.getElementById('closeoverlay');		closeOverlay.addEventListener('click',function(e){overlayOuter.style.display='none';e.stopPropagation();},false);
	var submitMulti=document.getElementById('platonicSubmitMulti');
	
	var header_usersets=document.getElementById('usersets');
		header_usersets.addEventListener('click',function(e){showObservationSets();e.stopPropagation();},false);
	var header_loadedobsset=document.getElementById('loadedobsset');
		header_loadedobsset.addEventListener('click',function(e){loadObservationSet(e);e.stopPropagation();},false);
	var header_loadedobs=document.getElementById('loadedobs');
	var header_loadedobsxofx=document.getElementById('loadedobsxofx');
	var header_highlevelnotes=document.getElementById('highlevelnotes');
	var header_obsetmeta=document.getElementById('obsetmeta');
	var maybe_videolink=document.getElementById('maybevideolink');
	
	var platonicObsSet=document.getElementById('platonicObsSet');
	var platonicObs=document.getElementById('platonicObs');
	var platonicMega=document.getElementById('platonicMega');
	var platonicMegaDropController=document.getElementById('platonicMegaDropController');
	var platonicMegaDropContainer=document.getElementById('platonicMegaDropContainer');
	var platonicMegaDropItem=document.getElementById('platonicMegaDropItem');
	var platonicMegaItem=document.getElementById('platonicMegaItem');
	var platonicVideoButton=document.getElementById('platonicVideoButton');
	
	var keepAlive=setInterval(function(){ sendPing(); }, 600000);		// send a ping to keep ajax functions alive every 10 mins -- OR provide a more graceful way to have ajax deal with timeouts
	window.addEventListener('keydown',doKeyDown,true);
	window.addEventListener('keyup',doKeyUp,true);
	
	
</script>
<!-- <script src="./js/ccoi.js"></script> -->
</body> 
</html>