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
function CCOI_Session (name, date, studentID, prompted, paths, minutes, seconds, observer, sessionNotes) {
  if (arguments.length === 1 && name.hasOwnProperty('_id')) {
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

    // the last "valid" values for this session
    this.minutes = minutes || 0;
    this.seconds = seconds || 0;

    this.label = CCOI_Session_label;
    this.concatPaths = concatPaths;
  }
}

// can't overload functions in JS so using janky arg.len check with renaming first param
/**
 *
 * @param {*} node
 * @param {*} choice
 * @param {int} minutes
 * @param {int} seconds
 * @param {string} extra
 * @param {string} notes
 */
function CCOI_Step (node, choice, minutes, seconds, extra, notes) {
  if (arguments.length === 1) {
    var obj = node;
    for (var prop in obj) {
      if (obj.hasOwnProperty(prop)) {
        this[prop] = obj[prop];
      }
    }

    this.nextNodeID = CCOI_Step_nextNodeID;
    this.branchDescription = CCOI_Step_branchDescription;
    this.extraType = CCOI_Step_extraType;
    this.output = CCOI_Step_output;
  } else {
    this.node = node || 0;
    this.choice = choice || 0;
    this.minutes = minutes || 0;
    this.seconds = seconds || 0;
    this.extra = extra || '';
    this.notes = notes || '';

    this.nextNodeID = CCOI_Step_nextNodeID;
    this.branchDescription = CCOI_Step_branchDescription;
    this.extraType = CCOI_Step_extraType;
    this.output = CCOI_Step_output;
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

/**
 * Returns the branch JSON object from schema for a given selection
 *
 * @param {int} node Index of node in schema
 * @param {int} choice Index of chosen edge in schema
 */
function getNodeFromChoice (nodeID, choice) {	
	// backstop for old node-choice system
	if(Number.isInteger(choice) || choice < 100000){
		var node = ccoi.ccoiSchema.getNode(nodeID);
		if(!node.branches){
			throw "Error in getNodeFromChoice(): No branches in node "+nodeID;
		}
		if (node.should_group_choices) {
			var choiceIndex = choice;
			var branches = node.branches;

			for (var groupIndex = 0; groupIndex < branches.length; groupIndex++) {
				var numChoices = branches[groupIndex].length;
				if (choiceIndex < numChoices) {
					return branches[groupIndex][choiceIndex];
				} else {
					choiceIndex -= numChoices;
				}
			}
			// shouldn't happen
			throw "Error in getNodeFromChoice(): No branch found with id "+choice;
		} else if(choice < node.branches.length) {
			return node.branches[choice];
		}
		else
			throw "Error in getNodeFromChoice(): No branch found with id "+choice;
	}
	
	// new hex-choice system
	if(ccoi.ccoiSchema.branches[choice]){
		return ccoi.ccoiSchema.branches[choice];
	}
	
	throw "Error in getNodeFromChoice(): No branch found with id "+choice;
}

/**
 * @returns {string} Returns the next node's ID from the schema
 */
function CCOI_Step_nextNodeID () {
	return this.choice === -1 ? null : getNodeFromChoice(this.node, this.choice).next;
}

/**
 * @returns {string} Returns the node's description from the schema
 */
function CCOI_Step_branchDescription () {
	var branchDescription;
	try {
		branchDescription = this.choice === -1 ? 'Other:' : getNodeFromChoice(this.node, this.choice).description;
	}
	catch (err) {
		// The missing branch should already have been caught and noted
		return "";
	}
	return branchDescription;
}

/**
 * This field is used only for those selections which need extra information,
 * e.g. a Peer or Adult ID
 *
 * @returns {string} If exists, returns the 'extra' field from the schema
 */
function CCOI_Step_extraType () {
  return this.choice === -1 ? '' : getNodeFromChoice(this.node, this.choice).extra;
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
		node = ccoi.ccoiSchema.getNode(ccoiStep.node);
	}
	catch(err) {
		return null;
	}
	if(node.branches == null)
		return null;
	
	try {
		// backstop for old id system
		if(Number.isInteger(ccoiStep.choice) && ccoiStep.choice <= 100000)
			choiceID = ChoiceID(ccoiStep.choice);
		// new (hex) id system
		else 
			choiceID = ccoi.ccoiSchema.getChoiceFromID(ccoiStep.choice).pretty_id;
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
	var outputText = '(' + this.minutes + ':';
	if (this.seconds < 10) { outputText += '0'; }
	outputText += this.seconds + ') ';

	var nodeChoice = NodeChoice(this);
	if(nodeChoice)
		outputText += nodeChoice + ': ' + this.branchDescription();
	else
		outputText += "<strong class='emptyNode'>[EMPTY NODE]</strong>";

	if (this.extra !== null && this.extra !== undefined && this.extra !== '') { outputText += ' "' + this.extra + '"'; }

	if (this.notes !== '' && this.notes !== null) { outputText += ' [' + this.notes + ']'; }

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
