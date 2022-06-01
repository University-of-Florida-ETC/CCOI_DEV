// Setting up Dropzone settings
optionsPreset = {
    paramName: "file", // The name that will be used to transfer the file
    maxFilesize: 2, // MB
    maxFiles: 1,
    autoProcessQueue: false
}

//Apply settings to each Dropzone item
Dropzone.options.wantPicDropzone = optionsPreset;
Dropzone.options.happenedPicDropzone = optionsPreset;

// Hard-code questions - no need to read these in or anything
radioQuestions = [ "The teacher?",
    "The program/software?",
    "My paper/worksheet?",
    "My classmate/peer?",
    "An anchor chart?",
    "Other?",
    "Did I read my code block-by-block?",
    "Does any part of my code work?",
    "Do I know which block/set of blocks is causing the problem?",
    "Do I know what the “problem” block(s) are supposed to do?",
    "Did I find an error inside of a block (e.g., number, operator, etc.)?",
    "Other?" ];

longQuestions = ["What did I want my code to do?", "What happened when I ran my code?"]

//Functions
//==================================

var getValues = function() {
    names = [];
    radioValues = [];
    textAreaValues = [];
    checkboxValues = [];
    images = [];

    names[0] = document.getElementById("studentName").value;
    if (names[0] == "")     names[0] = "[Student Name Blank]";
    names[1] = document.getElementById("teacherName").value;
    if (names[1] == "")     names[1] = "[Teacher Name Blank]";

    radioValues[0] = $('input[name=teacher]:checked').val();
    radioValues[1] = $('input[name=program]:checked').val();
    radioValues[2] = $('input[name=paper]:checked').val();
    radioValues[3] = $('input[name=classmate]:checked').val();
    radioValues[4] = $('input[name=anchor]:checked').val();
    radioValues[5] = $('input[name=directionsOther]:checked').val();
    radioValues[6] = $('input[name=read]:checked').val();
    radioValues[7] = $('input[name=any]:checked').val();
    radioValues[8] = $('input[name=causing]:checked').val();
    radioValues[9] = $('input[name=supposed]:checked').val();
    radioValues[10] = $('input[name=inside]:checked').val();
    radioValues[11] = $('input[name=codeOther]:checked').val();

    textAreaValues[0] = document.getElementById("whatDidIWant").value;
    textAreaValues[1] = document.getElementById("whatHappened").value;

    checkboxValues[0] = $('#checkWantPic').prop('checked');
    checkboxValues[1] = $('#checkHappenedPic').prop('checked');

    checkboxValues.forEach((value, index) => {
        if (value == true) {
            if (Dropzone.instances[index].files[0]) {
                images[index] = Dropzone.instances[index].files[0].dataURL;
            }
        }
    });

    return { names, radioValues, textAreaValues, checkboxValues, images };
}

var generatePopupTable = function(answers) {
    //Verify element with id yesNoSection exists
    container1 = document.getElementById("outputSection");
    if (!(container1)) { console.log("no output container element!"); return; }

    //Empty it
    container1.innerHTML="";
    container1.innerHTML += answers["names"][0] + " for " + answers["names"][1];

    //Set up the table
    table = document.createElement("table");
    container1.appendChild(table);
    table.innerHTML += "<thead> <tr> <th>Question</th> <th>Answer</th> </tr> </thead>";
    tableBody = document.createElement("tbody");
    table.appendChild(tableBody);

    //Add the information as rows to the tbody
    radioQuestions.forEach((question, index) => {
        let newRow = document.createElement("tr");

        var answer;
        if (answers["radioValues"][index]==1)      answer = "Yes";
        else if (answers["radioValues"][index]==0) answer = "No";
        else                                       answer = "Unanswered";

        newRow.innerHTML = "<th>"+question+"</th> <th>"+answer+"</th>";
        tableBody.appendChild(newRow);
    });

    //Add the information
    longQuestions.forEach((question, index) => {
        let essayRow = document.createElement("tr");
        let attachedRow = document.createElement("tr");
        tableBody.appendChild(essayRow);
        tableBody.appendChild(attachedRow);

        var answer;
        if (answers["radioValues"][index]==1)      answer = "Yes";
        else if (answers["radioValues"][index]==0) answer = "No";
        else                                       answer = "Unanswered";

        essayRow.innerHTML = "<th>"+question+"</th> <th>"+answers["textAreaValues"][index]+"</th>";

        attachedRow.innerHTML = "<th>Picture attached?</th>";
        if (answers["checkboxValues"][index]==1) {
            if (answers["images"][index]) {
                let rowCell = document.createElement("th");
                let imageItem = document.createElement("img");

                imageItem.id = "img"+index;
                imageItem.src = answers["images"][index];

                rowCell.appendChild(imageItem);
                attachedRow.appendChild(rowCell);
            }
            else {
                attachedRow.innerHTML += "<th>Checked, but no picture included</th>";
            }
        }
        else {
            attachedRow.innerHTML += "<th>No</th>";
        }
    });
}

var submit = function(){
    values = getValues();
    generatePopupTable(values);
    showPopup();
}

var submitGraphic = function() {
    values = getValues();
    generateGraphicPopup(values);
    showPopup();
}
/*
var generateGraphicPopup = function(answers) {
    //Verify element with id yesNoSection exists
    container1 = document.getElementById("yesNoSection");
    if (!(container1)) {
        console.log("no container1 element!")
        return;
    }
    //Empty it
    container1.innerHTML="";

    var width = window.innerWidth;
    var height = window.innerHeight;
    var canvas = document.createElement("canvas");

    var stage = new Konva.Stage({
      container: canvas,
      width: width,
      height: height,
    });

    var layer = new Konva.Layer();
    stage.add(layer);

    // main API:
    if (answers["checkboxValues"][0]==1 && answers["images"][0]) {
        var imageObj = new Image();
        imageObj.onload = function () {
            var image1 = new Konva.Image({
                x: 50,
                y: 50,
                image: imageObj,
                width: 106,
                height: 118,
            });

            // add the shape to the layer
            layer.add(image1);
        };
        imageObj.src = answers["images"][0];
    }

    container1.appendChild(canvas);
    showPopup();
}
*/

var printPopup = function() {
    const divContents = document.getElementById("outputSection").innerHTML;
    var a = window.open('', '', 'height=600, width=600');
    a.document.write('<html><head><meta charset="utf-8"><meta content="width=device-width, initial-scale=1.0" name="viewport"><title>Debugging Detective Results</title><style>'+'table {margin: 0 auto;}tbody tr th{font-weight: normal;text-align: left;padding: 5px;}tr:nth-child(even) {background-color: #f0f0f0;}th:nth-child(n+2) {border-left: 2px solid black;}thead tr th{background-color: #f0f0f0;border-bottom: 2px solid black;}img {max-height: 100px;}</style></head>');
    a.document.write('<body style=\'text-align:center;\'> <h3 style=\'margin-bottom: 0;\'>Debugging Detective Results</h3>');
    a.document.write(divContents);
    a.document.write('</body></html>');
    a.document.close();
    a.print();
}