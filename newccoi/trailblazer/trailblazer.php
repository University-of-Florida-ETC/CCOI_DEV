<?php 
	$ROOTPOINT='https://ccoi-dev.education.ufl.edu/trailblazer'; 
	$CCOI_requireslogin=true;
	$CCOI_requiressuperadmin=true;
	include $_SERVER['DOCUMENT_ROOT'].'/api/ccoi_session.php';
?>

<html lang="en-US">
<head>
	<meta charset="UTF-8" /><meta name="robots" content="index, follow" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>Trailblazer testing</title>
	<script type='text/javascript' src='<?php echo $ROOTPOINT; ?>/js/trailblazer.js'></script>
	<link rel='stylesheet' id='all-css'  href='<?php echo $ROOTPOINT; ?>/trailblazer.css' type='text/css' media='all' />
</head>
<body>


<div id="marks-overlay" class="marks-overlay">
<p id='title'>Trailblazer v0.9</p>
<p id='instructions'>Drag Megas wherever you like.<br />Click on the background to dismiss any window/viewer.<br />Shift-double-click the background to create a new Mega.<br />Alt-click a Mega header to edit the Mega.<br />Shift-double-click a Mega header to create a new node within it.</p>
</div>

<div id="marks-overlay2" class="marks-overlay2"><div class="marks-overcontent" id="marks-overcontent"><div id="marks-overhead">Uh oh...</div><div id="marks-overbody"><p id="errormsg">Error message here.</p></div></div></div>
<div id="marks-overlay3" class="marks-overlay3"><div class="marks-overcontent" id="marks-overcontent"><div id="marks-overhead">Message...</div><div id="marks-overbody"><p id="errormsg">General message here.</p></div></div></div>









<!--
<div draggable="true" class="platonicDraggable" style="top:10px;" id="platonicDraggable" iihs_order="0"><div class="draggableBody"><p class="choicetext">0</p><p class="draggableText">New Node</p><p class="nexttext">0</p></div><div class="draggableHover"><p class='choicehover'>0</p><p class='nexthover' nextid='0'>0</p></div></div>
-->

<div draggable="true" class="platonicDraggable" style="top:10px;" id="platonicDraggable" iihs_order="0"><div class="draggableBody"><div class="choicetext">0</div><div class='choicehover'></div><p class="draggableText">New Node</p><div class="nexttext" nextid='0'>0</div></div><div class="draggableExpanded"><div class="fulltitle">New Node Full Title Text</div><div class="extravar">ExtraVar</div><div class="extrainst">ExtraInst</div><div class="nodegroup">Path Type (subgroup)</div><div class="choicegroup">Choice Group if used</div><div class="nodeids">newid / oldid</div><div class="warning">warningmessage</div><div class="destination">title with colors and number</div></div><div class="draggableEditor" CG="0" CD="0" DEST="0" OT="" EXV="" EXI=""><div class="editorhead">Editing Node: 0</div><div class="editfulltitle"><p>Node Title</p><input type="text" id="editfulltitle" /></div><div class="editextravar"><p>Extra Variable Name (optional)</p><input type="text" id="editextravar" /></div><div class="editaside"><p>Extra Instructions (optional)</p><input type="text" id="editaside" /></div><div class="editcode">CODESELECTORGOESHERE</div><div class="editchoicegroup">CHOICESELECTORGOESHERE</div><div class="editdestination">DESTINATIONSELECTORGOESHERE</div><input type='button' class='editBclose' value='close editor' /><input type='button' class='editBsubmit' value='update node' /></div>
</div>

<!-- NG="0"     <div class="editnodegroup">NODESELECTORGOESHERE</div> =====  only megas have node groups -->

<div draggable="true" class="platonicMega" id="platonicMega" color="none" colornumber="1"><div class="pM_head"><p class="headtext">platonicMega</p><p class="colornumber">0</p></div><div class="pM_body"><p id="draginfo">Drag elements into the order you wish to display them<br />Click an element to view details.  Alt-click an element to edit it</p><div class="dragregion" id="dragregionX"></div></div><div class="megaEditor" NG="0" OT="X" CG="0"><div class="editfulltitle"><p>Node Title</p><input type="text" id="editfulltitle_0" /></div><div class="editnodegroup">NODESELECTORGOESHERE</div><div class="editcodegroup">CODESELECTORGOESHERE</div><input type='button' class='editBclose' value='close editor' /><input type='button' class='editBsubmit' value='update node' /></div></div>


