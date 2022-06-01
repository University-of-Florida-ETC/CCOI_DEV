var derServer='https://ccoi-dev.education.ufl.edu/';

//<div draggable="true" class="platonicDraggable" style="top:10px;" id="platonicDraggable" iihs_order="0">
//	<div class="draggableBody">
//		<div class="choicetext">0</div>
//		<div class='choicehover'>Zero</div>
//		<p class="draggableText">New Node</p>
//		<div class="nexttext" nextid='0'>0</div>
//	<div class="draggableExpanded">
// 		<div class="fulltitle">New Node Full Title Text</div>
// 		<div class="extravar">ExtraVar</div>
// 		<div class="nodegroup">Path Type (subgroup)</div>
// 		<div class="choicegroup">Choice Group if used</div>
// 		<div class="nodeids">newid / oldid</div>
// 		<div class="destination">title with colors and number</div>
// 	</div>
//</div></div>


//<div draggable="true" class="platonicMega" id="platonicMega" color="none" colornumber="1">
//		<div class="pM_head">
//			<p class="headtext">platonicMega</p>
//			<p class="colornumber"></p></div>
//		<div class="pM_body">
//			<p id="draginfo">Drag elements into the order you wish to display them<br />Double click an element to edit it</p>
//			<div class="dragregion" id="dragregionX"></div></div></div>

var keyboardModifier=null;
var ajaxkey=0;		var ajaxkey2=0;
var lastb;
var data4update=new Object();
var origdraggers;
var dragPosM=new Object();
var nextleftpx=40;

var dodropX='x';		var origvl='';		var origjl='';
var deruser=new Object();
var needRefresh=false;
var nowhideem=false;
var draggedMega;
var draggedItem;
var draggedGhost;
var megaZindex=10;
var lastSeenDetails=false;
var lastSeenEditor=false;
var lastSeenMegaEditor=false;
var nodeGroupSelector='<p>Select a Node Group</p><select class="nodeGroupSelector" id="nGS"><option value=""></option>';
var choiceGroupSelector='<p>Select a Choice Group</p><select class="choiceGroupSelector" id="cGS"><option value=""></option>';
var parentSelector='<p>Select a Parent Node</p><select class="parentSelector" id="pS"><option value=""></option>';
var destinationSelector='<p>Select a Destination</p><select class="destinationSelector" id="dS"><option value=""></option><option value="X">Event Terminates</option>';

var colorList=new Array('green','yellow','red','blue','aqua','purple');
var currentColorIndex=0;
var nodeGroupOrdinals=new Object();
var choiceGroupOrdinals=new Object();
var choicegroupicons=new Object();
var spawnedFirstMega=false;


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
function throwError(msg){
	document.getElementById('errormsg').innerHTML=msg;
	overlay4.style.display='block';
	setTimeout(function(){hideOverlays(4)},3000);
}
function sendMessage(head,msg){
	document.getElementById('marks-overhead9').innerHTML=head;
	document.getElementById('dermsg').innerHTML=msg;
	overlay6.style.display='block';
	setTimeout(function(){hideOverlays(6)},4000);
}

//	spawnMega('pm1','This is a title','green');			// == id, title, color
//		spawnNoob('ignored_if_2nd_item_exists','pm1',1224,'Poof!  Im here!',1,'pm2');

function goGetEm(tpid){
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
			var d=JSON.parse(data);
			for(e=0;e<d['nodeGroups'].length;e++){nodeGroupOrdinals[d['nodeGroups'][e]['t_id']]=e+1; nodeGroups[d['nodeGroups'][e]['t_id']]=d['nodeGroups'][e]['t_label'];nodeGroupSelector+="<option value='"+d['nodeGroups'][e]['t_id']+"'>"+d['nodeGroups'][e]['t_label']+"</option>";}    nodeGroupSelector+="</select>";
			for(e=0;e<d['choiceGroups'].length;e++){choiceGroupOrdinals[d['choiceGroups'][e]['t_id']]=e+1; choiceGroups[d['choiceGroups'][e]['t_id']]=d['choiceGroups'][e]['t_name'];choiceGroupSelector+="<option value='"+d['choiceGroups'][e]['t_id']+"'>"+d['choiceGroups'][e]['t_name']+"</option>";}    choiceGroupSelector+="</select>";
			
			var destinations=new Object();
			for(e=0;e<d['nodes'].length;e++){	destinations[d['nodes'][e]['t_megaid']]=d['nodes'][e]['title'];}		// preprocess megas to get title info for nodes destinations			
			for(e=0;e<d['nodes'].length;e++){		// ================== these are megas
				var n=d['nodes'][e];		var nextbit;
				var dercolor=nextColor();
			//	console.log('autospawning mega with: '+'mega_'+n['t_megaid']+' / '+n['title']+' / '+dercolor);
				spawnMega('mega_'+n['t_megaid'],n['title'],dercolor);
				
				for(var cg=0;cg<n['branches'].length;cg++){		// ============== these are choices in a mega -- this layer is for each choice group (at least one)
					for(var i=0;i<n['branches'][cg].length;i++){		// ============== here we get to actual choices
						var ch=n['branches'][cg][i];
						var extrabits=new Object();
						if(ch['t_nextid']=='terminate'){
							nextbit='terminate';
							extrabits['destination']='Path Terminates';
						}else{
							nextbit='mega_'+ch['t_nextid'];
							extrabits['destination']=destinations[ch['t_nextid']];
						}
						extrabits['nodeids']=ch['t_thisid']+' / '+ch['branch_id'];
						if(typeof(ch['extra']) != 'undefined'){extrabits['extravar']=ch['extra'];}
						
					//	console.log('      autospawning with: '+ch['t_parentid']+' / '+ch['t_thisid']+' / '+ch['description']+' / '+ch['t_choicegroup']+' / '+nextbit+' //// '+extrabits['nodeids']);
						spawnNoob('ignored_if_2nd_item_exists','mega_'+ch['t_parentid'],ch['t_thisid'],ch['description'],ch['t_choicegroup'],nextbit,extrabits);
					}
				}
			}
			updateMegaColors();
		}
	}
	sendStr='tpid='+tpid;
	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			//console.log(url);
	xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
}

