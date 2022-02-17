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
var nodeGroupSelector='<p>Select a Node Group</p><select class="nodeGroupSelector" id="nGS"><option value="0"></option>';
var choiceGroupSelector='<p>Select a Choice Group</p><select class="choiceGroupSelector" id="cGS"><option value="0"></option>';
var parentSelector='<p>Select a Parent Node</p><select class="parentSelector" id="pS"><option value="0"></option>';
var destinationSelector='<p>Select a Destination</p><select class="destinationSelector" id="dS"><option value="X"></option><option value="X">Event Terminates</option>';
var codeSelectors=new Object();
var codeGroupSelector='<p>Select a Code Group</p><select class="codeGroupSelector" id="cGS2"><option value="0"></option>';

var colorList=new Array('green','yellow','red','blue','aqua','purple');
var currentColorIndex=0;
var nodeGroupOrdinals=new Object();
var choiceGroupOrdinals=new Object();
var choicegroupicons=new Object();
var spawnedFirstMega=false;
var nodragging=false;
var arrowrotation=0;


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
	overlay2.style.display='block';
	setTimeout(function(){hideOverlays(2)},3000);
}
function sendMessage(head,msg){
	document.getElementById('marks-overhead3').innerHTML=head;
	document.getElementById('dermsg').innerHTML=msg;
	overlay3.style.display='block';
	setTimeout(function(){hideOverlays(3)},3000);
}

//	spawnMega('pm1','This is a title','green');			// == id, title, color
//		spawnNoob('ignored_if_2nd_item_exists','pm1',1224,'Poof!  Im here!',1,'pm2');

