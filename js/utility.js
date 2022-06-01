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
                <a class="btn-link session-edit" href="#" data-index="${i}">Demo Session</a>
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