/**
 * Constructor for CCOI_Session object, which forms the basis of a Student Observation
 * can't overload functions in JS so using janky arg.len check with renaming first param
 *
 * @param {string} name
 * @param {*} date
 * @param {string} studentID
 * @param {*} prompted
 * @param {Array[CCOI_Step[]]} paths
 * @param {int} minutes
 * @param {int} seconds
 * @param {string} observer
 * @param {string} sessionNotes
 */

//console.log("loaded");
function CCOI_Session (name, date, studentID, prompted, paths, minutes, seconds, observer, sessionNotes) {
  if (arguments.length === 1 && name.hasOwnProperty('id')) {
    var obj = name;
    for (var prop in obj) {
      if (obj.hasOwnProperty(prop)) {
        if (prop === 'paths' && obj.paths.length > 0) {
          this['paths'] = obj[prop].map(function (path) {
			if(path.label == null){ // old format, before we added path labels - this can be removed after all db is converted to new format
				return {
					'label' : -1,
					'steps' : path.map(function (step) {
								 return new CCOI_Step(step);
							   })
				};
			}
			else {
				return {
					'label' : path.label,
					'steps' : path.steps.map(function (step) {
								 return new CCOI_Step(step);
							   })
				};
			}
          });
        } else {
          this[prop] = obj[prop];
        }
      }

      this.dirty = false;
    }
	
	if(this.paths == null) this.paths = [];

    this.label = CCOI_Session_label;
    this.concatPaths = concatPaths;
  } else {
    this.name = name || '(Untitled)';
    this.observer = observer || '';
    this.date = date || '';
    this.studentID = studentID || '';
    this.videoURL = '';
    this.prompted = prompted || false;
    this.sessionNotes = sessionNotes || '';
    this.dirty = false;
	
    this.paths = paths || [];
	console.log(this.paths);
    // the last "valid" values for this session
    this.minutes = minutes || 0;
    this.seconds = seconds || 0;

    this.label = CCOI_Session_label;
    this.concatPaths = concatPaths;
  }
}

// can't overload functions in JS so using janky arg.len check with renaming first param
/**
 * @param {*}	node
 * @param {int} node_ID
 * @param {*}	choice
 * @param {int} choice_ID
 * @param {int} minutes
 * @param {int} seconds
 * @param {int} totalSeconds
 * @param {string} extra
 * @param {string} notes
 */
function CCOI_Step (node, node_ID, choice, choice_ID, ssnum, minutes, seconds, totalSeconds, extra, notes, stateIDStep) {
  if (arguments.length === 1) {
    var obj = node;
    for (var prop in obj) {
      if (obj.hasOwnProperty(prop)) {
        this[prop] = obj[prop];
      }
    }

    this.nextNodeID = CCOI_Step_nextNodeID;
    this.nextNodeIDInt = CCOI_Step_nextNodeID_int;
    this.branchDescription = CCOI_Step_branchDescription;
    this.extraType = CCOI_Step_extraType;
    this.output = CCOI_Step_output;
  } else {
  	this.ssnum = ssnum || 0;
	this.node = node || 0;
    this.nodeid = node_ID || 0;
	this.choice = choice || 0;
    this.choiceid = choice_ID || 0;
    //this.minutes = minutes || 0;
    //this.seconds = seconds || 0;
	this.totalSeconds = totalSeconds || 0;
    this.extra = extra || null;
    this.notes = notes || null;
	this.stateIDStep = stateIDStep || 0;

    this.nextNodeID = CCOI_Step_nextNodeID;
    this.nextNodeIDInt = CCOI_Step_nextNodeID_int;
    this.branchDescription = CCOI_Step_branchDescription;
    this.extraType = CCOI_Step_extraType;
    this.output = CCOI_Step_output;
	this.pathNodeID = CCOI_Step_PathNodeID;
  }

  this.timeInSeconds = function () {
    return (this.minutes * 60) + this.seconds;
  };
}

function concatPaths () {
  let collapsedEvents = [];
  let elapsedTime = 0;
  this.paths.steps.forEach((path, pathIndex) => {
    collapsedEvents.extend(path);
  });

  return collapsedEvents;
}