function goGetEm(tpid){
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){ 										//console.log(data);
			var d=JSON.parse(data);
			document.getElementById('title').innerText='Trailblazer v0.9: Editing Path ID '+loadedPID+' ('+d['path']+')';
			for(e=0;e<d['nodeGroups'].length;e++){nodeGroupOrdinals[d['nodeGroups'][e]['t_id']]=e+1; nodeGroups[d['nodeGroups'][e]['t_id']]=d['nodeGroups'][e]['t_label'];nodeGroupSelector+="<option value='"+d['nodeGroups'][e]['t_id']+"'>"+d['nodeGroups'][e]['t_label']+"</option>";}    nodeGroupSelector+="</select>";
			for(e=0;e<d['choiceGroups'].length;e++){
				choiceGroupOrdinals[d['choiceGroups'][e]['t_id']]=e+1; choiceGroups[d['choiceGroups'][e]['t_id']]=d['choiceGroups'][e]['t_name'];choiceGroupSelector+="<option value='"+d['choiceGroups'][e]['t_id']+"'>"+d['choiceGroups'][e]['t_name']+"</option>";
				if(choiceGroupOrdinals[d['choiceGroups'][e]['t_id']]>9){choicegroupicon=(parseInt(choiceGroupOrdinals[d['choiceGroups'][e]['t_id']],10)).toString(36).toUpperCase();}else{choicegroupicon=choiceGroupOrdinals[d['choiceGroups'][e]['t_id']];}			// choicegroupicon is the 1-9A-Z on the left of the draggable -- we use ordinals for situations where the lowest ID is12 or something...
					choicegroupicons[d['choiceGroups'][e]['t_id']]=choicegroupicon;
			}    choiceGroupSelector+="</select>";
			
			var destinations=new Object();
			var sortedCodeGroups=new Array();
			for(e=0;e<d['nodes'].length;e++){	destinations[d['nodes'][e]['t_megaid']]=d['nodes'][e]['title'];sortedCodeGroups.push(d['t_codegroup']);}		// preprocess megas to get title info for nodes destinations			
 			sortedCodeGroups.sort(function(a, b){return a-b}); 			for(q=1;q<sortedCodeGroups.length;q++){codeGroupSelector+='<option value="'+q+'">'+q+'</option>';}

			for(e=0;e<d['nodes'].length;e++){		// ================== these are megas
				var n=d['nodes'][e];		var nextbit;
				var dercolor=nextColor();
				console.log('autospawning mega with: '+'mega_'+n['t_megaid']+' / '+n['title']+' / '+dercolor+' / '+n['t_codegroup']);
				spawnMega('mega_'+n['t_megaid'],n['title'],n['ng'],n['t_codegroup'],dercolor);
				codeSelectors[n['t_codegroup']]='<p>Select a Code</p><select class="codeSelector" id="cS"><option value="0"></option>';
				sortedCodes=new Array();
				
				for(var cg=0;cg<n['branches'].length;cg++){		// ============== these are choices in a mega -- this layer is for each choice group (at least one)
					for(var i=0;i<n['branches'][cg].length;i++){		// ============== here we get to actual choices
						var ch=n['branches'][cg][i];
						var extrabits=new Object();
						if(ch['t_nextid']=='X' || ch['t_nextid']==''){
							nextbit='X';
							extrabits['destination']='Path Terminates';
						}else{
							nextbit='mega_'+ch['t_nextid'];
							extrabits['destination']=destinations[ch['t_nextid']];
						}
						extrabits['nodeids']=ch['t_thisid']+' / '+ch['branch_id'];
						if(typeof(ch['extra']) != 'undefined'){extrabits['extravar']=ch['extra'];}
						if(typeof(ch['aside']) != 'undefined'){extrabits['aside']=ch['aside'];}
						if(typeof(ch['t_inactive']) != 'undefined'){extrabits['t_inactive']=true;}		// shouldnt get this one
						
						if(ch['t_code'] != ''){sortedCodes.push(ch['t_code']);}
						
					//	console.log('      autospawning with: '+ch['t_parentid']+' / '+ch['t_thisid']+' / '+ch['description']+' / '+ch['t_choicegroup']+' / '+nextbit+' //// '+extrabits['nodeids']);
						spawnNoob('ignored_if_2nd_item_exists','mega_'+ch['t_parentid'],ch['t_thisid'],ch['description'],ch['t_code'],ch['t_choicegroup'],nextbit,extrabits);
					}
				}
				sortedCodes.sort(function(a, b){return a-b});
				for(q=0;q<sortedCodes.length;q++){codeSelectors[n['t_codegroup']]+='<option value="'+sortedCodes[q]+'">'+sortedCodes[q]+'</option>';}
			}
			



			
			// ====== NOW WE PROCESS ANY UNLINKED MEGAS ================
			if(typeof(d['unlinkedmegas'])!='undefined'){
					for(e=0;e<d['unlinkedmegas'].length;e++){	destinations[d['unlinkedmegas'][e]['t_megaid']]=d['unlinkedmegas'][e]['title'];}
					for(e=0;e<d['unlinkedmegas'].length;e++){		// ================== these are megas
						var n=d['unlinkedmegas'][e];		var nextbit;
						var dercolor='gray';
						console.log('autospawning unlinked '+e+' mega with: '+'mega_'+n['t_megaid']+' / '+n['title']+' / '+dercolor);
						spawnMega('mega_'+n['t_megaid'],n['title'],n['ng'],dercolor);
				
						// ====== these usually dont have child nodes, but might =======
						if(typeof(n['branches'])!='undefined'){
							for(var cg=0;cg<n['branches'].length;cg++){		// ============== these are choices in a mega -- this layer is for each choice group (at least one)
								for(var i=0;i<n['branches'][cg].length;i++){		// ============== here we get to actual choices
									var ch=n['branches'][cg][i];
									var extrabits=new Object();
									if(ch['t_nextid']=='X' || ch['t_nextid']==''){
										nextbit='X';
										extrabits['destination']='Path Terminates';
									}else{
										nextbit='mega_'+ch['t_nextid'];
										extrabits['destination']=destinations[ch['t_nextid']];
									}
									extrabits['nodeids']=ch['t_thisid']+' / '+ch['branch_id'];
									if(typeof(ch['extra']) != 'undefined'){extrabits['extravar']=ch['extra'];}
									if(typeof(ch['aside']) != 'undefined'){extrabits['aside']=ch['aside'];}
									if(typeof(ch['t_inactive']) != 'undefined'){extrabits['t_inactive']=true;}		// shouldnt get this one
						
								//	console.log('      autospawning with: '+ch['t_parentid']+' / '+ch['t_thisid']+' / '+ch['description']+' / '+ch['t_choicegroup']+' / '+nextbit+' //// '+extrabits['nodeids']);
									spawnNoob('ignored_if_2nd_item_exists','mega_'+ch['t_parentid'],ch['t_thisid'],ch['description'],ch['t_choicegroup'],nextbit,extrabits);
								}
							}
						}
					}
			} // end unlinked check
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
	if(nodragging==true){console.log('Dragging blocked due to open editor / detail viewer');return;}
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
	if(nodragging==true){console.log('Dragging blocked due to open editor / detail viewer');return;}
	var targetDR=findTheDR(e.target);		console.log('drop sees '+targetDR.id);
	var m=findMega(e.target);		var underscore=m.id.lastIndexOf('_');    var megaid=m.id.substring(underscore+1);
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
	var data4update=new Object();

	for(var n=0; n<vrdr.childNodes.length; n++){
		var baseid=vrdr.childNodes[n].id;
		var orderEncountered=n+1;
		// ===== OKAY -- the order attribute is being shifted during the drag process -- the value it finds is actually the new one we want
		var neworder=vrdr.childNodes[n].attributes.iihs_order.nodeValue;		
		if(orderEncountered != neworder){
			data4update[baseid]=neworder;				console.log('drop says for '+baseid+' / '+orderEncountered+' to '+neworder+' within mega '+megaid);
		}else{
			//console.log('drop says '+baseid+' needs no change');
		}
	}
	
	// =========== now we need a small AJAX to send the updates to the DB ===========
	var sendStr='op=1&mega='+megaid;
	if(Object.keys(data4update).length > 0){
		Object.keys(data4update).forEach(function(key,index) {sendStr+='&node'+key+'='+data4update[key];});		//console.log(sendStr);
	}
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
			console.log('d='+data);
		}
	}
	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			console.log(url);
	xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
	
	
	
	
}
function stopDrag(e){		// this only cleans up if the item is dropped outside of the droppable area ===================
	if(nodragging==true){console.log('Dragging blocked due to open editor / detail viewer');return;}
	var targetDR=e.target.parentNode;		//  console.log('stop sees '+targetDR.id);
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
//	console.log('stopM sees '+draggedMega.id+' at '+e.clientX+' and '+e.clientY);
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


function spawnNoob(e,tDR,id,text,code,choicegroup,nextmega,extrainfo){
	var superSpawn=false;
	if(typeof(tDR) != 'undefined'){		//console.log('defined tDR');
		var targetDR=document.getElementById(tDR).children[1].children[1];		// this is the mega's dragregion
	}else{
		var dermega=findMega(e.target);
		var targetDR=dermega.children[1].children[1];
		superSpawn=true;
	}
	var origheight=targetDR.clientHeight;	 //	console.log('spawn sees '+targetDR.id+' with '+origheight);
	var newheight=origheight+draggableBaseHeight
	targetDR.style.height=newheight+'px';		//console.log('spawn adds '+draggableBaseHeight+' to height to get '+newheight);
	var newNode=platonicDraggable.cloneNode(true);
		newNode.style.top=origheight+'px';
	
	var lastorder=0;
	if(targetDR.lastChild){lastorder=parseInt(targetDR.lastChild.attributes.iihs_order.nodeValue);}		// its possible that there are no draggables
	newNode.setAttribute('iihs_order',(lastorder+1));
	//	var bill=elRando();
	newNode.setAttribute('id',id);			//console.log('using '+id+' for id');
	
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
	
	if(typeof(extrainfo['t_inactive']) != 'undefined'){
		var tempy2=newNode.childNodes[0].childNodes[2].innerText.substring(0,46);
		newNode.childNodes[0].childNodes[2].innerHTML='<span style="font-size: 14px; color: #d20;">=(!!!)=</span>  '+tempy2;
	//	newNode.childNodes[1].childNodes[0].innerText='==== INACTIVE NODE ====  '+newNode.childNodes[1].childNodes[0].innerText;
		newNode.childNodes[1].childNodes[6].innerText='==== INACTIVE NODE ====<br />This node is marked as deleted, but is attached to this Mega'
	}
	
	// ======= choicegrup info ========
	if(typeof(choicegroup) != 'undefined'){
		if(choicegroup==0){
			newNode.childNodes[0].childNodes[0].innerText='';
			newNode.childNodes[1].childNodes[4].style.display='none';			// expanded
		}else{
//			if(choiceGroupOrdinals[choicegroup]>9){choicegroupicon=(parseInt(choiceGroupOrdinals[choicegroup],10)).toString(36).toUpperCase();}else{choicegroupicon=choiceGroupOrdinals[choicegroup];}			// choicegroupicon is the 1-9A-Z on the left of the draggable -- we use ordinals for situations where the lowest ID is12 or something...
//				choicegroupicons[choicegroup]=choicegroupicon;			//console.log('cg assign: '+choicegroup+' is '+choicegroupicon);
			newNode.childNodes[0].childNodes[0].innerText=choicegroupicons[choicegroup];
			newNode.childNodes[0].childNodes[1].innerText=choiceGroups[choicegroup];
			newNode.childNodes[1].childNodes[4].innerText='ChoiceGroup(s): '+choiceGroups[choicegroup];		// expanded
			newNode.childNodes[2].attributes.CG.nodeValue=choicegroup;	// editor
		}
	}
	newNode.childNodes[2].attributes.CD.nodeValue=code;		// CODE selector ammo
	// ======= destination info ========
	if(typeof(nextmega) != 'undefined'){
		newNode.childNodes[0].lastChild.attributes.nextid.nodeValue=nextmega;
		newNode.childNodes[1].childNodes[7].innerText='Destination: '+extrainfo['destination'];
		newNode.childNodes[2].attributes.DEST.nodeValue=nextmega;		// editor
	}
	
	if(typeof(extrainfo['extravar']) != 'undefined'){newNode.childNodes[1].childNodes[1].innerText='Extra Var: '+extrainfo['extravar'];}else{newNode.childNodes[1].childNodes[1].style.display='none';	}
	if(typeof(extrainfo['aside']) != 'undefined'){newNode.childNodes[1].childNodes[2].innerText='Extra Instructions: '+extrainfo['aside'];}else{newNode.childNodes[1].childNodes[2].style.display='none';	}

// 	if(typeof(extrainfo['nodegroup']) != 'undefined'){				// ======= only megas have node groups ========
// 		newNode.childNodes[1].childNodes[2].innerText='NodeGroup: '+extrainfo[''];}else{newNode.childNodes[1].childNodes[2].style.display='none';
// 		newNode.childNodes[2].attributes.NG.nodeValue=extrainfo['nodegroup'];		// editor
// 	}
	newNode.childNodes[1].childNodes[5].innerText='NodeID(s): '+extrainfo['nodeids'];

 	newNode.addEventListener('dragstart',function(e){pickUp(e);e.stopPropagation();},true);
 	newNode.addEventListener('dragend',function(e){stopDrag(e);e.stopPropagation();},true);
 	newNode.addEventListener('dblclick',function(e){nukeNoob(e);e.stopPropagation();},true);				//https://css-tricks.com/snippets/javascript/bind-different-events-to-click-and-double-click/
 	newNode.addEventListener('click',function(e){if(keyboardModifier=='alt'){flipEditor(e,'flip');}else{if(keyboardModifier !='esc'){flipDetails(e,'flip');}}e.stopPropagation();},false);	
 	
 	newNode.childNodes[0].childNodes[0].addEventListener('mouseover',function(e){showChoice(e);},true);
 	newNode.childNodes[0].childNodes[0].addEventListener('mouseout',function(e){hideChoice(e);},true);
 	
 	newNode.childNodes[2].addEventListener('click',function(e){e.stopPropagation();},false);			// clicking the editor itself should do nothing
 	newNode.childNodes[2].childNodes[7].addEventListener('click',function(e){flipEditor(e,'hide');e.stopPropagation();},true);		// editor close button
 	newNode.childNodes[2].childNodes[8].addEventListener('click',function(e){compileUpdatesForNode(e);flipEditor(e,'hide');e.stopPropagation();},true);		// editor submit
 	
 	if(superSpawn){
		newNode.childNodes[0].childNodes[3].className='nexttext next'+megaData[nextmega]['c'];		// set color
		newNode.childNodes[0].childNodes[3].innerText=megaData[nextmega]['cn'];							// set color number		
		newNode.childNodes[1].childNodes[7].className='destination next'+megaData[nextmega]['c'];		// set color
	}
 	
	targetDR.appendChild(newNode);
}
function spawnMega(id,title,ng,codegroup,color){
//	console.log('Mspawn sees '+id);
	var newNode=platonicMega.cloneNode(true);
	newNode.style.top='80px';
	newNode.style.left=nextleftpx+'px';					nextleftpx+=80;
	newNode.setAttribute('id',id);
	newNode.children[1].childNodes[1].setAttribute('id','dragregion_'+id);
	
	newNode.childNodes[2].attributes.OT.nodeValue=title;
	newNode.childNodes[2].attributes.NG.nodeValue=ng;
	newNode.childNodes[2].attributes.CG.nodeValue=codegroup;
	
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
	
	newNode.addEventListener('dragstart',function(e){pickUpMega(e);e.stopPropagation();},false);
	newNode.addEventListener('dragend',function(e){stopDragMega(e);e.stopPropagation();},false);
	newNode.firstChild.addEventListener('dblclick',function(e){if(keyboardModifier=='shift'){superSpawnNoob(e);}else{if(keyboardModifier=='esc'){nukeMega(e);}}e.stopPropagation();},false);		//
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
	editor.childNodes[2].innerHTML=codeGroupSelector;
		if(editor.attributes.CG.nodeValue != 0){editor.childNodes[2].childNodes[1].value=editor.attributes.CG.nodeValue;}
		
	editor.addEventListener('click',function(e){e.stopPropagation();},false);			// clicking the editor itself should do nothing
 	editor.childNodes[3].addEventListener('click',function(e){flipMegaEditor(e,'hide');e.stopPropagation();},true);		// editor close button
 	editor.childNodes[4].addEventListener('click',function(e){compileUpdatesForMega(e);flipMegaEditor(e,'hide');e.stopPropagation();},true);		// editor submit
	
	// =========== now we need a small AJAX to send the updates to the DB ===========	ONLY NEED THIS IF SPAWNING NEW NODE --- move this to super spawner tool
// 	var sendStr='op=4&mega='+megaid;
// 	sendStr+='&node'+key+'='+data4update[key];});		console.log(sendStr);
// 	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
// 	xmlHttp.onreadystatechange = function() {
// 		var data=getHTML(xmlHttp);
// 		if(data){
// 		
// 		}
// 	}
// 	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			//console.log(url);
// 	xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
	
}
function nukeNoob(e){
//	console.log('nukeNoob');			// double-clicking also triggers edit node
	if(keyboardModifier=='esc'){
		var victim=findDraggable(e.target);											console.log('going to nuke '+victim.id+'?');
		var d=findMega(e.target);			var underscore=d.id.lastIndexOf('_');    var megaid=d.id.substring(underscore+1);
		
		var sendStr='op=6&mega='+megaid+'&victim='+victim.id;					console.log(sendStr);
			var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
			xmlHttp.onreadystatechange = function() {
				var data=getHTML(xmlHttp);
				if(data){													console.log(data);
					var bits=data.split('|');
					if(bits[0]=='A'){
						var bits2=bits[1].split('_');
						if(typeof(bits2[1]) != 'undefined'){				// we're removing a node from a mega
							var targetDR=victim.parentElement;	 
							targetDR.removeChild(victim);
								var origheight=targetDR.clientHeight;	 	//console.log('spawn sees '+targetDR.id+' with '+origheight);
								var newheight=origheight - draggableBaseHeight
							targetDR.style.height=newheight+'px';		//console.log('spawn adds '+draggableBaseHeight+' to height to get '+newheight);						
						}else{														// we're removing a mega
							
						}
					}else{
						throwError('Failed to nuke Node: '+bits[1]);
					}
				}
			}
			sendStr+='&loadedpid='+loadedPID;
			var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			//console.log(url);
			xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
	}
}
function superSpawnNoob(e){		console.log('sSN');
	if(keyboardModifier=='shift'){
		// need to create a new node in theDB and feed spawnNode (maybe move lines and bits around 345-350)
		var d=findMega(e.target);			var underscore=d.id.lastIndexOf('_');    var megaid=d.id.substring(underscore+1);			var tDR;
		var sendStr='op=4&mega='+megaid;
			var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
			xmlHttp.onreadystatechange = function() {
				var data=getHTML(xmlHttp);
				if(data){													console.log(data+'X');
					var bits=data.split('|');
					if(bits[0]=='A'){
						text='New Node';		id=bits[1];		nextmega='X';			choicegroup=0;
						var extrainfo=new Object();		extrainfo['nodeids']=id;		extrainfo['destination']='Path Terminates';
						spawnNoob(e,tDR,id,text,choicegroup,nextmega,extrainfo);			// we have specifically not defined tDR here -- spawnNoob knows what to do
					}else{
						throwError('Failed to create new Node: '+bits[1]);
					}
				}
			}
			sendStr+='&loadedpid='+loadedPID;
			var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			//console.log(url);
			xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);

	}
}
function superSpawnMega(e){		console.log('sSM');
	if(keyboardModifier=='shift'){
			// need to create a new node in theDB and feed spawnMega
			var sendStr='op=5';
			var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
			xmlHttp.onreadystatechange = function() {
				var data=getHTML(xmlHttp);
				if(data){													console.log(data);
					var bits=data.split('|');
					if(bits[0]=='A'){
						var title='New Mega Node';		var id=bits[1];		var ng=0;		var color='gray';
						spawnMega(id,title,ng,color);
					}else{
						throwError('Failed to create new Mega: '+bits[1]);
					}
				}
			}
			sendStr+='&loadedpid='+loadedPID;
			var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			console.log(url);
			xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
	}
}
function nukeMega(e){
	console.log('nukeMega');
	if(keyboardModifier=='esc'){
		var d=findMega(e.target);			var underscore=d.id.lastIndexOf('_');    var victim=d.id.substring(underscore+1);		console.log('going to nuke '+victim+'?');
		
		var sendStr='op=7&mega='+victim;					//console.log(sendStr);
			var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
			xmlHttp.onreadystatechange = function() {
				var data=getHTML(xmlHttp);
				if(data){													console.log(data);
					var bits=data.split('|');
					if(bits[0]=='A'){
// 						var bits2=bits[1].split('_');
// 						if(typeof(bits2[1]) != 'undefined'){				// we're removing a node from a mega
// 							var targetDR=victim.parentElement;	 
// 							targetDR.removeChild(victim);
// 								var origheight=targetDR.clientHeight;	 	//console.log('spawn sees '+targetDR.id+' with '+origheight);
// 								var newheight=origheight - draggableBaseHeight
// 							targetDR.style.height=newheight+'px';		//console.log('spawn adds '+draggableBaseHeight+' to height to get '+newheight);						
						}else{														// we're removing a mega
							
						}
					}else{
//						throwError('Failed to nuke Mega: '+bits[1]);
					}
				}
			var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			console.log(url);
			xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
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
function flipDetails(e,f,g){
	if(e){var ee=e.target;}else{var ee=g;}
	var d=findDraggable(ee);				//var m=findMega(ee);			//console.log('fD sees '+d.id);
//	if(lastSeenDetails && lastSeenDetails != d.childNodes[1]){lastSeenDetails.style.display='none';lastSeenDetails=false;}
	if(lastSeenDetails && lastSeenDetails != d){flipDetails(false,'hide',lastSeenDetails);}
	
//	if(lastSeenEditor){lastSeenEditor.style.display='none';lastSeenEditor=false;}		// always kill any editor showing
	if(lastSeenEditor){flipEditor(false,'hide',lastSeenEditor);}		// always kill any editor showing

	var state=d.childNodes[1].style.display;
	if(state=='' || state=='none' || f=='show'){d.childNodes[1].style.display='block';lastSeenDetails=d;			//lastSeenDetails=d.childNodes[1];
		nodragging=true;d.attributes.draggable.nodeValue='false'; return;}				// we dont disable the mega here because it's unlikely to be an issue -- dragging an open node is bad, dragging a mega with an open node is unfortunate, but acceptable
	if(state=='block' || f=='hide'){d.childNodes[1].style.display='none';lastSeenDetails=false;
		nodragging=false; d.attributes.draggable.nodeValue='true';}
}
function flipEditor(e,f,g){			// data is populated on flip at present -- since there are a hundred of these with fake data, there's no need to wait for most of it (perhaps just the dropdowns)
	if(e){var ee=e.target;}else{var ee=g;}
	var d=findDraggable(ee);				var m=findMega(ee);	
	var expanded=d.childNodes[1];			var ids=expanded.childNodes[5].innerText;			var firstspace=ids.indexOf(' ');
	var editor=d.childNodes[2]; 
	
	
	if(lastSeenDetails && lastSeenDetails != d){flipDetails(false,'hide',lastSeenDetails);}		// always kill any details showing
	if(lastSeenEditor && lastSeenEditor != d){flipEditor(false,'hide',lastSeenEditor);}		// always kill any editor showing
	
	var state=editor.style.display;		//	console.log('flip sees '+f+' and '+state);
	if(state=='' || state=='none' || f=='show'){

		editor.childNodes[0].innerText='Editing Node '+ids.substring(firstspace);
		editor.childNodes[1].childNodes[1].value=editor.attributes.OT.nodeValue;			// title
		if(editor.attributes.EXV.nodeValue != "X"){editor.childNodes[2].childNodes[1].value=editor.attributes.EXV.nodeValue;}
		if(editor.attributes.EXI.nodeValue != "X"){editor.childNodes[3].childNodes[1].value=editor.attributes.EXI.nodeValue;}
	//	editor.childNodes[3].innerHTML=nodeGroupSelector;			// ==== only megas have node groups
	//		if(editor.attributes.NG.nodeValue != 0){editor.childNodes[3].childNodes[1].value=editor.attributes.NG.nodeValue;}
	
		var parentCode=m.childNodes[2].attributes.CG.nodeValue;		// get the CG from the megaeditor
		editor.childNodes[4].innerHTML=codeSelectors[parentCode];
			if(editor.attributes.CD.nodeValue != 0){editor.childNodes[4].childNodes[1].value=editor.attributes.CD.nodeValue;}
		
		editor.childNodes[5].innerHTML=choiceGroupSelector;
			if(editor.attributes.CG.nodeValue != 0){editor.childNodes[5].childNodes[1].value=editor.attributes.CG.nodeValue;}
		editor.childNodes[6].innerHTML=destinationSelector;
			if(editor.attributes.DEST.nodeValue != 0){editor.childNodes[6].childNodes[1].value=editor.attributes.DEST.nodeValue;}
		
		editor.style.display='block';lastSeenEditor=d; nodragging=true; d.attributes.draggable.nodeValue='false'; m.attributes.draggable.nodeValue='false'; return;
	}
		
	if(state=='block' || f=='hide'){editor.style.display='none';lastSeenEditor=false;
		nodragging=false; d.attributes.draggable.nodeValue='true'; m.attributes.draggable.nodeValue='true';}
}
function flipMegaEditor(e,f,g){
	if(e){var ee=e.target;}else{var ee=g;}
	var d=findMega(ee);
	var editor=d.childNodes[2];
	if(lastSeenMegaEditor && lastSeenMegaEditor != d){flipMegaEditorfalse,'hide',(lastSeenMegaEditor);}
	
	var state=editor.style.display;		//	console.log('flip sees '+f+' and '+state);
	if(state=='' || state=='none' || f=='show'){editor.style.display='block';lastSeenMegaEditor=d; 
		nodragging=true; d.attributes.draggable.nodeValue='false'; return;}		// we dont manage draggability of nodes here as the inputs are not overlapping any nodes -- if we have a huge editor, we'll have to revise this
	if(state=='block' || f=='hide'){editor.style.display='none';lastSeenMegaEditor=false;
		nodragging=false; d.attributes.draggable.nodeValue='true'; }
}
function hideAllFlips(){
// 	if(lastSeenDetails){lastSeenDetails.style.display='none';lastSeenDetails=false;}
// 	if(lastSeenEditor){lastSeenEditor.style.display='none';lastSeenEditor=false;}
// 	if(lastSeenMegaEditor){lastSeenMegaEditor.style.display='none';lastSeenMegaEditor=false;}
	if(lastSeenDetails){flipDetails(false,'hide',lastSeenDetails);}
	if(lastSeenEditor){flipEditor(false,'hide',lastSeenEditor);}
	if(lastSeenMegaEditor){flipMegaEditor(false,'hide',lastSeenMegaEditor);}		// the normal E received is an event -- we're passing the target of the earlier event
}


function compileUpdatesForNode(e){
	var d=findDraggable(e.target);				var m=findMega(e.target);			
		var underscore=m.id.lastIndexOf('_');  var megaid=m.id.substring(underscore+1);		//console.log('updating node for: '+d.id+' / m'+megaid);
	var editor=d.childNodes[2];
	var mini=d.childNodes[0];
	var expanded=d.childNodes[1];
	var updateData='op=2&node='+d.id+'&mega='+megaid+'&loadedpid='+loadedPID;
	var postUpdateRefresh=new Object();
	
	if(editor.attributes.OT.nodeValue != editor.childNodes[1].childNodes[1].value){
//		console.log('    need to update OT from '+editor.attributes.OT.nodeValue+' to '+editor.childNodes[1].childNodes[1].value);
		updateData+='&ot='+editor.childNodes[1].childNodes[1].value;
		postUpdateRefresh['ot']=editor.childNodes[1].childNodes[1].value;
	}
	if(editor.attributes.EXV.nodeValue != editor.childNodes[2].childNodes[1].value){
//		console.log('    need to update EXV from '+editor.attributes.EXV.nodeValue+' to '+editor.childNodes[2].childNodes[1].value);		// need to deal with value changed to null issue
		updateData+='&exv='+editor.childNodes[2].childNodes[1].value;
		postUpdateRefresh['exv']=editor.childNodes[2].childNodes[1].value;
	}
	if(editor.attributes.EXI.nodeValue != editor.childNodes[3].childNodes[1].value){
//		console.log('    need to update EXI from '+editor.attributes.EXI.nodeValue+' to '+editor.childNodes[3].childNodes[1].value);
		updateData+='&exi='+editor.childNodes[3].childNodes[1].value;
		postUpdateRefresh['exi']=editor.childNodes[3].childNodes[1].value;
	}
	if(editor.attributes.CD.nodeValue != editor.childNodes[4].childNodes[1].value){
//		console.log('    need to update CG from '+editor.attributes.CG.nodeValue+' to '+editor.childNodes[4].childNodes[1].value);
		updateData+='&cd='+editor.childNodes[4].childNodes[1].value;
		postUpdateRefresh['cd']=editor.childNodes[4].childNodes[1].value;
	}
	if(editor.attributes.CG.nodeValue != editor.childNodes[5].childNodes[1].value){
//		console.log('    need to update CG from '+editor.attributes.CG.nodeValue+' to '+editor.childNodes[4].childNodes[1].value);
		updateData+='&cg='+editor.childNodes[5].childNodes[1].value;
		postUpdateRefresh['cg']=editor.childNodes[5].childNodes[1].value;
	}
	if(editor.attributes.DEST.nodeValue != editor.childNodes[6].childNodes[1].value){
	//	console.log('    need to update DEST from '+editor.attributes.DEST.nodeValue+' to '+editor.childNodes[6].childNodes[1].value);
		updateData+='&dest='+editor.childNodes[6].childNodes[1].value;
		postUpdateRefresh['dest']=editor.childNodes[6].childNodes[1].value;
	}
	
	// ===== put together an AJAX packet === send === on GOOD, update card(s) and hideAllFlips()
//	console.log(updateData);
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
			console.log('data='+data);
			var bits=data.split('|');
			if(bits[0]=='A'){
					// ==== HERE we update the card nodeValue info  for further changes =====
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
						mini.childNodes[0].innerText=choicegroupicons[postUpdateRefresh['cg']];		// ================================== if a choicegroup has never been assigned to a node, it does not have a record in this object, even if it really exists
							mini.childNodes[1].innerText=choiceGroups[postUpdateRefresh['cg']];		// =========== see line 358 or thereabouts =============
						expanded.childNodes[2].innerText=postUpdateRefresh['cg'];
						editor.attributes.CG.nodeValue=postUpdateRefresh['cg'];		// the input doesnt need to update as it reflects what we just edited...  ;-)
					}
					if(typeof(postUpdateRefresh['dest']) != 'undefined'){	
						mini.childNodes[3].innerText=postUpdateRefresh['dest'];										// ====================== need to update the color / number of these
							mini.childNodes[3].attributes.nextid.nodeValue=postUpdateRefresh['dest'];		// ====================== need to update the color / number of these
							mini.childNodes[3].innerText=megaData[postUpdateRefresh['dest']]['cn'];							// set color number
							mini.childNodes[3].className='nexttext next'+megaData[postUpdateRefresh['dest']]['c'];		// set color
						expanded.childNodes[7].innerText=megaData[postUpdateRefresh['dest']]['t'];
							expanded.childNodes[7].className='destination next'+megaData[postUpdateRefresh['dest']]['c'];		// set color
						editor.attributes.DEST.nodeValue=postUpdateRefresh['dest'];		// the input doesnt need to update as it reflects what we just edited...  ;-)
					}
			}else{
				throwError('Failed to update Node: '+bits[1]);
			}
		}
	}
	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+updateData);			console.log(url);
	xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(updateData);

	

}

