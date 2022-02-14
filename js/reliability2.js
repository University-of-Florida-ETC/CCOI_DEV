var schema;
var StorageKey;
/*
		MARK'S NOTES
		okay -- so it look slike the only bits we really care about are:
			that the OBS has the same number of OBs
			and for each OB
				that the OEs selected are the same and that the times of the OEs are the same
					the original did not care about the OEs times, just that the OBs began and ended the same
				and then go through OBS-1 and check each OB/OE to see if it EXISTS (anywhere) in OBS-2 and that the found time matches (within X secs)
					then go and find anything in OBS-1 that doesn't match and note it
					then go and find anything in OBS-2 that doesn't match and note it
			report the num of matched items / number of items as a percentage

*/

$( document ).ready(function() {
	DoXMLHttpRequest('/storage/c-coi.json', parseCCOI);												// ====== go get the data and parse it out
	
	$.each(sessionsData, function( index, value ) {sessions[index] = new CCOI_Session(value);});			// ====== set up the sessions variable with the above
	
	$('button.irr4').click(function() {
		var messagesBox = $(this).closest('.videoContainer').find('.messagesBox');
		messagesBox.removeClass('hidden');
			
		var checkedBoxes = $(this).closest('.videoContainer').find('input[type=checkbox]:checked');
		if(checkedBoxes.length > 2)
			messagesBox.html("<p>You've checked too many boxes; only two sessions can be compared at a time.</p>");
		else if(checkedBoxes.length < 2)
			messagesBox.html("<p>Please check two boxes.</p>");
		else {
			var id0 = $(checkedBoxes['0']).val();
			var id1 = $(checkedBoxes['1']).val();
			var irrCheck = interRaterReliabilityFormatted(sessions[id0], sessions[id1]);
			
			messagesBox.empty().append(irrCheck);
		}
	});																		// ====== when they click the button let them choose which sessions to compare and RUN accuracy check
	
	$('button.irrTime').click(function() {
		var messagesBox = $(this).closest('.videoContainer').find('.messagesBox');
		messagesBox.removeClass('hidden');
			
		var checkedBoxes = $(this).closest('.videoContainer').find('input[type=checkbox]:checked');
		if(checkedBoxes.length > 2)
			messagesBox.html("<p>You've checked too many boxes; only two sessions can be compared at a time.</p>");
		else if(checkedBoxes.length < 2)
			messagesBox.html("<p>Please check two boxes.</p>");
		else {
			var id0 = $(checkedBoxes['0']).val();
			var id1 = $(checkedBoxes['1']).val();
			
			if(interraterTimeCheck(sessions[id0], sessions[id1]) === true){
				messagesBox.html("Inter-rater time check passed!");
			}
			else {
				var irrCheck = interraterTimeCheckFormatted(sessions[id0], sessions[id1]);
				messagesBox.html(irrCheck);
			}
		}
	});																	// ====== when they click the button let them choose which sessions to compare and RUN time check
});

function interraterTimeCheck (session1, session2) {
  // 5-10 seconds between biggest range of start and end events
  // This sets how many paths the coders are allowed to differ on. i.e., 1 means that if coder 1 has 3 paths, then coder 2 may have somewhere between 2 and 4 paths
  const NUM_PATH_DIFFERENCE_TOLERANCE = 0;
  const EVENT_TIME_TOLERANCE_SECS = 5;						  // This is the number of seconds that events from two different coders are allowed to differ in their start
  if (Math.abs(session1.paths.length - session2.paths.length) > NUM_PATH_DIFFERENCE_TOLERANCE) {						// ====== same # of Observations? (more or less)
    return [`Session "${session1.name}" has ${session1.paths.length} paths, ` + `Session "${session2.name}" has ${session2.paths.length} paths`];
  } else {
	var reasonsToFail = [];					var isFailing = false;
    const numPaths = session1.paths.length < session2.paths.length ? session1.paths.length : session2.paths.length;			// ===== take the longer set and...
    for (let pathIdx = 0; pathIdx < numPaths; pathIdx++) {
      const path1 = session1.paths[pathIdx].steps;			      const path2 = session2.paths[pathIdx].steps;
	  console.log("path1", path1, "path2", path2);
	  // Check start and end points of path for agreement within envent time tolerance. (We don't care about nodes between the start and end for IRR1.)
      if (Math.abs(path1[0].timeInSeconds - path2[0].timeInSeconds) > EVENT_TIME_TOLERANCE_SECS){									// ======= make sure the OBs begin more or less the same times for events (one way)
		isFailing = true;				var path1Start = path1.steps[0].timeInSeconds;						var path2Start = path2.steps[0].timeInSeconds;
        reasonsToFail.push(`Session "${session1.name}", path ${pathIdx+1} begins at ${path1Start} seconds, Session "${session2.name}", path ${pathIdx+1} begins at ${path2Start} seconds.`);
	  }
	  if(Math.abs(path1[path1.length-1].timeInSeconds - path2[path2.length-1].timeInSeconds) > EVENT_TIME_TOLERANCE_SECS) {				// ======= make sure the OBs end more or less the same times for events (the other way)
		isFailing = true;				var path1End = path1[path1.length-1].timeInSeconds;			var path2End = path2[path2.length-1].timeInSeconds;
        reasonsToFail.push(`Session "${session1.name}", path ${pathIdx+1} ends at ${path1End} seconds, Session "${session2.name}", path ${pathIdx+1} ends at ${path2End} seconds.`);
      }
    }
	if(isFailing){return reasonsToFail;}
    return true;
  }
}
function interraterTimeCheckFormatted (session1, session2) {
	irrFailReasons = interraterTimeCheck(session1, session2);
	var html = "<p>IRR level 1 failed for the following reasons:</p><ul>";
	for(var i = 0; i < irrFailReasons.length; i++)
		html += "<li>"+irrFailReasons[i]+"</li>";
	return html;
}						// ======= make the above pretty

