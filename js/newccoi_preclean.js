/*
	var leftInnerContainer=document.getElementById('leftInnerContainer');
	
	var platonicObsSet=document.getElementById('platonicObsSet');
	var platonicObs=document.getElementById('platonicObs');
	var platonicMega=document.getElementById('platonicMega');
	var platonicMegaDropController=document.getElementById('platonicMegaDropController');
	var platonicMegaDropItem=document.getElementById('platonicMegaDropItem');
	var platonicMegaItem=document.getElementById('platonicMegaItem');
*/
//	newNode.setAttribute('id',id);
//	newNode.childNodes[2].attributes.OT.nodeValue=text;
//	var targetDR=dermega.children[1].children[1];
//	newNode.setAttribute('iihs_order',(lastorder+1));
//	newNode.setAttribute('id',id);
//	newNode.childNodes[1].childNodes[4].style.display='none';
//	newNode.addEventListener('dragstart',function(e){pickUp(e);e.stopPropagation();},true);


//<div id="platonicObsSet" class="observationselect"><div class="col-sm-9 col-12 title">Platonic ObservationSet</div><div class="col-sm-3 col-12 selectoricons"><span class="oi oi-pencil px-2" title="Edit Session"></span><span class="oi oi-trash px-2" title="Delete Session"></span><span class="oi oi-pie-chart px-2" title="View Visualizations"></span></div></div>
//<div id="platonicObs" class="subsessionselect"><div class="col-sm-11 col-12 title">Platonic Observation</div><div class="col-sm-1 col-12 selectoricons"><span>+</span><span class="oi oi-pencil px-2" title="Edit Session"></span></div><div class="col-sm-11 col-12 notes"></div></div>
//<div id="platonicMega" class="mega"><div class="col-sm-12 col-12 timerblock"><div class="clock"><img src="/assets/images/clock.png" /></div><div class="timer">00:00</div><div class="incrementer"><span class="incr_beg">-5</span><span>-1</span><span>+1</span><span class="incr_end">+5</span></div><div class="timerinst">minutes will automatically<br />roll over with increment</div></div><div class="col-sm-12 col-12 title">MegaTitle</div><div class="megaoptioncontainer"></div></div>
//<div id="platonicMegaDropController" class="col-sm-12 col-12 dropdown_controller" onClick="flipContainer(this);">GroupName</div>
//<div id="platonicMegaDropContainer" class="col-sm-12 col-12 dropdown_container"></div>
//<div id="platonicMegaDropItem" class="col-sm-11 col-12 dropdown_option">Platonic Mega Dropdown Option</div>
//<div id="platonicMegaItem" class="col-sm-11 col-12 megaselect">Platonic Mega Item</div>

var derServer='https://ccoi-dev.education.ufl.edu/';
var loadedObservations=new Object();
var currentlyLoadedObservation=0;
var observationSets=new Object();
var currentlyLoadedObservationSet=0;
var observationElements=new Object();
var observationElements2=new Object();
var currentOpenMega=0;
var currentOpenContainer='';
var currentOpenContainers=new Object();
var fetchedPathData=new Object();
var currentlyLoadedPath=0;
var currentlyLoadedPathStartNode=0;
var viewingPlaygrounds=false;
var appVideoList=new Object();
var appPathList=new Object();
var timeAdjustedTimer=0;
var currentlyAdjustedTimeID=0;
var derDataHandoff= new Object();
var keyboardModifier=null;

function submitHandler_checkMultiSubmit(e){checkMultiSubmit(e);e.stopPropagation();};
function submitHandler_obSetNoob(e) {
	var derselect=e.target.previousSibling.firstChild;
	//console.log('looking at: '+opitem+' and index of \''+derselect.children[derselect.selectedIndex].value+'\'');
	overlayOuter.style.display='none'; 
	createNewObject(event,'OBS');
}
function submitHandler_videoEdit(e) {
	var derselect=e.target.previousSibling.firstChild;
	var targetOBSid=derselect.id;		var opitem=derselect.attributes['op'].value;
	var newData={}; newData[targetOBSid]={}		// very annoying that JS object keys cant accept variables UNLESS the object was created beforehand --- xx['yy']['zz']=1 is fine, but xx[yy][zz]=1 with yy and zz == 'yy' and 'zz' wont work ---- ah well....
	newData[targetOBSid][opitem]=derselect.options[derselect.selectedIndex].value; 		console.log('looking at: '+opitem+' and \''+derselect.options[derselect.selectedIndex].value+'\'');
	var rd=updateDatabase(funk,newData);
	overlayOuter.style.display='none'; 
	
	ds.attributes.videoid.nodeValue=derselect.options[derselect.selectedIndex].value;	// actually update the text of the edited item -- refreshes will also do this (note loadObSet refreshes from DB data)
	ds.innerHTML='&nbsp;&nbsp;'+derselect.options[derselect.selectedIndex].text;
	
	var bill=document.getElementById('launch_video_button');		//console.log('AAAAA '+bill);
	if(bill !== null){		// if there's already a videoUrl and we changed it, then there will be a button -- else we need to make the button
		var bbb=getTextWidth("Inconsolata",16,'Open '+derselect.options[derselect.selectedIndex].text);	
		bill.innerText='Open '+derselect.options[derselect.selectedIndex].text;
		bill.parentNode.style.width=(bbb+100)+'px';		// widen the button to fit the text
		bill.parentNode.lastChild.style.left=(bbb+140)+'px';	// shift the 1x / 1.5x / 2x buttons as well
	}else{
		var newNode=platonicVideoButton.cloneNode(true);		newNode.id='launch_video_button';		newNode.firstChild.innerText='Open '+derselect.options[derselect.selectedIndex].text;		newNode.style.display='block';		// none by css -- inline by default
		maybe_videolink.appendChild(newNode);		maybe_videolink.style.display='block';
	}
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

function flipContainer(target){
	var itemid=fetchedClickedID(target);											console.log('flipper found: '+itemid);
	var num=itemid.substring(11);														console.log('flipper looking for container_'+num+' to flip');
	var victim=document.getElementById('container_'+num);			console.log('current state: '+victim.style.display);
	victim.style.display='block';
	if(currentOpenContainer != '' && currentOpenContainer != num){hideLastContainer(); console.log('hiding last container: '+currentOpenContainer);}
	currentOpenContainer=num;
}
function hideLastContainer(){
	var victim=document.getElementById('container_'+currentOpenContainer);	victim.style.display='none';
}
function showMega(target){		//console.log('showMega sees '+typeof(target));
	if(typeof(target)=='number'){
		num=target;									console.log('showMega looking for '+num+' from explicit set');
	}else{
		if(typeof(target)=='object'){
			var itemid=fetchedClickedID(target);		//console.log('flipper found: '+itemid);
			var num=itemid.substring(7);											console.log('showMega looking for '+num+' from '+itemid+' to flip');
		}else{
			console.log('showMega needs either a number or an event object'); return;
		}
	}
	if(currentOpenMega>0){hideLastMega();}
	currentOpenMega=num;
	victim1=document.getElementById('maxiOE_'+currentOpenMega);		if(victim1){victim1.style.display='block';}
	victim2=document.getElementById('miniOE_'+currentOpenMega);			if(victim2){victim2.style.display='none';}
	if(typeof(currentOpenContainers[currentOpenMega])!='undefined'){currentOpenContainer=currentOpenContainers[currentOpenMega];}else{currentOpenContainer='';}
}
function hideLastMega(){
	if(typeof(currentOpenMega)!='undefined'){
		var victim1=document.getElementById('maxiOE_'+currentOpenMega);	if(victim1){victim1.style.display='none';}
		var victim2=document.getElementById('miniOE_'+currentOpenMega);	if(victim2){victim2.style.display='block';}
	}
}

function rightsideHandoff(){		console.log('RSH says hi!');
	// shove the left contents over to the right side when someone opens an observation
	// --- it appears to clone the elements, but not the action/events assigned -- good here as we dont have to scrub them, but can assign new ones
	document.getElementById('ob_0').style.display='none';			// ====== hide the Create New Obs button before shifting to the right side ==========
	rightInnerContainer.innerHTML=leftInnerContainer.innerHTML;
	var rsbits=document.getElementsByClassName("subsessionselect");		// note, this will grab both sets - need to filter below
	for (var i = 0; i < rsbits.length; i++) {
		var x=rsbits[i];
		console.log('checking subsessionselect '+x.id);
		if(leftOrRight(x)=='right'){		console.log('... and its a right one');
			x.addEventListener('click',function(e){loadObservation(e);e.stopPropagation();},true);		//	the transfer breaks the events, so set em up again
		}
	}
}
function switchPlayState(){
	viewingPlaygrounds=!viewingPlaygrounds; // switch true/false
	if(viewingPlaygrounds){leftAndRight.className=origLeftAndRightClass+' playground';}else{leftAndRight.className=origLeftAndRightClass;}
	showObservationSets();
}


function fetchUserObSets(u){
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
		//	console.log(data);
			observationSets=JSON.parse(data);
			showObservationSets();
		}
	}
	sendStr='uid2='+u;
	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			console.log(url);
	xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
}
function fetchDaPath(p){
	if(typeof(fetchedPathData[p]) != 'undefined'){		// did we already fetch this?
		var bits=fetchedPathData[p].split('|X|');
		choiceGroups=JSON.parse(bits[0]);
		observationElements=JSON.parse(bits[1]);		console.log('pulled path data from cache');
		mirrorPathData();
		currentlyLoadedPathStartNode=bits[2];
	}else{
		var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
		xmlHttp.onreadystatechange = function() {
			var data=getHTML(xmlHttp);
			if(data){ 
				var bits=data.split('|X|');
				choiceGroups=JSON.parse(bits[0]);
				observationElements=JSON.parse(bits[1]);
				fetchedPathData[p]=data;	// caching for later
				mirrorPathData();
				currentlyLoadedPathStartNode=bits[2];
			}
		}
		sendStr='pid2='+p;
		var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			console.log(url);
		xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
	}
}
function mirrorPathData(){
// need to re-process the path data from a megaid perspective to a pnid perspective for userData quick updates
	for (var m in observationElements){
		for (var g in observationElements[m]){
			for (var e in observationElements[m][g]){
				if(typeof(observationElements[m][g][e]['pnid'])!='undefined'){
					observationElements2[observationElements[m][g][e]['pnid']]=new Object();
					observationElements2[observationElements[m][g][e]['pnid']]['pnid']=observationElements[m][g][e]['pnid'];
					observationElements2[observationElements[m][g][e]['pnid']]['choiceid']=observationElements[m][g][e]['choiceid'];
					observationElements2[observationElements[m][g][e]['pnid']]['title']=observationElements[m][g][e]['title'];
					observationElements2[observationElements[m][g][e]['pnid']]['target']=observationElements[m][g][e]['target'];
					observationElements2[observationElements[m][g][e]['pnid']]['megaid']=m;
				}
			}
		}
	}
}
function fetchAppVids(a){
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){	appVideoList=JSON.parse(data);			//console.log(data);		// appPathList[1]->[startpnid] and [name]
		}
	}
	sendStr='vids4app2='+a;		// vids4app2 returns id=>name instead of other way for old app
	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			console.log(url);
	xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
}
function fetchAppPaths(a){
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){	appPathList=JSON.parse(data);			console.log(data);
		}
	}
	sendStr='paths4app2='+a;		// vids4app2 returns id=>name instead of other way for old app
	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			console.log(url);
	xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
}