var dragItems=new Object();
var dragregiontop;
var draggedItem;
var dragregionheight;

function allowDrop(e){e.preventDefault();  e.dataTransfer.dropEffect = 'link';}
function allowDropMega(e){e.preventDefault();  e.dataTransfer.dropEffect = 'link';}
function pickUp(e){
	var targetDR=e.target.parentNode;		  console.log('pickup sees '+targetDR.id);
	var ghost = e.target.cloneNode(true);		// the ghost is needed so we can drag it while the real item is off screen
	var styles='position:absolute; top:-50%; left:-50%; margin-left: -175px; z-index:-5; background-color:'+window.getComputedStyle(e.target).getPropertyValue('background-color')+'; border-color:'+window.getComputedStyle(e.target).getPropertyValue('border-color')+';';
	ghost.setAttribute('style',styles);
	ghost.setAttribute('id','derghost');
	draggedGhost=ghost;
	overlay.appendChild(ghost);			//console.log(styles+' / '+e.target.id+' / '+ghost.id);
	var posxy=getPosition(e.target);		//console.log(e.clientX+' / '+e.clientY+' / '+posxy['x']+' / '+posxy['y']);
	e.dataTransfer.setDragImage(ghost, (e.clientX-posxy['x']), (e.clientY-posxy['y']));
	
	//e.target.addEventListener('drag',function(e){dragItem(e);},true);
	e.target.addEventListener('drag',function_dragItem,true);
	draggedItem=e.target;
	draggedItem.style.opacity=0;
	dragregiontop=getPosition(targetDR).y;			console.log('drt='+dragregiontop);
	dragregionheight=targetDR.scrollHeight;
	
	dragItems=new Object();		// clear out the old data
	vrdr=targetDR;			//vrdr=dragregion;

	console.log(' ');		// heh -- a space :-)
	for(var n=0; n<vrdr.childNodes.length; n++){
		item=vrdr.childNodes[n];					//	console.log(n+' id='+item.id);
		dragItems[n]=new Object;
		dragItems[n]['top']=parseInt(item.style.top.slice(0,-2));	// strip off the 'px'
			dragItems[n]['otop']=dragItems[n]['top'];
		dragItems[n]['id']=item.id.substr(5);		// strip off the 'seid_'
		// === note = if an order is set to 999, then it is stored here and reassigned to the next item on drop
		dragItems[n]['order']=parseInt(item.attributes.iihs_order.nodeValue);
		//item.attributes.iihs_order.nodeValue=(n+1);			// ======= this line keeps reseting the item order based on HTML order -- wonder why?
		//dragItems[n]['order']=(n+1);									// ======= this line keeps reseting the item order based on HTML order -- wonder why?
		dragItems[n]['oorder']=dragItems[n]['order'];		//console.log('iihs_order for item '+item.id+' is '+dragItems[n]['order']+' and its top is '+dragItems[n]['top']);
	}
	console.log(' ');		// heh -- a space :-)
}
function dragItem(e){
	var targetDR=e.target.parentNode;
	var item=e.target;
	var itemtop=e.clientY-dragregiontop;				//console.log(e.clientY+' / '+dragregiontop);	// clientY seems relative to last position:relative like dragregiontop
	if(itemtop > 0 && itemtop < dragregionheight){
		vrdr=targetDR;			//vrdr=dragregion;
	
		var dikeys=Object.keys(dragItems);
		var bill=parseInt(item.attributes.iihs_order.nodeValue);
		for (var i in dikeys){																		//console.log('looking at '+i);
			var joe=vrdr.childNodes[i].id;
			if(bill > dragItems[i]['order']){
				if(itemtop < (dragItems[i]['top'] + incr2)){					//console.log('processing drag shifts '+joe+'/'+i+' down and shows top of '+dragItems[i]['top']+' === '+bill+' > '+dragItems[i]['order']+'  and  '+itemtop+' < '+dragItems[i]['top']+' + '+incr2);
				
								var x1=parseInt(vrdr.childNodes[i].attributes.iihs_order.nodeValue);
								var x2=vrdr.childNodes[i].getAttribute('iihs_order');
						//		console.log('on FOR '+i+', node '+i+' has orders of '+x1+' and '+x2);
				
					var hold=dragItems[i]['order'];
						dragItems[i]['order']=item.attributes.iihs_order.nodeValue;		//	console.log('setting the shifted item to order '+item.attributes.iihs_order.nodeValue+' in the order variable');
						item.setAttribute('iihs_order',hold);											//	console.log('setting the dragged item to order '+hold);
						item.style.top=dragItems[i]['top']+'px';										//	console.log('setting the dragged item to '+dragItems[i]['top']+'px');
					vrdr.childNodes[i].setAttribute('iihs_order',dragItems[i]['order']);		//	console.log('setting the shifted item (cN?) to order '+dragItems[i]['order']+' in the item html');
					vrdr.childNodes[i].style.top=(dragItems[i]['top']+draggableBaseHeight)+'px';		//	console.log('setting '+joe+'/'+i+' to '+(dragItems[i]['top']+draggableBaseHeight)+'px');
					dragItems[i]['top']+=draggableBaseHeight;
				}
			}else{
				if(bill < dragItems[i]['order']){
					if(itemtop > (dragItems[i]['top'] - incr2)){							//console.log('processing drag shifts '+joe+'/'+i+' up and shows top of '+dragItems[i]['top']+' === '+bill+' < '+dragItems[i]['order']+'  and  '+itemtop+' > '+dragItems[i]['top']+' + '+incr2);
					
									var x1=parseInt(vrdr.childNodes[i].attributes.iihs_order.nodeValue);
									var x2=vrdr.childNodes[i].getAttribute('iihs_order');
								//	console.log('on FOR '+i+', node '+i+' has orders of '+x1+' and '+x2);
					
						var hold=dragItems[i]['order'];
							dragItems[i]['order']=item.attributes.iihs_order.nodeValue;		//	console.log('setting the shifted item to order '+item.attributes.iihs_order.nodeValue+' in the order variable');
							item.setAttribute('iihs_order',hold);											//	console.log('setting the dragged item to order '+hold);
							item.style.top=dragItems[i]['top']+'px';										//	console.log('setting the dragged item to '+dragItems[i]['top']+'px');
						vrdr.childNodes[i].setAttribute('iihs_order',dragItems[i]['order']);	//		console.log('setting the shifted item (cN?) to order '+dragItems[i]['order']+' in the item html');
						vrdr.childNodes[i].style.top=(dragItems[i]['top']-draggableBaseHeight)+'px';		//	console.log('setting '+joe+'/'+i+' to '+(dragItems[i]['top']-draggableBaseHeight)+'px');
						dragItems[i]['top']-=draggableBaseHeight;
					}
				}
			}
		}
	}
}
function dropItem(e){
	var targetDR=findTheDR(e.target);		console.log('drop sees '+targetDR.id);
	e.preventDefault();
	if(draggedItem){
		draggedItem.style.opacity=1;
		draggedItem.removeEventListener('drag',function_dragItem,true);
		draggedItem=false;
	}
	console.log('xxx '+typeof(draggedGhost));
	overlay.removeChild(draggedGhost);
	draggedGhost=false;
	vrdr=targetDR;			//vrdr=dragregion;

	for(var n=0; n<vrdr.childNodes.length; n++){
		var baseid=vrdr.childNodes[n].id;
		var orderEncountered=n+1;
		// ===== OKAY -- the order attribute is being shifted during the drag process -- the value it finds is actually the new one we want
		var neworder=vrdr.childNodes[n].attributes.iihs_order.nodeValue;		
		if(orderEncountered != neworder){
			data4update[baseid]=neworder;				console.log('drop says for '+baseid+' / '+orderEncountered+' to '+neworder);
		}else{
			console.log('drop says '+baseid+' needs no change');
		}
	}
	
	// =========== now we need a small AJAX to send the updates to the DB ===========
	
	
	
	
	
	
}
function stopDrag(e){		// this only cleans up if the item is dropped outside of the droppable area ===================
	var targetDR=e.target.parentNode;		  console.log('stop sees '+targetDR.id);
	if(draggedItem){		// only reset if the drop event has not occurred (dropped outside of box)
		vrdr=targetDR;			//vrdr=dragregion;
		for(var n=0; n<vrdr.childNodes.length; n++){
			vrdr.childNodes[n].setAttribute('iihs_order',dragItems[n]['oorder']);
	 		vrdr.childNodes[n].style.top=dragItems[n]['otop']+'px';
	 		console.log(vrdr.childNodes[n].id+' is returned to order '+dragItems[n]['oorder']);
		}
		draggedItem.style.opacity=1;		// the above code is meant to reset the draggers if one is dropped outside the draggable area
	}
}