function compileUpdatesForMega(e){
	var d=findMega(e.target);			var underscore=d.id.lastIndexOf('_');    var megaid=d.id.substring(underscore+1);			console.log('updating MEGA for: '+megaid);
	var editor=d.childNodes[2];
	var mini=d.childNodes[0];		// mini is the normal header
	var expanded=d.childNodes[1];
	var updateData='op=3&mega='+megaid+'&loadedpid='+loadedPID;
	var postUpdateRefresh=new Object();
	
	if(editor.attributes.OT.nodeValue != editor.childNodes[0].childNodes[1].value){
		console.log('    need to update OT from '+editor.attributes.OT.nodeValue+' to '+editor.childNodes[0].childNodes[1].value);
		updateData+='&ot='+encodeURIComponent(editor.childNodes[0].childNodes[1].value);
		postUpdateRefresh['ot']=editor.childNodes[0].childNodes[1].value;
		
		// =========== NOW WE NEED TO FIND ANY NODES WITH THIS AS A DEST AND UPDATE THEIR EXPANDED TEXT
		
	}
	if(editor.attributes.NG.nodeValue != editor.childNodes[1].childNodes[1].value){
		console.log('    need to update NG from '+editor.attributes.NG.nodeValue+' to '+editor.childNodes[1].childNodes[1].value);		// need to deal with value changed to null issue
		updateData+='&ng='+editor.childNodes[1].childNodes[1].value;
		postUpdateRefresh['ng']=editor.childNodes[1].childNodes[1].value;
	}
	
	// ===== put together an AJAX packet === send === on GOOD, update card(s) and hideAllFlips()
//	console.log(updateData);
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
			console.log('data='+data);
			var bits=data.split('|');
			if(bits[0]=='A'){
				if(typeof(postUpdateRefresh['ot'])!='undefined'){
					//expanded
					mini.childNodes[0].innerText=postUpdateRefresh['ot'];
//					expanded.childNodes[0].innerText=postUpdateRefresh['ot'];
					editor.attributes.OT.nodeValue=postUpdateRefresh['ot'];		// the input doesnt need to update as it reflects what we just edited...  ;-)
				}
				if(typeof(postUpdateRefresh['ng'])!='undefined'){
					//expanded
					//mini
					editor.attributes.NG.nodeValue=postUpdateRefresh['ng'];		// the input doesnt need to update as it reflects what we just edited...  ;-)
				}
			}else{
				throwError('Failed to update Mega: '+bits[1]);
			}
		}
	}