function showObservationSets(){			// left side -- show a list of all ObS for user (not inactive for now)
	leftSide.style.opacity=0;
	leftInnerContainer.innerHTML='';			// wipe out all existing child nodes
	currentlyLoadedObservation=0;
	currentlyLoadedObservationSet=0;
	header_usersets.innerText='';
	header_obsetmeta.innerHTML='';													// clear out any existing OBS metadata
	header_obsetmeta.style.display='none';
	maybe_videolink.innerHTML='';		maybe_videolink.style.display='none';
	header_highlevelnotes.style.display='none';
	var maybeplayground='switch to the playground';		var settext=' Observation Sets';
		if(viewingPlaygrounds){maybeplayground='switch to research'; settext=' Playground Sets';}
	
	if(jsUserVars['first'].substring(-1)=='s'){apposS="'";}else{apposS="'s";}
	header_loadedobsset.innerText=jsUserVars['first']+apposS+settext;			// HERE we distinguish between Research and Playground ==========================
	header_loadedobsset.className='orange';
		header_loadedobs.className='';
	header_loadedobs.innerText='';
	header_loadedobsxofx.innerHTML='Select an observation set to view or edit the set or <span id="playstate" onClick="switchPlayState();">'+maybeplayground+'</span>';
	for (var e in observationSets){
		if( ( !viewingPlaygrounds && observationSets[e]['isPlayground']==0) || (viewingPlaygrounds && observationSets[e]['isPlayground']==1)){
			var newNode=platonicObsSet.cloneNode(true);			//	newNode.style.display='block';		// none by default - changing the id removes the d:n
				newNode.childNodes[0].innerText=observationSets[e]['name'];
					newNode.setAttribute('title','Path: '+appPathList[observationSets[e]['path']]['name']);		// this is getting called before the AJAX has time to reply with the data... reordered the loading calls
				newNode.id='obSet_'+e;
				newNode.addEventListener('click',function(e){loadObservationSet(e);e.stopPropagation();},false);
				newNode.lastChild.children[0].addEventListener('click',function(e){singleEdit(e);e.stopPropagation();},false);		// rename
					newNode.lastChild.children[0].setAttribute('opitem','name');
				newNode.lastChild.children[1].addEventListener('click',function(e){if(confirm("\nDELETING OBSERVATION SET\n===================================\n\nYou are about to delete an existing Observation Set.\nAre you sure about this?\n\n===================================\n")){nukeObSet(e);}e.stopPropagation();},false);	// nuke
				newNode.lastChild.children[2].addEventListener('click',function(e){visualize(e);e.stopPropagation();},false);		// visualize
			leftInnerContainer.appendChild(newNode);
		}
	}
	// CREATE NEW at bottom =========
	var newNode=platonicObsSet.cloneNode(true);			//	newNode.style.display='block';		// none by default - changing the id removes the d:n
	newNode.childNodes[0].innerText='Create New Observation Set';
		newNode.id='obSet_0';		newNode.setAttribute('opitem','dopath');
		newNode.removeChild(newNode.lastChild);
		newNode.addEventListener('click',function(e){doDrop(e);e.stopPropagation();},true);
	leftInnerContainer.appendChild(newNode);
	fadeInLS();
	
}