function pickUpMega(e){
	var victim=e.target;									  console.log('pickupM sees '+victim.id);
	var ghost =victim.cloneNode(true);		// the ghost is needed so we can drag it while the real item is off screen
	victim.style.opacity=0;
	megaZindex++;		victim.style.zIndex=megaZindex;
	victim.addEventListener('drag',function_dragMega,true);
	var styles='position:absolute; top:-1500px; left:-1500px; z-index:-5; background-color:'+window.getComputedStyle(victim).getPropertyValue('background-color')+'; border-color:'+window.getComputedStyle(victim).getPropertyValue('border-color')+';';
	ghost.setAttribute('style',styles);
	ghost.setAttribute('id','derghost');
	overlay.appendChild(ghost);																							//console.log(styles+' / '+e.target.id+' / '+ghost.id);
	var posxy=getPosition(victim);																						//console.log(e.clientX+' / '+e.clientY+' / '+posxy['x']+' / '+posxy['y']);
	e.dataTransfer.setDragImage(ghost, (e.clientX-posxy['x']), (e.clientY-posxy['y']));			//console.log('pickupM '+e.clientY+'-'+posxy['y']+'px and '+e.clientX+'-'+posxy['x']+'px');
	dragPosM['x']=e.clientX-posxy['x'];		dragPosM['y']=e.clientY-posxy['y'];

	draggedMega=victim;		// for reference in other functions
	
//	console.log('D header text height = '+victim.firstChild.firstChild.clientHeight);
//	stretchHeader(victim.id);
	
}
function dragMega(e){
//	console.log('dragM sees '+draggedMega.id+' at '+e.clientX+'-'+dragPosM['x']+'px and '+e.clientY+'-'+dragPosM['y']+'px');
	if(e.clientY>0){draggedMega.style.top=(e.clientY-dragPosM['y'])+'px';	   draggedMega.style.left=(e.clientX-dragPosM['x'])+'px';	}
}
function stopDragMega(e){
	console.log('stopM sees '+draggedMega.id+' at '+e.clientX+' and '+e.clientY);
//	if(e.clientY>0){draggedMega.style.top=(e.clientY-dragPosM['y'])+'px';	   draggedMega.style.left=(e.clientX-dragPosM['x'])+'px';	}	
	draggedMega.style.opacity=1;
	draggedMega=false;
}
function dropMega(e){
	e.preventDefault();
	if(draggedMega){
		console.log('dropM sees '+draggedMega.id+' at '+e.clientX+' and '+e.clientY);
		if(e.clientY>0){draggedMega.style.top=(e.clientY-dragPosM['y'])+'px';	   draggedMega.style.left=(e.clientX-dragPosM['x'])+'px';	}
//		draggedMega.removeEventListener('drag',function(e){dragMega(e);},true);
		draggedMega.removeEventListener('drag',function_dragMega,true);
		
		var derghost=document.getElementById('derghost');
		overlay.removeChild(derghost);
		//draggedMega=false;
	}
}

