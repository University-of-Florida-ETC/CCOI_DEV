/**
    * TODO: Plug in to necessary prompts
    * ! Need to make this more one-size fits all
    * * Example: function blurScreen(element: string)
    * ? Zack, what places did you have using this in mind?
*/

const blur = document.getElementById('blurOverlay');
function blurScreen() {
    blur.classList.toggle('blurred');
}

/**
 * Toggles the class 'popped' on the specified element
 * @param {string} element - the name of the element being toggled
 */
function showForm(element) {
    let theElement = document.getElementById(element);
    theElement.classList.toggle('popped');
}


function closePopups() {
    if (blur.classList.contains('blurred')) {
        blurScreen();
        const currentPopup = document.getElementsByClassName("popped")[0];
        currentPopup.classList.toggle("popped");
    }
}

function GetAjaxReturnObject(mimetype) { var xmlHttp = null; if (window.XMLHttpRequest) { xmlHttp = new XMLHttpRequest(); if (xmlHttp.overrideMimeType) { xmlHttp.overrideMimeType(mimetype); } } else if (window.ActiveXObject) { try { xmlHttp = new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) { try { xmlHttp = new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) { } } } return xmlHttp; }
function getHTML(httpRequest) { if (httpRequest.readyState === 4) { if (httpRequest.status === 200) { return httpRequest.responseText; } } }

function sendContact() {
    var xmlHttp = GetAjaxReturnObject('text/html'); if (xmlHttp == null) { alert("Your browser does not support AJAX!"); }
    xmlHttp.onreadystatechange = function () {
        var data = getHTML(xmlHttp);
        if (data) {
            //var d=JSON.parse(data);
            showHelpMenu();
            document.getElementById('emailResponseText').innerText = data;
            document.getElementById('emailResponse').classList.toggle('popped');
        }
    }

    var derURL = 'https://nutrition.education.ufl.edu/includes/email.php';
    var sendStr = 'userEmail=' + document.contactForm.email.value + '&userName=' + document.contactForm.fullname.value + '&message=' + document.contactForm.message.value;
    console.log(sendStr);
    xmlHttp.open('POST', derURL, true); xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); xmlHttp.send(sendStr);
}