function loadObservationSet(e){				// left side -- fetch the ObS data for this id and load the Obs in the left area -- also loads path information to build data from
	if(typeof(e.target)!='undefined'){
		var item=e.target;			var itemid=fetchedClickedID(item);		var id2load=0;
		if(itemid=='document'){
			id2load=currentlyLoadedObservationSet;		// here, they clicked on the header area to reload the existing set
		}else{
			id2load=itemid.substring(6);
			currentlyLoadedObservationSet=id2load;
		}
	}else{		// we've been passed a new id from the creator
		currentlyLoadedObservationSet = id2load = e;
	}
	if(currentlyLoadedObservationSet==0){console.log('no need to load from here');return;}
	
	leftSide.style.opacity=0;		//console.log('lObS got '+e);
	console.log('loading ObS '+id2load+' from '+itemid);
	leftInnerContainer.innerHTML='';														// wipe out all existing child nodes
	header_obsetmeta.innerHTML='';													// clear out any existing OBS metadata
	header_obsetmeta.style.display='none';
	maybe_videolink.innerHTML='';		maybe_videolink.style.display='none';
	
	var d=observationSets[id2load];
	
	fetchDaPath(d['path']);			// =========================================== this is HERE because a user can have sets assigned to different paths == dont like it, but unless we restrict based on app or something, we need to fetch -- but we CAN cache it to avoid further fetches
	currentlyLoadedPath=d['path'];
	
	if(jsUserVars['first'].substring(-1)=='s'){apposS="'";}else{apposS="'s";}
	header_usersets.innerText=jsUserVars['first']+apposS+' Observation Sets';
	// ========== ObservationSets (sessions) can have notes (and some do), though I dont know where to enter them yet ==================
	if(typeof(d['notes'])!='undefined' && d['notes']!=''){		//	console.log('OBS found '+d['notes']);
		var notetext=d['notes'];		 header_highlevelnotes.className='';		// restore normal font/spacing
		if(notetext.length > 800){var tempy=d['notes'].substring(0,800); var lastSpace=tempy.lastIndexOf(' '); notetext=d['notes'].substring(0,lastSpace)+'...'; header_highlevelnotes.className='tiny';}		// too damn many letters
		header_highlevelnotes.innerText=notetext; header_highlevelnotes.style.display='block';
		var othere='editOBSnote|_x_|notes|_x_|'+id2load+'|_x_|'+d['notes'];
		header_highlevelnotes.addEventListener('click',function(e){multiLineEdit(e,othere);e.stopPropagation();},true);
		header_highlevelnotes.setAttribute('title','Edit this Observation Note');
	}else{
		header_highlevelnotes.style.display='none';											// TODO -- should switch this over to "add a note" ===================
//		var othere='editOBSnote_notes_'+id2load+'_'+dd['notes'];
//		header_highlevelnotes.addEventListener('click',function(e){multiLineEdit(e,othere);e.stopPropagation();},true);
	}
header_loadedobsxofx.innerText='Select an observation to view or edit its responses';
	
	if(d['name']==null){ObSetTitle='Observation Set #'+id2load;}else{ObSetTitle=d['name'];}
	header_loadedobsset.innerText=ObSetTitle;
	header_loadedobsset.className='orange';
		header_loadedobs.className='';
	var hiddenHeader=document.createElement('h3');			hiddenHeader.innerHTML='<span class="hiddenHeaderSpan">Observations for:<br /></span>'+ObSetTitle;			leftInnerContainer.appendChild(hiddenHeader);
	header_loadedobs.innerText='';	header_obsetmeta.innerText='';	var spacer=' &nbsp; &nbsp; ';
		var ps=document.createElement('span');		ps.innerText='Path: '+appPathList[currentlyLoadedPath]['name'];
		
		var vs=document.createElement('span');		vs.id='videdit_'+id2load;		vs.className='editspan';		vs.setAttribute('opitem','videoid');		vs.setAttribute('title','Change the Video');					if(d['videoURL']!='' && d['videoURL']!=null){vs.innerHTML=spacer+d['videoURL'];vs.setAttribute('videoid',d['videoID']);}else{vs.innerHTML=spacer+'No Video';vs.setAttribute('videoid',0);}
		var pts=document.createElement('span');		pts.id='ptedit_'+id2load;		pts.className='editspan';	pts.setAttribute('opitem','placetime');	pts.setAttribute('title','Change the Place-Time');		if(d['placetime']!='' && d['placetime']!=null){pts.innerHTML=spacer+d['placetime'];}else{pts.innerHTML=spacer+'No Place-Time';}
		var ss=document.createElement('span');		ss.id='studedit_'+id2load;		ss.className='editspan';		ss.setAttribute('opitem','studentid');		ss.setAttribute('title','Change the StudentID');			if(d['studentid']!='' && d['studentid']!=null){ss.innerHTML=spacer+d['studentid'];}else{ss.innerHTML=spacer+'No StudentID';}
		
		vs.addEventListener('click',function(e){doDrop(e);e.stopPropagation();},true);
		pts.addEventListener('click',function(e){singleEdit(e);e.stopPropagation();},true);
		ss.addEventListener('click',function(e){singleEdit(e);e.stopPropagation();},true);
		
	header_obsetmeta.appendChild(ps); header_obsetmeta.appendChild(vs); header_obsetmeta.appendChild(pts); header_obsetmeta.appendChild(ss);		header_obsetmeta.style.display='block';
	
	if(d['videoURL'] != '' && d['videoURL'] != null){
		var bbb=getTextWidth("Inconsolata",16,'Open '+d['videoURL']);				console.log('videourl = '+d['videoURL']+' == '+bbb);
		var newNode=platonicVideoButton.cloneNode(true);		newNode.id='launch_video_button';	newNode.style.width=(bbb+100)+'px';	newNode.firstChild.innerText='Open '+d['videoURL'];		newNode.style.display='block';		// none by css -- inline by default
		//newNode.lastChild.style.left=(bbb+140)+'px';
		//newNode.addEventListener('click',function(){window.open('/newvideo_popout.php?vid='+d['videoID']);});
		newNode.href='/newvideo_popout.php?vid='+d['videoID'];
		maybe_videolink.appendChild(newNode);		maybe_videolink.style.display='block';
	}

	for (var e in d['observations']){			//console.log('loading obs '+e);
		var newNode=platonicObs.cloneNode(true);				newNode.style.display='block';		// none by default
		//	var sublabel=getFirstSubLabel(d['observations'][e]['ObResp']);
			var sublabel=d['observations'][e]['name'];
				if( sublabel == null){sublabel='Observation #'+e;}
			newNode.childNodes[1].innerText=sublabel;
			var extras=getAnySubInfo(d['observations'][e]['ObResp']);
			if(extras){newNode.childNodes[3].innerText=extras;newNode.childNodes[2].style.display='block';}
		newNode.children[0].children[0].innerHTML=humanTime(d['observations'][e]['ObResp'][0]['seconds'],false);		// need HTML for <br />
			newNode.id='ob_'+d['observations'][e]['ssid'];
			newNode.addEventListener('click',function(e){loadObservation(e);e.stopPropagation();},false);		// we're getting rid of the right side, so no need -- rightsideHandoff();
			newNode.childNodes[2].children[0].addEventListener('click',function(e){singleEdit(e);e.stopPropagation();},false);		// rename
			newNode.childNodes[2].children[0].setAttribute('opitem','name');
		leftInnerContainer.appendChild(newNode);
	}
	
//	launchVideoButton.innerHTML='<span class="obvid">Observing Video<br /></span>'+d['videoURL']+'<span class="oi oi-external-link px-2" title="Open Session Video"></span>';
//	playSpeeds.style.display='block';

	// CREATE NEW at bottom =========
	var newNode=platonicObsSet.cloneNode(true);			//	newNode.style.display='block';		// none by default - changing the id removes the d:n
	newNode.childNodes[0].innerText='Create New Observation';
		newNode.id='ob_0';
		newNode.removeChild(newNode.lastChild);
		newNode.addEventListener('click',function(e){createNewObject(e,'OB');e.stopPropagation();},true);
	leftInnerContainer.appendChild(newNode);
	
	fadeInLS();
}










