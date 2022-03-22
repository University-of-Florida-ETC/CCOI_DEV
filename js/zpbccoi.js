

var sessionList=new Object();
var derServer='https://ccoi-dev.education.ufl.edu/';

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

/*
function switchPlayState(){
	viewingPlaygrounds=!viewingPlaygrounds; // switch true/false
	if(viewingPlaygrounds){leftAndRight.className=origLeftAndRightClass+' playground';}else{leftAndRight.className=origLeftAndRightClass;}
	showsessionList();
}
*/

function fetchUserObSets2(u){
	//if(	! ((Object.keys(appVideoList).length>0) && (Object.keys(appPathList).length>0)) ){console.log('not ready to load... try again in half a sec'); setTimeout(function(){ fetchUserObSets2(userid);},500);return;}		// if we're not ready -- wait half a second and try again.
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
			sessionList=JSON.parse(data);
            console.log("The sessions are:");
            console.log(sessionList);

			let researchList = document.getElementById("research_session_list");
			for(var session in sessionList['research']){
				appendSessionLink2(researchList, session, sessionList['research'][session].name);
			}

			let playgroundsList = document.getElementById("playgrounds_session_list");
			for(var session in sessionList['playgrounds']){
				appendSessionLink2(playgroundsList, session, sessionList['playgrounds'][session].name);
			}
		}
	}
	sendStr='uid2='+u;
	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			console.log(url);
	xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
}

function appendSessionLink2(container, i, name) {
    if(name == null) {
		/*
        if (session.videoURL != null) {
            // If name not explicitly set, use video title
            name = session.videoURL.replace(/\.[^/.]+$/, "");
            name += " ("+session.observer+")";
        }
        else {*/
            name = "Session "+i;
        //}
    }
    let template = `
        <div class="row">
            <div class="col-sm-9 col-12">
                <a id="session_${i}_name" class="btn-link session-edit" href="#" data-index="${i}">${name}</a>
            </div>
            <div class="col-sm-3 col-12">
                <a class="btn-link session-edit" href="ZPB/observation?isPlayground=1&id=${i}" data-index="${i}"><span class="oi oi-pencil px-2" title="Edit Session" aria-hidden="true"></span></a>
                <a class="btn-link" href="ZPB/observation?isPlayground=1&id=${i}" data-index="${i}"><span class="oi oi-trash px-2" title="Delete Session" aria-hidden="true"></span></a>
                <a class="btn-link" href="/visualizations?session=${i}"><span class="oi oi-pie-chart px-2" title="View Visualizations" aria-hidden="true"></span></a>
            </div>
        </div>
    `;
    let wrapper = document.createElement("LI");
    wrapper.classList.add('session-listing');
    wrapper.classList.add('my-2');
	wrapper.innerHTML = template;
	container.appendChild(wrapper);
}

