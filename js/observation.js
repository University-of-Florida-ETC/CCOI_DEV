"use strict";

var ccoiObservation = (function () {
  var sessions = [];
  var alteredSessionData = new Object();
  var isDemo = false;
  var makingNewPath = true;
  var stateIDPath;
  var stateIDStep;
  var originalPathsLength;
  var isFirstSelection = true;
  var currentSessionID;
  var nodeID;
  var newNodeID;
  var popoutWindow;
  var isPathLabelAdded = false;
  var originalTraceLength = 0;

  var parts = window.location.search.substr(1).split("&");
  var $_GET = {};
  for (var i = 0; i < parts.length; i++) {
    var temp = parts[i].split("=");
    $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
  }

  console.log($_GET.url);

  function setDemoBool(sdb) {
    isDemo = sdb;
  }

  function addBranch(
    value,
    oldValue,
    description,
    checked,
    divToAttachTo,
    aside = false
  ) {
    let branchForm = $(divToAttachTo);
    if (checked === undefined) {
      checked = false;
    }
    if (aside) {
      description += '<br/><span class="aside">' + aside + "</span>";
    }
    branchForm.append(`
            <p>
                <input type="radio" name="choiceRadio" id="choiceRadio${value}" data-oldChoiceIndex="${oldValue}" value="${value}" ${
      checked == true ? "checked" : ""
    }>
                <label for="choiceRadio${value}" class="${
      aside ? "choiceOfList containsAside" : "choiceOfList"
    }">${description}</label>
            </p>
        `);
  }

  // This is called to set up the radio button choices
  function setUpNodeBranches(nextNodeID) {
    var node = ccoi.ccoiSchema.getNode(nextNodeID);
    nodeID = node.node_id;
    newNodeID = node.node_id;

    // console.log("Next node id:" + nextNodeID);
    // console.log("node id:" + nodeID);
    //console.log(nextNodeID);

    // Backstop for old ID system
    let prettyID = parseInt(node.id);

    // update title
    DOM.path_title.innerHTML = "(" + node.id + ") " + node.title;

    // clear the current list
    $("#branch_container").empty();
    $("#branch_container").append(
      '<form id="branch_radio_form" class="col-12 pt-3" action="javascript:void(0)"></form>'
    );

    let selectedChoice = null;
    //        console.log("this is the current session number in setupNode Branches: " + currentSessionID)
    //      console.log("SetUpNodeBranches: CurrentSession is: " + sessions[currentSessionID]);
    let currentStep =
      sessions[currentSessionID].paths[stateIDPath].steps[stateIDStep];
    // contains backstop for old ID system
    if (
      currentStep !== undefined &&
      (newNodeID == currentStep.nodeid || prettyID == currentStep.node)
    ) {
      DOM.timestamp_input_minutes.value = currentStep.minutes;
      DOM.timestamp_input_seconds.value = currentStep.seconds;
      if (currentStep.notes != undefined) {
        DOM.notes_input.value = currentStep.notes;
      }
      selectedChoice = currentStep.choiceid;
      //selectedChoice = selectedChoice.toString();
    }

    if (node.should_group_choices) {
      let numGroups = node.branches.length;
      let branchRadioForm = $("#branch_radio_form");
      branchRadioForm.append(
        '<div class="accordion" id="branch_group_accordion"></div>'
      );
      let radioButtonIndex = 0;

      for (let groupIndex = 0; groupIndex < numGroups; groupIndex++) {
        let groupName = node.branch_group_names[groupIndex];
        $("#branch_group_accordion").append(`
                    <div class="card mb-3">
                        <div class="card-header" id="heading_${groupIndex}">
                            <h5 class="mb-0">
                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapse_${groupIndex}" aria-expanded="${
          groupIndex == 0 ? "true" : "false"
        }" aria-controls="collapse_${groupIndex}">
                                    ${groupName}
                                </button>
                            </h5>
                        </div>
                        <div id="collapse_${groupIndex}" class="${
          groupIndex == 0 ? "collapse show" : "collapse"
        }" aria-labelledby="heading_${groupIndex}" data-parent="#branch_group_accordion">
                            <div id="collapse_${groupIndex}_body" class="card-body">
                            </div>
                        </div>
                    </div>
                `);
        let numBranches = node.branches[groupIndex].length;
        for (let branchIndex = 0; branchIndex < numBranches; branchIndex++) {
          let branch = node.branches[groupIndex][branchIndex];
          // function addBranch (value, description, checked, divToAttachTo, aside = false)
          let isSelected =
            selectedChoice == branch.branch_new_id ||
            selectedChoice === branch.pretty_id;
          addBranch(
            branch.branch_new_id,
            branch.branch_id,
            "(" + ChoiceID(branch.pretty_id) + ") " + branch.description,
            isSelected,
            `#collapse_${groupIndex}_body`,
            branch.aside
          );
          radioButtonIndex++;
        }
      }
      addBranch(
        -1,
        "Other (use notes)",
        selectedChoice === -1,
        "#branch_radio_form"
      );
    } else {
      let branches = node.branches;
      for (let i = 0; i < branches.length; i++) {
        for (let j = 0; j < branches[i].length; j++) {
          let branch = node.branches[i][j];
          let isSelected =
            selectedChoice == branch.branch_new_id ||
            selectedChoice === branch.pretty_id;
          addBranch(
            branch.branch_new_id,
            branch.branch_id,
            "(" + ChoiceID(branch.pretty_id) + ") " + branch.description,
            isSelected,
            "#branch_radio_form",
            branch.aside
          );
        }
        addBranch(-1, "Other (use notes)", selectedChoice === -1);
      }
    }

    bindPathEvents();
  }

  function loadSessionInfo(index) {
    $(DOM.session_list).addClass("d-none");

    let session = sessions[index];

    $("#session_title").val(session.name);
    $("#session_observer").val(session.observer);
    $("#session_student").val(session.studentID);
    $("#session_notes").html(session.sessionNotes);
    $("#session_date").val(session.date);
  }

  function refreshPathListing() {
    removeAllChildren(DOM.path_list);

    let paths = sessions[currentSessionID].paths;
    let numPaths = paths.length;
    for (let pathIndex = 0; pathIndex < numPaths; pathIndex++) {
      let list = addPathTraceToTable(paths, pathIndex);
      let thisTrace = paths[pathIndex].steps;
      for (let j = 0; j < thisTrace.length; j++) {
        addStepToPathTrace(thisTrace[j], list);
      }
    }
    $(".path-dropdown-btn").click(function (e) {
      let classList = $(this)[0].children[0].classList;
      classList.contains("oi-chevron-bottom")
        ? classList.replace("oi-chevron-bottom", "oi-chevron-top")
        : classList.replace("oi-chevron-top", "oi-chevron-bottom");
    });
    bindPathEvents();
  }

  function goToPathStart() {
    if (currentSessionID < 0 || currentSessionID > sessions.length) {
      return;
    }
    $(DOM.session_list).addClass("d-none");
    $(DOM.dom_group_1).removeClass("d-none");
    $(DOM.session_go_back).removeClass("d-none");
    $(DOM.save_session_button).removeClass("d-none");
    $(DOM.path_input).addClass("d-none");
    $(DOM.path_preview).addClass("d-none");
    loadSessionInfo(currentSessionID);
    refreshPathListing();
  }

  function addPathTraceToTable(paths, pathIndex) {
    let currentPath = paths[pathIndex];
    let name = "Path #" + (pathIndex + 1);
    if (
      currentPath.label != -1 &&
      currentPath.label != null &&
      currentPath.label != ""
    )
      name += " (" + currentPath.label + ")";

    let newOutputDIV = document.createElement("div");
    newOutputDIV.setAttribute("class", "path-listing-container");

    DOM.path_list.appendChild(newOutputDIV);

    if (name !== undefined) {
      let newOutputH = document.createElement("h5");
      newOutputH.setAttribute("data-index", pathIndex);
      newOutputH.setAttribute("class", "path-listing-header");
      name += `<a class="btn-link path-edit-icon" href="#" data-index="${pathIndex}"><span class="oi oi-pencil px-3" title="Edit Path" aria-hidden="true"></span></a>
            <a class="btn-link path-delete-icon" href="#" data-index="${pathIndex}"><span class="oi oi-trash" title="Delete Path" aria-hidden="true"></span></a>
            <button class="btn-link float-right path-dropdown-btn" data-toggle="collapse" data-target="#path_drop_${pathIndex}"><span class="oi oi-chevron-bottom" title="Show Path Steps" aria-hidden="true"></span></button>`;
      newOutputH.innerHTML = name;
      newOutputDIV.appendChild(newOutputH);
    }
    let list = document.createElement("ol");
    list.setAttribute("class", "collapse");
    list.setAttribute("id", "path_drop_" + pathIndex);
    newOutputDIV.appendChild(list);
    return list;
  }

  function addStepToPathTrace(step, list) {
    let newOutputLI = document.createElement("li");
    newOutputLI.innerHTML = step.output();
    list.appendChild(newOutputLI);
  }

  function deletePath(deleteIndex) {
    if (
      confirm(
        "Are you sure you want to delete path #" + (deleteIndex + 1) + "?"
      )
    ) {
      let session = sessions[currentSessionID];
      let paths, pathsCopy;
      paths = session.paths;

      // Might not be needed
      pathsCopy = session.paths.slice(0);
      paths.splice(deleteIndex, 1);
      makeDirty();
      // We have altered the session with the currentSessionID
      alteredSessionData.id = session.id;
      alteredSessionData.updateObsEl = 1;
      // If the altered session has been moved, we want to make sure we know its previous
      // index in the array, so we can delete the correct path on the backend
      if (alteredSessionData.paths == undefined) alteredSessionData.paths = [];
      if (alteredSessionData.paths[deleteIndex] == undefined)
        alteredSessionData.paths[deleteIndex] = pathsCopy[deleteIndex];
      // Backend is not zero-indexed, so we have to +1 to the delete index
      alteredSessionData.paths[deleteIndex].id = deleteIndex + 1;
      alteredSessionData.paths[deleteIndex].isDeleted = true;
      /*
            * Taken out because reordering is no longer needed
            if(alteredSessionData.paths[deleteIndex].oldIndex != undefined) {
                alteredSessionData.paths.forEach(function(path){
                    if (path.oldIndex === deleteIndex) {
                        path.isDeleted = true;
                    }
                });
            } else {
                // Else, the paths have not been moved and oldIndex has not been set
                // and we can simply set the path at the delete index to isDeleted
                alteredSessionData.paths[deleteIndex].isDeleted = true;
            }
            */
      console.log(alteredSessionData);
      goToPathStart();
    } else {
      return false;
    }
  }

  function submitBranch() {
    // reset to true so next branch will pause upon first radio button selection
    isFirstSelection = true;
    let choiceIndex = $(
      'input[name="choiceRadio"]:checked',
      "#branch_radio_form"
    )[0].dataset.oldchoiceindex;
    console.log("here is choice index");
    console.log(choiceIndex);
    let newChoiceIndex = $(
      'input[name="choiceRadio"]:checked',
      "#branch_radio_form"
    ).val();
    console.log("here is newChoiceindex");
    console.log(newChoiceIndex);
    if (choiceIndex === "-1") choiceIndex = -1;

    if (choiceIndex === undefined) {
      alert("Please select an option.");
      return false;
    }

    // process the form data into the structure
    let session = sessions[currentSessionID];
    let paths = session.paths;
    let currentTrace = paths[stateIDPath].steps;
    console.log("Current trace length: " + currentTrace.length);
    console.log("On stateIDStep #: " + stateIDStep);
    let currentStep = currentTrace[stateIDStep];
    let isPathSwitched = false;

    // Need to make the session dirty
    makeDirty();

    // if currentStep is defined that means you are retracing an existing path
    //console.log("current step: " + currentStep.nodeid);
    if (
      currentStep !== undefined &&
      (currentStep.nodeid != newNodeID ||
        currentStep.choiceid != newChoiceIndex)
    ) {
      console.log("Are we here?");
      // If changing the choice leads to the same node:
      var currentStepNextNodeID;
      // catch empty/deleted nodes
      console.log("What about here?");
      try {
        currentStepNextNodeID = currentStep.nextNodeID();
      } catch (err) {
        console.log(
          "We have caught an error sir! Dear fucking lord! The ship is sinking!"
        );
        currentStepNextNodeID = -1;
      }
      console.log("Confirming we are hitting line 289");
      console.log(newNodeID);
      console.log(newChoiceIndex);
      var newChoice = getNodeFromChoice(newNodeID, newChoiceIndex);
      console.log(newChoice);
      if (newChoice && currentStepNextNodeID === newChoice.next_id) {
        // I'm not sure if we actually have to do anything here to get the choice to quietly swap out.
        console.log("Changing choice, but it should go to the same node?");
      } else {
        if (
          stateIDStep + 1 < currentTrace.length &&
          confirm(
            "Changing your choice will delete the choices made in this path after this choice. Are you sure?"
          ) === false
        ) {
          return false;
        }
        originalTraceLength = currentTrace.length;
        currentTrace.splice(stateIDStep, currentTrace.length - stateIDStep);
        // We have now switched the route of the path and we will have to make note of that in alteredSessionData later
        currentStep = undefined;
        isPathSwitched = true;
      }
    }
    console.log("Did we skip the entire fucking thing?");
    let ssnum = (stateIDPath + 1).toString();
    let extra = null;
    let branchExtra =
      choiceIndex === -1
        ? null
        : getNodeFromChoice(newNodeID, newChoiceIndex).extra;
    if (
      branchExtra !== null &&
      branchExtra !== undefined &&
      branchExtra !== ""
    ) {
      let extraText =
        "\nComma separate if multiple. If unknown, P1, P2, ... or A1, A2, ... as necessary.";
      extra = prompt(
        "Please enter: " + branchExtra + extraText,
        currentStep === undefined ? "" : currentStep.extra
      );
    }

    let minutesValue = parseInt(DOM.timestamp_input_minutes.value, 10);
    if (isNaN(minutesValue)) {
      DOM.timestamp_input_minutes.value = minutesValue = session.minutes;
    } else {
      session.minutes = minutesValue;
    }

    let secondsValue = parseInt(DOM.timestamp_input_seconds.value, 10);
    if (isNaN(secondsValue)) {
      DOM.timestamp_input_seconds.value = secondsValue = session.seconds;
    } else {
      session.seconds = secondsValue;
    }

    let totalTime = minutesValue * 60 + secondsValue;

    let notes = DOM.notes_input.value;
    DOM.notes_input.value = "";

    // Node ID is a string in the DB
    let nodeIDString = newNodeID.toString();
    let step = new CCOI_Step(
      nodeID,
      nodeIDString,
      choiceIndex,
      newChoiceIndex,
      ssnum,
      minutesValue,
      secondsValue,
      totalTime,
      extra,
      notes,
      stateIDStep
    );

    let stepAJAX = new CCOI_Step_AJAX();

    // TODO: Move this logic into a separate function
    let newID = stateIDPath;
    if (currentStep != undefined) {
      console.log("condition 1");
      if (!deepEqual(currentStep, step)) {
        console.log("Edited");
        step.isEdited = true;
        // ! IN THIS CASE THE PATH IS EDITED!
        alteredSessionData.paths[newID].isEdited = true;
      }
    } else if (currentStep == undefined && isPathSwitched == true) {
      console.log("Condition 2");
      console.log("Undefined and path has been edited");
      // ! IN THIS CASE THE PATH IS ALSO EDITED!
      step.isEdited = true;
      alteredSessionData.paths[newID].isEdited = true;
    } else if (
      currentStep == undefined &&
      stateIDStep + 1 > originalTraceLength
    ) {
      console.log("Condition 3");
      // We know this is a completely new step because we have gone beyond the original length of the trace
      console.log("Undefined and new");
      step.isNew = true;
    } else if (
      currentStep == undefined &&
      stateIDStep + 1 <= originalTraceLength
    ) {
      console.log("condition 4");
      // We are still within the bounds of the original trace, so we know this is an "edited" step as far as the backend will be concerned
      console.log(
        "Undefined because step was deleted and then added back. Basically, just edited"
      );
      step.isEdited = true;
    }

    currentTrace[stateIDStep] = step;
    if (alteredSessionData.paths == undefined) alteredSessionData.paths = [];
    // If there is not currently a path in this ID, we know it hasn't been edited yet
    if (alteredSessionData.paths[stateIDPath] == undefined) {
      alteredSessionData.paths[stateIDPath] =
        sessions[currentSessionID].paths[stateIDPath];
    }
    if (alteredSessionData.paths[stateIDPath].isDeleted != undefined) {
      if (alteredSessionData.paths[stateIDPath].isDeleted == true) {
        newID = sessions[currentSessionID].paths.length;
      }
    }

    alteredSessionData.id = sessions[currentSessionID].id;
    alteredSessionData.updateObsEl = 1;
    //console.log(sessions[currentSessionID]);
    // Backend is not zero-indexed, so we have to +1 to stateIDPath
    console.log(step);
    alteredSessionData.paths[newID].id = stateIDPath + 1;

    stepAJAX = goGoAjax(step, stepAJAX);

    // ! How does one tell if a path isEdted?
    // * a path is edited if and only if the following are true:
    // *    > A CCOI_Step is edited rather than new
    // alteredSessionData.paths[newID].isEdited = true;
    // Before adding this step, we need to see if the index in alteredSessionData is empty
    // If it is empty, we know that this is a new step and can add it
    if (alteredSessionData.paths[newID].steps[stateIDStep] == undefined) {
      alteredSessionData.paths[newID].steps[stateIDStep] = step;
      alteredSessionData.paths[newID].steps[stateIDStep].isNew = true;
    } else {
    }
    alteredSessionData.paths[newID].steps[stateIDStep] = step;
    // Backend is not zero-indexed, so we have to +1 to stateIDPath
    alteredSessionData.paths[newID].steps[stateIDStep].ssnum = String(
      stateIDPath + 1
    );
    stateIDStep++;
    console.log(alteredSessionData);

    let nextNodeID = step.nextNodeIDInt();

    if (nextNodeID === null || nextNodeID === undefined || nextNodeID === "") {
      goToPathStart();
    } else {
      if (nextNodeID !== null) {
        setUpNodeBranches(nextNodeID);
      }

      if (makingNewPath) {
        addStepToPathTrace(step, DOM.path_preview_list);
      } else {
        refreshPathPreview();
      }
    }
  }

  /* * * * * * * * * * * * * * * *
   ********************************
   * * * * * * * * * * */

  function refreshPathPreview() {
    removeAllChildren(DOM.path_preview_list);

    let paths = sessions[currentSessionID].paths;
    let currentPath = paths[stateIDPath];

    let title = "Path #" + (stateIDPath + 1) + " Preview";
    DOM.path_preview_heading.innerHTML = title;

    let thisTrace = currentPath.steps;
    console.log(thisTrace);
    for (let j = 0; j < thisTrace.length; j++) {
      addStepToPathTrace(thisTrace[j], DOM.path_preview_list);
    }

    bindPathEvents();
  }

  function refreshNodePreview() {
    removeAllChildren(/* DOM.node_preview_list */);

    console.log(sessions[currentSessionID]);
    let nodes = sessions[currentSessionID].nodes;
    let paths = sessions[currentSessionID].paths;

    let title = "Node #" + (stateIDPath + 1) + " Preview";
    DOM.path_preview_heading.innerHTML = title;
  }

  function preparePath() {
    $(DOM.dom_group_1).addClass("d-none");

    // set up the branch selection
    setUpNodeBranches(ccoi.ccoiSchema.firstNodeID);

    refreshPathPreview();
    $(DOM.path_input).removeClass("d-none");
    $(DOM.path_preview).removeClass("d-none");
  }

  function findSessionIndexById(arr, id) {
    const requiredIndex = arr.findIndex((el) => {
      return el.id == id;
    });

    return requiredIndex;
  }

  function startNewPath() {
    makingNewPath = true;
    // Reset path
    DOM.path_label.value = "";
    // form new path output
    console.log(sessions);
    console.log("This is the gamer variable: " + sessionID);
    let sessionIndex = findSessionIndexById(sessions, sessionID);
    console.log(sessionIndex);
    let session = sessions[sessionIndex];
    console.log(
      sessions[sessionIndex] + "\n This is the current session... is it?"
    );
    let paths = session.paths;
    stateIDPath = paths.length;
    if (isDemo) {
      alteredSessionData.id = currentSessionID;
      session.id = currentSessionID;
    } else {
      alteredSessionData.id = session.id;
    }

    if (alteredSessionData.paths == undefined) {
      alteredSessionData.paths = [];
    }
    // If the new path is attempting to be placed into a deleted index, we need to
    // set isEdited to true and isDeleted to false
    if (alteredSessionData.paths[stateIDPath] != undefined) {
      if (alteredSessionData.paths[stateIDPath].isDeleted == true) {
        alteredSessionData.paths[stateIDPath + 1] = {
          label: -1,
          steps: [],
          id: stateIDPath + 1,
          isNew: true,
        };
      }
    } else {
      // Else it's a new index and we need to set isNew to true
      alteredSessionData.paths[stateIDPath] = {
        label: -1,
        steps: [],
        id: stateIDPath + 1,
        isNew: true,
      };
    }
    console.log(alteredSessionData);
    paths.push({ label: -1, steps: [] });
    stateIDStep = 0;

    preparePath();
  }

  function retracePath(index) {
    makingNewPath = false;

    // Set path label if it exists
    let pathLabel = sessions[currentSessionID].paths[index].label;
    if (pathLabel != -1 && pathLabel != null && pathLabel != "") {
      DOM.path_label.value = sessions[currentSessionID].paths[index].label;
    }
    stateIDPath = index;
    stateIDStep = 0;

    preparePath();
  }

  function pathGoBack() {
    let paths = sessions[currentSessionID].paths;
    let currentTrace = paths[stateIDStep].steps;

    if (stateIDStep === 0) {
      goToPathStart();

      return;
    }

    if (makingNewPath) {
      currentTrace.splice(stateIDStep, currentTrace.length - stateIDStep);
      removeLastChild(DOM.path_preview_list);
    }

    stateIDStep--;
    setUpNodeBranches(currentTrace[stateIDStep].nodeid);
  }

  function makeDirty() {
    sessions[currentSessionID].dirty = true;

    $(DOM.save_session_button).removeClass("disabled");
  }

  function setPathLabel() {
    let session = sessions[currentSessionID];
    let paths = session.paths;
    let currentPath = paths[stateIDPath];
    let pathValue = DOM.path_label.value;
    if (pathValue != null && pathValue != "") {
      makeDirty();
      alteredSessionData.id = sessions[currentSessionID].id;
      // If alteredSessionData does not currently have a path at the current index
      // paths have not yet been altered or this is a new path, either way we can start adding data
      function makeNewAtIndex(newAt, pv) {
        newAt.label = pv;
        newAt.isDeleted = false;
        newAt.originalIndex = stateIDPath;
      }
      if (alteredSessionData.paths == undefined) {
        console.log("Alt paths undefined");
        alteredSessionData.paths = [];
        alteredSessionData.paths[stateIDPath] = {};
        makeNewAtIndex(alteredSessionData.paths[stateIDPath], pathValue);
      } else if (alteredSessionData.paths[stateIDPath] == undefined) {
        alteredSessionData.paths[stateIDPath] = {};
        // Else if only the path at the current index is undefined, create an empty object and fill it
        makeNewAtIndex(alteredSessionData.paths[stateIDPath], pathValue);
      } else {
        // Else this path exists in alteredSessionData so we only want to change its paths label
        alteredSessionData.paths[stateIDPath].label = pathValue;
      }
      console.log(alteredSessionData);
      currentPath.label = pathValue;
    }
  }

  function bindPathEvents() {
    $(".path-delete-icon").click(function () {
      deletePath($(this).data().index);
    });
    if (isPathLabelAdded == false) {
      isPathLabelAdded = true;
      $(DOM.path_label_button).click(function () {
        setPathLabel();
      });
    }
    $(".path-edit-icon").click(function () {
      retracePath($(this).data().index);
    });

    $('#branch_radio_form input[type="radio"]').change(function () {
      makeDirty();

      // MCB add back once we have video
      // TODO: change to support video generically, even on same page
      /*
            if (popoutWindow && isFirstSelection) {
                isFirstSelection = false;
                popoutWindow.video.pause();
                let currentTime = Math.round(popoutWindow.video.currentTime);
                let mins = Math.floor(parseInt(currentTime) / 60);
                let secs = currentTime % 60;
                $("input[type='number']#timestampInputMinutes").val(mins);
                $("input[type='number']#timestampInputSeconds").val(secs);
            }
            */
    });
  }

  function refreshSessionList() {
    currentSessionID = findSessionIndexById(sessions, sessionID);
    //console.log ("Here's yo current session id: " + currentSessionID);
    // if (DOM.session_list === null) {
    //     console.log("BURP!");
    //     return;
    // }
    // removeAllChildren(DOM.session_list);
    // $(DOM.session_list).removeClass('d-none');
    // $(DOM.irr_button).removeClass('d-none');

    // let sessionsLength = sessions.length;
    // for (let i = 0; i < sessionsLength; i++) {
    //     let session = sessions[i];
    //     appendSessionLink(DOM.session_list, i, isDemo);
    //     if (isDemo) $('#session_'+i+'_name').text("Demo Session")
    //     else if (session.name != null) $('#session_'+i+'_name').text(session.name);
    //     else if (session.videoURL != null) {
    //         // If name not explicitly set, use video title
    //         let name = session.videoURL.replace(/\.[^/.]+$/, "");
    //         name += " ("+session.observer+")";
    //         $('#session_' + i + '_name').text(name);
    //     } else {
    //         $('#session_'+i+'_name').text("Session "+i);
    //     }
    // }

    // /*$('.session-edit').click(function() {
    //     currentSessionID = $(this).data().index;
    //     $(DOM.new_session_button).addClass('d-none');
    //     // This is used to add new paths to alteredSessionData
    //     originalPathsLength = sessions[currentSessionID].paths.length;
    //     goToPathStart(currentSessionID);
    // });*/

    // $('.session-edit').click(function() {
    //     console.log("Refreshing current variables");
    //     console.log("Found session index: " + findSessionIndexById(sessions, sessionID));
    //     currentSessionID = findSessionIndexById(sessions, sessionID);
    //     $(DOM.new_session_button).addClass('d-none');
    //     originalPathsLength = sessions[currentSessionID].paths.length;
    //     goToPathStart(currentSessionID);
  }

  /*
        $('.sessionDeleteIcon').click(function () {
            deleteSession($(this).data().index);
        });
        */

  function getDemoSessions() {
    let localSessions = localStorage.getItem("sessions");
    if (localSessions != null && localSessions != "" && localSessions != "[]") {
      $(DOM.irr_button).removeClass("d-none");
      sessions = [];
      let tempSession = [];
      // preserve newlines, etc - use valid JSON
      /*localSessions = localSessions.replace(/\\n/g, "\\n")
                .replace(/\\'/g, "\\'")
                .replace(/\\"/g, '\\"')
                .replace(/\\&/g, "\\&")
                .replace(/\\r/g, "\\r")
                .replace(/\\t/g, "\\t")
                .replace(/\\b/g, "\\b")
                .replace(/\\f/g, "\\f");
*/
      // remove non-printable and other non-valid JSON chars
      //localSessions = localSessions.replace(/[\u0000-\u001F]+/g,"");
      localSessions = localSessions.replace(/\'/gi, "");
      tempSession = JSON.parse(localSessions);
      for (var i = 0; i < tempSession.length; i++) {
        sessions[i] = new CCOI_Session(tempSession[i]);
      }

      refreshSessionList();
    } else {
      $(DOM.new_session_button).removeClass("d-none");
    }
  }

  function getSessions() {
    if (isDemo) {
      getDemoSessions();
    } else {
      console.log("starting getSessions for pid: " + jsUserVars["pid"]);
      if (jsUserVars["pid"] != undefined) {
        ccoi.callToAPI("/api/ccoi_ajax.php?uid=" + jsUserVars["pid"]).then(
          function (responseText) {
            //console.log("no longer printing sessions. Insert responseText here for all sessions.")
            sessions = [];
            $(DOM.new_session_button).removeClass("d-none");
            if (responseText != null && responseText != "null") {
              //console.log(JSON.parse(responseText));
              sessions = JSON.parse(responseText);
              for (let i = 0; i < sessions.length; i++) {
                sessions[i] = new CCOI_Session(sessions[i]);
              }
              refreshSessionList();
            } else {
              $("#empty_sessions").removeClass("d-none");
            }
          },
          function (error) {
            console.log(error);
          }
        );
      } else {
        let conf = confirm("You need to login before observing!");
        if (conf == true) {
          window.location.replace("/login");
        } else {
          window.location.replace("/login");
        }
      }
    }
  }

  function createSession(name, observer, id) {
    let newSession = new CCOI_Session(name);
    newSession.observer = observer;
    newSession._id = id;
    sessions.push(newSession);

    refreshSessionList();
  }

  function addNewSession() {
    $(DOM.new_session_button).attr("disabled", true);

    if (isDemo) {
      $(DOM.new_session_button).addClass("d-none");
    }

    let numSessions = sessions.length + 1;
    let sessionTitle = "Session " + numSessions;

    if (isDemo) {
      createSession("Demo Session", "Demo User", 10001);
      let jsonSessions = JSON.stringify(sessions);
      localStorage.setItem("sessions", jsonSessions);
    } else {
      $("#empty_sessions").addClass("d-none");
      const observer = jsUserVars["first"] + " " + jsUserVars["last"];
      let sessionNum = sessions.length;
      createSession("New Session", observer, sessionNum);
    }
  }

  function updateData() {
    console.log("Is the error happening before? Or after?");
    let sessionsToUpdate = [];

    sessions.forEach(function (session) {
      if (session.dirty) {
        session.dirty = false;
        sessionsToUpdate.push(session);
      }
    });
    console.log(JSON.stringify(sessionsToUpdate));

    if (isDemo) {
      let demoSession = JSON.stringify(sessionsToUpdate);
      localStorage.setItem("sessions", demoSession);
      sessionsToUpdate.length = 0;
      $("#save_session_button").addClass("disabled");
    } else {
      //console.log(alteredSessionData);

      for (let i = alteredSessionData.paths.length - 1; i >= 0; i--) {
        if (alteredSessionData.paths[i] == null) {
          alteredSessionData.paths.splice(i, 1);
        }
      }
      //console.log(alteredSessionData);
    }
    for (let i = alteredSessionData.paths.length - 1; i >= 0; i--) {
      if (alteredSessionData.paths[i] == null) {
        alteredSessionData.paths.splice(i, 1);
      }
    }
    //removeEmptyKeys(alteredSessionData);
    alteredSessionData = removeEmptyAndOld(alteredSessionData);
    let sendData = JSON.stringify(alteredSessionData);
    console.log("Data being transmitted to save:");
    console.log(sendData);

    //TODO: AJAX HERE
    var xmlHttp = GetAjaxReturnObject("text/html");
    if (xmlHttp == null) {
      alert("Your browser does not support AJAX!");
      return;
    }
    
    xmlHttp.onreadystatechange = function () {
      var data = getHTML(xmlHttp);
      if (data) {
        console.log("AJAX returns this:");
        console.log(data);
      } else {
        console.log("Here?");
      }
    };
    console.log('aaa');
    console.log(alteredSessionData);
    // console.log('xxx' + $.param(alteredSessionData));
    // var sendStr = "updateObsEl=1&" + $.param(alteredSessionData);
    // console.log("sendStr:");
    // console.log(sendStr);
    // var url = encodeURI(derServer + "ZPB/zpb_ajax.php?" + sendStr);
    // console.log(url);
    // xmlHttp.open("POST", url, true);
    // xmlHttp.setRequestHeader(
    //   "Content-Type",
    //   "application/x-www-form-urlencoded"
    // );
    // xmlHttp.send(sendStr);

    //ccoi.callToAPI('/api/ccoi_ajax.php', sendData);
  }

  /*
    * The following three functions are disabled as the reorder functionality is not used
    function toggleDraggables(toAdd) {
        let containers = $('.path-listing-container');
        if (containers == undefined) { return; }
        else if (toAdd) {
            $(DOM.reorder_paths_button).addClass('d-none');
            $(DOM.finish_reorder_button).removeClass('d-none');
            containers.addClass('draggable');
            containers.attr('draggable', 'true');
        }
        else if (!toAdd) {
            $(DOM.reorder_paths_button).removeClass('d-none');
            $(DOM.finish_reorder_button).addClass('d-none');
            containers.removeClass('draggable');
            containers.attr('draggable', 'false');
        }
    }

    function reorderArray(initArray, indexArray) {
        let tempArray = [];
        for (let i=0; i<initArray.length; i++) {
            tempArray[indexArray[i]] = initArray[i];
        }
        for (let j=0; j<initArray.length; j++)
        {
            initArray[j]   = tempArray[j];
            indexArray[j] = j;
        }
        return initArray;
    }

    function setNewPathOrder() {
        console.log(alteredSessionData.paths);
        let newPathOrder = [];
        let currentSession = sessions[currentSessionID];
        let initialPathsArray = currentSession.paths;
        $('.path-listing-header').each(function(index) {
            newPathOrder[$(this).data('index')] = index;
        });
        console.log("New path array");
        console.log(newPathOrder);
        // If alteredSessionData does not have an active ID, nothing has been changed
        if (alteredSessionData.id == undefined) {
            alteredSessionData.id = currentSession.id;
            alteredSessionData.paths = initialPathsArray;
            alteredSessionData.paths.forEach( function (path, index) {
                path.isDeleted = false;
                path.originalIndex = index;
            });
        } else if (initialPathsArray.length == alteredSessionData.paths.length) {
            // Else the session has be altered in some way
            // If our session paths and altered session paths are the same length,
            // the change made (either an add, delete, or previous reorder) will only require us
            // to reorder the altered sessionDataPaths
            sessions[currentSessionID].paths = reorderArray(initialPathsArray, newPathOrder);
            alteredSessionData.paths = reorderArray(alteredSessionData.paths, newPathOrder);
            console.log(alteredSessionData.paths);
        }
        // Comment this out or delete if reorder is added back
        sessions[currentSessionID].paths.forEach(function(path, index) {
            path.isDeleted = false;
            // If this is the first change made, neither of these will be set
            // if they are not set, set them
            if (path.isDeleted == undefined) path.isDeleted = false;
            if(path.originalIndex == undefined) path.originalIndex = index;
        });
        sessions[currentSessionID].paths = reorderArray(initialPathsArray, newPathOrder);
    }
    */

  function popoutListeners() {
    $("#vid_speed_1x").click(function () {
      popoutWindow.changeSpeed(1.0);
    });
    $("#vid_speed_1_5x").click(function () {
      popoutWindow.changeSpeed(1.5);
    });
    $("#vid_speed_2x").click(function () {
      popoutWindow.changeSpeed(2.0);
    });
  }

  function launchVideoFromSession() {
    let videoID = $("#session_video_url").val();
    popoutWindow = window.open("/video-player"); // to avoid browser pop up blockers, we have to load the pop up window directly in the on click, not in the ajax call.
    // Add click event to proceed and play button now that we have a video
    $(DOM.proceed_and_play_button).click(function () {
      submitBranch();
      popoutWindow.video.play();
    });
    popoutListeners();
    if (isDemo) {
      let videoSRC = "/videofiles/7ccU4vf8zW7bto1s5Ry63qRl.webm";
      popoutWindow.src = videoSRC;
      popoutWindow.videoTitle = "Demo Session Video";
    } else {
      $.ajax({
        url: "/api/ccoi_ajax.php?fetchvid=" + $_GET.url,
        method: "GET",
        contentType: "application/json; charset=utf-8",
        success: function (data) {
          let videoSRC = data[0];
          let videoTitle = $("#session_video_title").val();
          popoutWindow.src = videoSRC;
          popoutWindow.videoTitle = videoTitle;
        },
      }).fail(function (err) {
        console.log(err);
        console.log(this);
      });
    }
  }

  function launchVideoFrameFromSession() {
    let videoID = $("#session_video_url").val();

    let videoFrame = document.createElement("iframe");
    videoFrame.class = "embed-responsive-item";

    // popoutWindow = window.open('/video-player'); // to avoid browser pop up blockers, we have to load the pop up window directly in the on click, not in the ajax call.
    // // Add click event to proceed and play button now that we have a video
    // $(DOM.proceed_and_play_button).click(function () {
    //     submitBranch();
    //     popoutWindow.video.play();
    // });
    // popoutListeners();
    if (isDemo) {
      let videoSRC = "https://youtube.com/embed/astISOttCQ0";
      videoFrame.src = videoSRC;
      videoFrame.videoTitle = "Demo Session Video";
      let frameContainer = document.getElementById("videoFrameContainer");
      frameContainer.appendChild(videoFrame);
    } else {
      $.ajax({
        url: "/api/ccoi_ajax.php?fetchvid=" + videoID,
        method: "GET",
        contentType: "application/json; charset=utf-8",
        success: function (data) {
          let videoSRC = data[0];
          let videoTitle = $("#session_video_title").val();
          videoFrame.src = videoSRC;
          videoFrame.videoTitle = videoTitle;
        },
      }).fail(function (err) {
        console.log(err);
        console.log(this);
      });
    }
  }

  function bindSessionMetaForm() {
    $(DOM.session_meta_form).change(function (e) {
      let changedName = e.target.name;
      makeDirty();
      alteredSessionData.id = sessions[currentSessionID].id;
      alteredSessionData[changedName] = e.target.value;
      console.log(alteredSessionData);
      // Old manner of updating session, which sends the whole session with overwritten value (still used for demo)
      sessions[currentSessionID][changedName] = e.target.value;
    });
  }

  function bindListeners() {
    $(window).on("beforeunload", function () {
      let isDirty = !$(DOM.save_session_button).hasClass("disabled");
      if (isDirty) {
        return "Are you sure you want to leave?";
      } else {
        return undefined;
      }
    });
    $(DOM.new_session_button).click(addNewSession);
    $(DOM.add_path_button).click(startNewPath);
    $(DOM.proceed_button).click(submitBranch);
    $(DOM.save_session_button).click(updateData);
    $(DOM.path_go_back).click(pathGoBack);
    //Update the function below to switch between popup window or in window experience
    $(DOM.launch_video_button).click(launchVideoFrameFromSession);
    //Revist once implementing IRR correctly.
    //$(DOM.irr_button).click()
    /*
        * Reorder functionality disabled, as it is not actually used by researchers
        $(DOM.reorder_paths_button).click(function(e) {
            toggleDraggables(true);
            ccoiDraggables.setDragabbles(document.querySelectorAll('.draggable'), document.querySelectorAll('.draggable-container'));
            ccoiDraggables.initiateDraggables();
        });
        $(DOM.finish_reorder_button).click(function(e) {
            makeDirty();
            setNewPathOrder();
            console.log(alteredSessionData);
            toggleDraggables(false);
            $('.path-order-disclaimer').removeClass('d-none');
        });
         */
    bindSessionMetaForm();
  }

  return {
    isDemo: isDemo,
    currentSessionID: currentSessionID,
    setDemoBool: setDemoBool,
    getSessions: getSessions,
    bindListeners: bindListeners,
  };
})();