function interRaterReliabilityCheck(session1, session2) {
	if(interraterTimeCheck(session1, session2) !== true){return "IRR level 1 failed; not running IRR2.";}				// ======== did the time check run?
	const EVENT_TIME_TOLERANCE_SECS = 5;
	// Make deep copies of the sessions so that we can modify them with impunity.
	// This shortcut to deep copy doesn't copy object methods, so we use a helper to calculate each node's timestamp for later.
	session1Copy = irr2_timeToSeconds(JSON.parse(JSON.stringify(session1)));
	session2Copy = irr2_timeToSeconds(JSON.parse(JSON.stringify(session2)));

	// We can assume that the sessions have the same number of paths if they passed IRR1. (====== this assumes that NUM_PATH_DIFFERENCE_TOLERANCE was set to zero ===)
	const numPaths = session1Copy.paths.length;
	var totalAgreement = [];			var tableAgreement = [];
	
	for(var i = 0; i < numPaths; i++){
		const path1 = session1Copy.paths[i].steps;
		const path2 = session2Copy.paths[i].steps;									// ======== roll through each set of  Observations together
		console.log("path1", path1, "path2", path2)
		var agreementPath = [0,0];											// Create empty agreement array
		var tableAgreementPath = [];
		
		// First pass (perfect match pass): for each node n in all nodes in Primary rater's path
		for(var j = 0; j < path1.length; j++) {
			var stepToMatch = path1[j];
			var foundAgreement = false;													// ======== in each OB, check the ObElements -- grab elementX
			// Look for matching node in Secondary rater's path based on following criteria:
			for(var k = 0; k < path2.length; k++) {
				var stepToCheck = path2[k];														// ======== check to see if it matches __AN__ ObElement in the other path -- could be in different place
				if(!foundAgreement &&
				   stepToMatch.node == stepToCheck.node && 						// Same node ID
				   stepToMatch.choice == stepToCheck.choice &&						// ======== NOTE ===== these two are really just the PNID  :-) ========================
				   Math.abs(stepToMatch.timeInSeconds - stepToCheck.timeInSeconds) <= EVENT_TIME_TOLERANCE_SECS && // +/- 5 second agreement
				   !stepToCheck.matched ){ // Secondary rater's node not already matched
							// If matching node found:
							foundAgreement = true;																		// ======== if the above bits are true... note that
							agreementPath[0]++;										// Increment num_agreements
							stepToMatch.matched = true;							// Mark this node in Primary rater's path as matched
							stepToCheck.matched = true;							// Mark node in Secondary rater's path as matched
							agreementPath[1]++;										// Increment num_decisions
							console.log("stepToMatch",stepToMatch);		// Get pretty node label
							let prettyTitle1 = schema.nodes[stepToMatch.node].id+"-"+schema.branches[stepToMatch.choice].pretty_id;
							let prettyTitle2 = schema.nodes[stepToCheck.node].id+"-"+schema.branches[stepToCheck.choice].pretty_id;
							tableAgreementPath.push({
								path1Time: stepToMatch.timeInSeconds, 
								path1Node: prettyTitle1, 
								path1Title: session1.paths[i].steps[j].branchDescription(),															// ======== store all this in the output variable
								path2Time: stepToCheck.timeInSeconds, 
								path2Node: prettyTitle2, 
								path2Title: session1.paths[i].steps[k].branchDescription(),
								errorRow: false });
				}
			}
		}
		// Second pass (any match pass): for each UNMATCHED node n in all nodes in Primary rater's path
		for(var j = 0; j < path1.length; j++) {
			var stepToMatch = path1[j];
			if(!stepToMatch.matched){								// ======================== now we're checking for unmatched bits... ======================================================
				var foundMatch = false;
				// Look for matching node in Secondary rater's path based on following criteria:
				for(var k = 0; k < path2.length; k++) {
					var stepToCheck = path2[k];
					if(!foundMatch &&
					   Math.abs(stepToMatch.timeInSeconds - stepToCheck.timeInSeconds) <= EVENT_TIME_TOLERANCE_SECS && // +/- 5 second agreement
					   !stepToCheck.matched ){ // Secondary rater's node not already matched
						// If matching node found:
						foundMatch = true;
						// Mark this node in Primary rater's path as matched
						stepToMatch.matched = true;
						// Mark node in Secondary rater's path as matched
						stepToCheck.matched = true;
						
						let prettyTitle1 = schema.nodes[stepToMatch.node].id+"-"+schema.branches[stepToMatch.choice].pretty_id;
						let prettyTitle2 = schema.nodes[stepToCheck.node].id+"-"+schema.branches[stepToCheck.choice].pretty_id;
						
						tableAgreementPath.push({
							path1Time: stepToMatch.timeInSeconds, 
							path1Node: prettyTitle1, 
							path1Title: session1.paths[i].steps[j].branchDescription(),
							path2Time: stepToCheck.timeInSeconds, 
							path2Node: prettyTitle2, 
							path2Title: session1.paths[i].steps[k].branchDescription(),
							errorRow: true,
							errorTitle: "Timestamps match but node or edge disagrees."});
					}
				}
				agreementPath[1]++;							// Increment num_decisions
				if(!foundMatch){
					let prettyTitle = schema.nodes[stepToMatch.node].id+"-"+schema.branches[stepToMatch.choice].pretty_id;
					
					tableAgreementPath.push({
						path1Time: stepToMatch.timeInSeconds, 
						path1Node: prettyTitle, 
						path1Title: session1.paths[i].steps[j].branchDescription(),
						errorRow: true,
						errorTitle: "Cannot match to another node within "+EVENT_TIME_TOLERANCE_SECS+" seconds."});
				}
			}
		}
		// Second pass: Run through each UNMATCHED node in Secondary rater's path
		for(var j = 0; j < path2.length; j++) {
			if(!path2[j].matched){
				agreementPath[1]++;					// Increment num_decisions							// ======================== ...aaaand again but simpler... ======================================================
				let prettyTitle = schema.nodes[path2[j].node].id+"-"+schema.branches[path2[j].choice].pretty_id;
				tableAgreementPath.push({
					path2Time: path2[j].timeInSeconds, 
					path2Node: prettyTitle,
					path2Title: session1.paths[i].steps[j].branchDescription(),
					errorRow: true,
					errorTitle: "Cannot match to another node within "+EVENT_TIME_TOLERANCE_SECS+" seconds."
				});
			}
		}
		totalAgreement.push(agreementPath);
		tableAgreement.push(tableAgreementPath);
	}
	
	var n = 0;			var d = 0;				// Decide percent agreement for the path
	for(var i = 0; i < totalAgreement.length; i++){
		// percent_agreement = num_agreement ÷ num_decisions
		var n1 = totalAgreement[i][0];
		totalAgreement[i].push(1.0*n1/totalAgreement[i][1]);
		n += n1;
		d += totalAgreement[i][1];
	}
	totalAgreement.push(1.0*n/d);
	return [totalAgreement, tableAgreement];
}
function interRaterReliabilityFormatted(session1, session2) {
	var irrCheck = interRaterReliabilityCheck(session1, session2);
	if(Array.isArray(irrCheck) && Array.isArray(irrCheck[0])){
		var html = $("<div>");
		html.append("<p>").text("Path agreement:");
		var list = $("<ul>");
		for(var i = 0; i < irrCheck[0].length-1; i++){
			var percent = irrCheck[0][i][2]*100;
			percent = Math.round(percent * 100) / 100;
			list.append($("<li>").text("Path "+(i+1)+": "+percent+"%"));
		}
		html.append(list);
		
		var totalPercent = irrCheck[0][irrCheck[0].length-1]*100;
		totalPercent = Math.round(totalPercent * 100) / 100;
		html.append($("<p>").text("Average agreement across path nodes: "+totalPercent+"%"));
		html.append(interRaterReliabilityBuildTable(irrCheck[1], irrCheck[0]));
		return html;
	}
	else {
		return interraterTimeCheckFormatted(session1, session2);
	}
}											// ======================== now make it all purty =================

