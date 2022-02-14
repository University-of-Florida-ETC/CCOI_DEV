function removeLastChild (node) {
    node.removeChild(node.lastChild);
}

function removeAllChildren (node) {
    while (node.lastChild) {
        removeLastChild(node);
    }
}

function setSectionTitle (str) {
    document.title = str + ' | C-COI | University of Florida';
};

function totalTime(timeInSecs) {
    var minutes = Math.floor(timeInSecs/60);
    var seconds = Math.round(timeInSecs - minutes * 60);
    if (seconds < 10) seconds = String(seconds).padStart(2, '0');
    return (minutes + ":" + seconds);
}

function totalSec(minutes, seconds) {
    let totalTimeSec = minutes*60 + seconds;
    return totalTimeSec;
}

function appendSessionLink(container, i, isDemo) {
    let template = `
        <div class="row">
            <div class="col-sm-9 col-12">
                <a id="session_${i}_name" class="btn-link session-edit" href="#" data-index="${i}"></a>
            </div>
            <div class="col-sm-3 col-12">
                <a class="btn-link session-edit" href="#" data-index="${i}"><span class="oi oi-pencil px-2" title="Edit Session" aria-hidden="true"></span></a>
                <a class="btn-link" href="#" data-index="${i}"><span class="oi oi-trash px-2" title="Delete Session" aria-hidden="true"></span></a>
                ${isDemo==true ? '<a class="btn-link" href="/visualizations?demo=true" data-index="${i}"><span class="oi oi-pie-chart px-2" title="View Visualizations" aria-hidden="true"></span></a>' : 
    '<a class="btn-link" href="/visualizations?session=${i}"><span class="oi oi-pie-chart px-2" title="View Visualizations" aria-hidden="true"></span></a>'}
            </div>
        </div>
    `;
    let wrapper = document.createElement("LI");
    wrapper.classList.add('session-listing');
    wrapper.classList.add('my-2');
	wrapper.innerHTML = template;
	container.appendChild(wrapper);
}

function isFunction(functionToCheck) {
    return functionToCheck && {}.toString.call(functionToCheck) === '[object Function]';
}

function isObject(object) {
    return object != null && typeof object === 'object';
}

function removeEmptyAndOld(obj) {
    // Removes empty key/value pairs and the old hex id items
    Object.keys(obj).forEach(key =>
        (obj[key] && typeof obj[key] === 'object') && removeEmptyAndOld(obj[key]) ||
        (obj[key] === undefined || obj[key] === null || obj[key] === "" || obj[key] === "null" || key === "node" || key === "choice") && delete obj[key]
    );
    return obj;
};

function deepEqual(object1, object2) {
    const keys1 = Object.keys(object1);
    const keys2 = Object.keys(object2);

    if (keys1.length !== keys2.length) {
        return false;
    }

    for (const key of keys1) {
        const val1 = object1[key];
        const val2 = object2[key];
        // We can ignore functions when looking for deep object equality
        if (isFunction(val1) && isFunction(val2)) break;
        const areObjects = isObject(val1) && isObject(val2);
        if (
            areObjects && !deepEqual(val1, val2) ||
            !areObjects && val1 !== val2
        ) {
            return false;
        }
    }

    return true;
}

function isStepEqual(dbStep, frontendStep) {
    if (dbStep.choice != frontendStep.choice)  return false;
    if (dbStep.extra != frontendStep.extra) {
        // The values are stored differently on the backend than they are on the frontend in CCOI Steps
        if(!(dbStep.extra==null && frontendStep.extra=="")) {
            return false;
        }
    }
    if (dbStep.minutes != frontendStep.minutes) return false;
    if (dbStep.node != frontendStep.node) return false;
    if (dbStep.notes != frontendStep.notes) {
        // The values are stored differently on the backend than they are on the frontend in CCOI Steps
        if(!(dbStep.notes==null && frontendStep.notes=="")) {
            return false;
        }
    }
    if (dbStep.seconds != frontendStep.seconds) return false;
    else return true;
}

// Creates the divs for the visualization page's stat tracker section
function appendStatTracker(container, statTracker) {
    let template = `
        <div class="row">
           <div class="col-md-4 col-sm-12 pb-5 fact-container">
               <div class="fact-box">
                   <div class="fact-description">Problem Solving</div>
                   <span class="oi oi-clock fact-icon"></span>
                   <h5>Solved / Unsolved</h5>
                   <span class="fact-num">${statTracker.solved} / ${statTracker.unsolved}</span>
                   <h5>Solved Time / Unsolved Time</h5>
                   <span class="fact-num">${totalTime(statTracker.solvedTime)} / ${totalTime(statTracker.unsolvedTime)}</span>
               </div>
           </div>
           <div class="col-md-4 col-sm-12 pb-5 fact-container">
               <div class="fact-box">
                   <div class="fact-description">Interactions</div>
                   <span class="oi oi-people fact-icon"></span>
                   <h5>Student-Driven</h5>
                   <span class="fact-num">${statTracker.studentDriven}</span>
                   <h5>Peer-Driven / Adult-Driven</h5>
                   <span class="fact-num">${statTracker.peerDriven} / ${statTracker.adultDriven}</span>
               </div>
           </div>
           <div class="col-md-4 col-sm-12 pb-5 fact-container">
               <div class="fact-box">
                   <div class="fact-description">Independent</div>
                   <span class="oi oi-person fact-icon"></span>
                   <h5>Paths</h5>
                   <span class="fact-num">${statTracker.independentCount}</span>
                   <h5>Avg. Time Before Interaction</h5>
                   <span class="fact-num">${totalTime(statTracker.avgTimeBeforeInteraction)}</span>
               </div>
           </div>
       </div>
        <div class="row">
            <div class="col pb-2 fact-container">
                <div class="fact-box">
                    <div class="fact-description">Student-Driven Interactions</div>
                    <span class="oi oi-people fact-icon"></span>
                    <div class="row">
                        <div class="col-md-4 col-sm-12 pb-2">
                            <!--<h5>Computing Problem Solving</h5>-->
                            <h5>Collaborative Problem Solving</h5>
                            <span class="fact-num">${statTracker.cps}</span>
                            <h5>Total Time</h5>
                            <span class="fact-num">${totalTime(statTracker.cpsTime)}</span>
                        </div>
                        <div class="col-md-4 col-sm-12 pb-2">
                            <!--<h5>Computing Non-Problem Solving</h5>-->
                            <h5>Computing Discussion (Non-Problem Solving)</h5>
                            <span class="fact-num">${statTracker.cds}</span>
                            <h5>Total Time</h5>
                            <span class="fact-num">${totalTime(statTracker.cdsTime)}</span>
                        </div>
                        <div class="col-md-4 col-sm-12 pb-2">
                            <h5>Non-computing Interaction</h5>
                            <span class="fact-num">${statTracker.ncds}</span>
                            <h5>Total Time</h5>
                            <span class="fact-num">${totalTime(statTracker.ncdsTime)}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.innerHTML = template;
}