function changeMegaColor(target,color){
	
}

var function_dragMega=function(e){dragMega(e);}
var function_dragItem=function(e){dragItem(e);}

function findTheDR(dr){if(dr.className=='dragregion'){return dr;}else{return findTheDR(dr.parentNode);}}
function findDraggable(dr){if(dr.className=='platonicDraggable'){return dr;}else{return findDraggable(dr.parentNode);}}
function findMega(dr){if(dr.className.substring(0,12)=='platonicMega'){return dr;}else{return findMega(dr.parentNode);}}


function spawnNoob(e,tDR,id,text,choicegroup,nextmega,extrainfo){
	if(typeof(tDR) != 'undefined'){var targetDR=document.getElementById(tDR).children[1].children[1];}else{var targetDR=e.target.parentElement.children[1].children[1];}
	var origheight=targetDR.clientHeight;	 //	console.log('spawn sees '+targetDR.id+' with '+origheight);
	var newheight=origheight+draggableBaseHeight
	targetDR.style.height=newheight+'px';		//console.log('spawn adds '+draggableBaseHeight+' to height to get '+newheight);
	var newNode=platonicDraggable.cloneNode(true);
		newNode.style.top=origheight+'px';
	
	var lastorder=0;
	if(targetDR.lastChild){lastorder=parseInt(targetDR.lastChild.attributes.iihs_order.nodeValue);}		// its possible that there are no draggables
	newNode.setAttribute('iihs_order',(lastorder+1));
	//	var bill=elRando();
	newNode.setAttribute('id',id);
	
	// ======= title info ========
	if(typeof(text) != 'undefined'){
		var tempy=text;	var lastSpace=0;
		if(text.length > 54){
			tempy=text.substring(0,54);
			lastSpace=tempy.lastIndexOf(' ');
			tempy=text.substring(0,lastSpace)+'...';
		}
		newNode.childNodes[0].childNodes[2].innerText=tempy;
		newNode.childNodes[1].childNodes[0].innerText=text;
		newNode.childNodes[2].attributes.OT.nodeValue=text;		// editor
	}
	
	// ======= choicegrup info ========
	if(typeof(choicegroup) != 'undefined'){
		if(choicegroup==0){
			newNode.childNodes[0].childNodes[0].innerText='';
			newNode.childNodes[1].childNodes[4].style.display='none';			// expanded
		}else{
			if(choiceGroupOrdinals[choicegroup]>9){choicegroupicon=(parseInt(choiceGroupOrdinals[choicegroup],10)).toString(36).toUpperCase();}else{choicegroupicon=choiceGroupOrdinals[choicegroup];}			// choicegroupicon is the 1-9A-Z on the left of the draggable -- we use ordinals for situations where the lowest ID is12 or something...
				choicegroupicons[choicegroup]=choicegroupicon;
			newNode.childNodes[0].childNodes[0].innerText=choicegroupicon;
			newNode.childNodes[0].childNodes[1].innerText=choiceGroups[choicegroup];
			newNode.childNodes[1].childNodes[4].innerText='ChoiceGroup(s): '+choiceGroups[choicegroup];		// expanded
			newNode.childNodes[2].attributes.CG.nodeValue=choicegroup;	// editor
		}
	}
	// ======= destination info ========
	if(typeof(nextmega) != 'undefined'){
		newNode.childNodes[0].lastChild.attributes.nextid.nodeValue=nextmega;
		newNode.childNodes[1].childNodes[6].innerText='Destination: '+extrainfo['destination'];
		newNode.childNodes[2].attributes.DEST.nodeValue=nextmega;		// editor
	}
	
	if(typeof(extrainfo['extravar']) != 'undefined'){newNode.childNodes[1].childNodes[1].innerText='Extra Var: '+extrainfo['extravar'];}else{newNode.childNodes[1].childNodes[1].style.display='none';	}
	if(typeof(extrainfo['aside']) != 'undefined'){newNode.childNodes[1].childNodes[2].innerText='Extra Instructions: '+extrainfo['aside'];}else{newNode.childNodes[1].childNodes[2].style.display='none';	}

// 	if(typeof(extrainfo['nodegroup']) != 'undefined'){				// ======= only megas have node groups ========
// 		newNode.childNodes[1].childNodes[2].innerText='NodeGroup: '+extrainfo[''];}else{newNode.childNodes[1].childNodes[2].style.display='none';
// 		newNode.childNodes[2].attributes.NG.nodeValue=extrainfo['nodegroup'];		// editor
// 	}
	newNode.childNodes[1].childNodes[5].innerText='NodeID(s): '+extrainfo['nodeids'];

 	newNode.addEventListener('dragstart',function(e){console.log('pickup');pickUp(e);e.stopPropagation();},true);
 	newNode.addEventListener('dragend',function(e){stopDrag(e);e.stopPropagation();},true);
 	newNode.addEventListener('dblclick',function(e){nukeNoob(e);e.stopPropagation();},true);				//https://css-tricks.com/snippets/javascript/bind-different-events-to-click-and-double-click/
 	newNode.addEventListener('click',function(e){if(keyboardModifier=='alt'){flipEditor(e,'flip');}else{flipDetails(e,'flip');}e.stopPropagation();},false);	
 	
 	newNode.childNodes[0].childNodes[0].addEventListener('mouseover',function(e){showChoice(e);},true);
 	newNode.childNodes[0].childNodes[0].addEventListener('mouseout',function(e){hideChoice(e);},true);
 	
 	newNode.childNodes[2].addEventListener('click',function(e){e.stopPropagation();},false);			// clicking the editor itself should do nothing
 	newNode.childNodes[2].childNodes[6].addEventListener('click',function(e){flipEditor(e,'hide');e.stopPropagation();},true);		// editor close button
 	newNode.childNodes[2].childNodes[7].addEventListener('click',function(e){compileUpdatesForNode(e);e.stopPropagation();},true);		// editor submit
 	
	targetDR.appendChild(newNode);
}
function spawnMega(id,title,color){
	console.log('Mspawn sees '+id);
	var newNode=platonicMega.cloneNode(true);
	newNode.style.top='80px';
	newNode.style.left=nextleftpx+'px';					nextleftpx+=80;
	newNode.setAttribute('id',id);
	newNode.children[1].childNodes[1].setAttribute('id','dragregion_'+id);
	
	megaData[id]=new Object();		// this is also used down in updateMegaColors - lets prime it here
	megaData[id]['t']=title;
	
	destinationSelector+="<option value='"+id+"'>"+title+"</option>";
	parentSelector+="<option value='"+id+"'>"+title+"</option>";
	
	if(!spawnedFirstMega){			// we want to add a star or something to the first node in the path
		var star=document.createElement("IMG");
			star.setAttribute('id','firstNode');
			star.setAttribute('src',derServer+'trailblazer/images/icon_star_white.png');
			newNode.appendChild(star);
		spawnedFirstMega=true;
	}
		
	if(typeof(title) != 'undefined'){newNode.firstChild.firstChild.innerText=title;}
	if(typeof(color) != 'undefined'){newNode.className+=" pM" + color;		newNode.attributes.color.nodeValue=color;}
	
	newNode.addEventListener('dragstart',function(e){console.log('pickupM');pickUpMega(e);e.stopPropagation();},false);
	newNode.addEventListener('dragend',function(e){stopDragMega(e);e.stopPropagation();},false);
	newNode.firstChild.addEventListener('dblclick',function(e){spawnNoob(e);e.stopPropagation();},false);
	newNode.firstChild.addEventListener('click',function(e){if(keyboardModifier=='alt'){flipMegaEditor(e,'show');}e.stopPropagation();},false);
			
	overlay.appendChild(newNode);
	setMeUp(newNode.children[1].children[1]);		// sending the dragregion in this newnode
	
	var editor=newNode.childNodes[2];
	
	if(newNode.firstChild.firstChild.clientHeight > 20){
		newNode.firstChild.style.height=newNode.firstChild.firstChild.clientHeight+'px';
		editor.style.top=(newNode.firstChild.firstChild.clientHeight + 12)+'px';
	}
	
	editor.childNodes[0].childNodes[1].value=title;
	editor.childNodes[1].innerHTML=nodeGroupSelector;
		if(editor.attributes.NG.nodeValue != 0){editor.childNodes[1].childNodes[1].value=editor.attributes.NG.nodeValue;}
		
	editor.addEventListener('click',function(e){e.stopPropagation();},false);			// clicking the editor itself should do nothing
 	editor.childNodes[2].addEventListener('click',function(e){flipMegaEditor(e,'hide');e.stopPropagation();},true);		// editor close button
 	editor.childNodes[3].addEventListener('click',function(e){compileUpdatesForMega(e);e.stopPropagation();},true);		// editor submit
	
	
}
function nukeNoob(e){
	console.log('nukeNoob');
	if(keyboardModifier=='shift'){
		var victim=e.target;												console.log('going to nuke '+victim.id+'?');
		var targetDR=victim.parentElement;	 
		targetDR.removeChild(victim);

		var origheight=targetDR.clientHeight;	 	//console.log('spawn sees '+targetDR.id+' with '+origheight);
		var newheight=origheight - draggableBaseHeight
		targetDR.style.height=newheight+'px';		//console.log('spawn adds '+draggableBaseHeight+' to height to get '+newheight);
	}
}