function interRaterReliabilityBuildTable(tableAgreement, agreementData){
	var html = $("<div>").addClass("tableContainer");
	for(var i = 0; i < tableAgreement.length; i++){
		var table = $("<table>").addClass("irrTable");
		table.append($("<caption>").text("Path "+(i+1)));
		
		var thead = $("<thead>").append(
			$("<tr>").append(
				$("<th colspan='2'>").text("Session 1"),
				$("<th colspan='2'>").text("Session 2"),
				$("<th>")
			),
			$("<tr>").append(
				$("<th>").text("Time"),
				$("<th>").text("Code"),
				$("<th>").text("Code"),
				$("<th>").text("Time"),
				$("<th>")
			)
		);
		table.append(thead);
		
		var tbody = $("<tbody>");
		tableAgreement[i].sort(sortByTime);
		for(var j = 0; j < tableAgreement[i].length; j++){
			var currentCell = tableAgreement[i][j];
			tbody.append(
				$("<tr>").addClass(currentCell.errorRow?"error":"").append(
					$("<td>").text(currentCell.path1Node?secondsToTime(currentCell.path1Time):""),
					$("<td>").text(currentCell.path1Node?currentCell.path1Node:"").attr("title", currentCell.path1Node?currentCell.path1Title:""),
					$("<td>").text(currentCell.path2Node?currentCell.path2Node:"").attr("title", currentCell.path2Node?currentCell.path2Title:""),
					$("<td>").text(currentCell.path2Node?secondsToTime(currentCell.path2Time):""),
					$("<td>").text(currentCell.errorRow?"disagree: "+currentCell.errorTitle:"agree")
						.addClass("agreementCell")
						.attr("title", currentCell.errorTitle?currentCell.errorTitle:"")
			));
		}
		table.append(tbody);
		
		var tfoot = $("<tfoot>");
		tfoot.append($("<tr>").append(
			$("<td colspan='4'>").text("Agreements:"),
			$("<td>").text(agreementData[i][0])));
		tfoot.append($("<tr>").append(
			$("<td colspan='4'>").text("Decisions:"),
			$("<td>").text(agreementData[i][1])));
		tfoot.append($("<tr>").append(
			$("<td colspan='4'>").text("Percent agreement:"),
			$("<td>").text(Math.round(agreementData[i][2] * 100) + "%")
				.attr("title", Math.round(agreementData[i][2] * 10000)/100 + "%")
		));
		table.append(tfoot);
		
		html.append(table);
	}
	return html;
}					// ======================== and this is how it gets purty =========