function loadObservation(e,openme){					// NOTE: NUM instead if ID --- this is the1st, 2nd, etc obs in the set -- left side -- load an Ob into the left box (shift ObS data to right if needed -- use above path data to build elements on the fly
	leftSide.style.opacity=0;
	if(typeof(e.target)!='undefined'){
		var item=e.target;			var itemid=fetchedClickedID(item);		var id2load=0;
		if(itemid=='document'){
			id2load=currentlyLoadedObservation;		// here, they clicked on the header area to reload the existing obs
		}else{
			id2load=itemid.substring(3);
			currentlyLoadedObservation=id2load;
		}
	}else{		// we've been passed a new ides from the creator
		currentlyLoadedObservation = id2load = e;		itemid='direct';
	}
	console.log('loading Ob '+id2load+' from '+itemid);
	
	var dd=observationSets[currentlyLoadedObservationSet]['observations'][id2load];
	var d=dd['ObResp'];		//console.log('====='); console.log(d);	// tech doc has ['ObResp'], but maybe we can remove that, if no non object data like 'title' or something
//	var sublabel=getFirstSubLabel(d);
	var sublabel=dd['name'];			if(sublabel == '' || sublabel==null){sublabel='Observation #'+id2load;}
	header_loadedobs.innerHTML=sublabel;
	header_loadedobsxofx.innerText='Select an observation element to view or edit its responses';
	header_loadedobsset.className='';
	header_loadedobs.className='orange';
	// ========== Observations (subsessions) can have notes now ==================
	if(dd['notes'] != null){		console.log('found '+dd['notes']);
		var notetext=dd['notes'];		 header_highlevelnotes.className='';		// restore normal font/spacing
		if(dd['notes'].length > 800){var tempy=dd['notes'].substring(0,800); var lastSpace=tempy.lastIndexOf(' '); notetext=dd['notes'].substring(0,lastSpace)+'...'; header_highlevelnotes.className='tiny';}		// too damn many letters
		header_highlevelnotes.innerText=notetext; header_highlevelnotes.style.display='block';
		var othere='editOnote|_x_|notes|_x_|'+id2load+'|_x_|'+dd['notes'];
		header_highlevelnotes.addEventListener('click',function(e){multiLineEdit(e,othere);e.stopPropagation();},true);		header_highlevelnotes.setAttribute('title','Edit this Observation Note');
	}else{
		header_highlevelnotes.innerText='No Notes';  header_highlevelnotes.style.display='block';
//		header_highlevelnotes.style.display='none';
		var othere='editOnote|_x_|notes|_x_|'+id2load+'|_x_|';
		header_highlevelnotes.addEventListener('click',function(e){multiLineEdit(e,othere);e.stopPropagation();},true);
	}
	header_obsetmeta.innerText=''; header_obsetmeta.style.display='none';			// ObSets only
	maybe_videolink.innerHTML='';		maybe_videolink.style.display='none';
	
		leftInnerContainer.innerHTML='';						//	console.log('loading Ob '+id2load);
		for(var e=0;e<d.length;e++){		// d (dd['ObResp']) is an ARRAY not an OBJECT
			var megaid=d[e]['megaid'];
			var said=d[e]['SAid'];
			var newMega=platonicMega.cloneNode(true);				//	newMega.style.display='block';		// none by default - changing the id removes the d:n
				newMega.id='ObsElem_'+said;			//	console.log('spawning mega node for sa'+said+' / m'+megaid);
				newMega.attributes.mega.nodeValue=d[e]['megaid'];		// used on click above to see if above click target is same as existing target -- see line 338 (or so) below
			
			var element=observationElements[megaid];		//	console.log(element);
			var miniMega=newMega.firstChild;
				miniMega.id='miniOE_'+said;			// look 60 lines or so down for population commands (need the answer for that)
				var answertext='( NO OPTION SELECTED )';		var answeredNode;	var answeredNodeSAid;  var notetextarray=new Array();
				miniMega.addEventListener('click',function(e){showMega(e);e.stopPropagation();},true);
				if( typeof(d[e]['extra']) != 'undefined'){notetextarray.push(d[e]['extra']); console.log('this item has an extra ==========');}
				if( typeof(d[e]['notes']) != 'undefined'){notetextarray.push(d[e]['notes']); console.log('this item has an note ==========');}
			var maxiMega=newMega.lastChild;
				maxiMega.id='maxiOE_'+said;
				maxiMega.firstChild.innerText=element[0][0]['title'];//+megaid;
				maxiMega.children[1].addEventListener('click',function(e){hideLastMega();e.stopPropagation();},true);		// red-x to close without opening another mega
		//		if(d[e]['seconds']==''){d[e]['seconds']=0;}
				maxiMega.lastChild.children[1].innerText=humanTime(d[e]['seconds']);
				maxiMega.lastChild.children[1].attributes.storedtime.nodeValue=d[e]['seconds'];
				
				var incrementer=maxiMega.lastChild.children[2];
					incrementer.children[0].addEventListener('click',function(e){adjustTime(e,-5);e.stopPropagation();},true);
					incrementer.children[1].addEventListener('click',function(e){adjustTime(e,-1);e.stopPropagation();},true);
					incrementer.children[2].addEventListener('click',function(e){adjustTime(e,1);e.stopPropagation();},true);
					incrementer.children[3].addEventListener('click',function(e){adjustTime(e,5);e.stopPropagation();},true);
			
			if(element.length>1 || !Array.isArray(element)){				// this mega is grouping choices -- if the choicegroups (indices) dont start with 1, we have a problem here (node46 for example starts with 4) -- JSON doesnt do assoc arrays, so uses object notation instead of array -- switch all to object
				for (var t in element){
					if(t != 0){		//	need to skip the first item
						var newNode=platonicMegaDropController.cloneNode(true);				//console.log('spawning controller node '+t+' of '+(element.length-1)+' with '+t.length+' elements'+' for '+said);
							newNode.id='controller_'+e+'_'+t;
							newNode.innerText=choiceGroups[t];
						maxiMega.appendChild(newNode);
						var newNode2=platonicMegaDropContainer.cloneNode(true);				newNode2.style.display='none';		// while set to none in css file, cant really read that reliably -- explicit setting lets the flipper work fine
							newNode2.id='container_'+e+'_'+t;
						for (var z in element[t]){
					//		console.log('spawning element in container node '+z+' of '+element[t].length+' for '+t);
							var newNode3=platonicMegaDropItem.cloneNode(true);
							newNode3.id='choice_'+element[t][z]['choiceid'];
							newNode3.attributes.pnid.nodeValue=element[t][z]['pnid'];
							newNode3.attributes.choicetarget.nodeValue=element[t][z]['target'];			// we compare this to the PNid attribute in the existing next node to see if changes are needed downstream...


							newNode3.innerText=element[t][z]['title'];//+' / '+element[t][z]['choiceid']+' / '+element[t][z]['target'];


							if(d[e]['choiceid'] == element[t][z]['choiceid']){
								newNode3.style.backgroundColor='#e8a50c'; newNode2.style.display='block';answertext=element[t][z]['title'];answeredNode=newNode3; answeredNodeSAid=d[e]['SAid'];
								newNode.style.backgroundColor='#e8a50c';	currentOpenContainers[said]=e+'_'+t;
							}else{
								newNode3.addEventListener('click',function(e){makeChoice(e);e.stopPropagation();},true);		// can't choose something you already chose
							}
							newNode2.appendChild(newNode3);
						}
						maxiMega.appendChild(newNode2);
					}
				}
				
			}else{
		//		console.log('element has one key');
				for(var z=1;z<element[0].length;z++){			//console.log('spawning un-contained node '+z+' of '+(element[0].length-1)+' for '+said);
					var newNode=platonicMegaItem.cloneNode(true);		//	newNode.style.display='block';		// none by default - changing the id removes the d:n
					newNode.innerText=element[0][z]['title'];//+' / '+element[0][z]['choiceid']+' / '+element[0][z]['target'];
					newNode.id='choice_'+element[0][z]['choiceid'];
					newNode.attributes.pnid.nodeValue=element[0][z]['pnid'];
					newNode.attributes.choicetarget.nodeValue=element[0][z]['target'];
					
				//	console.log(d[e]['choiceid']+' == '+element[0][z]['choiceid' ]);
					
					if( d[e]['choiceid'] == element[0][z]['choiceid' ]){
						newNode.style.backgroundColor='#e8a50c';answertext=element[0][z]['title']; answeredNode=newNode; answeredNodeSAid=d[e]['SAid'];
					}else{
						newNode.addEventListener('click',function(e){makeChoice(e);e.stopPropagation();},true);		// can't choose something you already chose
					}
					maxiMega.appendChild(newNode);
				}
			}
			// setting this here, so we can include the answer text as part of the mega
			miniMega.children[0].children[0].innerHTML=humanTime(d[e]['seconds'],true);		// need HTML for <br />
			miniMega.children[1].innerText=element[0][0]['title'];//+megaid;
			miniMega.children[2].innerHTML=answertext;					// \u00a0 because &nbsp; doesnt work
			if(typeof(answeredNode)!='undefined' && answeredNodeSAid == d[e]['SAid']){				// === IF we have an answered node, it could have notes
				if(notetextarray.length>0){								// notes / extras for an ObsElement
						var ptempy=notetextarray.join(' / '); 			//console.log(notetextarray);  //console.log('answeredNode has a note: '+notetextarray.length);		// we'll shorten this later if needed;			
						var respNote=document.createElement('div');		respNote.className='notes';		respNote.innerText=ptempy;		// this is the note itself in small text
						var noteEdit=document.createElement('div');		noteEdit.id='editOEnote_notes_'+answeredNodeSAid;  noteEdit.className='selectoricons';		noteEdit.innerHTML='<span class="oi oi-pencil px-2" title="Edit Note"></span>';
						noteEdit.addEventListener('click',function(e){multiLineEdit(e);e.stopPropagation();},true);
						answeredNode.appendChild(respNote);		answeredNode.appendChild(noteEdit);
						answeredNode.style.paddingRight='40px';
				
					var tempy=ptempy;
					if(ptempy.length>750){tempy=ptempy.substr(0,300);  lastSpace=tempy.lastIndexOf(' ');			tempy=tempy.substring(0,lastSpace)+'...';	}
					miniMega.children[4].innerText=tempy;
					miniMega.children[4].style.display='block';
					miniMega.children[3].style.display='none';		// hide the "add note" button -- one can expand the note to edit it
				}else{
					console.log(answeredNodeSAid+' is getting the add note bit');
					var noteEdit=document.createElement('div');	noteEdit.id='editOEnote_notes_'+answeredNodeSAid;  noteEdit.className='selectoricons';		noteEdit.innerHTML='<span>+</span><span class="oi oi-pencil px-2" title="Add New Note"></span>';
					noteEdit.addEventListener('click',function(e){multiLineEdit(e);e.stopPropagation();},true);
					answeredNode.appendChild(noteEdit);
					answeredNode.style.paddingRight='40px';
				}
			}
			
			leftInnerContainer.appendChild(newMega);
		}
		
// 									var tempytest=document.createElement('p');
// 									tempytest.innerText='Tempy Test';
// 									tempytest.addEventListener('click',function(e){tempyObs(e);e.stopPropagation();},true);
// 									leftInnerContainer.appendChild(tempytest);
	if(typeof(openme)!='undefined'){showMega(openme);}
	fadeInLS();
}













function makeChoice(e){
	if(e.target.classList.contains('mega_drop_option')){
		var momndad=e.target.parentNode.parentNode.parentNode;
		var said=e.target.parentNode.parentNode.id.substr(7);		// immediate parent is a drop container
	}else{
		var momndad=e.target.parentNode.parentNode;
		var said=e.target.parentNode.id.substr(7);
	}
//	console.log('momndad = '+momndad.id);
	var dertarget=e.target.attributes.choicetarget.nodeValue;				var derdata={};		var existingNextTarget=0;		var totalNoob=0;  var noWorries=0;
	var choice=e.target.id.substr(7);
	var pnid=e.target.attributes.pnid.nodeValue;
	var tempy=' (no existing)';			var nukes=[];
	if(momndad.nextSibling != null){
		var existingNextTarget=momndad.nextSibling.attributes.mega.nodeValue;
		if(existingNextTarget == dertarget){
			tempy=' which is the same as the current one, so no changes needed';			noWorries=1;
		}else{
			var grandmaskids=momndad.parentNode.childNodes;
			for(var t=(grandmaskids.length-1);t>0;t--){			//console.log('grannieskids = '+grandmaskids[t].id);
				if(grandmaskids[t] != momndad){
					var gmk=grandmaskids[t].id.substr(8);
					nukes.push(gmk);
					derdata[gmk]={};		derdata[gmk]['nuke']=1;
				}
			}
			tempy=' (was '+existingNextTarget+') so well have to clear out SAids: '+nukes.join(', ')+')';
		}
		// ========= here we need to throw a warning that existing downstream selections will be lost =============
	}else{
		totalNoob=1;
	}
	derdata[said]={};  
	if(totalNoob>0){
		console.log('SAid of '+said+' selected a new choice: '+choice+' (PNid:'+pnid+')  pointing to ITS FIRST target Mega of '+dertarget+tempy);
	}else{
		if(existingNextTarget>0){		// looks like some deletes are in here as well
			console.log('SAid of '+said+' selected a new choice: '+choice+' (PNid:'+pnid+')  pointing to new target Mega of '+dertarget+tempy);
		}else{	// === last OE in the chain, but not the first either ===
			console.log('SAid of '+said+' selected a new choice: '+choice+' (PNid:'+pnid+')  pointing to new target Mega of '+dertarget+tempy);
		}
	}
	if(e.target.parentNode.id.substr(0,9)=='container'){
		var secs=e.target.parentNode.parentNode.children[2].children[1].attributes.storedtime.nodeValue; // finding the time from a multi layer option set
	}else{
		var secs=e.target.parentNode.children[2].children[1].attributes.storedtime.nodeValue; // finding the time from a single layer option set
	}
	
	
	derdata[said]['swap']=pnid;

	if(dertarget != currentlyLoadedPathStartNode){		// only need to make a new OE if the choice selected does not terminate
		derdata[said]['noob']=currentlyLoadedObservationSet+'_'+currentlyLoadedObservation+'_'+dertarget+'_'+secs;
	}else{
		console.log('===== terminating Observation with this selection');
	}

	if(keyboardModifier=='alt'){			// ========== if they hold down ALT while clicking a choice, then they can add a note beforehand ==========
		derDataHandoff=derdata;
		multiLineEdit(e,'newOEwithnote|_x_|notes|_x_|'+said+'|_x_|');	// no initial text
	}else{
		updateDatabase('OE',derdata);
	}
	// === looks like the line below runs before things can be updated === TODOTODO -- UD runs before, but UD's call to ULD comes after === line 650 may handle this
	//if(dertarget == currentlyLoadedPathStartNode){loadObservation(currentlyLoadedObservation);}		// sort of a repeat -- but we need to split these up -- if terminating, then no noob and no noob means no refresh of the leftwindow area, so lets force one
}