function setMeUp(dr){
	stretchDragRegion(dr);			//	console.log('SMU sees '+dr.id);
	dr.addEventListener('dragover',function(e){allowDrop(e);},true);dr.addEventListener('drop',function(e){dropItem(e);},true);
}

function stretchDragRegion(dr){
	var tempy=((dr.childNodes.length*draggableBaseHeight)+10)+'px';
	dr.style.height=tempy;  //console.log('set to '+tempy);
}
function stretchHeader(id){
	var tempy=document.getElementById(id);
	console.log('sH header text height = '+tempy.firstChild.firstChild.clientHeight);
}

function showChoice(e){e.target.parentNode.childNodes[1].style.display='block';}
function hideChoice(e){e.target.parentNode.childNodes[1].style.display='none';}
function flipDetails(e,f){
	var d=findDraggable(e.target);
	if(lastSeenDetails && lastSeenDetails != d.childNodes[1]){lastSeenDetails.style.display='none';lastSeenDetails=false;}
	if(lastSeenEditor){lastSeenEditor.style.display='none';lastSeenEditor=false;}		// always kill any editor showing
	var state=d.childNodes[1].style.display;		//	console.log('flip sees '+f+' and '+state);
	if(state=='' || state=='none' || f=='show'){d.childNodes[1].style.display='block';lastSeenDetails=d.childNodes[1];}
	if(state=='block' || f=='hide'){d.childNodes[1].style.display='none';lastSeenDetails=false;}
}
function flipEditor(e,f){			// data is populated on flip at present -- since there are a hundred of these with fake data, there's no need to wait for most of it (perhaps just the dropdowns)
	var d=findDraggable(e.target);
	var expanded=d.childNodes[1];			var ids=expanded.childNodes[4].innerText;			var firstspace=ids.indexOf(' ');
	var editor=d.childNodes[2]; 
	
	if(lastSeenDetails){lastSeenDetails.style.display='none';lastSeenDetails=false;}		// always kill any details showing
	if(lastSeenEditor && lastSeenEditor != d.childNodes[2]){lastSeenEditor.style.display='none';lastSeenEditor=false;}

		editor.childNodes[0].innerText='Editing Node '+ids.substring(firstspace);
		editor.childNodes[1].childNodes[1].value=editor.attributes.OT.nodeValue;			// title
		if(editor.attributes.EXV.nodeValue != "X"){editor.childNodes[2].childNodes[1].value=editor.attributes.EXV.nodeValue;}
		if(editor.attributes.EXI.nodeValue != "X"){editor.childNodes[3].childNodes[1].value=editor.attributes.EXI.nodeValue;}
	//	editor.childNodes[3].innerHTML=nodeGroupSelector;			// ==== only megas have node groups
	//		if(editor.attributes.NG.nodeValue != 0){editor.childNodes[3].childNodes[1].value=editor.attributes.NG.nodeValue;}
		editor.childNodes[4].innerHTML=choiceGroupSelector;
			if(editor.attributes.CG.nodeValue != 0){editor.childNodes[4].childNodes[1].value=editor.attributes.CG.nodeValue;}
		editor.childNodes[5].innerHTML=destinationSelector;
			if(editor.attributes.DEST.nodeValue != 0){editor.childNodes[5].childNodes[1].value=editor.attributes.DEST.nodeValue;}
		
		
	var state=editor.style.display;		//	console.log('flip sees '+f+' and '+state);
	if(state=='' || state=='none' || f=='show'){editor.style.display='block';lastSeenEditor=editor; return;}
	if(state=='block' || f=='hide'){editor.style.display='none';lastSeenEditor=false;}
}
function hideAllFlips(){
	if(lastSeenDetails){lastSeenDetails.style.display='none';lastSeenDetails=false;}
	if(lastSeenEditor){lastSeenEditor.style.display='none';lastSeenEditor=false;}
}
function flipMegaEditor(e,f){
	var d=findMega(e.target);
	var editor=d.childNodes[2];
	if(lastSeenMegaEditor && lastSeenMegaEditor != d.childNodes[2]){lastSeenMegaEditor.style.display='none';lastSeenMegaEditor=false;}
	
	var state=editor.style.display;		//	console.log('flip sees '+f+' and '+state);
	if(state=='' || state=='none' || f=='show'){editor.style.display='block';lastSeenMegaEditor=editor; return;}
	if(state=='block' || f=='hide'){editor.style.display='none';lastSeenMegaEditor=false;}
}

