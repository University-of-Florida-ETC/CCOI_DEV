"use strict";

var ccoiObservation = (function (){
    var sessions = [];
    var isDemo = false;
    var makingNewPath = true;
    var stateIDPath;
    var stateIDStep;
    var isFirstSelection = true;
    var currentSessionID;
    var nodeID;
    var popoutWindow;
    
    function setDemoBool(sdb) {
        isDemo = sdb;
    }
    
    function addBranch (value, description, checked, divToAttachTo, aside = false) {
        let branchForm = $(divToAttachTo);
        if (checked === undefined) {
            checked = false;
        }
        if(aside){ description+='<br/><span class="aside">' + aside + '</span>';}
        branchForm.append(`
            <p>
                <input type="radio" name="choiceRadio" id="choiceRadio${value}" value="${value}" ${checked==true ? 'checked' : ''}>
                <label for="choiceRadio${value}" class="${aside ? 'choiceOfList containsAside' : 'choiceOfList'}">${description}</label>
            </p>
        `);
    }

    // This is called to set up the radio button choices
    function setUpNodeBranches (nextNodeID) {
        var node = ccoi.ccoiSchema.getNode(nextNodeID);
        nodeID = node.node_id;

        // Backstop for old ID system
        let prettyID = parseInt(node.id);

        // update title
        DOM.path_title.innerHTML = "("+node.id+") "+node.title;

        // clear the current list
        // TODO: update to clear branchContainer - this will clear nothing the first time ran
        $('#branch_container').empty();
        $('#branch_container').append('<form id="branch_radio_form" class="col-12 pt-3" action="javascript:void(0)"></form>');

        let selectedChoice = null;
        let currentStep = sessions[currentSessionID].paths[stateIDPath].steps[stateIDStep];
        // contains backstop for old ID system
        if (currentStep !== undefined && (nodeID === currentStep.node || prettyID === currentStep.node)) {
            DOM.timestamp_input_minutes.value = currentStep.minutes;
            DOM.timestamp_input_seconds.value = currentStep.seconds;
            DOM.notes_input.value = currentStep.notes;
            selectedChoice = currentStep.choice;
        }

        // TODO: update to add contents to branchContainer
        if (node.should_group_choices) {
            let numGroups = node.branches.length;
            let branchRadioForm = $('#branch_radio_form');
            branchRadioForm.append('<div class="accordion" id="branch_group_accordion"></div>');
            let radioButtonIndex = 0;

            for (let groupIndex = 0; groupIndex < numGroups; groupIndex++) {
                let groupName = node.branch_group_names[groupIndex];
                $('#branch_group_accordion').append(`
                    <div class="card mb-3">
                        <div class="card-header" id="heading_${groupIndex}">
                            <h5 class="mb-0">
                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapse_${groupIndex}" aria-expanded="${groupIndex == 0 ? 'true' : 'false' }" aria-controls="collapse_${groupIndex}">
                                    ${groupName}
                                </button>
                            </h5>
                        </div>
                        <div id="collapse_${groupIndex}" class="${groupIndex == 0 ? 'collapse show' : 'collapse' }" aria-labelledby="heading_${groupIndex}" data-parent="#branch_group_accordion">
                            <div id="collapse_${groupIndex}_body" class="card-body">
                            </div>
                        </div>
                    </div>
                `);
                let numBranches = node.branches[groupIndex].length;
                for (let branchIndex = 0; branchIndex < numBranches; branchIndex++) {
                    let branch = node.branches[groupIndex][branchIndex];
                    // function addBranch (value, description, checked, divToAttachTo, aside = false)
                    let isSelected = selectedChoice === branch.branch_id || selectedChoice === branch.pretty_id; 
                    addBranch(branch.branch_id, '(' + ChoiceID(branch.pretty_id) + ') ' + branch.description, isSelected, `#collapse_${groupIndex}_body`, branch.aside);
                    radioButtonIndex++;
                }
            }
            addBranch(-1, 'Other (use notes)', selectedChoice === -1, '#branch_radio_form');
        } else {
            let numBranches = node.branches.length;
            for (let i = 0; i < numBranches; i++) {
                let branch = node.branches[i];
                let isSelected = selectedChoice === branch.branch_id || selectedChoice === branch.pretty_id; 
                addBranch(branch.branch_id, '(' + ChoiceID(branch.pretty_id) + ') ' + branch.description, isSelected, '#branch_radio_form', branch.aside);
            }
            addBranch(-1, 'Other (use notes)', selectedChoice === -1);
        }

        bindPathEvents();
    }
    
    function loadSessionInfo(index) {
        $(DOM.session_list).addClass("d-none");
        
        let session = sessions[index];
        
        $('#session_title').val(session.name);
        $('#session_observer').val(session.observer);
        $('#session_student').val(session.studentID);
        $('#session_notes').val(session.sessionNotes);
        $('#session_date').val(session.date);
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
        $('.path-dropdown-btn').click(function(e){
            let classList = $(this)[0].children[0].classList;
            classList.contains('oi-chevron-bottom') ? classList.replace('oi-chevron-bottom', 'oi-chevron-top')
                : classList.replace('oi-chevron-top', 'oi-chevron-bottom');
        });
        bindPathEvents();
    }
    
    function goToPathStart() {
        if (currentSessionID < 0 || currentSessionID > sessions.length) { return; }
        $(DOM.session_list).addClass('d-none');
        $(DOM.dom_group_1).removeClass('d-none');
        $(DOM.session_go_back).removeClass('d-none');
        $(DOM.save_session_button).removeClass('d-none');
        $(DOM.path_input).addClass('d-none');
        $(DOM.path_preview).addClass('d-none');
        loadSessionInfo(currentSessionID);
        refreshPathListing();
    }
    
    function addPathTraceToTable(paths, pathIndex) {

        let currentPath = paths[pathIndex];
        let name = 'Path #' + (pathIndex + 1);
        if(currentPath.label != -1 && currentPath.label != null && currentPath.label != '')
          name += " (" + currentPath.label + ")";

        let newOutputDIV = document.createElement('div');
        newOutputDIV.setAttribute('class', 'path-listing-container');

        DOM.path_list.appendChild(newOutputDIV);

        if (name !== undefined) {
            let newOutputH = document.createElement('h5');
            newOutputH.setAttribute('data-index', pathIndex);
            newOutputH.setAttribute('class', 'path-listing-header');
            name += `<a class="btn-link path-edit-icon" href="#" data-index="${pathIndex}"><span class="oi oi-pencil px-3" title="Edit Path" aria-hidden="true"></span></a>
            <a class="btn-link path-delete-icon" href="#" data-index="${pathIndex}"><span class="oi oi-trash" title="Delete Path" aria-hidden="true"></span></a>
            <button class="btn-link float-right path-dropdown-btn" data-toggle="collapse" data-target="#path_drop_${pathIndex}"><span class="oi oi-chevron-bottom" title="Show Path Steps" aria-hidden="true"></span></button>`;
            newOutputH.innerHTML = name;
            newOutputDIV.appendChild(newOutputH);
        }
        let list = document.createElement('ol');
        list.setAttribute('class', 'collapse');
        list.setAttribute('id', "path_drop_"+pathIndex);
        newOutputDIV.appendChild(list);
        return list;
    }
    
    function addStepToPathTrace(step, list) {
        let newOutputLI = document.createElement('li');
        newOutputLI.innerHTML = step.output();
        list.appendChild(newOutputLI);
    }
    
    function deletePath(index) {
        if (confirm('Are you sure you want to delete path #' + (index + 1) + '?')) {
            let session = sessions[currentSessionID];
            let paths = session.paths;
            paths.splice(index, 1);
            session.dirty = true;
            $(DOM.save_session_button).removeClass('disabled');
            goToPathStart();
        } else { return false; }
    }

    function submitBranch () {
        // reset to true so next branch will pause upon first radio button selection
        isFirstSelection = true;
        let choiceIndex = $('input[name="choiceRadio"]:checked', '#branch_radio_form').val();
        if(choiceIndex === "-1")
            choiceIndex = -1;

        if (choiceIndex === undefined) {
            alert('Please select an option.');
            return false;
        }

        // process the form data into the structure
        let session = sessions[currentSessionID];
        let paths = session.paths;
        let currentTrace = paths[stateIDPath].steps;
        console.log("state id path");
        console.log(stateIDPath);
        let currentStep = currentTrace[stateIDStep];

        // Need to make the session dirty
        makeDirty();

        // if currentStep is defined that means you are retracing an existing path
        if (currentStep !== undefined && (currentStep.node !== nodeID || currentStep.choice !== choiceIndex)) {
            // If changing the choice leads to the same node:
            var currentStepNextNodeID;
            // catch empty/deleted nodes
            try {
                currentStepNextNodeID = currentStep.nextNodeID()
            }
            catch(err){
                currentStepNextNodeID = -1;
            }

            var newChoice = getNodeFromChoice(nodeID, choiceIndex);
            if(newChoice && currentStepNextNodeID === newChoice.next){
                // I'm not sure if we actually have to do anything here to get the choice to quietly swap out.
                console.log("Changing choice, but it should go to the same node?");
            }
            else {
                if (stateIDStep + 1 < currentTrace.length && confirm('Changing your choice will delete the choices made in this path after this choice. Are you sure?') === false) { return false; }

                currentTrace.splice(stateIDStep, currentTrace.length - stateIDStep);
                currentStep = undefined;
            }
        }

        let extra = null;
        let branchExtra = choiceIndex === -1 ? null : getNodeFromChoice(nodeID, choiceIndex).extra;
        if (branchExtra !== null && branchExtra !== undefined && branchExtra !== '') {
            let extraText = "\nComma separate if multiple. If unknown, P1, P2, ... or A1, A2, ... as necessary.";
            extra = prompt('Please enter: ' + branchExtra + extraText, currentStep === undefined ? '' : currentStep.extra);
        }

        let minutesValue = parseInt(DOM.timestamp_input_minutes.value, 10);
        if (isNaN(minutesValue)) { DOM.timestamp_input_minutes.value = minutesValue = session.minutes; } else { session.minutes = minutesValue; }

        let secondsValue = parseInt(DOM.timestamp_input_seconds.value, 10);
        if (isNaN(secondsValue)) { DOM.timestamp_input_seconds.value = secondsValue = session.seconds; } else { session.seconds = secondsValue; }

        let notes = DOM.notes_input.value;
        DOM.notes_input.value = '';

        let step = new CCOI_Step(nodeID, choiceIndex, minutesValue, secondsValue, extra, notes);

        currentTrace[stateIDStep] = step;
        stateIDStep++;

        let nextNodeID = step.nextNodeID();

        if (nextNodeID === null || nextNodeID === undefined || nextNodeID === '') {
            goToPathStart();
        } else {
            if (nextNodeID !== null) {
                setUpNodeBranches(nextNodeID);
            }

            if (makingNewPath) { addStepToPathTrace(step, DOM.path_preview_list); } else { refreshPathPreview(); }
        }
    }

    function refreshPathPreview () {
        removeAllChildren(DOM.path_preview_list);

        let paths = sessions[currentSessionID].paths;
        let currentPath = paths[stateIDPath];

        let title = 'Path #' + (stateIDPath + 1) + ' Preview';
        DOM.path_preview_heading.innerHTML = title;

        let thisTrace = currentPath.steps;
        for (let j = 0; j < thisTrace.length; j++) {
            addStepToPathTrace(thisTrace[j], DOM.path_preview_list);
        }

        bindPathEvents();
    }
    
    function preparePath() {

        $(DOM.dom_group_1).addClass('d-none');

        // set up the branch selection
        setUpNodeBranches(ccoi.ccoiSchema.firstNodeID);

        setSectionTitle('Path #' + (stateIDPath + 1));

        //closeAllDivs();
        refreshPathPreview();
        $(DOM.path_input).removeClass('d-none');
        $(DOM.path_preview).removeClass('d-none');
    }
    
    function startNewPath() {
        makingNewPath = true;
        // Reset path
        DOM.path_label.value = '';
        // form new path output
        let paths = sessions[currentSessionID].paths;
        stateIDPath = paths.length;
        paths.push({label: -1, steps: []});
        stateIDStep = 0;

        preparePath();
    }
    
    function retracePath (index) {
        makingNewPath = false;

        // Set path label if it exists
        let pathLabel = sessions[currentSessionID].paths[index].label;
        if (pathLabel != -1 && pathLabel != null && pathLabel != '') {
            DOM.path_label.value = sessions[currentSessionID].paths[index].label;
        }
        stateIDPath = index;
        stateIDStep = 0;

        preparePath();
    }

    function pathGoBack () {
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
        setUpNodeBranches(currentTrace[stateIDStep].node);
    }

    function makeDirty() {
        sessions[currentSessionID].dirty = true;

        $(DOM.save_session_button).removeClass('disabled');
    }

    function setPathLabel() {
        let session = sessions[currentSessionID];
        let paths = session.paths;
        let currentPath = paths[stateIDPath];
        let pathValue = DOM.path_label.value;
        if (pathValue != null && pathValue != "") {
            makeDirty();
            currentPath.label = pathValue;
        }
    }

    function bindPathEvents () {
        $('.path-delete-icon').click(function() {
            deletePath($(this).data().index);
        });
        $(DOM.path_label_button).click(function() {
           setPathLabel();
        });
        $('.path-edit-icon').click(function() {
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
    
    function refreshSessionList () {
        if (DOM.session_list === null) {
            return;
        }
        removeAllChildren(DOM.session_list);
        $(DOM.session_list).removeClass('d-none');
        $(DOM.irr_button).removeClass('d-none');

        let sessionsLength = sessions.length;
        for (let i = 0; i < sessionsLength; i++) {
            let session = sessions[i];
            appendSessionLink(DOM.session_list, i, isDemo);
        }
        
        $('.session-edit').click(function() {
            currentSessionID = $(this).data().index;
            goToPathStart(currentSessionID);
        });

        /*
        $('.sessionDeleteIcon').click(function () {
            deleteSession($(this).data().index);
        });
        */
    }
    
    function getDemoSessions() {
        let localSessions = localStorage.getItem("sessions");
        if (localSessions != null && localSessions != "" && localSessions != "[]") {
            $(DOM.irr_button).removeClass('d-none');
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
            localSessions = localSessions.replace(/\'/gi,'');
            console.log(localSessions);
            tempSession = JSON.parse(localSessions);
            for (var i=0; i<tempSession.length; i++) {
                sessions[i] = new CCOI_Session(tempSession[i]);
            }
            
            refreshSessionList();
        } else {
            $(DOM.new_session_button).removeClass('d-none');
        }

    }
    
    function getSessions() {
        if(isDemo) {
            getDemoSessions();
        } else {
            //ccoi.callToAPI('/api/ccoi_ajax')
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
        $(DOM.new_session_button).attr('disabled', true);

        if (isDemo) { $(DOM.new_session_button).addClass('d-none'); }
        
        let numSessions = sessions.length + 1;
        let sessionTitle = 'Session ' + numSessions;
            
        if (isDemo) {
            createSession("Demo Session", "Demo User", 10001);
            let jsonSessions = JSON.stringify(sessions);
            localStorage.setItem("sessions", jsonSessions);
        } else {
            // Hit API to create session
        }
    }

    function updateData () {
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
            $('#save_session_button').addClass('disabled');
        } else {
            // Hit API to update
        }
    }

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
        let newPathOrder = [];
        console.log(sessions[currentSessionID].paths);
        let initialPathsArray = sessions[currentSessionID].paths;
        $('.path-listing-header').each(function(index) {
            newPathOrder[$(this).data('index')] = index;
        });
        sessions[currentSessionID].paths = reorderArray(initialPathsArray, newPathOrder);
    }

    function popoutListeners() {
        $('#vid_speed_1x').click(function() {
            popoutWindow.changeSpeed(1.0);
        });
        $('#vid_speed_1_5x').click(function() {
            popoutWindow.changeSpeed(1.5);
        });
        $('#vid_speed_2x').click(function() {
            popoutWindow.changeSpeed(2.0);
        });
    }

    function launchVideoFromSession () {
        let videoID = $('#session_video_url').val();
        popoutWindow = window.open('/video-player'); // to avoid browser pop up blockers, we have to load the pop up window directly in the on click, not in the ajax call.
        // Add click event to proceed and play button now that we have a video
        $(DOM.proceed_and_play_button).click(function () {
            submitBranch();
            popoutWindow.video.play();
        });
        popoutListeners();
        if(isDemo) {
            let videoSRC = '/videofiles/7ccU4vf8zW7bto1s5Ry63qRl.webm';
            popoutWindow.src = videoSRC;
            popoutWindow.videoTitle = 'Demo Session Video'
        }
        else {
            
        }
    }

    function bindSessionMetaForm () {
        $(DOM.session_meta_form).change( function(e) {
            let changedName = e.target.name;
            sessions[currentSessionID].dirty = true;
            sessions[currentSessionID][changedName] = e.target.value;
            $(DOM.save_session_button).removeClass('disabled');
        });
    }
    
    function bindListeners() {
        $(window).on('beforeunload', function () {
            let isDirty = !$(DOM.save_session_button).hasClass('disabled');
            if (isDirty) {
                return 'Are you sure you want to leave?';
            } else {
                return undefined;
            }
        });
        $(DOM.new_session_button).click(addNewSession);
        $(DOM.add_path_button).click(startNewPath);
        $(DOM.proceed_button).click(submitBranch);
        $(DOM.save_session_button).click(updateData);
        $(DOM.path_go_back).click(pathGoBack);
        $(DOM.launch_video_button).click(launchVideoFromSession);
        $(DOM.reorder_paths_button).click(function(e) {
            toggleDraggables(true);
            ccoiDraggables.setDragabbles(document.querySelectorAll('.draggable'), document.querySelectorAll('.draggable-container'));
            ccoiDraggables.initiateDraggables();
        });
        $(DOM.finish_reorder_button).click(function(e) {
            makeDirty();
            setNewPathOrder();
            toggleDraggables(false);
            $('.path-order-disclaimer').removeClass('d-none');
        });
        bindSessionMetaForm();
    }
    
    return {
        isDemo: isDemo,
        currentSessionID: currentSessionID,
        setDemoBool: setDemoBool,
        getSessions: getSessions,
        bindListeners: bindListeners
    }
    
})();