// ====================================================================================================


function updateDatabase(f,d){				// ajax bit sending data to the system -- each choice is sent and the local JS ObS object is updated
	// ==== F (function) - D (data) to be altered - always an object organized by targetid and data type
	// ==== updateDatabase('OS',{8:{title:'New Title',student:'7',notes:'These are some new notes for this session/OS'}});
	// ==== updateDatabase('OE',{1766:{pn:'128',extra:'4',timer:'177',notes:'kid spoke to kid #4'},1777:{nuke:1}});		//  D is multi-function - update 1766 and remove 1767
	// ==== updateDatabase('OE',{156:{noob:'156'}});
	//console.log('updateDatabase says: yay!');		//console.log(d);
	var gotaNoob='';	var gotaSwap='';
	for (var t in d){for (var dd in d[t]){
		console.log('updateDatabase sees '+f+' work for ID: '+t+' ===== '+dd+' / '+d[t][dd]);
		if(dd=='noob'){gotaNoob=f;}
		if(dd=='swap'){gotaSwap=f;}
	} }
	var dd=JSON.stringify(d);
	
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
			console.log('received data = '+data);
			if(data.substr(0,1)=='A'){
				var data2=data.substr(2);		// remove the A| leaving the JSON data
				if(data2 != ''){var rd=JSON.parse(data2);}else{var rd=new Object();}

				updateLocalData(f,d,rd);			//// ======= update the local info store before reloading the screen

				if(gotaNoob != ''){
					var noobid=rd[f+'noob0'];
					if(noobid != ''){
						switch(gotaNoob){
							case 'OBS': 	var bits=noobid.split('_');  currentlyLoadedObservationSet=bits[1];	loadObservationSet(currentlyLoadedObservationSet); break;
							case 'OB': 	currentlyLoadedObservation=noobid; 	 createNewObject(appPathList[currentlyLoadedPath]['startpnid']+'_newob','OE');	break;	// new OB also auto-creates a new OE using the start pnid
							case 'OE': 	var bits=noobid.split('_'); loadObservation(currentlyLoadedObservation,Number(bits[3]));		// here we're reloading the Ob, opening the new OE  	// loadObs either takes an event or an id for the first, if a second, it tells it to open that id exposing its OE(s)
												
						}
					}else{console.log('no valid noob id');}
					
					// if we have a swap (which just updates PNid pointers on choices --- but there is no corresponding noob in this action, then it's a terminator and still needs updating
					//if(gotaNoob == '' && gotaSwap != ''){loadObservation(currentlyLoadedObservation);}
				}
			}else{
				// error X
			}
		}
	}
	sendStr='play='+viewingPlaygrounds+'&dafunc='+f+'&updates='+dd;			//console.log(sendStr);
	var url =  encodeURI(derServer+'api/ccoi_update2.php');
	xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);	
}


// ====================================================================================================


function updateLocalData(f,d,returnData){
	var gotaNoob='';	var gotaSwap='';
	for (var t in d){ for (var dd in d[t]){		if(dd=='noob'){gotaNoob=f;}		if(dd=='swap'){gotaSwap=f;}  }}	// preprocess

	for (var t in d){ for (var dd in d[t]){
		console.log('updateLocalData sees '+f+' work for ID: '+t+' ===== ('+dd+') / '+d[t][dd]);
		switch(dd){
			case 'noob':	console.log('noobing');// this can have multiples but probably wont
					var noobid=returnData[f+'noob0'];		// this is either a new sessionid / subsesisonid / or block of info with SAid and such
					switch(f){	
						case 'OBS':	var bits=noobid.split('_');		noobid=bits[1];   var path=bits[0];
											observationSets[noobid]={};  observationSets[noobid]['isPlayground']=viewingPlaygrounds;  observationSets[noobid]['path']=path;  observationSets[noobid]['observer']=jsUserVars['pid'];
											observationSets[noobid]['name']='New Observation Set';  observationSets[noobid]['placetime']='';  //observationSets[noobid]['notes']='';  
											observationSets[noobid]['studentid']='';  observationSets[noobid]['videoURL']='';  observationSets[noobid]['videoID']='';
											observationSets[noobid]['observations']={};
											break;
						case 'OB':		observationSets[currentlyLoadedObservationSet]['observations'][noobid]={}; observationSets[currentlyLoadedObservationSet]['observations'][noobid]['name']='New Observation';
											observationSets[currentlyLoadedObservationSet]['observations'][noobid]['ssid']=noobid;  //observationSets[currentlyLoadedObservationSet]['observations'][noobid]['notes']='';
											observationSets[currentlyLoadedObservationSet]['observations'][noobid]['ObResp']=[];  	// ARRAY not OBJECT
											break;
						case 'OE':		var bits=noobid.split('_');		console.log(bits);	//console.log(observationElements2[bits[0]]);
											var noob={};	// ================================ THIS ONE GETS A LOT OF ACTIVITY -- each choice makes a new one of these ============
											noob['SAid']=bits[3];  noob['PNid']=bits[0];  noob['megaid']=bits[1];  noob['choiceid']=0;  noob['seconds']=bits[2];  //noob['notes']='';			// noob['megaid']=observationElements2[bits[0]]['megaid']; 
											observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'].push(noob);	// push this to end of ARRAY
											break;
					}
					break; // DD switch
			case 'swap':	console.log('swapping');
					switch(f){	
						case 'OE': 	var index=fetchIndexFromSAid(t);		//returnData is just the new PNID
											var bits=returnData[f+'swap'+t].split('_');  				console.log(bits);	console.log(observationElements2[bits[0]]);
											observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['PNid']=bits[0];
											observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['megaid']=bits[1];
											if(typeof(returnData[f+'update'+t]) != 'undefined' && typeof(returnData[f+'update'+t]['notes'])=='undefined'){		// only remove the notes on a swap if we havent 'ALT-Choiced' a new note -- the UPDATE step in this loop will deal with it
												delete(observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['notes']);		// notes=='' means something else
											}
											observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['choiceid']=observationElements2[bits[0]]['choiceid'];	//console.log('ASSIGNING '+observationElements2[bits[0]]['choiceid']);	// need to get this
										//	console.log(observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]);
											break;	// this is the only use of swap
					}	break;
			case 'nuke':	console.log('nuking');	// this can have multiples and often does
					var nukeid=t;		// not really needed
					switch(f){	
						case 'OBS':	delete( observationSets[nukeid] ); showObservationSets();  break;
						case 'OB':		delete( observationSets[currentlyLoadedObservationSet]['observations'][nukeid] );  loadObservationSet(currentlyLoadedObservationSet);  break;
						case 'OE':		var index=fetchIndexFromSAid(nukeid);		var e=0;		// since these come in blocks sometimes, the line below just starts chopping if there's anything to chop
											if(index>=0){observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'].splice( index,1 );}
											break;
					} break; // DD switch


					
			// updates dont come in with the UPDATE  tag, but rather with the thing being updated		
			default:	console.log('updating (default)');	// this is for UPDATES and can have multiples and often does
					var updateinfo=returnData[f+'update'+t];		console.log(updateinfo);		// this is the bit(s) of data being updated
					for (var u in updateinfo){
						switch(f){	
							case 'OBS':	observationSets[t][u]=updateinfo[u]; 
												console.log('OBS updated '+u+' in '+t);
												if(u=='videoid'){observationSets[t]['videoURL']=appVideoList[updateinfo[u]];console.log('updated videoURL in '+t+' as well');}		// when updating the videoID, we dont need a DB update for URL, but we need to update the local store
												if(u=='notes'){
													var n=document.getElementById('highlevelnotes');		var notetext=d[t][dd];
													if(notetext.length > 800){var tempy=notetext.substring(0,800); var lastSpace=tempy.lastIndexOf(' '); notetext=tempy.substring(0,lastSpace)+'...'; header_highlevelnotes.className='tiny';}
													n.innerText=notetext;		// updateinfo will have backslashes
												}
												break;
							case 'OB':		observationSets[currentlyLoadedObservationSet]['observations'][t][u]=updateinfo[u]; 
												if(u=='notes'){
													var n=document.getElementById('highlevelnotes');		var notetext=d[t][dd];
													if(notetext.length > 800){var tempy=notetext.substring(0,800); var lastSpace=tempy.lastIndexOf(' '); notetext=tempy.substring(0,lastSpace)+'...'; header_highlevelnotes.className='tiny';}
													n.innerText=notetext;		// updateinfo will have backslashes
												}
												console.log('OB updated '+u+' in '+t);
												break;
							case 'OE':		// this one will depend on whether we reuse the last valid SAid -- not really -- other bits may change though -- affects OE noob as well
												var index=fetchIndexFromSAid(t);
												observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index][u]=updateinfo[u];
												
												if(u=='notes'){		// need to update text in minimega and maximega areas
 													var miniMega=document.getElementById('ObsElem_'+t).firstChild;
 														var notetext=updateinfo[u];
 														if(notetext.length > 300){var tempy=notetext.substring(0,300); var lastSpace=tempy.lastIndexOf(' '); notetext_m=tempy.substring(0,lastSpace)+'...';}
 														if(notetext.length > 800){var tempy=notetext.substring(0,800); var lastSpace=tempy.lastIndexOf(' '); notetext=tempy.substring(0,lastSpace)+'...';}
 														miniMega.lastChild.innerText=notetext_m;		miniMega.lastChild.style.display='block';			// miniMega notes - always exists
 														
 											//		console.log('looking for choice '+observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['choiceid']+' from '+currentlyLoadedObservationSet+' and '+currentlyLoadedObservation+' and '+index);
 													var daChoice=document.getElementById('choice_'+observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['choiceid']);		// this one is harder as it's tied to the choice selected
														// trouble here is that if there is no existing notes block, we need to make one...
														if(daChoice.children.length==2){		// already a note there...
															daChoice.firstChild.innerText=notetext;
														}else{
															if(gotaNoob == ''){		// dont do this if this is a noobwithnote
																daChoice.lastChild.remove();	// remove the 'add note' link
															}
															// no note with edit, but instead link to add note
															var respNote=document.createElement('div');		respNote.className='notes';		respNote.innerText=notetext;		// this is the note itself in small text
															var noteEdit=document.createElement('div');		noteEdit.id='editOEnote_notes_'+t;  noteEdit.className='selectoricons';		noteEdit.innerHTML='<span class="oi oi-pencil px-2" title="Edit Note"></span>';
															noteEdit.addEventListener('click',function(e){multiLineEdit(e);e.stopPropagation();},true);
															daChoice.appendChild(respNote);		daChoice.appendChild(noteEdit);			// daChoice.style.paddingRight='40px';
														}
												}
												
												
												if(u=='seconds'){	
													var e=document.getElementById('ObsElem_'+t);
													e.firstChild.firstChild.firstChild.innerHTML=humanTime(d[t][dd],true);	//minimega with br -- updating the time (live) in the obs view is a little trickier -- next obs load will catch it though
												}
												
												console.log('OE updated '+u+' in index '+index+' ('+t+')');
												break;
						}
						
					} // end for
		} // end DD switch
	} } // end bigger for and little for
	// if we have a swap (which just updates PNid pointers on choices --- but there is no corresponding noob in this action, then it's a terminator and still needs updating
	if(gotaNoob == '' && gotaSwap != ''){loadObservation(currentlyLoadedObservation);console.log('RELOADING OB');}		// NOOBS trigger a reload after this function is done
}