function sortByTime(a, b){
  if(a.path1Time && b.path1Time)
	return ((a.path1Time < b.path1Time) ? -1 : ((a.path1Time > b.path1Time) ? 1 : 0));
  if(a.path2Time && b.path2Time)
	return ((a.path2Time < b.path2Time) ? -1 : ((a.path2Time > b.path2Time) ? 1 : 0));
  if(a.path1Time) return -1;
  return 1;
}
function secondsToTime(seconds){
	if(!seconds || seconds == 0) return "0:00";
	
	return Math.floor(seconds/60.0)+":"+(((seconds%60)<10)?"0":"")+(seconds%60);
}
// Helper function that takes a deep copied session object and assigns timeInSeconds to all path nodes.
function irr2_timeToSeconds(session){
	for(var i = 0; i < session.paths.length; i++){
		path = session.paths[i].steps;
		for(var j = 0; j < path.length; j++){
			path[j].timeInSeconds = path[j].minutes*60 + path[j].seconds;
		}
	}
	
	return session;
}
function irr2_separateNodes(session){
	for(var i = 0; i < session.paths.length; i++){
		path = session.paths[i].steps;
		length = path.length;
		for(var j = 0; j < length; j++){
			path[j].timeInSeconds = path[j].minutes*60 + path[j].seconds;
			session.paths[i].steps.push({"node": path[j].node+"."+path[j].choice, "timeInSeconds": path[j].timeInSeconds});
		}
	}
	return session;
}