function compileUpdatesForNode(e){
	var d=findDraggable(e.target);			console.log('updating node for: '+d.id);
	var editor=d.childNodes[2];
	var mini=d.childNodes[0];
	var expanded=d.childNodes[1];
	var updateData='';
	var postUpdateRefresh=new Object();
	
	if(editor.attributes.OT.nodeValue != editor.childNodes[1].childNodes[1].value){
		console.log('    need to update OT from '+editor.attributes.OT.nodeValue+' to '+editor.childNodes[1].childNodes[1].value);
		updateData+='&ot='+editor.childNodes[1].childNodes[1].value;
		postUpdateRefresh['ot']=editor.childNodes[1].childNodes[1].value;
	}
	if(editor.attributes.EXV.nodeValue != editor.childNodes[2].childNodes[1].value){
		console.log('    need to update EXV from '+editor.attributes.EXV.nodeValue+' to '+editor.childNodes[2].childNodes[1].value);		// need to deal with value changed to null issue
		updateData+='&exv='+editor.childNodes[2].childNodes[1].value;
		postUpdateRefresh['exv']=editor.childNodes[2].childNodes[1].value;
	}
	if(editor.attributes.EXI.nodeValue != editor.childNodes[3].childNodes[1].value){
		console.log('    need to update EXI from '+editor.attributes.EXI.nodeValue+' to '+editor.childNodes[3].childNodes[1].value);
		updateData+='&exi='+editor.childNodes[3].childNodes[1].value;
		postUpdateRefresh['exi']=editor.childNodes[3].childNodes[1].value;
	}
	if(editor.attributes.CG.nodeValue != editor.childNodes[4].childNodes[1].value){
		console.log('    need to update CG from '+editor.attributes.CG.nodeValue+' to '+editor.childNodes[4].childNodes[1].value);
		updateData+='&cg='+editor.childNodes[4].childNodes[1].value;
		postUpdateRefresh['cg']=editor.childNodes[4].childNodes[1].value;
	}
	if(editor.attributes.DEST.nodeValue != editor.childNodes[5].childNodes[1].value){
		console.log('    need to update DEST from '+editor.attributes.DEST.nodeValue+' to '+editor.childNodes[5].childNodes[1].value);
		updateData+='&dest='+editor.childNodes[5].childNodes[1].value;
		postUpdateRefresh['dest']=editor.childNodes[5].childNodes[1].value;
	}
	
	// ===== put together an AJAX packet === send === on GOOD, update card(s) and hideAllFlips()
	// PathNodes has       node1 (mega) / choice (dragger) / node2 (dest) / choiceorder / choicegroup === and pathtype (only node 1) / nsubgroup (only 2 in node 1 and 2 in 128)
	// Nodes has              title / extra / aside / and nodegroup (megas)
	
	
	if(typeof(postUpdateRefresh['ot']) != 'undefined'){
		var tempy=postUpdateRefresh['ot'];	var lastSpace=0;		if(tempy.length > 54){			tempy=tempy.substring(0,54);			lastSpace=tempy.lastIndexOf(' ');			tempy=tempy.substring(0,lastSpace)+'...';		}
		
		mini.childNodes[2].innerText=tempy;		// includes the shorter ... version from above
		expanded.childNodes[0].innerText=postUpdateRefresh['ot'];
		editor.attributes.OT.nodeValue=postUpdateRefresh['ot'];		// the input doesnt need to update as it reflects what we just edited...  ;-)
	}
	if(typeof(postUpdateRefresh['exv']) != 'undefined'){	
		expanded.childNodes[1].innerText=postUpdateRefresh['exv'];
		editor.attributes.EXV.nodeValue=postUpdateRefresh['exv'];		// the input doesnt need to update as it reflects what we just edited...  ;-)
	}
	if(typeof(postUpdateRefresh['exi']) != 'undefined'){	
		expanded.childNodes[2].innerText=postUpdateRefresh['exi'];
		editor.attributes.EXI.nodeValue=postUpdateRefresh['exi'];		// the input doesnt need to update as it reflects what we just edited...  ;-)
	}	
	if(typeof(postUpdateRefresh['cg']) != 'undefined'){	
		mini.childNodes[0].innerText=choicegroupicons[postUpdateRefresh['cg']];		// ================================== NOT QUITE ACCURATE? ========== the value represents an ID, not a 1,2,3,4..A,B,C pattern
			mini.childNodes[1].innerText=choiceGroups[postUpdateRefresh['cg']];		// =========== see line 358 or thereabouts =============
		expanded.childNodes[2].innerText=postUpdateRefresh['cg'];
		editor.attributes.CG.nodeValue=postUpdateRefresh['cg'];		// the input doesnt need to update as it reflects what we just edited...  ;-)
	}
	if(typeof(postUpdateRefresh['dest']) != 'undefined'){	
		mini.childNodes[3].innerText=postUpdateRefresh['dest'];										// ====================== need to update the color / number of these
			mini.childNodes[3].attributes.nextid.nodeValue=postUpdateRefresh['dest'];		// ====================== need to update the color / number of these
			mini.childNodes[3].innerText=megaData[postUpdateRefresh['dest']]['cn'];							// set color number
			mini.childNodes[3].className='nexttext next'+megaData[postUpdateRefresh['dest']]['c'];		// set color
		expanded.childNodes[6].innerText=megaData[postUpdateRefresh['dest']]['t'];
			expanded.childNodes[6].className='destination next'+megaData[postUpdateRefresh['dest']]['c'];		// set color
		editor.attributes.DEST.nodeValue=postUpdateRefresh['dest'];		// the input doesnt need to update as it reflects what we just edited...  ;-)
	}
}