//	var updateData=encodeURIComponent(updateData);
	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+updateData);			console.log(updateData);
	xmlHttp.open('POST', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(updateData);


}


var megaColorCounts=new Object();		//	megaColorCounts['green']=0;    megaColorCounts['yellow']=0;    megaColorCounts['blue']=0;    megaColorCounts['red']=0;    megaColorCounts['aqua']=0;    megaColorCounts['purple']=0;    megaColorCounts['gray']=0;     megaColorCounts['terminate']=0;    
var megaData=new Object();						megaData['X']=new Object();    megaData['X']['c']='terminate';    megaData['X']['cn']='X';

function updateMegaColors(){
	megaColorCounts['green']=0;    megaColorCounts['yellow']=0;    megaColorCounts['blue']=0;    megaColorCounts['red']=0;    megaColorCounts['aqua']=0;    megaColorCounts['purple']=0;    megaColorCounts['gray']=0;     megaColorCounts['terminate']=0;    
	var megas=document.getElementsByClassName('platonicMega');
	if(megas){
		for(e=0;e<megas.length;e++){
			if(megas[e].id != 'platonicMega'){
				megaColorCounts[megas[e].attributes.color.nodeValue]++;
				megas[e].attributes.colornumber.nodeValue=megaColorCounts[megas[e].attributes.color.nodeValue];		// store in element
				if(typeof(nextid) == 'undefined'){megaData[megas[e].id]=new Object();}
				megaData[megas[e].id]['c']=megas[e].attributes.color.nodeValue;			console.log('loading megaData for '+megas[e].id+' as '+megas[e].attributes.color.nodeValue);
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
				if(typeof(nextid) != 'undefined' && nextid != 0){		//	console.log(e+' has '+nextid);
			//		items[e].lastChild.lastChild.className='nexthover next'+megaData[nextid]['c'];		// set color
			//		items[e].lastChild.lastChild.innerText=megaData[nextid]['cn'];							// set color number
					
					items[e].childNodes[0].childNodes[3].className='nexttext next'+megaData[nextid]['c'];		// set color
					items[e].childNodes[0].childNodes[3].innerText=megaData[nextid]['cn'];							// set color number
					
					items[e].childNodes[1].childNodes[7].className='destination next'+megaData[nextid]['c'];		// set color
				}
			}
		}
	}
}






