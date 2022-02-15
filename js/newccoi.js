
//var derServer='https://ccoi-dev.education.ufl.edu/';		// this is only loaded in newobserve.php which now sets this itself dev/prod
var allObservationSets=new Object();
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
var fetchedCodeData=new Object();
var codeData=new Object();
var currentlyLoadedPath=0;
var currentlyLoadedPathStartNode=0;
var viewingPlaygrounds=false;
var appVideoList=new Object();
var appPathList=new Object();
var timeAdjustedTimer=0;
var currentlyAdjustedTimeID=0;
var derDataHandoff= new Object();
var keyboardModifier=null;
var swapHikeID='';

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
	if(currentOpenContainer != '' && currentOpenContainer != num){hideLastContainer();}		//console.log('hiding last container: '+currentOpenContainer);
	currentOpenContainer=num;
}
function hideLastContainer(){var victim=document.getElementById('container_'+currentOpenContainer);	victim.style.display='none';}
function showMega(target){
	if(typeof(target)=='number'){
		num=target;														console.log('showMega looking for '+num+' from explicit set');
	}else{
		if(typeof(target)=='object'){
			var itemid=fetchedClickedID(target);
			var num=itemid.substring(7);						console.log('showMega looking for '+num+' from '+itemid+' to flip');
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
function switchPlayState(){
	viewingPlaygrounds=!viewingPlaygrounds; // switch true/false
	if(viewingPlaygrounds){leftAndRight.className=origLeftAndRightClass+' playground';}else{leftAndRight.className=origLeftAndRightClass;}
	showObservationSets();
}


function fetchUserObSets(u){
	console.log("fetchUserObSets()");
	if(	! ((Object.keys(appVideoList).length>0) && (Object.keys(appPathList).length>0)) ){console.log('not ready to load... try again in half a sec'); setTimeout(function(){ fetchUserObSets(userid);},500);return;}		// if we're not ready -- wait half a second and try again.
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
			allObservationSets=JSON.parse(data);
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
				observationElements=JSON.parse(bits[1]);			//console.log('====fetchDaPath===== '+bits[1]);
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
function fetchDaCodes(p){
	if(typeof(fetchedCodeData[p]) != 'undefined'){		// did we already fetch this?
		codeData=JSON.parse(fetchedCodeData[p]);
	}else{
		var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
		xmlHttp.onreadystatechange = function() {
			var data=getHTML(xmlHttp);
			if(data){ 
				codeData=JSON.parse(data);
			}
		}
		sendStr='pid3='+p;
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
					if(typeof(observationElements[m][g][e]['extra'])!='undefined'){observationElements2[observationElements[m][g][e]['pnid']]['extra']=observationElements[m][g][e]['extra'];}
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

function showObservationSets(){
	console.log("showObservationSets()");
	leftSide.style.opacity=0;
	leftInnerContainer.innerHTML='';			// wipe out all existing child nodes
	currentlyLoadedObservation=0;
	currentlyLoadedObservationSet=0;
	header_usersets.innerText='';
	header_obsetmeta.innerHTML='';
	header_obsetmeta.style.display='none';
	maybe_videolink.innerHTML='';		maybe_videolink.style.display='none';
	header_highlevelnotes.style.display='none';
	var maybeplayground='switch to the playground';		var settext=' Observation Sets';
		if(viewingPlaygrounds){maybeplayground='switch to research'; settext=' Playground Sets';}
	
	if(jsUserVars['first'].substring(-1)=='s'){apposS="'";}else{apposS="'s";}
	header_loadedobsset.innerText=jsUserVars['first']+apposS+settext;
	header_loadedobsset.className='orange';
		header_loadedobs.className='';
	header_loadedobs.innerText='';
	header_loadedobsxofx.innerHTML='Select an observation set to view or edit the set or <span id="playstate" onClick="switchPlayState();">'+maybeplayground+'</span>';
	if( !viewingPlaygrounds ){observationSets=allObservationSets['playground'];}else{observationSets=allObservationSets['research'];}
	for (var e in observationSets){
		if( ( !viewingPlaygrounds && observationSets[e]['isPlayground']==0) || (viewingPlaygrounds && observationSets[e]['isPlayground']==1)){
			var newNode=platonicObsSet.cloneNode(true);
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
	var newNode=platonicObsSet.cloneNode(true);
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
	
	console.log("id2load = "+id2load);
	console.log("observationSets = ");
	console.log(observationSets);
	var d=observationSets[id2load];
	console.log("d=observationSets[id2load] =");
	console.log(d);
	console.log("d['path']"+d['path']);
	fetchDaPath(d['path']);			// =========================================== this is HERE because a user can have sets assigned to different paths == dont like it, but unless we restrict based on app or something, we need to fetch -- but we CAN cache it to avoid further fetches
	fetchDaCodes(d['path']);
	currentlyLoadedPath=d['path'];
	
	if(jsUserVars['first'].substring(-1)=='s'){apposS="'";}else{apposS="'s";}
	header_usersets.innerText=jsUserVars['first']+apposS+' Observation Sets';
	// ========== ObservationSets (sessions) can have notes (and some do), though I dont know where to enter them yet ==================
	var notespan=document.createElement('span');
	
	if(typeof(d['notes'])!='undefined' && d['notes']!=''){			console.log('OBS found '+d['notes']);
		var notetext=d['notes'].replace(/\\n|\\r\\n|\\r/g,"\n");		 header_highlevelnotes.className='';		// restore normal font/spacing
		if(notetext.length > 800){var tempy=d['notes'].substring(0,800); var lastSpace=tempy.lastIndexOf(' '); notetext=d['notes'].substring(0,lastSpace)+'...'; header_highlevelnotes.className='tiny';}		// too damn many letters
//		notetext=notetext.replace(/\\n|\\r\\n|\\r/g,"\n");
		notespan.innerText=notetext;
			var othere='editOBSnote|_x_|notes|_x_|'+id2load+'|_x_|'+d['notes'];
			notespan.addEventListener('click',function(e){multiLineEdit(e,othere);e.stopPropagation();},true);
	}else{
		notespan.innerText='No Notes'; 
			var othere='editOBSnote|_x_|notes|_x_|'+id2load+'|_x_|';
			notespan.addEventListener('click',function(e){multiLineEdit(e,othere);e.stopPropagation();},true);
	}
header_highlevelnotes.innerText='';		header_highlevelnotes.appendChild(notespan);	  header_highlevelnotes.style.display='block';		header_highlevelnotes.setAttribute('title','Edit this Observation Set Note');
header_loadedobsxofx.innerText='Select an observation to view or edit its responses';
	
	if(d['name']==null){ObSetTitle='Observation Set #'+id2load;}else{ObSetTitle=d['name'];}
		var obtextbit=document.createElement('a');		obtextbit.href='#';		obtextbit.innerText=ObSetTitle;		obtextbit.id='dertext';
//	header_loadedobsset.innerText=ObSetTitle;
	header_loadedobsset.innerText='';				header_loadedobsset.appendChild(obtextbit);
		var editlink=document.createElement('button');		editlink.id='obSet2_'+id2load;			editlink.className='oi oi-tag oi2';		editlink.setAttribute('opitem','name');		editlink.setAttribute('title','Rename Observation Set');		editlink.addEventListener('click',function(e){singleEdit(e);e.stopPropagation();},false);
		header_loadedobsset.appendChild(editlink);
	header_loadedobsset.className='orange';
		header_loadedobs.className='';
	var hiddenHeader=document.createElement('h3');			hiddenHeader.innerHTML='<span class="hiddenHeaderSpan">Observations for:<br /></span>'+ObSetTitle;			leftInnerContainer.appendChild(hiddenHeader);
	header_loadedobs.innerText='';	header_obsetmeta.innerText='';	var spacer=' &nbsp; &nbsp; ';
		var ps=document.createElement('button');		ps.innerText='Path: '+appPathList[currentlyLoadedPath]['name'];
		
		var vs=document.createElement('button');		vs.id='videdit_'+id2load;		vs.className='editspan';		vs.setAttribute('opitem','videoid');		vs.setAttribute('title','Change the Video');					if(d['videoURL']!='' && d['videoURL']!=null){vs.innerHTML=spacer+d['videoURL'];vs.setAttribute('videoid',d['videoID']);}else{vs.innerHTML=spacer+'No Video';vs.setAttribute('videoid',0);}
		var pts=document.createElement('button');		pts.id='ptedit_'+id2load;		pts.className='editspan';	pts.setAttribute('opitem','placetime');	pts.setAttribute('title','Change the Place-Time');		if(d['placetime']!='' && d['placetime']!=null){pts.innerHTML=spacer+d['placetime'];}else{pts.innerHTML=spacer+'No Place-Time';}
		var ss=document.createElement('button');		ss.id='studedit_'+id2load;		ss.className='editspan';		ss.setAttribute('opitem','studentid');		ss.setAttribute('title','Change the StudentID');			if(d['studentid']!='' && d['studentid']!=null){ss.innerHTML=spacer+d['studentid'];}else{ss.innerHTML=spacer+'No StudentID';}
		
		vs.addEventListener('click',function(e){doDrop(e);e.stopPropagation();},true);
		pts.addEventListener('click',function(e){singleEdit(e);e.stopPropagation();},true);
		ss.addEventListener('click',function(e){singleEdit(e);e.stopPropagation();},true);
		
	header_obsetmeta.appendChild(ps); header_obsetmeta.appendChild(vs); header_obsetmeta.appendChild(pts); header_obsetmeta.appendChild(ss);		header_obsetmeta.style.display='block';
	
	if(d['videoURL'] != '' && d['videoURL'] != null){
		var bbb=getTextWidth("Inconsolata",16,'Open '+d['videoURL']);				console.log('videourl = '+d['videoURL']+' == '+bbb);
		var newNode=platonicVideoButton.cloneNode(true);		newNode.id='launch_video_button';	newNode.style.width=(bbb+100)+'px';	newNode.firstChild.innerText='Open '+d['videoURL'];		newNode.style.display='block';		// none by css -- inline by default
		newNode.href=derDevProd+'/newvideo_popout.php?vid='+d['videoID'];
		maybe_videolink.appendChild(newNode);		maybe_videolink.style.display='block';
	}

	var unsortedObs={};  var dertime=0;  var dertime_inc={};
	for (var e in d['observations']){
		dertime=parseInt(d['observations'][e]['ObResp'][0]['seconds'])*10;
		if(typeof(dertime_inc[dertime])=='undefined'){dertime_inc[dertime]=0;}
		if(typeof(unsortedObs[dertime])!='undefined'){dertime_inc[dertime]+=1;dertime+=dertime_inc[dertime];} console.log(d['observations'][e]['name']+' ('+e+') gets unsorted slot '+dertime);
		unsortedObs[dertime]=e;
	}
	sortedObs = Object.keys(unsortedObs).sort().reduce((obj, key) => { obj[key] = unsortedObs[key]; return obj;}, {});		console.log(JSON.stringify(sortedObs));

//	for (var e in d['observations']){			
	for (var sorted_not_e in sortedObs){
		var e=sortedObs[sorted_not_e];
		console.log('loading obs '+e);
		var newNode=platonicObs.cloneNode(true);
			var sublabel=d['observations'][e]['name'];
				if( sublabel == null){sublabel='Observation #'+e;}
			newNode.childNodes[1].innerText=sublabel;
			var extras=getAnySubInfo(d['observations'][e]['ObResp']);
			if(extras){newNode.childNodes[3].innerText=extras;newNode.childNodes[2].style.display='block';}
		newNode.children[0].children[0].innerHTML=humanTime(d['observations'][e]['ObResp'][0]['seconds'],false);		// need HTML for <br />
			newNode.id='ob_'+d['observations'][e]['ssid'];
			newNode.childNodes[1].addEventListener('click',function(e){loadObservation(e);e.stopPropagation();},false);		// we're getting rid of the right side, so no need -- rightsideHandoff();
			newNode.childNodes[2].children[0].addEventListener('click',function(e){singleEdit(e);e.stopPropagation();},false);		// rename
			newNode.childNodes[2].children[0].setAttribute('opitem','name');
		leftInnerContainer.appendChild(newNode);
	}

	// CREATE NEW at bottom =========
	var newNode=platonicObsSet.cloneNode(true);
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
	var lastseconds=0;  var lastMiniMega;
	
	var dd=observationSets[currentlyLoadedObservationSet]['observations'][id2load];
	var d=dd['ObResp'];		//console.log('====='); console.log(d);	// tech doc has ['ObResp'], but maybe we can remove that, if no non object data like 'title' or something
	var sublabel=dd['name'];			if(sublabel == '' || sublabel==null){sublabel='Observation #'+id2load;}
//	header_loadedobs.innerHTML=sublabel;
	var obtextbit=document.createElement('a');		obtextbit.href='#';				obtextbit.innerText=sublabel;		obtextbit.id='dertext';
	header_loadedobs.innerText='';				header_loadedobs.appendChild(obtextbit);
		var editlink=document.createElement('button');		editlink.id='ob2_'+id2load;			editlink.className='oi oi-tag oi2';		editlink.setAttribute('opitem','name');		editlink.setAttribute('title','Rename Observation');		editlink.addEventListener('click',function(e){singleEdit(e);e.stopPropagation();},false);
		header_loadedobs.appendChild(editlink);
	header_loadedobsxofx.innerText='Select an observation element to view or edit its responses';
	header_loadedobsset.className='';
	header_loadedobs.className='orange';
	// ========== Observations (subsessions) can have notes now ==================
	var notespan=document.createElement('span');
	if(dd['notes'] != null){		console.log('found '+dd['notes']);
	//	notetext=notetext.replace(/\\n|\\r\\n|\\r/g,"\n");
		var notetext=dd['notes'].replace(/\\n|\\r\\n|\\r/g,"\n");		 header_highlevelnotes.className='';		// restore normal font/spacing
		if(dd['notes'].length > 800){var tempy=dd['notes'].substring(0,800); var lastSpace=tempy.lastIndexOf(' '); notetext=dd['notes'].substring(0,lastSpace)+'...'; header_highlevelnotes.className='tiny';}		// too damn many letters
		notespan.innerText=notetext;
			var othere='editOnote|_x_|notes|_x_|'+id2load+'|_x_|'+d['notes'];
			notespan.addEventListener('click',function(e){multiLineEdit(e,othere);e.stopPropagation();},true);
	}else{
		notespan.innerText='No Notes';
			var othere='editOnote|_x_|notes|_x_|'+id2load+'|_x_|';
			notespan.addEventListener('click',function(e){multiLineEdit(e,othere);e.stopPropagation();},true);
	}
	header_highlevelnotes.innerText='';		header_highlevelnotes.appendChild(notespan);	 header_highlevelnotes.style.display='block';		header_highlevelnotes.setAttribute('title','Edit this Observation Note');

	header_obsetmeta.innerText=''; 		header_obsetmeta.style.display='none';			// ObSets only
	maybe_videolink.innerHTML='';		maybe_videolink.style.display='none';
	var noteshandoff={};
	
		leftInnerContainer.innerHTML='';						//	console.log('loading Ob '+id2load);
		for(var e=0;e<d.length;e++){								// d (dd['ObResp']) is an ARRAY not an OBJECT
			var megaid=d[e]['megaid'];
			var said=d[e]['SAid'];
			var newMega=platonicMega.cloneNode(true);
				newMega.id='ObsElem_'+said;							//	console.log('spawning mega node for sa'+said+' / m'+megaid);
				newMega.attributes.mega.nodeValue=d[e]['megaid'];		// used on click above to see if above click target is same as existing target -- see line 338 (or so) below
			var element=observationElements[megaid];			//console.log('element='+observationElements[1]);
			var miniMega=newMega.firstChild;
				miniMega.id='miniOE_'+said;			// look 60 lines or so down for population commands (need the answer for that)
				var answertext='( NO OPTION SELECTED )';		var answeredNode;	var answeredNodeSAid;  var notetextarray=new Array();  var noteData=''; var extraData='';
				miniMega.addEventListener('click',function(e){showMega(e);e.stopPropagation();},true);
				if( typeof(d[e]['extra']) != 'undefined'){notetextarray.push(observationElements2[d[e]['PNid']]['extra']+': '+d[e]['extra']); extraData=d[e]['extra']; console.log('this item has an extra ==========');}
				if( typeof(d[e]['notes']) != 'undefined'){notetextarray.push(d[e]['notes']); noteData=d[e]['notes']; console.log('this item has an note ========== ');}
			var maxiMega=newMega.lastChild;
				maxiMega.id='maxiOE_'+said;
				maxiMega.firstChild.innerText=element[0][0]['title'];//+megaid;
				maxiMega.children[1].addEventListener('click',function(e){hideLastMega();e.stopPropagation();},true);		// red-x to close without opening another mega
				maxiMega.lastChild.children[1].innerText=humanTime(d[e]['seconds']);
				maxiMega.lastChild.children[1].attributes.storedtime.nodeValue=d[e]['seconds'];
				
				var incrementer=maxiMega.lastChild.children[2];
					incrementer.children[0].addEventListener('click',function(e){adjustTime(e,-5);e.stopPropagation();},true);
					incrementer.children[1].addEventListener('click',function(e){adjustTime(e,-1);e.stopPropagation();},true);
					incrementer.children[2].addEventListener('click',function(e){adjustTime(e,1);e.stopPropagation();},true);
					incrementer.children[3].addEventListener('click',function(e){adjustTime(e,5);e.stopPropagation();},true);
					incrementer.children[4].addEventListener('click',function(e){adjustTime(e,30);e.stopPropagation();},true);
					incrementer.children[5].addEventListener('click',function(e){adjustTime(e,300);e.stopPropagation();},true);
			
			var asidebit='';						var answerhascode=false;
			
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
							newNode3.id='choice_'+said+'_'+element[t][z]['choiceid'];
							newNode3.attributes.pnid.nodeValue=element[t][z]['pnid'];
							newNode3.attributes.choicetarget.nodeValue=element[t][z]['target'];			// we compare this to the PNid attribute in the existing next node to see if changes are needed downstream...
							if(element[t][z]['aside'] != null){asidebit='<p>( '+element[t][z]['aside']+' )</p>';}else{asidebit='';}
							if(element[t][z]['code'] != null){
								newNode3.innerHTML=element[t][z]['code']+' : '+element[t][z]['title']+asidebit;
								newNode3.title=element[t][z]['code']+' : '+element[t][z]['codedesc'];
							}else{
								newNode3.innerHTML=element[t][z]['title']+asidebit;
							}
							if(d[e]['choiceid'] == element[t][z]['choiceid']){
								newNode3.style.backgroundColor='#e8a50c'; newNode2.style.display='block';
								if(element[t][z]['code'] != null){
									answertext=element[t][z]['code']+' : '+element[t][z]['title'];
									answerhascode=true;																	// ==========================================================================================
								}else{
									answertext=element[t][z]['title'];
								}
								answeredNode=newNode3; answeredNodeSAid=d[e]['SAid'];
								newNode.style.backgroundColor='#e8a50c';	currentOpenContainers[said]=e+'_'+t;
				//				newNode3.addEventListener('click',function(e){makeChoice(e);e.stopPropagation();},false);		// can't choose something you already chose
								newNode3.addEventListener('click',function(e){var thismega=fetchItemWithAttribute(e.target,'mega'); var nextmegaid=thismega.nextSibling.id.substr(8); showMega(nextmegaid);},false);
							}else{
								newNode3.addEventListener('click',function(e){makeChoice(e);e.stopPropagation();},false);
							}
							if(element[t][z]['code'] != null){
								var codeDetailsContainer=document.createElement('div');		codeDetailsContainer.id='codeDetails_'+element[t][z]['code'];  codeDetailsContainer.className='selectoricons2';		//codeDetails.innerHTML='<button class="oi oi-book" title="View coding details" />';
								var codeDetails=document.createElement('button');				codeDetails.className='oi oi-book';		codeDetails.attributes.title='View coding details';
									codeDetails.addEventListener('click',function(e){showCodeDetails(e);e.stopPropagation();},true);
								codeDetailsContainer.appendChild(codeDetails);
								newNode3.style.paddingRight='40px';
								newNode3.appendChild(codeDetailsContainer);
							}
							newNode2.appendChild(newNode3);
						}
						maxiMega.appendChild(newNode2);
					}
				}
				
			}else{
				for(var z=1;z<element[0].length;z++){								//console.log('spawning un-contained node '+z+' of '+(element[0].length-1)+' for '+said);
					var newNode=platonicMegaItem.cloneNode(true);
					if(element[0][z]['aside'] != null){asidebit='<p>( '+element[0][z]['aside']+' )</p>';}else{asidebit='';}
					if(element[0][z]['code'] != null){
						newNode.innerHTML=element[0][z]['code']+' : '+element[0][z]['title']+asidebit;
						newNode.title=element[0][z]['code']+' : '+element[0][z]['codedesc'];
					}else{
						newNode.innerText=element[0][z]['title']+asidebit;
					}
					newNode.id='choice_'+said+'_'+element[0][z]['choiceid'];
					newNode.attributes.pnid.nodeValue=element[0][z]['pnid'];
					newNode.attributes.choicetarget.nodeValue=element[0][z]['target'];
					
				//	console.log(d[e]['choiceid']+' == '+element[0][z]['choiceid' ]);
					
					if( d[e]['choiceid'] == element[0][z]['choiceid' ]){
						newNode.style.backgroundColor='#e8a50c';
						if(element[0][z]['code'] != null){
							answertext=element[0][z]['code']+' : '+element[0][z]['title'];
							answerhascode=true;																	// ==========================================================================================
						}else{
							answertext=element[0][z]['title']; 
						}
						answeredNode=newNode; answeredNodeSAid=d[e]['SAid'];
						newNode.addEventListener('click',function(e){var thismega=fetchItemWithAttribute(e.target,'mega'); if(thismega.nextSibling!=null){var nextmegaid=parseInt(thismega.nextSibling.id.substr(8)); showMega(nextmegaid);}else{alert('I\'m sorry... I can\'t do that Dave...');}},false);
					}else{
						newNode.addEventListener('click',function(e){makeChoice(e);e.stopPropagation();},false);		// can't choose something you already chose
					}
					if(element[0][z]['code'] != null){
						var codeDetailsContainer=document.createElement('div');		codeDetailsContainer.id='codeDetails_'+element[0][z]['code'];  codeDetailsContainer.className='selectoricons2';		//codeDetails.innerHTML='<button class="oi oi-spreadsheet" title="View coding details" />';		//  book // document // list-rich // signpost // spreadsheet
						var codeDetails=document.createElement('button');				codeDetails.className='oi oi-book';		codeDetails.attributes.title='View coding details';
							codeDetails.addEventListener('click',function(e){showCodeDetails(e);e.stopPropagation();},true);
						codeDetailsContainer.appendChild(codeDetails);
						newNode.style.paddingRight='40px';
						newNode.appendChild(codeDetailsContainer);
					}
					maxiMega.appendChild(newNode);
				}
			}
			// setting this here, so we can include the answer text as part of the mega
			miniMega.children[0].children[0].innerHTML=humanTime(d[e]['seconds'],true);		// need HTML for <br />
//				if(d[e]['seconds'] < lastseconds){
				//	lastMiniMega.children[0].style.borderRight='2px solid #e8a50c';
//					lastMiniMega.children[0].children[0].innerHTML=lastMiniMega.children[0].children[0].innerHTML+'<span title="timestamp out of sequence" class="oi oi-clock oi3"></span>';
//				}				lastseconds=d[e]['seconds'];   lastMiniMega=miniMega;
//				border-right: 2px solid #e8a50c;
			miniMega.children[1].innerText=element[0][0]['title'];
			miniMega.children[2].innerHTML=answertext
			if(typeof(answeredNode)!='undefined' && answeredNodeSAid == d[e]['SAid']){				// === IF we have an answered node, it could have notes
				if(notetextarray.length>0){								// notes / extras for an ObsElement
						var ptempy=notetextarray.join("\n"); 			//console.log(notetextarray);  //console.log('answeredNode has a note: '+notetextarray.length);		// we'll shorten this later if needed;			
						var respNote=document.createElement('div');		respNote.className='notes';		respNote.innerText=ptempy.replace(/\\n|\\r\\n|\\r/g,"\n");;		// this is the note itself in small text
							if(noteData != ''){respNote.setAttribute('noteData',noteData);}
							if(extraData != ''){respNote.setAttribute('extraData',extraData);}
						var noteEditContainer=document.createElement('div');		noteEditContainer.id='editOEnote_notes_'+answeredNodeSAid;  noteEditContainer.className='selectoricons';		//noteEdit.innerHTML='<button class="oi oi-pencil" title="Add New Note" />';
						var noteEdit=document.createElement('button');				noteEdit.className='oi oi-pencil';		noteEdit.attributes.title='Add New Note';
						noteEdit.addEventListener('click',function(e){multiLineEdit(e);e.stopPropagation();},true);
						noteEditContainer.appendChild(noteEdit);
//						noteEdit.addEventListener('click',function(e){multiLineEdit(e);e.stopPropagation();},true);
						answeredNode.appendChild(respNote);		answeredNode.appendChild(noteEditContainer);
						answeredNode.style.paddingRight='40px';
						if(answerhascode){noteEdit.style.right='40px'; answeredNode.style.paddingRight='80px';}
				
					var tempy=ptempy;
					if(ptempy.length>750){tempy=ptempy.substr(0,300);  lastSpace=tempy.lastIndexOf(' ');			tempy=tempy.substring(0,lastSpace)+'...';	}
					miniMega.children[4].innerText=tempy;
					miniMega.children[4].style.display='block';
					miniMega.children[3].style.display='none';		// hide the "add note" button -- one can expand the note to edit it
				}else{
			//		console.log(answeredNodeSAid+' is getting the add note bit');
					var noteEdit=document.createElement('div');	noteEdit.id='editOEnote_notes_'+answeredNodeSAid;  noteEdit.className='selectoricons';		
						noteEdit.innerHTML='<span>+</span><button class="oi oi-pencil" title="Add New Note" />';
					noteEdit.addEventListener('click',function(e){multiLineEdit(e);e.stopPropagation();},true);
					answeredNode.appendChild(noteEdit);
					answeredNode.style.paddingRight='40px';
					if(answerhascode){noteEdit.style.right='40px'; answeredNode.style.paddingRight='80px';}
				}
			}
			
			leftInnerContainer.appendChild(newMega);
		}
	check4Increment();
	if(typeof(openme)!='undefined' && openme!=''){showMega(openme);}
	fadeInLS();
}













function makeChoice(e){
	var realtarget=fetchItemWithAttribute(e.target,'pnid');		console.log('fIWA returns '+realtarget.id);
	if(realtarget.classList.contains('mega_drop_option')){
		var theMega=realtarget.parentNode.parentNode.parentNode;
		var said=realtarget.parentNode.parentNode.id.substr(7);		// immediate parent is a drop container
	}else{
		var theMega=realtarget.parentNode.parentNode;
		var said=realtarget.parentNode.id.substr(7);
	}
	console.log('theMega = '+theMega.id);
	var derdata={};		var existingNextTarget=0;		var totalNoob=0;  var noWorries=0;
	//var dertarget=e.target.attributes.choicetarget.nodeValue;
	var dertarget=fetchAttribute(e.target,'choicetarget');			console.log('fA returns '+dertarget);
	//var choice=e.target.id.substr(7);
	var choice=fetchedClickedID(e.target);		choice=choice.substr(7);			console.log('clickedID(frag) was '+choice);
	//var pnid=e.target.attributes.pnid.nodeValue;
	var pnid=fetchAttribute(e.target,'pnid');					console.log('fA returns '+pnid);
	var tempy=' (no existing)';			var nukes=[];
	if(theMega.nextSibling != null){
		var existingNextTarget=theMega.nextSibling.attributes.mega.nodeValue;
		swapHikeID=0;
		// ========== the next 3 lines check to see if we need to blast out the downstream items -- commenting this out will auto blast downstream -- BUT that prevents "clicking through" - following your choices to review
		// ============ earlier note: cleared this out due to a few different issues in changing old data that happened to have the same target root (peer vs adult lower items) -- scrubbing them all out makes it easier
		if(existingNextTarget == dertarget){
			tempy=' which is the same as the current one, so no downstream changes needed';			noWorries=1;
			swapHikeID=parseInt(theMega.nextSibling.id.substr(8));
		}else{
			var grandmaskids=theMega.parentNode.childNodes;
			for(var t=(grandmaskids.length-1);t>0;t--){			//console.log('grannieskids = '+grandmaskids[t].id);
				if(grandmaskids[t] != theMega){
					var gmk=grandmaskids[t].id.substr(8);
					nukes.push(gmk);
					derdata[gmk]={};		derdata[gmk]['nuke']=1;
				}else{
					break;		// once we get to the changes node, we can leave the earlier ones alone -- remember we're going backwards
				}
			}
			tempy=' (was '+existingNextTarget+') so well have to clear out SAids: '+nukes.join(', ')+')';
		}
		// ========= here we ought to throw a warning that existing downstream selections will be lost =============
	}else{
		totalNoob=1;
	}
	derdata[said]={};  
	if(totalNoob>0){
		console.log('SAid of '+said+' selected a new choice: '+choice+' (PNid:'+pnid+')  pointing to ITS FIRST target Mega of '+dertarget+tempy);
	}else{
		if(existingNextTarget>0){		// looks like some deletes are in here as well
			console.log('SAid of '+said+' selected a replacement choice: '+choice+' (PNid:'+pnid+')  pointing to new target Mega of '+dertarget+tempy);		// tempy is a message that there is no need for downstream cleanup
			if(swapHikeID!=0){console.log('well need to open up the next pre-existing mega -- '+swapHikeID);}
		}else{	// === last OE in the chain, but not the first either ===
			console.log('SAid of '+said+' selected a new choice: '+choice+' (PNid:'+pnid+')  pointing to new target Mega of '+dertarget+tempy);
		}
	}
	if(e.target.parentNode.id.substr(0,9)=='container'){
		var secs=realtarget.parentNode.parentNode.children[2].children[1].attributes.storedtime.nodeValue; // finding the time from a multi layer option set
	}else{
		var secs=realtarget.parentNode.children[2].children[1].attributes.storedtime.nodeValue; // finding the time from a single layer option set
	}



	
	derdata[said]['swap']=pnid;
	if(dertarget != currentlyLoadedPathStartNode){		// only need to make a new OE if the choice selected does not terminate
		if(noWorries==0){
			derdata[said]['noob']=currentlyLoadedObservationSet+'_'+currentlyLoadedObservation+'_'+dertarget+'_'+secs;
		}else{
			console.log('===== no change in target, so reload Obs with next segment open');
		}
	}else{
		console.log('===== terminating Observation with this selection');
	}






	if(keyboardModifier=='alt'){			// ========== if they hold down ALT while clicking a choice, then they can add a note beforehand ==========
		derDataHandoff=derdata;
		multiLineEdit(e,'newOEwithnote|_x_|notes|_x_|'+said+'|_x_|');	// no initial text
	}else{
		if(typeof(observationElements2[pnid]['extra']) != 'undefined'){		// ======= if their choice requires extra data, it should be requested by default
			console.log('========================== need an extra bit of info: '+observationElements2[pnid]['extra']+' =============================');
			
			/*
			
			
			
											we should do this as a separate quick data grab (like confirm) and store that extra bit on the side for future processing if detected
											
											extrabit=prompt('Please enter the relevant '+observationElements2[pnid]['extra']);
											
											
			
			*/
			updateDatabase('OE',derdata);
		}else{
			updateDatabase('OE',derdata);
		}
	}
}


// ====================================================================================================


function updateDatabase(f,d){				// ajax bit sending data to the system -- each choice is sent and the local JS ObS object is updated
	// ==== F (function) - D (data) to be altered - always an object organized by targetid and data type
	// ==== updateDatabase('OS',{8:{title:'New Title',student:'7',notes:'These are some new notes for this session/OS'}});
	// ==== updateDatabase('OE',{1766:{pn:'128',extra:'4',timer:'177',notes:'kid spoke to kid #4'},1777:{nuke:1}});		//  D is multi-function - update 1766 and remove 1767
	// ==== updateDatabase('OE',{156:{noob:'156'}});
	//console.log('updateDatabase says: yay!');		//console.log(d);
	var gotaNoob='';	var gotaSwap='';
	var d2=new Object;		// non-URL encoded version for upData
	for (var t in d){
		d2[t]={};	// fucking JS
		for (var dd in d[t]){
			console.log('updateDatabase sees '+f+' work for ID: '+t+' ===== '+dd+' / '+d[t][dd]);	
			d2[t][dd]=d[t][dd];
			d[t][dd]=encodeURIComponent(d[t][dd]);			// if you add & or other bits in a notes field, it causes lots of problems...  ;-)   looks like it causes problems by encoding \n as its code which is not allowed in JSON
			if(dd=='noob'){gotaNoob=f;}
			if(dd=='swap'){gotaSwap=f;}
	} }
	var dd=JSON.stringify(d);
	
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
			console.log('UD received data = '+data);
			if(data.substr(0,1)=='A'){
				var data2=data.substr(2);		// remove the A| leaving the JSON data
				if(data2 != ''){var rd=JSON.parse(data2);}else{var rd=new Object();}

				updateLocalData(f,d2,rd);			//// ======= update the local info store before reloading the screen

				if(gotaNoob != ''){
					var noobid=rd[f+'noob0'];
					if(noobid != ''){
						switch(gotaNoob){
							case 'OBS': 	var bits=noobid.split('_');  currentlyLoadedObservationSet=bits[1];	loadObservationSet(currentlyLoadedObservationSet); break;
							case 'OB': 	currentlyLoadedObservation=noobid; 	 createNewObject(appPathList[currentlyLoadedPath]['startpnid']+'_newob','OE');	break;	// new OB also auto-creates a new OE using the start pnid
							case 'OE': 	var bits=noobid.split('_'); loadObservation(currentlyLoadedObservation,Number(bits[3]));		// here we're reloading the Ob, opening the new OE  	// loadObs either takes an event or an id for the first, if a second, it tells it to open that id exposing its OE(s)
												
						}
					}else{console.log('no valid noob id');}
				}
			}else{
				// error X -- so what happens when it fails?
			}
		}
	}
	sendStr='play='+viewingPlaygrounds+'&dafunc='+f+'&updates='+dd;			console.log(sendStr);
	var url =  encodeURI(derServer+'api/ccoi_update2.php');
	xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);	
}


// ====================================================================================================


function updateLocalData(f,d,returnData){
	var gotaNoob='';	var gotaSwap='';
	for (var t in d){ for (var dd in d[t]){		if(dd=='noob'){gotaNoob=f;}		if(dd=='swap'){gotaSwap=f;}  }}	// preprocess -- f is the level/function (OE, OBS, etc)

	for (var t in d){ for (var dd in d[t]){
		console.log('updateLocalData sees '+f+' work for ID: '+t+' ===== ('+dd+') / '+d[t][dd]);
		switch(dd){
			case 'noob':	console.log('noobing');				// this can have multiples but probably wont
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
											observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['PNid']=bits[0];		// there IS a bits[2] but for swaps, it's 0 -- what we need is the SA of the next item
											observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['megaid']=bits[1];											
											if(typeof(returnData[f+'update'+t]) != 'undefined' && typeof(returnData[f+'update'+t]['notes'])=='undefined'){		// only remove the notes on a swap if we havent 'ALT-Choiced' a new note -- the UPDATE step in this loop will deal with it
												delete(observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['notes']);		// notes=='' means something else
											}
											observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['choiceid']=observationElements2[bits[0]]['choiceid'];	// need to get this
											
											// ======== we may need to remove any "extra" info that might be attached (student #4)
											
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
					var updateinfo=returnData[f+'update'+t];		//console.log(updateinfo);		// this is the bit(s) of data being updated
					for (var u in updateinfo){
						switch(f){	
							case 'OBS':	observationSets[t][u]=updateinfo[u]; 
												console.log('OBS updated '+u+' in '+t);
												if(u=='videoid'){observationSets[t]['videoURL']=appVideoList[updateinfo[u]]; observationSets[t]['videoID']=updateinfo[u];}		// when updating the videoID, we dont need a DB update for URL, but we need to update the local store  console.log('updated videoURL in '+t+' as well');
												if(u=='notes'){
													var notespan=document.createElement('span');		var notetext=updateinfo[u].replace(/\\n|\\r\\n|\\r/g,"\n");
													if(notetext.length > 800){var tempy=notetext.substring(0,800); var lastSpace=tempy.lastIndexOf(' '); notetext=tempy.substring(0,lastSpace)+'...'; header_highlevelnotes.className='tiny';}
													notespan.innerText=notetext;
														var othere='editOBSnote|_x_|notes|_x_|'+t+'|_x_|'+d[t][dd];
														notespan.addEventListener('click',function(e){multiLineEdit(e,othere);e.stopPropagation();},true);
														header_highlevelnotes.innerText='';		header_highlevelnotes.appendChild(notespan);	 header_highlevelnotes.style.display='block';
													header_highlevelnotes.setAttribute('title','Edit this Observation Set Note');
												}
												break;
							case 'OB':		observationSets[currentlyLoadedObservationSet]['observations'][t][u]=updateinfo[u]; 
												if(u=='notes'){
													var notespan=document.createElement('span');		var notetext=updateinfo[u].replace(/\\n|\\r\\n|\\r/g,"\n");
													if(notetext.length > 800){var tempy=notetext.substring(0,800); var lastSpace=tempy.lastIndexOf(' '); notetext=tempy.substring(0,lastSpace)+'...'; header_highlevelnotes.className='tiny';}
													notespan.innerText=notetext;
														var othere='editOnote|_x_|notes|_x_|'+t+'|_x_|'+d[t][dd];
														notespan.addEventListener('click',function(e){multiLineEdit(e,othere);e.stopPropagation();},true);
													header_highlevelnotes.innerText='';		header_highlevelnotes.appendChild(notespan);	 header_highlevelnotes.style.display='block';
													header_highlevelnotes.setAttribute('title','Edit this Observation Note');
												}
												console.log('OB updated '+u+' in '+t);
												break;
							case 'OE':		// this one will depend on whether we reuse the last valid SAid -- not really -- other bits may change though -- affects OE noob as well
												var index=fetchIndexFromSAid(t);
												observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index][u]=updateinfo[u];
												
												if(u=='notes'){		// need to update text in minimega and maximega areas
 													var miniMega=document.getElementById('ObsElem_'+t).firstChild;
													
														var hrclean=updateinfo[u].replace(/\\n|\\r\\n|\\r/g,"\n");
														var notetext=hrclean;		var notetext_m=hrclean;
														//observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index][u]=hrclean;		// we're re-doing the local storage to account for the hard returns
														
 														//var notetext=updateinfo[u];		var notetext_m=updateinfo[u];
 														if(notetext.length > 300){var tempy=notetext.substring(0,300); var lastSpace=tempy.lastIndexOf(' '); var notetext_m=tempy.substring(0,lastSpace)+'...';}
 														if(notetext.length > 800){var tempy=notetext.substring(0,800); var lastSpace=tempy.lastIndexOf(' '); var notetext=tempy.substring(0,lastSpace)+'...';}
 														miniMega.lastChild.innerText=notetext_m;		miniMega.lastChild.style.display='block';			// miniMega notes - always exists
 														
 													console.log('looking for choice '+observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['choiceid']+' from '+currentlyLoadedObservationSet+' and '+currentlyLoadedObservation+' and '+index);
 													var daChoice=document.getElementById('choice_'+t+'_'+observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][index]['choiceid']);		// this one is harder as it's tied to the choice selected
														// trouble here is that if there is no existing notes block, we need to make one...
														
														var daIconBox=fetchItemWithClass(daChoice,'selectoricons');		//console.log('zzz '+daIconBox.id);
														var existingNote=fetchItemWithClass(daChoice,'notes');	
														
														if(existingNote){		// already a note there...
															existingNote.innerHTML=notetext;		console.log('already a note to edit');
														}else{
															if(gotaNoob == ''){		// dont do this if this is a noobwithnote
																daChoice.lastChild.remove();	// remove the 'add note' link
															}		console.log('need to make a note');
															// no note with edit, but instead link to add note -- check to see if a 'selectoricons' div is already there (for codebook)
															var respNote=document.createElement('div');		respNote.className='notes';		respNote.innerHTML=notetext;		// this is the note itself in small text
															daChoice.appendChild(respNote);
															
													//		console.log('xxx0='+daChoice.children[0].className);		// ===
													//		console.log('xxx1='+daChoice.children[1].className);
													//		console.log('xxx2='+daChoice.children[2].className);
															
															if(daIconBox){		console.log('reuse existing icon container');
																var noteEdit=document.createElement('button');				noteEdit.className='oi oi-pencil';		noteEdit.attributes.title='Add New Note';
																noteEdit.addEventListener('click',function(e){multiLineEdit(e);e.stopPropagation();},true);
																daIconBox.insertBefore(noteEdit,daIconBox.childNodes[0]);
															}else{			console.log('need to make an icon container');
																var noteEditContainer=document.createElement('div');		noteEditContainer.id='editOEnote_notes_'+t;  noteEditContainer.className='selectoricons2';		
																var noteEdit=document.createElement('button');				noteEdit.className='oi oi-pencil';		noteEdit.attributes.title='Add New Note';
																noteEdit.addEventListener('click',function(e){multiLineEdit(e);e.stopPropagation();},true);
																noteEditContainer.appendChild(noteEdit);
																daChoice.appendChild(noteEditContainer);
															}
																		// daChoice.style.paddingRight='40px';
														}
												}
												
												
												if(u=='seconds'){	
													var e=document.getElementById('ObsElem_'+t);	// should check below in case they've moved on before the update happens
													if(e != null){
														e.firstChild.firstChild.firstChild.innerHTML=humanTime(d[t][dd],true);
														check4Increment();
													}	//minimega with br -- updating the time (live) in the obs view is a little trickier -- next obs load will catch it though
													
												}
												
												console.log('OE updated '+u+' in index '+index+' ('+t+')');
												break;
						}
						
					} // end for
		} // end DD switch
	} } // end bigger for and little for
	// if we have a swap (which just updates PNid pointers on choices --- but there is no corresponding noob in this action, then it's a terminator and still needs updating
	// if a noob, then the OB is reloaded and the next one opened up (in updateDatabase) -- if it's just a swap, then we still reload, but whether we open something depends on if there is a terminator
	if(gotaNoob == '' && gotaSwap != 0){loadObservation(currentlyLoadedObservation,swapHikeID);console.log('RELOADING OB');}		// NOOBS trigger a reload after this function is done
}