function compileUpdatesForMega(e){
	var d=findMega(e.target);			console.log('updating MEGA for: '+d.id);
	var editor=d.childNodes[2];
	var mini=d.childNodes[0];
	var expanded=d.childNodes[1];
	var updateData='';
	var postUpdateRefresh=new Object();
	
	if(editor.attributes.OT.nodeValue != editor.childNodes[0].childNodes[1].value){
		console.log('    need to update OT from '+editor.attributes.OT.nodeValue+' to '+editor.childNodes[1].childNodes[1].value);
		updateData+='&ot='+editor.childNodes[0].childNodes[1].value;
		postUpdateRefresh['ot']=editor.childNodes[0].childNodes[1].value;
		
		// =========== NOW WE NEED TO FIND ANY NODES WITH THIS AS A DEST AND UPDATE THEIR EXPANDED TEXT
		
	}
	if(editor.attributes.NG.nodeValue != editor.childNodes[1].childNodes[1].value){
		console.log('    need to update NG from '+editor.attributes.NG.nodeValue+' to '+editor.childNodes[2].childNodes[1].value);		// need to deal with value changed to null issue
		updateData+='&ng='+editor.childNodes[1].childNodes[1].value;
		postUpdateRefresh['ng']=editor.childNodes[1].childNodes[1].value;
	}


}