function CCOI_Step_AJAX (node, node_ID, choice, choice_ID, ssnum, nextNodeID, nextNodeIDInt, branchDescription, extraType, output, pathNodeID, totalSeconds, extra, notes, stateIDStep) { 
	if (arguments.length === 1) {
		var obj = node;
		for (var prop in obj) {
		  if (obj.hasOwnProperty(prop)) {
			this[prop] = obj[prop];
		  }
		}
	
		this.nextNodeID = 0 || nextNodeID
		this.nextNodeIDInt = 0 || nextNodeIDInt;
		this.branchDescription = 'NULL' || branchDescription;
		this.extraType = 'NULL' || extraType;
		this.output = 'NULL' || output;
	  } else {
		  this.ssnum = ssnum || 0;
		this.node = node || 0;
		this.nodeid = node_ID || 0;
		this.choice = choice || 0;
		this.choiceid = choice_ID || 0;
		//this.minutes = minutes || 0;
		//this.seconds = seconds || 0;
		this.totalSeconds = totalSeconds || 0;
		this.extra = extra || null;
		this.notes = notes || null;
		this.stateIDStep = stateIDStep || 0;
	
		this.nextNodeID = 0 || nextNodeID;
		this.nextNodeIDInt = 0 || nextNodeIDInt;
		this.branchDescription = 'NULL' || branchDescription;
		this.extraType = '' || extraType;
		this.output = '' || output;
		this.pathNodeID = pathNodeID || 0;
	  }
	

}
/**
 * This function takes two params, and will duplicate {base} onto {ajax}, populating the object method slots with filled values. I am so sorry about this.  
 * @param {object} base - the base array 
 * @param {object} ajax - the object you intend to use with AJAX
 * @return {object} ajaxReadyObject
 */
function goGoAjax(base, ajax) {
	//
	//ajax.nextNodeID = base.nextNodeID();
	//ajax.nextNodeIDInt = base.nextNodeIDInt();
	//ajax.branchDescription = base.branchDescription();
	//ajax.extraType = base.extraType();
	//ajax.output = base.output();
	//ajax.pathNodeID = base.pathNodeID();
	//COMMENCE FIXY PROTOCOL
	ajax.nodeid = base.nodeid;
	ajax.ssnum = base.ssnum;
	ajax.node = base.node;
	ajax.choice = base.choice; 
	ajax.choiceid = base.choiceid;
	ajax.totalSeconds = base.totalSeconds;
	ajax.extra = base.extra;
	ajax.notes = base.notes; 
	ajax.stateIDStep = base.stateIDStep;
	
	
	if(base.isEdited != undefined){
		ajax.isEdited = base.isEdited;
	}

	if(base.isNew != undefined){
		ajax.isNew = base.isNew;
	}

	if(isNaN(base.timeInSeconds) == false ) {
		ajax.timeInSeconds = base.timeInSeconds;
	}

	console.log("here is the AJAX version of CCOI_step after gogoajax");
	console.log(ajax);
	
	
	return ajax;
}


/**
 * Returns the branch JSON object from schema for a given selection
 *
 * @param {int} node Index of node in schema
 * @param {int} choice Index of chosen edge in schema
 */
function getNodeFromChoice (nodeID, choice) {	
	//new ID system that Mark setup
	console.log("Get node from choice:" + choice);
	console.log("This is your node ID:" + nodeID); 
	if(Number.isInteger(choice) || choice < 100000){
		var node = ccoi.ccoiSchema.getNode(nodeID);
		let printableNode = JSON.stringify(node);
		console.log("Here is the node json");
		console.log(printableNode);
		if(!node.branches){
			throw "Error in getNodeFromChoice(): No branches in node "+nodeID;
		}
		if(ccoi.ccoiSchema.branches[choice]) {
			return ccoi.ccoiSchema.branches[choice];
		}
		else
			throw "Error in getNodeFromChoice(): No branch found with id " + choice;
	}
	
	// old hex-choice system
	if(ccoi.ccoiSchema.branches[choice]){
		return ccoi.ccoiSchema.branches[choice];
	}
	
	throw "Error in getNodeFromChoice(): No branch found with id "+choice;
}

/**
 * @returns {string} Returns the next node's hex ID from the schema
 */
function CCOI_Step_nextNodeID () {
	console.log("nextNodeID print beginning:");
	console.log(this.nodeid);
	console.log(this.choiceid);
	return this.choiceid === -1 ? null : getNodeFromChoice(this.nodeid, this.choiceid).next;
}