// === convert this to work using x and y instead of a click event (where we get a sample e.clientX and e.clientY from)
function rotateDerArrowFromClick(e){
//	== alternate way  to get top/left without having to explicitly set it as we do in tb.php ==
//	var derarrow_left=window.getComputedStyle(derarrow,null).getPropertyValue('left');
//	var derarrow_top=window.getComputedStyle(derarrow,null).getPropertyValue('top');
	var derarrow_top=parseInt(derarrow.style.top.slice(0,-2));		derarrow_top+=40;		//get the number and add 40px to center on circle
	var derarrow_left=parseInt(derarrow.style.left.slice(0,-2));			derarrow_left+=40;

	var dy=e.clientY-derarrow_top;		
	var dx=e.clientX-derarrow_left;		
	var derrotation=Math.atan2(dy,dx);
	derarrow.style.transform='rotate('+derrotation+'rad)';
	//console.log(derarrow_left+' / '+e.clientX+' / '+derarrow.style.transform+' / '+derrotation);
}

function rotateDerArrowFromXY(x,y){
	var derarrow_top=parseInt(derarrow.style.top.slice(0,-2));		derarrow_top+=40;		//get the number and add 40px to center on circle
	var derarrow_left=parseInt(derarrow.style.left.slice(0,-2));			derarrow_left+=40;		// we should have just set this to the hovered item point
	var dy=y-derarrow_top;		
	var dx=x-derarrow_left;		
	var derrotation=Math.atan2(dy,dx);
	derarrow.style.transform='rotate('+derrotation+'rad)';
}



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
	
//	if(evt.keyCode==71){arrowrotation-=15; derarrow.style.transform='rotate('+arrowrotation+'deg)'; console.log(derarrow.style.transform+' / '+arrowrotation);}
//	if(evt.keyCode==72){arrowrotation+=15; derarrow.style.transform='rotate('+arrowrotation+'deg)'; console.log(derarrow.style.transform+' / '+arrowrotation);}
}
function doKeyUp(evt){
	keyboardModifier=null;
}

function hideOverlays(a){
// 	if(a==1){
// 		if(objectHasData(data4update)){if(confirm('You\'ve made some changes.  Do you want to send them before closing?')){updateData();}}
// 		if(overlay){overlay.style.display='none';}if(overcontent1){overcontent1.style.display='none';}if(overcontent2){overcontent2.style.display='none';}if(overcontent3){overcontent3.style.display='none';}
// 	}
	if(a==2){if(overlay2){overlay2.style.display='none';}}
	if(a==3){if(overlay3){overlay3.style.display='none';}}
}