var megaColorCounts=new Object();			megaColorCounts['green']=0;    megaColorCounts['yellow']=0;    megaColorCounts['blue']=0;    megaColorCounts['red']=0;    megaColorCounts['aqua']=0;    megaColorCounts['purple']=0;    megaColorCounts['gray']=0;     megaColorCounts['terminate']=0;    
var megaData=new Object();						megaData['terminate']=new Object();    megaData['terminate']['c']='terminate';    megaData['terminate']['cn']='X';

function updateMegaColors(){
	megaColorCounts['green']=0;    megaColorCounts['yellow']=0;    megaColorCounts['blue']=0;    megaColorCounts['red']=0;    megaColorCounts['aqua']=0;    megaColorCounts['purple']=0;    megaColorCounts['gray']=0;     megaColorCounts['terminate']=0;    
	var megas=document.getElementsByClassName('platonicMega');
	if(megas){
		for(e=0;e<megas.length;e++){
			if(megas[e].id != 'platonicMega'){
				megaColorCounts[megas[e].attributes.color.nodeValue]++;
				megas[e].attributes.colornumber.nodeValue=megaColorCounts[megas[e].attributes.color.nodeValue];		// store in element
				if(typeof(nextid) == 'undefined'){megaData[megas[e].id]=new Object();}
				megaData[megas[e].id]['c']=megas[e].attributes.color.nodeValue;			console.log('loading data for '+megas[e].id+' as '+megas[e].attributes.color.nodeValue);
				megaData[megas[e].id]['cn']=megaColorCounts[megas[e].attributes.color.nodeValue];								// store in variable for below
				
				megas[e].firstChild.lastChild.innerText=megaData[megas[e].id]['cn'];
			}
		}
	}
	var items=document.getElementsByClassName('platonicDraggable');
	if(items){
		for(e=0;e<items.length;e++){
			if(items[e].id != 'platonicDraggable'){
				var nextid=items[e].childNodes[0].lastChild.attributes.nextid.nodeValue;
				if(typeof(nextid) != 'undefined' && nextid != 0){			console.log(e+' has '+nextid);
			//		items[e].lastChild.lastChild.className='nexthover next'+megaData[nextid]['c'];		// set color
			//		items[e].lastChild.lastChild.innerText=megaData[nextid]['cn'];							// set color number
					
					items[e].childNodes[0].childNodes[3].className='nexttext next'+megaData[nextid]['c'];		// set color
					items[e].childNodes[0].childNodes[3].innerText=megaData[nextid]['cn'];							// set color number
					
					items[e].childNodes[1].childNodes[6].className='destination next'+megaData[nextid]['c'];		// set color
				}
			}
		}
	}
}






// NEED A LOADING AJAX CALL TO FETCH NODEGROUPS AND CHOICE GROUPS TO POPULATE SELECT BOXES FOR THE EDITORS
// THEN USE THE POPUP / SHOW CODE TO ADJUST THE CONTENT OF THE EDITABLE ITEMS AND ADD THE SELECTS
// ======== this is already part of the initial TPID ajax call ======nodeGroups (id, derorder, humanname, parent) and choicegroups (id / name) populated =========
// 
// NEED ANOTHER AJAX TO SEND THE CHANGES BACK AND 'UPDATE' THE CARD
// 
// LASTLY NEED A WAY TO CHANGE THE DESTINATION -- BEST IS PROBABLY SELECT OF MEGAS -- color number coded











//function objectHasData(a){for (x in a) {return true;} return false;}

function nextColor(){
	var tempy=colorList[currentColorIndex];		currentColorIndex++;  if(currentColorIndex==6){currentColorIndex=0;}
	return tempy;
}

function elRando(){var tempy=(Math.random() * Math.pow(2, 54)).toString(36).slice(2)+(Math.random() * Math.pow(2, 54)).toString(36).slice(2)+(Math.random() * Math.pow(2, 54)).toString(36).slice(2)+(Math.random() * Math.pow(2, 54)).toString(36).slice(2)+(Math.random() * Math.pow(2, 54)).toString(36).slice(2)+(Math.random() * Math.pow(2, 54)).toString(36).slice(2)+(Math.random() * Math.pow(2, 54)).toString(36).slice(2)+(Math.random() * Math.pow(2, 54)).toString(36).slice(2);		return tempy.substring(4,36);}
function getPosition(element) {
		var xPosition = 0;    var yPosition = 0;
		while(element) {
			xPosition += (element.offsetLeft - element.scrollLeft + element.clientLeft);
			yPosition += (element.offsetTop - element.scrollTop + element.clientTop);
			element = element.offsetParent;
		}
		return { x: xPosition, y: yPosition };
	}
function doKeyDown(evt){
	if(evt.keyCode==16){keyboardModifier='shift';}
	if(evt.keyCode==18){keyboardModifier='alt';}		// cntrl is used by the browser -- leave it be
	if(evt.keyCode==27){keyboardModifier='esc';}
}
function doKeyUp(evt){
	keyboardModifier=null;
}



