// STUFF USED TO CALL DATABASE
//=================================================================================
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

function fetchUserObSets(u){
	//if(	! ((Object.keys(appVideoList).length>0) && (Object.keys(appPathList).length>0)) ){console.log('not ready to load... try again in half a sec'); setTimeout(function(){ fetchUserObSets2(userid);},500);return;}		// if we're not ready -- wait half a second and try again.
	var xmlHttp=GetAjaxReturnObject('text/html');if (xmlHttp==null) {alert ("Your browser does not support AJAX!");return;}
	xmlHttp.onreadystatechange = function() {
		var data=getHTML(xmlHttp);
		if(data){
			observationSets=JSON.parse(data);
            console.log("The observation sets are:");
            console.log(observationSets);
            transformData(observationSets);
		}
	}
	sendStr='uid2='+u;
	var url =  encodeURI(derServer+'api/ccoi_ajax.php?'+sendStr);			console.log(url);
	xmlHttp.open('GET', url, true);xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xmlHttp.send(sendStr);
}

//PUT LIST OF VIEWABLE OBS SETS ON PAGE
//=================================================================================
function transformData(observationSets){
    let list = document.getElementById("obsList");

    observationSets.forEach((element, index) => {
        let newLink = document.createElement("a");
        newLink.innerText=element.name;
        newLink.setAttribute("href", "obsset?id="+element.id);

        list.appendChild(newLink);
        list.appendChild("br");
    });
}