// ====================================================================================================


function createNewObject(e,type){			// TODO -- can prob remove e
	console.log('createNewObject says: yay!');		
	// ==== updateDatabase('OBS',{0:{noob:'0'}});							=== adding a new ObSet uses session data (person / path / app), so we dont really send much
	// ==== updateDatabase('OB',{0:{noob:'8'}});							=== add a new OB (subsess) to OBS#8
	// ==== updateDatabase('OE',{0:{noob:'8|156|128|90'}});		=== little tougher -- add new tbSA with sid:8, ssid:156, pnid:128, and 90 seconds
	var data={};  data[0]={'noob':0};		var bits={};
	switch(type){
		case 'OBS': 	data[0]['noob']=e.target.previousSibling.firstChild.value;		// get the select path info
		
							// need to note === viewingPlaygrounds ====== TODO TODO
		
							var rd=updateDatabase('OBS',data);		// have to wait on this -- doDrop can trigger it
							break;
		case 'OB': 	data[0]['noob']=currentlyLoadedObservationSet;
							var rd=updateDatabase('OB',data);
							break;
		case 'OE': 	var bits=e.split('_');
							data[0]['noob']=currentlyLoadedObservationSet+'_'+currentlyLoadedObservation+'_'+bits[0]+'_'+bits[1];
							var rd=updateDatabase('OE',data);
							break;		// PN needs to be passed here if something clicked
	}
	
}


function multiLineEdit(e,other_e){		//	bits we need here -- OP / text / opitem ('notes')
	var op='';	 var item=''; var targetid=0; var initialText=''; var over=0; var down=0; var ww=0; var hh=0; var doheader=false; var headertext=''; var placeholdertext=''; var nbsp=String.fromCharCode(160);
	var xy=getPosition(e.target);				console.log('multiLineEdit says: x is '+xy['x']+' and y is '+xy['y']);
	if(typeof(other_e) != 'undefined'){
		var bits=other_e.split('|_x_|');	op=bits[0]; item=bits[1]; targetid=bits[2]; initialText=bits[3];			//console.log('mLE got '+initialText);			// sometimes the clicked item cant easily carry its information, so we explicitly send it
	}else{
		var derid=fetchedClickedID(e);			var underscore=derid.indexOf('_');		if(underscore>0){var bits=derid.split('_'); op=bits[0]; item=bits[1]; targetid=bits[2];}
		var opitem=fetchOpItem(e); 			console.log('no other-e');
	}
//	console.log('multiLineEdit found '+op+' / '+targetid);
	switch(op){	
		case 'newOEwithnote': ww=600; hh=340; over=60; down=30; doheader=true; headertext='Add a Note';
											break;
		case 'editOEnote':		if(typeof(e.target.parentNode.previousSibling.innerText) != 'undefined'){initialText=e.target.parentNode.previousSibling.innerText;}
											ww=600; hh=340; over=-560; down=30; doheader=true; if(initialText != ''){headertext='Edit Note';}else{headertext='Add a Note';}
											break;
		case 'editOnote':			ww=600; hh=340; over=60; down=30; doheader=true; if(initialText != ''){headertext='Edit Observation Note';}else{headertext='Add an Observation Note';}
											break;
		case 'editOBSnote':		ww=600; hh=340; over=60; down=30; doheader=true; if(initialText != ''){headertext='Edit Observation Set Note';}else{headertext='Add an Observation Set Note';}
											break;
	}
	
	if(initialText != ''){placeholdertext='Edit this note... click the green check to finish';}else{placeholdertext='add a note... click the green check to finish';}
	
//	console.log('multiLineEdit found '+op+' / '+targetid+' with info: '+initialText+' (MARK: if no info, then fine if original blank)');
	setUpOverlayInner(xy['y']+down,xy['x']+over,ww,hh,doheader,headertext);
	overlayBody.innerText='';
	var noteElement=document.createElement('textarea');
		noteElement.type='textarea';		noteElement.id=item+'_'+targetid;		noteElement.className='multiline';		noteElement.placeholder=placeholdertext;		  noteElement.value=initialText;
		noteElement.setAttribute('old',initialText);		noteElement.setAttribute('op',op);		
	overlayBody.appendChild(noteElement);
	var bill=document.getElementById('submitmulti');			if(bill !== null){bill.remove();}
	var newNode=submitMulti.cloneNode(true);    newNode.id='submitmulti';    
	newNode.addEventListener("click", function(e){checkMultiSubmit(e);e.stopPropagation();},false);
	overlayInner.appendChild(newNode);
	
	overlayOuter.style.display='block';
}		// this one is for multi-line edits

function checkMultiSubmit(e){
	var ee=e.target.previousSibling.firstChild;		var funk='';
	if(ee.value != ee.attributes.old.nodeValue){
		var bits=ee.id.split('_');			// get the info from the id we assigned on activation
		var newData={}; newData[bits[1]]={};		// very annoying that JS object keys cant accept variables UNLESS the object was created beforehand --- xx['yy']['zz']=1 is fine, but xx[yy][zz]=1 with yy and zz == 'yy' and 'zz' wont work ---- ah well....
		switch(ee.attributes.op.nodeValue){
			case 'newOEwithnote': funk='OE'; newData=loadExtraDataFromHandoff(newData); newData[bits[1]][bits[0]]=ee.value; break;		// we do this to make sure we process the note AFTER the choice has been processed
			case 'editOEnote': funk='OE'; newData[bits[1]][bits[0]]=ee.value; break;
			case 'editOnote': funk='OB'; newData[bits[1]][bits[0]]=ee.value; break;
			case 'editOBSnote': funk='OBS'; newData[bits[1]][bits[0]]=ee.value; break;
		}
		updateDatabase(funk,newData);
	}else{
		console.log('checkMultiSubmit says: no change');
	}
	overlayOuter.style.display='none';
}