// ====================================================================================================


function createNewObject(e,type){			// TODO -- can prob remove e
	console.log('createNewObject says: yay!');		
	// ==== updateDatabase('OBS',{0:{noob:'0'}});							=== adding a new ObSet uses session data (person / path / app), so we dont really send much
	// ==== updateDatabase('OB',{0:{noob:'8'}});							=== add a new OB (subsess) to OBS#8
	// ==== updateDatabase('OE',{0:{noob:'8|156|128|90'}});		=== little tougher -- add new tbSA with sid:8, ssid:156, pnid:128, and 90 seconds
	var data={};  data[0]={'noob':0};		var bits={};
	switch(type){
		case 'OBS': 	data[0]['noob']=e.target.previousSibling.firstChild.value;		// get the select path info							// need to note === viewingPlaygrounds ====== TODO TODO
							var rd=updateDatabase('OBS',data);
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
	var xy=getPosition(e.target);
	if(typeof(other_e) != 'undefined'){
		var bits=other_e.split('|_x_|');	op=bits[0]; item=bits[1]; targetid=bits[2]; initialText=bits[3];			console.log('mLE got '+initialText);			// sometimes the clicked item cant easily carry its information, so we explicitly send it -- BUT -- only once per page
	}else{
		var derid=fetchedClickedID(e);			var underscore=derid.indexOf('_');		if(underscore>0){var bits=derid.split('_'); op=bits[0]; item=bits[1]; targetid=bits[2];}
		var opitem=fetchOpItem(e); 			console.log('no other-e');
	}
//	console.log('multiLineEdit found '+op+' / '+targetid);
	console.log('multiLineEdit '+op+' says: x is '+xy['x']+' and y is '+xy['y']+' with '+targetid+' and pNpS of '+derid);
	switch(op){	
		case 'newOEwithnote': ww=600; hh=340; over=60; down=30; doheader=true; headertext='Add a Note';
											break;
		case 'editOEnote':		var maybenotes=document.getElementById(derid).parentNode.firstElementChild;//		console.log('found '+maybenotes.className);
											if(typeof(maybenotes.attributes.noteData) != 'undefined'){initialText=maybenotes.attributes.noteData.nodeValue;}
											ww=600; hh=340; over=-560; down=30; doheader=true; if(initialText != ''){headertext='Edit Note';}else{headertext='Add a Note';}
											break;
		case 'editOnote':			ww=600; hh=340; over=60; down=30; doheader=true; if(initialText != ''){headertext='Edit Observation Note';}else{headertext='Add an Observation Note';}
											break;
		case 'editOBSnote':		ww=600; hh=340; over=60; down=30; doheader=true; if(initialText != ''){headertext='Edit Observation Set Note';}else{headertext='Add an Observation Set Note';}
											break;
	}
//	console.log('IT: '+initialText);
	if(initialText != ''){placeholdertext='Edit this note... click the green check to finish';}else{placeholdertext='add a note... click the green check to finish';}
	
	
//	console.log('multiLineEdit found '+op+' / '+targetid+' with info: '+initialText+' (MARK: if no info, then fine if original blank)');
	setUpOverlayInner(xy['y']+down,xy['x']+over,ww,hh,doheader,headertext);
	overlayBody.innerText='';
	var noteElement=document.createElement('textarea');
		noteElement.type='textarea';		noteElement.id=item+'_'+targetid;		noteElement.className='multiline';		noteElement.placeholder=placeholdertext;		  noteElement.value=initialText.replace(/\\n|\\r\\n|\\r/g,"\n");;
		noteElement.setAttribute('old',initialText);		noteElement.setAttribute('op',op);		
	overlayBody.appendChild(noteElement);
	var bill=document.getElementById('submitmulti');			if(bill !== null){bill.remove();}
	var newNode=submitMulti.cloneNode(true);    newNode.id='submitmulti';    
	newNode.addEventListener("click", function(e){checkMultiSubmit(e);e.stopPropagation();},false);
	overlayInner.appendChild(newNode);
	
	overlayOuter.style.display='block';
	noteElement.focus();			noteElement.selectionEnd=0;
	noteElement.scrollTop=0;
}		// this one is for multi-line edits

function checkMultiSubmit(e){
	var ee=e.target.previousSibling.firstChild;		var funk='';
	if(ee.value != ee.attributes.old.nodeValue){
		var bits=ee.id.split('_');								// get the info from the id we assigned on activation
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
	var derid=fetchedClickedID(e);			var underscore=derid.indexOf('_');		setPlaceholder=false;
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
		case 'obSet2':	var ds=e.target.parentNode.children[0]; initialText=ds.innerText; 
								ww=600; hh=54; over=0; down=30; doheader=true; headertext='Edit Observation Set Name'; 	funk='OBS';			// obSet2 is for the OTHER edit name link
								break;
		case 'ob':			var ds=e.target.parentNode.parentNode.children[1]; initialText=ds.innerText; 
								ww=600; hh=54; over=-400; down=30; doheader=true; headertext='Edit Observation Name';  	funk='OB';
								break;
		case 'ob2':		var ds=e.target.parentNode.children[0]; initialText=ds.innerText; 
								ww=600; hh=54; over=0; down=30; doheader=true; headertext='Edit Observation Name';  	funk='OB';			// ob2 is for the OTHER edit name link
								break;
		case 'ptedit':		targetid=currentlyLoadedObservationSet; var ds=e.target; initialText=ds.innerText.substr(e.target.innerText.lastIndexOf(nbsp+' ')+2); 	if(initialText=='No Place-Time'){setPlaceholder=true;}
								ww=300; hh=54; over=0; down=30; doheader=true; headertext='Edit Place-Time';  	funk='OBS';  maybespaces='&nbsp; &nbsp; ';
								break;
		case 'studedit':	targetid=currentlyLoadedObservationSet; var ds=e.target; initialText=ds.innerText.substr(e.target.innerText.lastIndexOf(nbsp+' ')+2);  	if(initialText=='No StudentID'){setPlaceholder=true;}
								ww=300; hh=54; over=60; down=30; doheader=true; headertext='Edit Student ID';  	funk='OBS';  maybespaces='&nbsp; &nbsp; ';
								break;
	}
	console.log('singleEdit found '+op+' / '+targetid+' with info: '+initialText);
	setUpOverlayInner(xy['y']+down,xy['x']+over,ww,hh,doheader,headertext);
	overlayBody.innerText='Hit enter to submit...';
	var noteElement=document.createElement('input');
		noteElement.type='text';		noteElement.id='noteInput';		noteElement.className='singleline';		noteElement.style.width=(ww-20)+'px';  
		if(setPlaceholder){noteElement.placeholder=initialText;}else{noteElement.value=initialText;}
		noteElement.setAttribute('op',op);		noteElement.setAttribute('id',targetid);		noteElement.setAttribute('old',initialText);
		noteElement.addEventListener("keydown", function(event) {
			if (event.keyCode === 13) {											// I could probably just set these directly since opitem seems to work...
				if(event.target.value != event.target.attributes.old.nodeValue){
					var newData={}; newData[event.target.id]={};		// very annoying that JS object keys cant accept variables UNLESS the object was created beforehand --- xx['yy']['zz']=1 is fine, but xx[yy][zz]=1 with yy and zz == 'yy' and 'zz' wont work ---- ah well....
			//		newData[event.target.id][opitem]=encodeURIComponent(event.target.value);
					newData[event.target.id][opitem]=event.target.value;			console.log('looking at: '+opitem+' and \''+event.target.value+'\' from '+ds.id);
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
	noteElement.focus();		noteElement.selectionEnd=0;
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
	overlayBody.innerText='';
	var selectElement=document.createElement('select');
		selectElement.id='noteInput';		selectElement.className='dropdowner';		selectElement.style.width=(ww-20)+'px';
		selectElement.setAttribute('op',op2);		selectElement.setAttribute('id',targetid);		//selectElement.setAttribute('old',initialID);
		var bill=document.getElementById('submitmulti');			if(bill !== null){bill.remove();}
		switch(op){	
			case 'videdit':	for (var v in appVideoList){		//console.log('videdit drop found option '+v+' with '+appVideoList[v]);
										var selectOption=document.createElement('option');		selectOption.value=v;		selectOption.innerText=appVideoList[v];		
										if( v == initialID){selectOption.selected=true;}
										selectElement.appendChild(selectOption);
									}
									var newNode=submitMulti.cloneNode(true);    newNode.id='submitmulti';    
									newNode.addEventListener("click", function(e){
												var derselect=e.target.previousSibling.firstChild;
												var targetOBSid=derselect.id;		var opitem=derselect.attributes['op'].value;
												var newData={}; newData[targetOBSid]={}		// still annoying that JS object keys cant accept variables UNLESS the object was created beforehand --- xx['yy']['zz']=1 is fine, but xx[yy][zz]=1 with yy and zz == 'yy' and 'zz' wont work ---- ah well....
												newData[targetOBSid][opitem]=derselect.options[derselect.selectedIndex].value; 		console.log('looking at: '+opitem+' and \''+derselect.options[derselect.selectedIndex].value+'\'');
												var rd=updateDatabase(funk,newData);
												overlayOuter.style.display='none'; 
	
												ds.attributes.videoid.nodeValue=derselect.options[derselect.selectedIndex].value;	// actually update the text of the edited item -- refreshes will also do this (note loadObSet refreshes from DB data)
												ds.innerHTML='&nbsp;&nbsp;'+derselect.options[derselect.selectedIndex].text;
	
												var bill=document.getElementById('launch_video_button');
												if(bill !== null){		// if there's already a videoUrl and we changed it, then there will be a button -- else we need to make the button
													var bbb=getTextWidth("Inconsolata",16,'Open '+derselect.options[derselect.selectedIndex].text);	
													bill.firstChild.innerText='Open '+derselect.options[derselect.selectedIndex].text;
													bill.style.width=(bbb+100)+'px';		// widen the button to fit the text
													bill.href=derDevProd+'/newvideo_popout.php?vid='+derselect.options[derselect.selectedIndex].value;
												}else{
													var newNode=platonicVideoButton.cloneNode(true);		newNode.id='launch_video_button';		newNode.style.display='block';		// none by css -- inline by default
													var bbb=getTextWidth("Inconsolata",16,'Open '+derselect.options[derselect.selectedIndex].text);	
													newNode.firstChild.innerText='Open '+derselect.options[derselect.selectedIndex].text;
													maybe_videolink.appendChild(newNode);		maybe_videolink.style.display='block';
													newNode.style.width=(bbb+100)+'px';		// widen the button to fit the text
													newNode.href=derDevProd+'/newvideo_popout.php?vid='+derselect.options[derselect.selectedIndex].value;
												}
												e.stopPropagation();
									},false);
									overlayInner.appendChild(newNode);
									break;
			case 'obSet':	for (var p in appPathList){
										var selectOption=document.createElement('option');		selectOption.value=p;		selectOption.innerText=appPathList[p]['name'];		selectElement.appendChild(selectOption);
									}
									var newNode=submitMulti.cloneNode(true);    newNode.id='submitmulti';    
									newNode.addEventListener("click", function(e){
												var derselect=e.target.previousSibling.firstChild;
												overlayOuter.style.display='none'; 
												createNewObject(event,'OBS');
												e.stopPropagation();
									},false);
									overlayInner.appendChild(newNode);
									break;
	}

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

function loadExtraDataFromHandoff(h){
	for (var i in derDataHandoff){
		if(typeof(h[i])=='undefined'){h[i]={};}		// somedays, I really hate JS
		for (var f in derDataHandoff[i]){
			h[i][f]=derDataHandoff[i][f];		//console.log('LEXDFH found '+i+' and '+f+' with '+derDataHandoff[i][f]);
		}
	}
	return h;
}

function showCodeDetails(e){
	var derid=fetchedClickedID(e);		//	console.log('yay '+ derid);
	var code='x';
	var underscore=derid.indexOf('_');		if(underscore>0){var bits=derid.split('_'); code=bits[1];}
	if(code != 'x'){
		console.log('lets show '+code+'    /    '+codeData[code]['codedesc']+'    /    '+codeData[code]['codeexp']+'    /    '+codeData[code]['coderules']);
		
		setUpOverlayInner(0,0,600,400,true,'Details for code '+code);
		overlayBody.innerHTML='<h4>Description</h4><p>'+codeData[code]['codedesc']+'</p><h4>Explanation</h4><p>'+codeData[code]['codeexp']+'</p><h4>Coding Rules</h4><p>'+codeData[code]['coderules']+'</p>';
			overlayBody.style.height='400px';		overlayBody.style.overflow='auto';	// override the normall settings
		overlayOuter.style.display='block';
	}
}

function setUpOverlayInner(t,l,w,h,head,headtext){
	// need to make sure the box isn't placed PARTLY offscreen as it's a fixed item -- no scroll -- if window is 1000px tall and we put this at 900px, then most of it stays offscreen =========
	if( ! head ){overlayHead.style.display='none';}else{overlayHead.innerText=headtext; h+=32; overlayHead.style.display='block';}
	var ww = window.innerWidth;	var wh = window.innerHeight;
	if( t+h > wh ){t=(wh-h-20);}
	if( l+w > ww ){l=(ww-w-20);}			// if the box would extend outside of the window area, pull it inside
//position:absolute; top:50%; margin-top: -150px; left:50%; margin-left: -200px;	

// ===== NOTE - at present T and L have no meaning ========

	overlayInner.style.marginTop=((h/2)*-1)+'px';
	overlayInner.style.marginLeft=((w/2)*-1)+'px';
	overlayInner.style.width=w+'px';
	overlayInner.style.height=h+'px';
	
	overlayBody.style.height='auto';		overlayBody.style.overflow='visible';
}

function fetchedClickedID(x){		//console.log('fetcher received '+x.id);
	if(typeof(x.currentTarget)!='undefined'){x=x.currentTarget;} //console.log('fetcher hunted down event target '+x.currentTarget)
	if(x==document){return 'document';}	//	console.log('fetcher found '+x.id);
	if(typeof(x.id) != 'undefined' && x.id != '' && x.id.indexOf('_')>0){return x.id;}else{return fetchedClickedID(x.parentNode);}		//console.log('not this one...');		
}
function fetchAttribute(x,a){		//console.log('fetcher received '+x.id);
//	if(typeof(x.currentTarget)!='undefined'){x=x.currentTarget;} //console.log('fetcher hunted down event target '+x.currentTarget)
	if(x==document){return 'document';}	//	console.log('fetcher found '+x.id);
	if(typeof(x.attributes[a]) != 'undefined'){return x.attributes[a].nodeValue;}else{return fetchAttribute(x.parentNode,a);}		//console.log('not this one...');		
}
function fetchItemWithAttribute(x,a){		//console.log('fetcher received '+x.id+' and '+a);
//	if(typeof(x.currentTarget)!='undefined'){x=x.currentTarget;} //console.log('fetcher hunted down event target '+x.currentTarget)
	if(x==document){return 'document';}	//	console.log('fetcher found '+x.id);
	if(typeof(x.attributes[a]) != 'undefined'){return x;}else{return fetchItemWithAttribute(x.parentNode,a);}		//console.log('not this one...');		
}
function fetchOpItem(x){
	if(typeof(x.target)!='undefined'){x=x.target; }		//console.log('fetcherOp hunted down event target');
	if(x==document){return false;}
	if(x.hasAttribute('opitem')){return x.attributes['opitem'].nodeValue;}else{return fetchOpItem(x.parentNode);}	//	console.log('not this one...');		//console.log('fetcherOp found '+x.attributes['opitem'].nodeValue);
}
function fetchItemWithClass(x,c){
	for(var kid in x.childNodes){			// kid will be a string even if it's an index 0,1,2, etc
		if(parseInt(kid) > -1){
	//		console.log('fIWC looking for '+c+': found '+kid+' '+typeof(kid)+' with '+x.childNodes[kid].className);
			if(typeof(x.childNodes[kid].className) !='undefined' && x.childNodes[kid].className.indexOf(c)>=0){return x.childNodes[kid];}
		}
	}
	return false;
}
function fetchIndexFromSAid(said){
	for(var e=0;e<observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'].length;e++){
		if( observationSets[currentlyLoadedObservationSet]['observations'][currentlyLoadedObservation]['ObResp'][e]['SAid']==said ){return e;}
	}
	return -1;
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
function totalSec(minutes, seconds) {   var totalTimeSec = minutes*60 + seconds;    return totalTimeSec;}
function check4Increment(){
	var megas=document.getElementsByClassName('mega');		var lastseconds=0;	var mini,maxi,secs;
	for(var e=0;e<megas.length;e++){
		if(megas[e].id != 'platonicMega'){
			mini=megas[e].children[0];
			maxi=megas[e].children[1];
			secs=parseInt(maxi.children[2].children[1].attributes.storedtime.value);		console.log('check4Increment -- '+secs+' // '+lastseconds+' for '+megas[e].id+' and '+e);
			if(secs < lastseconds){
				console.log('check4Increment needs a clock for '+megas[e].id+' and '+e+' -- '+secs+' // '+lastseconds);
				lastMiniMega.children[0].children[0].innerHTML=lastMiniMega.children[0].children[0].innerHTML+'<span title="timestamp out of sequence" class="oi oi-clock oi3"></span>';
			}
			lastseconds=secs;   lastMiniMega=mini;
		}
	}
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