<svg id="derarrow" width="226px" height="80px" viewBox="0 0 226 80" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <defs><path d="M38.5,39.5 L214.5,39.5" id="path-1"></path><filter id="f3" x="-50%" y="-50%" width="200%" height="200%"><feOffset result="offOut" in="SourceAlpha" dx="0" dy="0" /><feColorMatrix result="matrixOut" in="offOut" type="matrix" values="0 0 0   0 0 0   0 0 0   0 0 0   0 0 0   0 0 0   .5 0" /><feGaussianBlur result="blurOut" in="matrixOut" stdDeviation="4" /><feBlend in="SourceGraphic" in2="blurOut" mode="normal" /></filter></defs>
    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><path id="Line-2" d="M166,15 L215,39.5 L166,64 L166,44 L62.5875188,44.0006087 C60.4743302,55.3819686 50.4935897,64 38.5,64 C24.9690236,64 14,53.0309764 14,39.5 C14,25.9690236 24.9690236,15 38.5,15 C50.493942,15 60.4749167,23.6185378 62.5877051,35.0003945 L166,35 L166,15 Z M38.5,24 C29.9395864,24 23,30.9395864 23,39.5 C23,48.0604136 29.9395864,55 38.5,55 C47.0604136,55 54,48.0604136 54,39.5 C54,30.9395864 47.0604136,24 38.5,24 Z" stroke="#444444" fill="#FFFFFF" fill-rule="nonzero" filter="url(#f3)"></path></g>
</svg>

<!--
<script type='text/javascript'>
	var tinymce_base={selector: 'textarea.tinymce',entity_encoding:'raw',menubar:false, forced_root_block : "", plugins: 'link,code', branding: false, width: 480, height: 215, resize: false, statusbar: false, toolbar: 'undo redo | bold italic subscript superscript | bullist numlist outdent indent | link | code', init_instance_callback: function (editor) {editor.on('blur', function (e) {editor.save(); collectData(editor.id,actcode[editor.id],tablecode[editor.id],'GitoutThereNfetchit'); });}};
</script>
-->

<script type='text/javascript'>

	var overlay=document.getElementById('marks-overlay');
		if(overlay){
			overlay.addEventListener('click',function(e){hideAllFlips();e.stopPropagation();},false);
		//	overlay.addEventListener('click',function(e){rotateDerArrowFromClick(e);e.stopPropagation();},false);
			overlay.addEventListener('dblclick',function(e){superSpawnMega(e);e.stopPropagation();},false);		//e.stopPropagation();
		}
	var platonicDraggable=document.getElementById('platonicDraggable');
	var platonicMega=document.getElementById('platonicMega');
	
	var draggableBaseHeight=platonicDraggable.clientHeight+4;		// adding 4 for the extra spacing in the list -- if not displayed, then there's no height, so opacity time and top -100px
	var incr2=draggableBaseHeight/2;
	var origdraggers=new Object();	//	origdraggers['1']='22';	origdraggers['2']='23';	origdraggers['3']='24';
	
	var nodeGroups=new Object();
	var choiceGroups=new Object();

	var overlay2=document.getElementById('marks-overlay2');
	var overlay3=document.getElementById('marks-overlay3');
	
	var derarrow=document.getElementById('derarrow');
		derarrow.style.top='100px';		derarrow.style.left='1200px';


//{"description":"Student works independently on a computing related task","next":"9591c0","path_type":"Independent","branch_id":"c83b4e","node_sub_group":"independent_computing","t_parentid":"1","t_thisid":"6","t_nextid":"128","t_choicegroup":0},

// 	spawnMega('pm1','This is a title','green');			// == id, title, color
// 		spawnNoob('ignored_if_2nd_item_exists','pm1',1224,'Poof!  Im here!',1,'pm2');					// spawnNoob(e,tDR,id,text,choicegroup,nextmega) -- first item is ignored_if_2nd_item_exists and is meant for spawning new items as event
// 		spawnNoob('ignored_if_2nd_item_exists','pm1',1225,'And here!',1,'pm3');
// 		spawnNoob('ignored_if_2nd_item_exists','pm1',1226,'And still here!',1,'pm3');

<?php
	$loadedPID=1;
	if(!empty($_GET['pid']) & is_numeric($_GET['pid'])){$loadedPID=$_GET['pid']+0;}
	echo "var loadedPID=$loadedPID;\n";
?>
goGetEm(loadedPID);

window.addEventListener('keydown',doKeyDown,true);
window.addEventListener('keyup',doKeyUp,true);

overlay.addEventListener('dragover',function(e){allowDropMega(e);},true);
overlay.addEventListener('drop',function(e){dropMega(e);},true);

<?php 
	if(!empty($derfooterjs)){echo $derfooterjs;}
?>

</script>
</body>
</html>