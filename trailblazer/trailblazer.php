<?php $ROOTPOINT='https://ccoi-dev.education.ufl.edu/trailblazer'; ?>

<html lang="en-US">
<head>
	<meta charset="UTF-8" /><meta name="robots" content="index, follow" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>Trailblazer testing</title>
	<script type='text/javascript' src='<?php echo $ROOTPOINT; ?>/js/trailblazer.js'></script>
	<link rel='stylesheet' id='all-css'  href='<?php echo $ROOTPOINT; ?>/trailblazer.css' type='text/css' media='all' />
</head>
<body>


<div id="marks-overlay" class="marks-overlay"></div>

<!--
<div id="marks-overlay4" class="marks-overlay4">
<div class="marks-overcontent7" id="marks-overcontent7"><div id="pM_head7">Uh oh...</div><div id="pM_body"><p id="errormsg">Error message here.</p></div></div>
</div>
-->









<!--
<div draggable="true" class="platonicDraggable" style="top:10px;" id="platonicDraggable" iihs_order="0"><div class="draggableBody"><p class="choicetext">0</p><p class="draggableText">New Node</p><p class="nexttext">0</p></div><div class="draggableHover"><p class='choicehover'>0</p><p class='nexthover' nextid='0'>0</p></div></div>
-->

<div draggable="true" class="platonicDraggable" style="top:10px;" id="platonicDraggable" iihs_order="0"><div class="draggableBody"><div class="choicetext">0</div><div class='choicehover'>Zero</div><p class="draggableText">New Node</p><div class="nexttext" nextid='0'>0</div></div><div class="draggableExpanded"><div class="fulltitle">New Node Full Title Text</div><div class="extravar">ExtraVar</div><div class="extrainst">ExtraInst</div><div class="nodegroup">Path Type (subgroup)</div><div class="choicegroup">Choice Group if used</div><div class="nodeids">newid / oldid</div><div class="destination">title with colors and number</div></div><div class="draggableEditor" CG="0" DEST="0" OT="" EXV="" EXI=""><div class="editorhead">Editing Node: 0</div><div class="editfulltitle"><p>Node Title</p><input type="text" id="editfulltitle" /></div><div class="editextravar"><p>Extra Variable Name (optional)</p><input type="text" id="editextravar" /></div><div class="editaside"><p>Extra Instructions (optional)</p><input type="text" id="editaside" /></div><div class="editchoicegroup">CHOICESELECTORGOESHERE</div><div class="editdestination">DESTINATIONSELECTORGOESHERE</div><input type='button' class='editBclose' value='close editor' /><input type='button' class='editBsubmit' value='update node' /></div>
</div>

<!-- NG="0"     <div class="editnodegroup">NODESELECTORGOESHERE</div> =====  only megas have node groups -->

<div draggable="true" class="platonicMega" id="platonicMega" color="none" colornumber="1"><div class="pM_head"><p class="headtext">platonicMega</p><p class="colornumber">0</p></div><div class="pM_body"><p id="draginfo">Drag elements into the order you wish to display them<br />Double click an element to edit it</p><div class="dragregion" id="dragregionX"></div></div><div class="megaEditor" NG="0" OT="X"><div class="editfulltitle"><p>Node Title</p><input type="text" id="editfulltitle_0" /></div><div class="editnodegroup">NODESELECTORGOESHERE</div><input type='button' class='editBclose' value='close editor' /><input type='button' class='editBsubmit' value='update node' /></div></div>


<!--


<script type='text/javascript'>
	var tinymce_base={selector: 'textarea.tinymce',entity_encoding:'raw',menubar:false, forced_root_block : "", plugins: 'link,code', branding: false, width: 480, height: 215, resize: false, statusbar: false, toolbar: 'undo redo | bold italic subscript superscript | bullist numlist outdent indent | link | code', init_instance_callback: function (editor) {editor.on('blur', function (e) {editor.save(); collectData(editor.id,actcode[editor.id],tablecode[editor.id],'GitoutThereNfetchit'); });}};
</script>
-->

<script type='text/javascript'>

	var overlay=document.getElementById('marks-overlay');        if(overlay){overlay.addEventListener('click',function(e){hideAllFlips();e.stopPropagation();},false);}
	var platonicDraggable=document.getElementById('platonicDraggable');
	var platonicMega=document.getElementById('platonicMega');
	
	var draggableBaseHeight=platonicDraggable.clientHeight+4;		// adding 4 for the extra spacing in the list -- if not displayed, then there's no height, so opacity time and top -100px
	var incr2=draggableBaseHeight/2;
	var origdraggers=new Object();	//	origdraggers['1']='22';	origdraggers['2']='23';	origdraggers['3']='24';
	
	var nodeGroups=new Object();
	var choiceGroups=new Object();


//{"description":"Student works independently on a computing related task","next":"9591c0","path_type":"Independent","branch_id":"c83b4e","node_sub_group":"independent_computing","t_parentid":"1","t_thisid":"6","t_nextid":"128","t_choicegroup":0},

// 	spawnMega('pm1','This is a title','green');			// == id, title, color
// 		spawnNoob('ignored_if_2nd_item_exists','pm1',1224,'Poof!  Im here!',1,'pm2');					// spawnNoob(e,tDR,id,text,choicegroup,nextmega) -- first item is ignored_if_2nd_item_exists and is meant for spawning new items as event
// 		spawnNoob('ignored_if_2nd_item_exists','pm1',1225,'And here!',1,'pm3');
// 		spawnNoob('ignored_if_2nd_item_exists','pm1',1226,'And still here!',1,'pm3');


goGetEm(1);

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