function singleEdit(e){
	var xy=getPosition(e.target);		var op='';	 var item=''; var targetid=0;	var funk='';		console.log('singleLineEdit says: x is '+xy['x']+' and y is '+xy['y']);
	var initialText=''; var over=0; var down=0; var ww=0; var hh=0;		var doheader=false; var headertext='';		var nbsp=String.fromCharCode(160);    var maybespaces='';
	var derid=fetchedClickedID(e);			var underscore=derid.indexOf('_');
		if(underscore>0){
			var bits=derid.split('_');
			if(bits.length==2){op=bits[0]; targetid=bits[1];}
			if(bits.length==3){op=bits[0]; item=bits[1]; targetid=bits[2];}
		}
	var bill=document.getElementById('submitmulti');			if(bill !== null){bill.remove();}
	var opitem=fetchOpItem(e);
	switch(op){	
		case 'obSet':		var ds=e.target.parentNode.parentNode.children[0]; initialText=ds.innerText; 
								ww=600; hh=54; over=-560; down=30; doheader=true; headertext='Edit Observation Set Name'; 	funk='OBS';
								break;
		case 'ob':			var ds=e.target.parentNode.parentNode.children[1]; initialText=ds.innerText; 
								ww=600; hh=54; over=-600; down=30; doheader=true; headertext='Edit Observation Name';  	funk='OB';
								break;
// 		case 'videdit':	targetid=currentlyLoadedObservationSet; var ds=e.target; initialText=ds.innerText.substr(e.target.innerText.lastIndexOf(nbsp+' ')+2); 		// the +2 is because the thing we're searching for has 2 chars and we dont want those
// 								ww=400; hh=54; over=60; down=30; doheader=true; headertext='Edit Video URL';  	funk='OBS';
// 								break;
		case 'ptedit':		targetid=currentlyLoadedObservationSet; var ds=e.target; initialText=ds.innerText.substr(e.target.innerText.lastIndexOf(nbsp+' ')+2); 
								ww=300; hh=54; over=0; down=30; doheader=true; headertext='Edit Place-Time';  	funk='OBS';  maybespaces='&nbsp; &nbsp; ';
								break;
		case 'studedit':	targetid=currentlyLoadedObservationSet; var ds=e.target; initialText=ds.innerText.substr(e.target.innerText.lastIndexOf(nbsp+' ')+2); 
								ww=300; hh=54; over=60; down=30; doheader=true; headertext='Edit Student ID';  	funk='OBS';  maybespaces='&nbsp; &nbsp; ';
								break;
	}
	console.log('singleEdit found '+op+' / '+targetid+' with info: '+initialText);
	setUpOverlayInner(xy['y']+down,xy['x']+over,ww,hh,doheader,headertext);
	overlayBody.innerText='Hit enter to submit...';			//overlaySubmit.style.display='none';	// no need for submit button here
	var noteElement=document.createElement('input');
		noteElement.type='text';		noteElement.id='noteInput';		noteElement.className='singleline';		noteElement.style.width=(ww-20)+'px';  noteElement.value=initialText;
		noteElement.setAttribute('op',op);		noteElement.setAttribute('id',targetid);		noteElement.setAttribute('old',initialText);
	//	noteElement.addEventListener("click", function(event) {event.stopPropagation();},false);		// need this so the initial "select the field" click doesn't get through to the overlay and close it
		noteElement.addEventListener("keyup", function(event) {
			if (event.keyCode === 13) {		// I could probably just set these directly since opitem seems to work...
				if(event.target.value != event.target.attributes.old.nodeValue){
					var newData={}; newData[event.target.id]={};		// very annoying that JS object keys cant accept variables UNLESS the object was created beforehand --- xx['yy']['zz']=1 is fine, but xx[yy][zz]=1 with yy and zz == 'yy' and 'zz' wont work ---- ah well....
					newData[event.target.id][opitem]=event.target.value; 		console.log('looking at: '+opitem+' and \''+event.target.value+'\' from '+ds.id);
					if(maybespaces==''){
						ds.innerText=event.target.value;	// actually update the text of the edited item -- refreshes will also do this (note loadObSet refreshes from DB data)
					}else{
						ds.innerHTML=maybespaces+event.target.value;	// need HTML for the &nbsp;s
					}
					updateDatabase(funk,newData);
				}else{
					console.log('singleEdit says: no change');
				}
			overlayOuter.style.display='none'; 
		}});
	overlayBody.insertBefore(noteElement,overlayBody.childNodes[0]);
	overlayOuter.style.display='block';
}

function doDrop(e){
	var xy=getPosition(e.target);		var op='';	 var op2='';	 var item=''; var targetid=0;	var funk='';	var initialID=0;		console.log('doDrop says: x is '+xy['x']+' and y is '+xy['y']);
	var initialText=''; var over=0; var down=0; var ww=0; var hh=0;		var doheader=false; var headertext='';		var nbsp=String.fromCharCode(160);
	var derid=fetchedClickedID(e);			var underscore=derid.indexOf('_');
		if(underscore>0){
			var bits=derid.split('_');
			if(bits.length==2){op=bits[0]; targetid=bits[1];}
			if(bits.length==3){op=bits[0]; item=bits[1]; targetid=bits[2];}
		}
	var opitem=fetchOpItem(e);
	switch(op){	
		case 'videdit':	targetid=currentlyLoadedObservationSet; 		op2='videoid';
								initialID=e.target.attributes.videoid.nodeValue;		// zero for no vid
								var ds=e.target;
								ww=400; hh=54; over=60; down=30; doheader=true; headertext='Edit Video URL';  	funk='OBS';
								break;
		case 'obSet':	targetid=currentlyLoadedObservationSet; 		op2='noob';
								initialID='noob';	// nothing needed here
								ww=400; hh=54; over=60; down=30; doheader=true; headertext='Select a Path for this Set';  	funk='OBS';
								break;
	}
	console.log('doDrop found '+op+' / '+targetid+' with info: '+initialID);
	setUpOverlayInner(xy['y']+down,xy['x']+over,ww,hh,doheader,headertext);
	overlayBody.innerText='';		//overlaySubmit.style.display='none';	// default no need for submit button here
	var selectElement=document.createElement('select');
		selectElement.id='noteInput';		selectElement.className='dropdowner';		selectElement.style.width=(ww-20)+'px';
		selectElement.setAttribute('op',op2);		selectElement.setAttribute('id',targetid);		//selectElement.setAttribute('old',initialID);

		var bill=document.getElementById('submitmulti');			if(bill !== null){bill.remove();}
	//	if(typeof(currentSubmitHandler)=='function'){overlaySubmit.removeEventListener('click',currentSubmitHandler);}		//console.log('clearing overSubClick of '+overSubClick);}
		
		switch(op){	
			case 'videdit':	for (var v in appVideoList){		//console.log('videdit drop found option '+v+' with '+appVideoList[v]);
										var selectOption=document.createElement('option');		selectOption.value=v;		selectOption.innerText=appVideoList[v];		
										if( v == initialID){selectOption.selected=true;}
										selectElement.appendChild(selectOption);
									}
							//		currentSubmitHandler=submitHandler_videoEdit; overlaySubmit.addEventListener("click", submitHandler_videoEdit);		//console.log('setting new overSubClick of '+overSubClick);
									//
									var newNode=submitMulti.cloneNode(true);    newNode.id='submitmulti';    
									newNode.addEventListener("click", function(e){
												var derselect=e.target.previousSibling.firstChild;
												var targetOBSid=derselect.id;		var opitem=derselect.attributes['op'].value;
												var newData={}; newData[targetOBSid]={}		// very annoying that JS object keys cant accept variables UNLESS the object was created beforehand --- xx['yy']['zz']=1 is fine, but xx[yy][zz]=1 with yy and zz == 'yy' and 'zz' wont work ---- ah well....
												newData[targetOBSid][opitem]=derselect.options[derselect.selectedIndex].value; 		console.log('looking at: '+opitem+' and \''+derselect.options[derselect.selectedIndex].value+'\'');
												var rd=updateDatabase(funk,newData);
												overlayOuter.style.display='none'; 
	
												ds.attributes.videoid.nodeValue=derselect.options[derselect.selectedIndex].value;	// actually update the text of the edited item -- refreshes will also do this (note loadObSet refreshes from DB data)
												ds.innerHTML='&nbsp;&nbsp;'+derselect.options[derselect.selectedIndex].text;
	
												var bill=document.getElementById('launch_video_button');		//console.log('AAAAA '+bill);
												if(bill !== null){		// if there's already a videoUrl and we changed it, then there will be a button -- else we need to make the button
													var bbb=getTextWidth("Inconsolata",16,'Open '+derselect.options[derselect.selectedIndex].text);	
													bill.firstChild.innerText='Open '+derselect.options[derselect.selectedIndex].text;
													bill.style.width=(bbb+100)+'px';		// widen the button to fit the text
										//			bill.lastChild.style.left=(bbb+140)+'px';	// shift the 1x / 1.5x / 2x buttons as well
						//							newNode.href='/newvideo_popout.php?vid='+d['videoID'];
												}else{
													var newNode=platonicVideoButton.cloneNode(true);		newNode.id='launch_video_button';		newNode.style.display='block';		// none by css -- inline by default
													var bbb=getTextWidth("Inconsolata",16,'Open '+derselect.options[derselect.selectedIndex].text);	
													newNode.firstChild.innerText='Open '+derselect.options[derselect.selectedIndex].text;
													maybe_videolink.appendChild(newNode);		maybe_videolink.style.display='block';
														newNode.style.width=(bbb+100)+'px';		// widen the button to fit the text
										//				newNode.lastChild.style.left=(bbb+140)+'px';	// shift the 1x / 1.5x / 2x buttons as well
							//						newNode.href='/newvideo_popout.php?vid='+d['videoID'];
												}
												e.stopPropagation();
									},false);
									overlayInner.appendChild(newNode);
									break;
			case 'obSet':	for (var p in appPathList){		//console.log('dopath found option '+p+' with '+appPathList[p]['name']);
										var selectOption=document.createElement('option');		selectOption.value=p;		selectOption.innerText=appPathList[p]['name'];		selectElement.appendChild(selectOption);
									}
							//		currentSubmitHandler=submitHandler_obSetNoob; overlaySubmit.addEventListener("click", submitHandler_obSetNoob);		//console.log('setting new overSubClick of '+overSubClick);
									var newNode=submitMulti.cloneNode(true);    newNode.id='submitmulti';    
									newNode.addEventListener("click", function(e){
												var derselect=e.target.previousSibling.firstChild;
												//console.log('looking at: '+opitem+' and index of \''+derselect.children[derselect.selectedIndex].value+'\'');
												overlayOuter.style.display='none'; 
												createNewObject(event,'OBS');
												e.stopPropagation();
									},false);
									overlayInner.appendChild(newNode);
									break;
	}
		
	//overlaySubmit.style.display='block';		// need to show the submit button
	overlayBody.appendChild(selectElement);
	overlayOuter.style.display='block';	
}