function fetchUserObSets3(u){
	//if(	! ((Object.keys(appVideoList).length>0) && (Object.keys(appPathList).length>0)) ){console.log('not ready to load... try again in half a sec'); setTimeout(function(){ fetchUserObSets2(userid);},500);return;}		// if we're not ready -- wait half a second and try again.
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
			sessionList=JSON.parse(data);
            console.log("The sessions are:");
            console.log(sessionList);

			let params = new URLSearchParams(document.location.search);
			let id = params.get("id");
			let isPlayground = params.get("isPlayground");
			let playorreal = isPlayground ? 'playgrounds' : 'research';


			if(sessionList[playorreal][id]){
				let currentsession = sessionList[playorreal][id];
				if(currentsession.name){
					document.getElementById("sessionTitle").innerText = currentsession.name + (isPlayground ? " (Playground)" : " (Research)");
					document.getElementById("session_title").value = currentsession.name;
				}
				if(currentsession.studentID){
					document.getElementById("studentID").value= currentsession.studentID;
				}
				if(currentsession.placetime){
					document.getElementById("session_date").value = currentsession.placetime;
				}
				if(currentsession.videoURL){
					document.getElementById("session_video_title").value = currentsession.videoURL;
				}
				if(currentsession.notes){
					document.getElementById("session_notes").value = currentsession.notes;
				}
				if(currentsession.observations){
					console.log("currentsession.observations");
					console.log(currentsession.observations);
					let destination = document.getElementById("path_list");
					for(var observation in currentsession.observations){
						console.log("observation = ");
						console.log(observation);
						let obsHTML = `<div class="path-listing-container">
						<h5 data-index="${index}" class="path-listing-header">${observation.name}
							<a class="btn-link path-edit-icon" href="#" data-index="0"><span class="oi oi-pencil px-3" title="Edit Path" aria-hidden="true"></span></a>
							<a class="btn-link path-delete-icon" href="#" data-index="0"><span class="oi oi-trash" title="Delete Path" aria-hidden="true"></span></a>
							<button class="btn-link float-right path-dropdown-btn collapsed" data-toggle="collapse" data-target="#path_drop_0" aria-expanded="false"><span class="oi oi-chevron-bottom" title="Show Observation Nodes" aria-hidden="true"></span></button>
						</h5>
						<ol class="collapse" id="path_drop_${index}" style="">`;
						element.ObResp.forEach(obRespObject => {
							let minutes = parseInt(obRespObject.seconds) / 60;
							let seconds = ("0" + (parseInt(obRespObject.seconds) % 60)).slice(-2);
							let notes = "";
							if(obRespObject.notes){
								notes = " ["+obRespObject.notes+"]";
							}
							obsHTML+=`
							<li>(${minutes}:${seconds}) ${obRespObject.PNid}-${obRespObject.SAid}: This is where the decoded name will go${notes}</li>`
						});
						obsHtml += `
							</ol>
						</div>`;
						destination.innerHTML += obsHTML;
					}
					//This one doesn't work!!
					if(currentsession.observations.length > 0){
						let destination = document.getElementById("path_list");
						currentsession.observations.forEach((element, index) => {
							console.log("currentsession.observations.element = ");
							console.log(element);
							let obsHTML = `<div class="path-listing-container">
							<h5 data-index="${index}" class="path-listing-header">${element.name}
								<a class="btn-link path-edit-icon" href="#" data-index="0"><span class="oi oi-pencil px-3" title="Edit Path" aria-hidden="true"></span></a>
								<a class="btn-link path-delete-icon" href="#" data-index="0"><span class="oi oi-trash" title="Delete Path" aria-hidden="true"></span></a>
								<button class="btn-link float-right path-dropdown-btn collapsed" data-toggle="collapse" data-target="#path_drop_0" aria-expanded="false"><span class="oi oi-chevron-bottom" title="Show Observation Nodes" aria-hidden="true"></span></button>
							</h5>
							<ol class="collapse" id="path_drop_${index}" style="">`;
							element.ObResp.forEach(obRespObject => {
								let minutes = parseInt(obRespObject.seconds) / 60;
								let seconds = ("0" + (parseInt(obRespObject.seconds) % 60)).slice(-2);
								let notes = "";
								if(obRespObject.notes){
									notes = " ["+obRespObject.notes+"]";
								}
								obsHTML+=`
								<li>(${minutes}:${seconds}) ${obRespObject.PNid}-${obRespObject.SAid}: This is where the decoded name will go${notes}</li>`
							});
							obsHtml += `
								</ol>
							</div>`;
							destination.innerHTML += obsHTML;
						});
					}
				}
			}
			else
				notAllowedInEditor();
		}
	}
	sendStr='uid2='+u;
	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			console.log(url);
	xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
}

function notAllowedInEditor() {
	console.log("You would have been kicked out here!");
}

function fetchUserObSets4(u){
	//if(	! ((Object.keys(appVideoList).length>0) && (Object.keys(appPathList).length>0)) ){console.log('not ready to load... try again in half a sec'); setTimeout(function(){ fetchUserObSets2(userid);},500);return;}		// if we're not ready -- wait half a second and try again.
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
			console.log(data);
			/*
			sessionList=JSON.parse(data);
            console.log("The sessions are:");
            console.log(sessionList);

			let researchList = document.getElementById("research_session_list");
			for(var session in sessionList['research']){
				appendSessionLink2(researchList, session, sessionList['research'][session].name);
			}

			let playgroundsList = document.getElementById("playgrounds_session_list");
			for(var session in sessionList['playgrounds']){
				appendSessionLink2(playgroundsList, session, sessionList['playgrounds'][session].name);
			}
			*/
		}
	}
	sendStr='debug=1&uid2='+u;
	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			console.log(url);
	xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
}