/**
 * @returns {string} Returns the next node's hex ID from the schema
 */
function CCOI_Step_nextNodeID_int () {
	console.log("beginning nextNodeID_int print: ")
	console.log(this.nodeid);
	console.log(this.choiceid);
	return this.choiceid === -1 ? null : getNodeFromChoice(this.nodeid, this.choiceid).next_id;
}

/**
 * @returns {string} Returns the node's description from the schema
 */
function CCOI_Step_branchDescription () {
	var branchDescription;
	try {
		branchDescription = this.choiceid === -1 ? 'Other:' : getNodeFromChoice(this.nodeid, this.choiceid).description;
	}
	catch (err) {
		// The missing branch should already have been caught and noted
		return "";
	}
	return branchDescription;
}
/**
 * Function used to obtain the pathNodeID, using the branch new_id.
 * @returns int
 */
function CCOI_Step_PathNodeID () { 
	let pathNodeID;
	try {
		pathNodeID = this.choiceid === -1 ? 'Other:' : getNodeFromChoice(this.nodeid, this.choiceid).branch_new_id; 
	}
	catch (err) {
		return "Bruh this ain't workin like u thought it would!!!! FIX YO SHIZ!"
	}
}

/**
 * This field is used only for those selections which need extra information,
 * e.g. a Peer or Adult ID
 *
 * @returns {string} If exists, returns the 'extra' field from the schema
 */
function CCOI_Step_extraType () {
  return this.choiceid === -1 ? '' : getNodeFromChoice(this.nodeid, this.choiceid).extra;
}

/**
 * @returns {string} Returns a 1-indexed ID for an edge
 */
function ChoiceID (choice) {
  return (choice === -1 ? '+' : choice);
}

/**
 *
 * @param {CCOI_Step} ccoiStep
 * @returns {string} Returns the Node and Edge IDs for a given CCOI_Step
 * Used only for pretty-format ids, not for cannonical (hex) ids.
 */
function NodeChoice (ccoiStep) {
	var choiceID = null;
	
	var node;
	try {
		node = ccoi.ccoiSchema.getNode(ccoiStep.nodeid);
	}
	catch(err) {
		return null;
	}
	if(node.branches == null)
		return null;
	
	try {
		// new Integer system
		choiceID = ChoiceID(ccoiStep.choiceid);
	}
	catch(err) {
		return node.id + '-' + "<strong class='emptyNode'>[DELETED BRANCH]</strong>";
	}

	return node.id + '-' + choiceID;
}

/**
 * Used to display a text-based timeline
 * e.g. (2:20) 7-1: Peer verbally responds to the student's curiosity, excitement or accomplishment
 *
 * @param {boolean} plaintext True for plaintext, false for HTML bolded
 * @returns {string} A given step in a path
 */
function CCOI_Step_output (plaintext) {
	
	/*This output text is old and crusty. Real output text should have total seconds, not minutes seconds. Epic fail!*/
	/*var outputText = '(' + this.minutes + ':';
	if (this.seconds < 10) { outputText += '0'; }
	outputText += this.seconds + ') ';*/

	var outputText = '(' + this.totalSeconds + ')';

	var nodeChoice = NodeChoice(this);
	if(nodeChoice)
		outputText += nodeChoice + ': ' + this.branchDescription();
	else
		outputText += "<strong class='emptyNode'>[EMPTY NODE]</strong>";

	if (this.extra !== null && this.extra !== undefined && this.extra !== '') { outputText += ' "' + this.extra + '"'; }

	if (this.notes !== '' && this.notes !== null && this.notes != undefined) { outputText += ' [' + this.notes + ']'; }

	var nextNodeID;
	try {
		nextNodeID = this.nextNodeID();
		if (nextNodeID === null || nextNodeID === undefined || nextNodeID === '') { 
			outputText += plaintext === true ? '--END' : '<b>&mdash;END</b>'; 
		}
	}
	catch {
		// do nothing. it's already handled.
	}


	return outputText;
}

function CCOI_Session_label () {
  var label = this.name;
  if (this.date !== '' && this.date != null) { label += ' / ' + this.date; }
  if (this.studentID !== '' && this.studentID != null) { label += ' / ' + this.studentID; }

  return label;
}