function nukeObSet(e){
	derid=fetchedClickedID(e.target);
	var underscore=derid.indexOf('_');		if(underscore>0){var bits=derid.split('_'); nukeid=bits[1];}
	console.log('nukeObSet says: yay!  lets nuke '+nukeid);
	var data={};  data[nukeid]={'nuke':nukeid};
	var rd=updateDatabase('OBS',data);
}

function visualize(e){
	console.log('visualize says: yay!');
}

function tempyObs(){
	var tempy={SAid:8888,PNid:0,megaid:1,choiceid:0,seconds:0};
	//var tempy={SAid:8888,PNid:87,megaid:46,choiceid:92,seconds:999};		// appears to work
	observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'].push(tempy);
	loadObservation(currentlyLoadedObservation,8888);
}

function loadExtraDataFromHandoff(h){
	for (var i in derDataHandoff){
		if(typeof(h[i])=='undefined'){h[i]={};}		// somedays, I really hate JS
		for (var f in derDataHandoff[i]){
			h[i][f]=derDataHandoff[i][f];		//console.log('LEXDFH found '+i+' and '+f+' with '+derDataHandoff[i][f]);
		}
	}
	return h;
}

function setUpOverlayInner(t,l,w,h,head,headtext){
	// need to make sure the box isn't placed PARTLY offscreen as it's a fixed item -- no scroll -- if window is 1000px tall and we put this at 900px, then most of it stays offscreen =========
	if( ! head ){overlayHead.style.display='none';}else{overlayHead.innerText=headtext; h+=32; overlayHead.style.display='block';}
	
	var ww = window.innerWidth;	var wh = window.innerHeight;
	
	if( t+h > wh ){t=(wh-h-20);}
	if( l+w > ww ){l=(ww-w-20);}			// if the box would extend outside of the window area, pull it inside
	
	overlayInner.style.top=t+'px';
	overlayInner.style.left=l+'px';
	overlayInner.style.width=w+'px';
	overlayInner.style.height=h+'px';
}
function leftOrRight(x){
	if(x==document){return 'document';}
	if(typeof(x.id) != 'undefined'){
//		console.log('checking LoR '+x.id);
		if(x.id == 'leftInnerContainer'){return 'left';}
		if(x.id == 'rightInnerContainer'){return 'right';}
		if(x.id == 'leftandright'){return 'neither';}
	}
	return leftOrRight(x.parentNode);
}
function fetchedClickedID(x){
	if(typeof(x.target)!='undefined'){x=x.target; }//console.log('fetcher hunted down event target');
	if(x==document){return 'document';}
	if(x.id != '' && x.id.indexOf('_')>0){return x.id;}else{return fetchedClickedID(x.parentNode);}		console.log('not this one...');		//console.log('fetcher found '+x.id);
}
function fetchOpItem(x){
	if(typeof(x.target)!='undefined'){x=x.target; }		//console.log('fetcherOp hunted down event target');
	if(x==document){return false;}
	if(x.hasAttribute('opitem')){return x.attributes['opitem'].nodeValue;}else{return fetchOpItem(x.parentNode);}		console.log('not this one...');		//console.log('fetcherOp found '+x.attributes['opitem'].nodeValue);
}
function fetchIndexFromSAid(said){
	for(var e=0;e<observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'].length;e++){
		if( observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][e]['SAid']==said ){return e;}
	}
	return -1;
}
function getFirstSubLabel(d){
	for(var t=0;t<d.length;t++){if(typeof(d[t]['sublabel']) != 'undefined'){return d[t]['sublabel'];}}
}
function getAnySubInfo(d){
	var output=new Array;
	for(var t=0;t<d.length;t++){
		if(typeof(d[t]['extra']) != 'undefined'){ output.push(d[t]['extra']); }
		if(typeof(d[t]['notes']) != 'undefined'){ output.push(d[t]['notes']); }
	}
	if(output.length>0){return output.join(' / ');}else{return false;}
}
function fadeInLS() {
    var op = 0.01;		var rate = 0.01;		// rate give an ease out feel
    var timer = setInterval(function () {
        if (op >= 1){clearInterval(timer);}
        leftSide.style.opacity = op;
        leftSide.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op += op * (0.05+rate);
        rate+=0.01;
    }, 10);
}

function adjustTime(e,inc){
	if(currentlyAdjustedTimeID==0){
		var derid=fetchedClickedID(e);			var underscore=derid.indexOf('_');		var targetid=0;		if(underscore>0){var bits=derid.split('_'); targetid=bits[1]; currentlyAdjustedTimeID=targetid;}
	}else{
		targetid=currentlyAdjustedTimeID;
	}
	if(targetid>0){
		var timetext=e.target.parentNode.parentNode.children[1];
		var oldtime=parseInt(timetext.attributes.storedtime.nodeValue);
		var newtime=oldtime+inc;
		if(newtime<0){newtime=0;}
		timetext.attributes.storedtime.nodeValue=newtime;
		timetext.innerText=humanTime(timetext.attributes.storedtime.nodeValue);
		if(timeAdjustedTimer>0){clearTimeout(timeAdjustedTimer);timeAdjustedTimer=0;}		// clear out any existing timer
		timeAdjustedTimer = setTimeout(function(){ reallyAdjustTime(targetid,newtime); }, 3000);
	}
}
function reallyAdjustTime(t,tt){
	console.log('really adjusting time now for: '+t+' and '+tt);
	var newData={}; newData[t]={}; newData[t]['seconds']=tt; updateDatabase('OE',newData); currentlyAdjustedTimeID=0;
}
function humanTime(timeInSecs,addbreak) {
	var derbreak='';	if(addbreak){derbreak='<br />';}
	if(timeInSecs<0){return '0:'+derbreak+'00';}
    var minutes = Math.floor(timeInSecs/60);				// note: if time is negative, then minutes gets weird  //  -1 second gives you -1 minutes and the next calc (with a minus) is -1 minus -1*60 == 59... so -1:59
    if (minutes < 10) minutes = String(minutes).padStart(2, '0');
    var seconds = Math.round(timeInSecs - minutes * 60);
    if (seconds < 10) seconds = String(seconds).padStart(2, '0');
    return (minutes + ':' + derbreak + seconds);
}
function totalSec(minutes, seconds) {
    let totalTimeSec = minutes*60 + seconds;
    return totalTimeSec;
}

function sendPing(){
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {		var data=getHTML(xmlHttp);		if(data){console.log(data);}}
	sendStr='ping=1';			var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			//console.log(url);
	xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
}
function getPosition(element) {
	var xPosition = 0;    var yPosition = 0;
	while(element) {
		xPosition += (element.offsetLeft - element.scrollLeft + element.clientLeft);
		yPosition += (element.offsetTop - element.scrollTop + element.clientTop);
		element = element.offsetParent;
	}
	return { x: xPosition, y: yPosition };
}
function getTextWidth(f1,f2,tx) {			// strangely, this works -- thought SPANs always returned zero
  
            text = document.createElement("span");
            document.body.appendChild(text);
  
            text.style.font = f1;
            text.style.fontSize = f2 + "px";
            text.style.height = 'auto';
            text.style.width = 'auto';
            text.style.position = 'absolute';
            text.style.whiteSpace = 'no-wrap';
            text.innerHTML = tx;
  
            width = Math.ceil(text.clientWidth);
       //     formattedWidth = width + "px";
            document.body.removeChild(text);
            return width;
        }
function doKeyDown(evt){
	if(evt.keyCode==16){keyboardModifier='shift';}
	if(evt.keyCode==18){keyboardModifier='alt';}		// cntrl is used by the browser -- leave it be
	if(evt.keyCode==27){keyboardModifier='esc';}
}
function doKeyUp(evt){
	keyboardModifier=null;
}