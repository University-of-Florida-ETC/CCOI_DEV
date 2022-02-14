"use strict";

var ccoiVizGenerator = (function () {

    // Private Variables
    let nodes;
    let timelines = [];
    let shouldAllow = false;
    let useDefaultSchema = true;
    let isStudentDriven = false;

    function setUseDefaultSchema(sds) {
        useDefaultSchema = sds;
    }

    function fillSessionSelector(sessions) {
        console.log(sessions);
        for(let i=0; i<sessions.length; i++) {
            $(DOM.viz_session_select_ul).append(`
                <li class="col"><input type="checkbox" id="session_${i}" data-index="${i}" class="cb-custom" />
                    <label for="session_${i}"><strong>${sessions[i].observer}: </strong>${sessions[i].name}</label>
                 </li>
            `)
        }
    }

    function checkSelectionStatus() {
        let sessionInputs = DOM.viz_session_select_ul.getElementsByTagName("INPUT");
        let chartInputs = DOM.viz_chart_select_ul.getElementsByTagName("INPUT");
        shouldAllow=false;
        for(let sesh of sessionInputs) {
            if(sesh.checked) {
                for(let chart of chartInputs) {
                    if(chart.checked) {
                        shouldAllow=true;
                    }
                }
            }
        }
        if(shouldAllow){DOM.viz_select_btn.classList.remove('disabled');}
        else if(!shouldAllow && !DOM.viz_select_btn.classList.contains('disabled')){DOM.viz_select_btn.classList.add('disabled');}
    }

    function sankeyJSONFromSession(session) {
        let sankeyObject = {};
        sankeyObject.nodes = [];
        sankeyObject.links = [];

        let paths = session.paths;
        let stepCounter = 0;
        let nodeCounter = 0;
        let traversedNodes = [];
        let minimumHeight = 10; //Setable value to ensure that links that took 0 time will still display on the graph

        for(let i=0; i<paths.length; i++) {
            let steps = paths[i].steps;

            for(let j=0; j<steps.length-1; j++ ) {
                sankeyObject.links[stepCounter] = {};
                sankeyObject.links[stepCounter]["source"] = steps[j].nodeid;
                sankeyObject.links[stepCounter]["target"] = steps[j+1].nodeid;
                sankeyObject.links[stepCounter]["choice"] = ccoi.ccoiSchema.branches[steps[j].choiceid].description;
                sankeyObject.links[stepCounter]["value"] = totalSec(steps[j+1].minutes, steps[j+1].seconds) - totalSec(steps[j].minutes, steps[j].seconds) + minimumHeight;
                sankeyObject.links[stepCounter]["optimal"] = "yes";
                sankeyObject.links[stepCounter]["startTime"] = totalSec(steps[j].minutes, steps[j].seconds);
                sankeyObject.links[stepCounter]["endTime"] = totalSec(steps[j+1].minutes, steps[j+1].seconds);
                sankeyObject.links[stepCounter]["pathNum"] = i+1; // To avoid Path # 0
                // If this step ends the path, get the description so we can understand how/why path ended
                if (j+1 == steps.length-1) {
                    sankeyObject.links[stepCounter]["endChoice"] = ccoi.ccoiSchema.branches[steps[j+1].choiceid].description;
                }
                stepCounter++;

                if(!traversedNodes.includes(steps[j].nodeid)) {
                    traversedNodes[nodeCounter] = steps[j].nodeid;
                    sankeyObject.nodes[nodeCounter] = {};
                    sankeyObject.nodes[nodeCounter]["name"] = steps[j].nodeid;
                    sankeyObject.nodes[nodeCounter]["col"] = nodeCounter;
                    nodeCounter++;
                } else if (!traversedNodes.includes(steps[j+1].nodeid)) {
                    traversedNodes[nodeCounter] = steps[j+1].nodeid;
                    sankeyObject.nodes[nodeCounter] = {};
                    sankeyObject.nodes[nodeCounter]["name"] = steps[j+1].nodeid;
                    sankeyObject.nodes[nodeCounter]["col"] = nodeCounter;
                    nodeCounter++;
                }
            }
        }
        return sankeyObject;
    }

    /**
     *  Helper function to create color timeline.
     *  Modified from http://bl.ocks.org/Rokotyan/0556f8facbaf344507cdc45dc3622177
     */

    function colorSpectrum(start, end, colorMap) {
        let timelineDiv = document.getElementById("color_timeline");
        let timelineWidth = timelineDiv.offsetWidth;

        function byte2Hex(n) {
            var nybHexString = "0123456789ABCDEF";
            return String(nybHexString.substr((n >> 4) & 0x0F,1)) + nybHexString.substr(n & 0x0F,1);
        }

        function RGB2Color(r,g,b) {
            return '#' + byte2Hex(r) + byte2Hex(g) + byte2Hex(b);
        }

        function addLabels(container, start, end) {
            document.getElementById("time_start").innerHTML = totalTime(start);
            document.getElementById("time_end").innerHTML = totalTime(end);
        }

        function makeColorGradient(frequency1, frequency2, frequency3,
                                   phase1, phase2, phase3,
                                   center, width, len) {
            if (center == undefined)   center = 128;
            if (width == undefined)    width = 127;
            if (len == undefined)      len = 50;

            let timelineSquareSize = timelineWidth/len;

            let timeDiff = end-start;
            let timeBucket = timeDiff/len;

            addLabels(timelineDiv, start, end);

            for (var i = 0; i < len; i++) {
                var red = Math.sin(frequency1*i + phase1) * width + center;
                var grn = Math.sin(frequency2*i + phase2) * width + center;
                var blu = Math.sin(frequency3*i + phase3) * width + center;

                var bucketStart = Math.round(start + timeBucket*i);
                var bucketEnd = Math.round((start + timeBucket*(i+1)),2);
                timelineDiv.innerHTML += '<div title="'+totalTime(bucketStart)+' : '+totalTime(bucketEnd)+'" class="timelineBlock" style="background-color: ' + RGB2Color(red,grn,blu) + '; width: ' + timelineSquareSize + 'px; height: ' + timelineSquareSize + 'px;"></div>';
                colorMap.set(RGB2Color(red,grn,blu), [bucketStart, bucketEnd]);
            }
        }

        makeColorGradient(.10,.10,.10,0,2,4,128,127,30);
    }

    function createSankey(json) {
        $('#sankey_container').removeClass('d-none');
        var margin = { top: 50, right: 10, bottom: 60, left: 10};
        var width = 700;
        var height = 290;
        let data = json;
        var sankey = d3.sankeyCircular()
            .nodeWidth(10)
            .nodePadding(20) //note that this will be overridden by nodePaddingRatio
            //.nodePaddingRatio(0.5)
            .size([width, height])
            .nodeId(function (d) {
                return d.name;
            })
            .nodeAlign(d3.sankeyJustify)
            .iterations(5)
            .circularLinkGap(1)
            .sortNodes("col")

        var svg = d3.select("#sankey_area").append("svg")
            .attr("width", '100%')
            .attr("height", '100%')
            .attr('viewBox','0 0 720 400')
            .attr('preserveAspectRatio','xMinYMin')
            //.attr("width", width + margin.left + margin.right)
            //.attr("height", height + margin.top + margin.bottom);

        var g = svg.append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top+ ")")

        var linkG = g.append("g")
            .attr("class", "links")
            .attr("fill", "none")
            .attr("stroke-opacity", 0.7)
            .selectAll("path");

        var nodeG = g.append("g")
            .attr("class", "nodes")
            .attr("font-family", "sans-serif")
            .attr("font-size", 10)
            .selectAll("g");

        //run the Sankey + circular over the data
        let sankeyData = sankey(data);
        let sankeyNodes = sankeyData.nodes;
        let sankeyLinks = sankeyData.links;

        let firstLinkEnd = sankeyLinks[0].endTime;
        let lastLinkEnd = sankeyLinks[sankeyLinks.length-1].endTime;

        var colorMap = new Map();

        colorSpectrum(firstLinkEnd, lastLinkEnd, colorMap);

        var lastValue;

        function linksToColors(endTime){
            function colorCallback(values, key) {
                if ((endTime >= values[0] && endTime < values[1]) || endTime == values[1]) {
                    lastValue = key;
                }
            }
            colorMap.forEach(colorCallback);
        }

        let depthExtent = d3.extent(sankeyNodes, function (d) { return d.depth; });

        var nodeColour = d3.scaleSequential(d3.interpolateCool)
            .domain([0,width]);

        var node = nodeG.data(sankeyNodes)
            .enter()
            .append("g");

        node.append("rect")
            .attr("x", function (d) {
                return d.x0;
            })
            .attr("y", function (d) { return d.y0; })
            .attr("height", function (d) {
                return d.y1 - d.y0 > 10 ? d.y1 - d.y0 : 10;
            })
            .attr("width", function (d) { return d.x1 - d.x0; })
            .style("fill", function (d) { return ccoi.ccoiSchema.nodes[d.name].group_hex; })
            .style("opacity", 0.5)
            .on("mouseover", function (d) {
                let thisName = d.name;
                node.selectAll("rect")
                    .style("opacity", function (d) {
                        return highlightNodes(d, thisName)
                    })

                d3.selectAll(".sankey-link")
                    .style("opacity", function (l) {
                        return l.source.name == thisName || l.target.name == thisName ? 1 : 0.7;
                    })

                node.selectAll("text")
                    .style("opacity", function (d) {
                        return highlightNodes(d, thisName)
                    })
            })
            .on("mouseout", function (d) {
                node.selectAll("rect").style("opacity", 0.5);
                d3.selectAll(".sankey-link").style("opacity", 0.7);
                d3.selectAll("text").style("opacity", 1);
            })
            .on("click", function(d) {
                sankeyInfo(d, 'Node');
            })

        node.append("text")
            .attr("x", function (d) { return (d.x0 + d.x1) / 2; })
            .attr("y", function (d) { return d.y0 - 12; })
            .attr("dy", "0.35em")
            .attr("text-anchor", "middle")
            .text(function (d) {
                return "Node "+ccoi.ccoiSchema.nodes[d.name].id;
            });

        node.append("title")
            .text(function (d) {
                if (d.targetLinks.length != 0 || d.sourceLinks.length != 0) {
                    return "Node: " + d.name + "\n\nTotal Incoming Time: " + totalTime(nodeTime(d.targetLinks)) + "\nTotal Outgoing Time: " + totalTime(nodeTime(d.sourceLinks)) + "\nTotal Incoming Steps: " + d.targetLinks.length + "\nTotal Outgoing Steps: " + d.sourceLinks.length;
                } else {
                    return "Node was not traversed during this session.";
                }
            });

        var link = linkG.data(sankeyLinks)
            .enter()
            .append("g")

        link.append("path")
            .attr("class", "sankey-link")
            .attr("d", function(link){
                return link.path;
            })
            .style("stroke-width", function (d) { return Math.max(1, d.width); })
            .style("opacity", 0.7)
            .style("stroke", function (d) {
                linksToColors(d.endTime);
                return lastValue;
            })
            .on("mouseover", function (d) {
                let thisIndex = d.index;

                link.selectAll("path")
                    .style("opacity", function (d) {
                        return highlightPaths(d, thisIndex)
                    })
            })
            .on("mouseout", function (d) {
                link.selectAll("path").style("opacity", 0.7);
            })
            .on("click", function(d) {
                sankeyInfo(d, 'Path');
            })
        ;

        link.append("title")
            .text(function (d) {
                return d.source.name + " → " + d.target.name + "\n Step Number: " + (d.index+1) /* To avoid Step #0 */ + "\n Start time: " + totalTime(d.startTime) + "\n End time: " + totalTime(d.endTime) + "\n Total time: " + totalTime(d.value-10); /* TODO: Set min width of links to 10 instead of add 10 to their value. Very bad.*/
            });

        // Show arrows on the graph
        let arrows = pathArrows()
            .arrowLength(10)
            .gapLength(150)
            .arrowHeadSize(4)
            .path(function(link){ return link.path })

        var arrowsG = linkG.data(sankeyLinks)
            .enter()
            .append("g")
            .attr("class", "g-arrow")
            .call(arrows)

        function nodeTime(timeArray) {
            var totalSeconds = 0;

            for(var i=0; i<timeArray.length; i++){
                totalSeconds += timeArray[i].value;
            }
            return totalSeconds;
        }

        function highlightPaths(path, index) {
            let opacity = 0.7;

            if (path.index == index) {
                opacity = 1;
            }
            return opacity;
        }

        function highlightNodes(node, name) {

            let opacity = 0.3

            if (node.name == name) {
                opacity = 1;
            }
            node.sourceLinks.forEach(function (link) {
                if (link.target.name == name) {
                    opacity = 1;
                };
            })
            node.targetLinks.forEach(function (link) {
                if (link.source.name == name) {
                    opacity = 1;
                };
            })

            return opacity;

        }

        function sankeyInfo(d, type) {
            let text;
            var sankey = document.getElementById("sankey_info");
            sankey.innerHTML = '';
            if (type == 'Node') {
                text = `
            <div class="container">
                <div class="row">
                    <div class="col-md-12"><h4 class="nodeInfoHeader">${type} ${ccoi.ccoiSchema.nodes[d.name].id} Information</h4></div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <span><strong><span class="oi oi-question-mark"></span> Question: </strong>${ccoi.ccoiSchema.nodes[d.name].title}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <span><strong><span class="oi oi-clock"></span> Total Incoming Time: </strong>${totalTime(nodeTime(d.targetLinks))}</span>
                    </div>
                    <div class="col-md-6">
                        <span><strong><span class="oi oi-clock"></span> Total Outgoing Time: </strong>${totalTime(nodeTime(d.sourceLinks))}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <span><strong><span class="oi oi-account-login"></span> Total Incoming Steps: </strong>${d.targetLinks.length}</span>
                    </div>
                    <div class="col-md-6">
                        <span><strong><span class="oi oi-account-logout"></span> Total Outgoing Steps: </strong>${d.sourceLinks.length}</span>
                    </div>
                </div>
            </div>`
            } else {
                text = `
            <div class="container">
                <div class="row">
                    <div class="col-md-6"><h4 class="nodeInfoHeader">Step ${d.index+1} Information</h4></div>
                    <div class="col-md-6" style="text-align: center;">
                        <span><strong><span class="oi oi-fork"></span> Path #: </strong>${d.pathNum}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <span><strong><span class="oi oi-question-mark"></span> Question: </strong>${ccoi.ccoiSchema.nodes[d.source.name].title}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <span><strong><span class="oi oi-info"></span> Action: </strong>${d.choiceid}</span>
                    </div>
                </div>
                ${d.endChoice ?
                    `<div class="row">
                    <div class="col-md-12">
                        <span><strong><span class="oi oi-info"></span> Path Ending Choice: </strong>${d.endChoice}</span>
                    </div>
                </div>` : ''
                }
                <div class="row">
                    <div class="col-md-4">
                        <span><strong><span class="oi oi-clock"></span> Start Time: </strong>${totalTime(d.startTime)}</span>
                    </div>
                    <div class="col-md-4">
                        <span><strong><span class="oi oi-clock"></span> End Time: </strong>${totalTime(d.endTime)}</span>
                    </div>
                    <div class="col-md-4">
                        <span><strong><span class="oi oi-clock"></span> Total Time: </strong>${totalTime(d.value-10)}</span>
                    </div>
                </div>
            </div>`
            }
            var wrapper = document.createElement("div");
            wrapper.innerHTML = text;
            sankey.appendChild(wrapper);
        }
    }

    /**
     *  Function to initialize the data we need for the session stats section of the viz page.
     *  Includes: Solved, unsolved, solvedTime, unsolvedTime, studentDriven, peerDriven, adultDriven,
     *  independentCount, avgTimeBeforeInteraction, computingProblemSolving(cps), nonComputingProblemSolving(ncps), nonProblemSolving(nps)
     */
    function StatTracker(solved, unsolved, solvedTime, unsolvedTime, studentDriven, peerDriven, adultDriven, independentCount, avgTimeBeforeInteraction, cps, cds, ncds, cpsTime, cdsTime, ncdsTime) {
        this.solved = solved;
        this.unsolved = unsolved;
        this.solvedTime = solvedTime;
        this.unsolvedTime = unsolvedTime;
        this.studentDriven = studentDriven;
        this.peerDriven = peerDriven;
        this.adultDriven = adultDriven;
        this.independentCount = independentCount;
        this.avgTimeBeforeInteraction = avgTimeBeforeInteraction;
        this.cps = cps;
        this.cds = cds;
        this.ncds = ncds;
        this.cpsTime = cpsTime;
        this.cdsTime = cdsTime;
        this.ncdsTime = ncdsTime;
    }

    // Function to determine the path the decision will lead to and then increment that type of path counter
    function routeSwitch(choice, statTracker) {
        switch(choice) {
            case "2":
            case "4":
            case "8":
                isStudentDriven = true;
                statTracker.studentDriven++;
                break;
            case "3":
                isStudentDriven = false;
                statTracker.peerDriven++;
                break;
            case "5":
                isStudentDriven = false;
                statTracker.adultDriven++;
                break;
            case "6":
            case "7":
                isStudentDriven = false;
                statTracker.independentCount++;
                break;
            default:
                isStudentDriven = false;
                break;
        }
    }

    /**
     *  Switch to determine if a student driven path is one of the interactive paths of interest for the stat tracker
     */
    function interactiveTypeSwitch(groupName, time, statTracker) {
        switch(groupName) {
            //case "Computing Problem Solving": -> Collaborative problem solving
            case "Collaborative Problem Solving":
                statTracker.cps++;
                statTracker.cpsTime+=time;
                break;
            //case "Computing Discussion": -> Computing Discussion (Non-Problem Solving)
            case "Computing Discussion (Non-Problem Solving)":
                statTracker.cds++;
                statTracker.cdsTime+=time;
                break;
            case "Non-Computing Interaction":
                statTracker.ncds++;
                statTracker.ncdsTime+=time;
                break;
            default:
                break;
        }
    }
    /**
     *  Create a timeline data object from this session's information.
     *	This will be used later to render the timeline and pie charts.
     */
    function createTimeline(session, statTracker){
        let timelines = [];
        // Loop over every path in this session.
        session.paths.forEach(function (path, i, paths){
            console.log(path.steps[0]);
            let firstChoice = ccoi.ccoiSchema.getChoiceFromID(path.steps[0].choiceid);

            if(useDefaultSchema) {
                routeSwitch(path.steps[0].choiceid, statTracker);
                if(path.steps[0].choiceid == "2" || path.steps[0].choiceid == "4" || path.steps[0].choiceid == "8") {
                    console.log("path.steps[0]");
                    console.log(path.steps[0]);
                }
            }

            // If node_sub_group is set on the path's first choice, then this path uses a different method than the default to calculate time spent in specific task categories. We should use the createChoiceTimeline function to gather its timeline data.
            if(firstChoice.node_sub_group){
                // Loop over every step in this path to build a full path timeline.
                let pathTimelines = path.steps.reduce((result, currentStep, stepIndex, steps) => {
                    let choiceTimeline = createChoiceTimeline(currentStep, stepIndex, steps);
                    if(choiceTimeline) result.push(choiceTimeline);
                    return result;
                }, []);
                timelines = timelines.concat(pathTimelines);
                return;
            }

            // Otherwise, use the "default," recursive createSubTimeline function to create the timeline for this path.
            let pathTimeline = createSubTimeline(path.steps, 0, statTracker);
            timelines = timelines.concat(pathTimeline);

            if(isStudentDriven){ console.log(i + " " + timelines[i+1]);}

            // If we are in a student driven path we want to see if its an interactive path of interest
            if(isStudentDriven && timelines[i] && useDefaultSchema) {
                console.log("path type");
                console.log(timelines[i]);

                interactiveTypeSwitch(timelines[i].path_type, timelines[i].duration, statTracker);
            }
        });
        console.log("timelines");
        console.log(timelines);
        return timelines;
    }

    /**
     *  Helper function that divides a path into timelines using the node_sub_group property.
     */
    function createChoiceTimeline(currentStep, stepIndex, steps){
        let choice = ccoi.ccoiSchema.getChoiceFromID(currentStep.choiceid);
        let nodeGroups = ccoi.ccoiSchema.nodeGroups;

        // We need a "next step" to calculate the duration of this step. If this is the last step, skip.
        if(stepIndex >= steps.length - 1) return null;
        if(!choice.node_sub_group) return null;

        let nextStep = steps[stepIndex+1];
        let start_time = totalSec(currentStep.minutes, currentStep.seconds);
        let end_time = totalSec(nextStep.minutes, nextStep.seconds);
        let duration = end_time - start_time;
        let stepInfo = [ccoi.ccoiSchema.getChoiceFromID(currentStep.choiceid), ccoi.ccoiSchema.getChoiceFromID(nextStep.choiceid)]

        let nodeGroup = nodeGroups.find((nodeGroup) => { return nodeGroup.machine_name == choice.node_sub_group});
        return {
            "start_time": start_time,
            "end_time": end_time,
            "duration": duration,
            "path_type": nodeGroup.label,
            "chart_fill": "#"+(nodeGroup.chart_fill || "b71c1c"),
            "description": "<em>No Description.</em>", // TODO: fill description here
            "decisions": stepInfo
        };
    }
    /**
     *  Recursive helper function that divides a path into timelines using the nodes' group information.
     */
    let pathCounter = 0;
    function createSubTimeline(steps, currentIndex, statTracker){
        // Get node groups from schema
        let nodeGroups = ccoi.ccoiSchema.nodeGroups;

        // TODO: Solve problem with empty paths array in this function Moores 12 2018
        // console.log(steps);

        // We need a "next step" to calculate the duration of this step. If this is the last step, skip.
        if(currentIndex >= steps.length -1 || currentIndex < 0) {
            return [];
        }

        let currentStep = steps[currentIndex];

        let stepsInfo = [];

        let currentStepTime = totalSec(currentStep.minutes, currentStep.seconds);
        let nextStep = steps[currentIndex+1];
        let nextStepTime = totalSec(nextStep.minutes, nextStep.seconds);
        // If the next step shares the same timestamp as this step, skip to the next step
        if(currentStepTime == nextStepTime){
            return createSubTimeline(steps, currentIndex+1, statTracker);
        }

        // Find next step that doesn't share this node's group
        let currentNode = ccoi.ccoiSchema.getNode(currentStep.nodeid);

        let currentNodeGroup = nodeGroups.find((nodeGroup) => { return nodeGroup.machine_name == currentNode.groups[0]});
        let endStepIndex = steps.findIndex((step, stepIndex) => {
            return stepIndex > currentIndex && ccoi.ccoiSchema.getNode(step.nodeid).groups[0] != currentNodeGroup.machine_name;
        });
        if(endStepIndex == -1) endStepIndex = steps.length -1;
        let endStep = steps[endStepIndex];
        let endStepNode = ccoi.ccoiSchema.getNode(endStep.nodeid);

        // Calculate duration of this segment
        // If the end step assumes_previous_timestamp, ignore this segment
        if(endStepNode.assumes_previous_timestamp){
            return createSubTimeline(steps, endStepIndex, statTracker);
        }
        let start_time = totalSec(currentStep.minutes, currentStep.seconds);
        // If this segment assumes_previous_timestamp, shift the start and end steps back by 1 before calculating start/end time
        if(currentNode.assumes_previous_timestamp){
            let previous_step = steps[currentIndex-1];
            start_time = totalSec(previous_step.minutes, previous_step.seconds);
            endStep = steps[endStepIndex-1];
        }
        let end_time = totalSec(endStep.minutes, endStep.seconds);
        let duration = end_time - start_time;

        for (let i=currentIndex; i<endStepIndex; i++){
            stepsInfo.push(ccoi.ccoiSchema.getChoiceFromID(steps[i].choiceid));

            // If we're using the default schema, we want to use stat tracker
            if (useDefaultSchema){
                // If this step is (un)solved, increment (un)solved and add time to (un)solved
                switch (steps[i].choiceid) {
                    case "96":
                        statTracker.solved++;
                        statTracker.solvedTime += duration;
                        break;
                    case "95":
                        statTracker.unsolved++;
                        statTracker.unsolvedTime += duration;
                        break;
                    default:
                        break;
                }
            }
        }

        let subTimeline = [{
            "start_time": start_time,
            "end_time": end_time,
            "duration": duration,
            "path_type": currentNodeGroup.label,
            "chart_fill": "#"+(currentNodeGroup.chart_fill || "b71c1c"),
            "description": "<em>No description provided.</em>", // TODO: fill description here
            "decisions": stepsInfo
        }];

        return subTimeline.concat(createSubTimeline(steps, endStepIndex, statTracker));
    }

    function chartInfo(pathType, timelines, container) {
        let text;
        let pathArray = [];
        var chart = document.getElementById(container);
        timelines.forEach((timeline, timelineIndex) => {
            if(timeline.path_type == pathType) {
                pathArray.push(timeline);
            }
        });
        chart.innerHTML = '';
        text = `
    <div class="container">
        <div class="row">
            <div class="col-md-12"><h4 class="nodeInfoHeader">${pathType} Paths</h4></div>
        </div>
        ${pathArray.map((path, i) => `
            <div class="row">
                <div class="col-md-12"><span><strong>Path ${i+1} (Length: ${totalTime(path.duration)})</strong><br/>Route: ${path.decisions.map((decision, i) => `${decision.description}`).join(" --> ")}</span></div>
            </div>
        `
        ).join("")}
    </div>`;
        let wrapper = document.createElement("div");
        wrapper.innerHTML = text;
        chart.appendChild(wrapper);
    }

    function displayPie(data, totalTime) {
        $("#pie_container").removeClass("d-none");
        $("#pie_info_box").removeClass("d-none");
        let pieChart = new d3pie("pie_container", {
            "size": {
                "canvasHeight": 400,
                "canvasWidth": 600,
                "pieOuterRadius": "65%"
            },
            "data": {
                "content": data
            },
            "labels": {
                "outer": {
                    "format": "label-value2",
                    "pieDistance": 2
                },
                "inner": {
                    "format": "none"
                },
                "mainLabel": {
                    "fontSize": 12,
                    "font": "inherit"
                },
                "value": {
                    "font": "inherit",
                    "fontSize": 12,
                    "color": "#777"
                },
                "lines": {
                    "enabled": true,
                    "color": "#cccccc"
                },
                "truncation": {
                    "enabled": true
                },
                // This formatter function renders both the outer pointer-style label (time in seconds) and the inner wedge label (% of total time). Both will display at the same time, and the same function is called for both.
                "formatter": (context) => {
                    let label = context.label;

                    if (context.section === 'outer' && context.part === "value") {
                        if (context.value === 1)
                            label = label + ' second ('+(label/totalTime*100).toFixed(2)+'%)';
                        else
                            label = label + ' seconds ('+(label/totalTime*100).toFixed(2)+'%)';
                    }
                    return label;
                }
            },
            "callbacks": {
                onClickSegment: function(a) {
                    chartInfo(a.data.label, timelines, "pie_info");
                }
            }
        });
    }

    // Fill label and color arrays with only unique values
    function labelAndColorArray(data, labels = [], colors = []) {
        for (let i=0; i<data.length; i++) {
            let timesArray = data[i].times;
            let shouldAdd = false;
            for (let j=0; j<timesArray.length; j++) {
                if(timesArray[j].starting_time != timesArray[j].ending_time) {
                    shouldAdd = true;
                }
            }
            if (shouldAdd && !labels.includes(data[i].label)) {
                labels.push(data[i].label);
                colors.push(data[i].color);
            }
        }
    }

    function displayTimeline(data) {
        $("#timeline_container").removeClass("d-none");
        $("#timeline_info_box").removeClass("d-none");

        // Take each label and color from the data and put them into arrays for the pie chart legend
        let labels = [];
        let colors = [];

        labelAndColorArray(data, labels, colors);

        // Add enough room at the top for our legend
        let topMargin = labels.length * 30;

        let timelineChart = d3.timelines()
            .relativeTime()
            .margin({left:30, right:30, top:topMargin, bottom:0})
            .tickFormat({
                format: function(d) { return d3.timeFormat("%M:%S")(d) },
                tickTime: d3.timeMinutes,
                tickInterval: 30,
                tickSize: 15,
            })
            .click(function (data, index, label) {
                chartInfo(data.type, timelines, "timeline_info");
            });

        var svg = d3.select("#timeline_container").insert("svg",'.hoverData').attr("width", 600).attr("height", 250)
            .datum(data).call(timelineChart);

        var size = 10;
        svg.selectAll("mydots")
            .data(labels)
            .enter()
            .append("rect")
            .attr("x", 0)
            .attr("y", function(d,i){ return i*(size+8) + 5})
            .attr("width", size)
            .attr("height", size)
            .style("fill", function(d,i) { return colors[i]})

        // Add one dot in the legend for each name.
        svg.selectAll("mylabels")
            .data(labels)
            .enter()
            .append("text")
            .attr("x",  size*1.2)
            .attr("y", function(d,i){ return  i*(size+8) + (size/2) + 5})
            .style("fill", "#777")
            .text(function(d){ return d})
            .attr("text-anchor", "left")
            .style("alignment-baseline", "middle")
    }

    // TODO: Need to fix the way timelines are generated when there are multiple sessions
    function prepareTimeline(shouldTimeline) {
        let timelineData = {};
        let totalTime = 0;
        timelines.forEach((currentTimeline, timelineIndex) => {
            if (shouldTimeline){
                // Initialize some extra data for this segment of the timeline, if it doesn't already exist.
                if(!timelineData[currentTimeline.path_type]){
                    timelineData[currentTimeline.path_type] = [];
                }
                // Add this timeline segment's info to the timeline render array.
                timelineData[currentTimeline.path_type].push({
                    "starting_time": (1000*currentTimeline.start_time),
                    "ending_time": (1000*currentTimeline.end_time),
                    "description": currentTimeline.description,
                    "color": currentTimeline.chart_fill,
                    "type": currentTimeline.path_type
                });
                let timelineDataFormatted = [];
                for(let label in timelineData){
                    timelineDataFormatted.push({
                        "label": label,
                        "times": timelineData[label],
                        "color": timelineData[label][0].color
                    });
                }
                displayTimeline(timelineDataFormatted);
            }
        });
    }

    function preparePieAndTimeline(shouldPie, shouldTimeline) {
        // We use the timelines array to store data for both timeline and pie chart data. This data has already been built, but we need to separate it into specifically structured render arrays for the pie and timeline charts.
        let pieData = {};
        let timelineData = {};
        // Store the total time for use in the pie chart
        let totalTime = 0;

        // Loop over each timeline segment to arrange data for the pie/timeline chart rendering functions.
        timelines.forEach((currentTimeline, timelineIndex) => {
            if(shouldPie) {
                // Initialize some extra data for this segment of the pie chart, if it doesn't already exist.
                if (!pieData[currentTimeline.path_type]) {
                    pieData[currentTimeline.path_type] = {
                        "value": 0,
                        "label": currentTimeline.path_type,
                        "color": currentTimeline.chart_fill
                    };
                }
                totalTime += currentTimeline.duration;
                pieData[currentTimeline.path_type].value += currentTimeline.duration; // Add this timeline segment's duration to the appropriate pie chart segment.
            }
            if (shouldTimeline){
                // Initialize some extra data for this segment of the timeline, if it doesn't already exist.
                if(!timelineData[currentTimeline.path_type]){
                    timelineData[currentTimeline.path_type] = [];
                }
                // Add this timeline segment's info to the timeline render array.
                timelineData[currentTimeline.path_type].push({
                    "starting_time": (1000*currentTimeline.start_time),
                    "ending_time": (1000*currentTimeline.end_time),
                    "description": currentTimeline.description,
                    "color": currentTimeline.chart_fill,
                    "type": currentTimeline.path_type
                });
            }
        });

        if(shouldPie) {
            let pieDataFormatted = Object.values(pieData);
            displayPie(pieDataFormatted, totalTime);
        }
        if(shouldTimeline) {
            let timelineDataFormatted = [];
            for(let label in timelineData){
                timelineDataFormatted.push({
                    "label": label,
                    "times": timelineData[label],
                    "color": timelineData[label][0].color
                });
            }
            displayTimeline(timelineDataFormatted);
        }
    }

    // Helper function to get average of array values
    function getAvg(array) {
        const total = array.reduce((acc, c) => acc + c, 0);
        return total / array.length;
    }

    // Get the average time that an independent path or paths take(s) before an interaction occurs
    function timeB4Interaction(timelines, statTracker) {
        let timesArray = [];
        let timeTracker = 0;
        for (let timeline of timelines) {
            if(timeline.path_type == "Independent Problem Solving" || timeline.path_type == "Non-Computing Independent") {
                timeTracker += timeline.duration;
            } else if (timeTracker != 0) {
                timesArray.push(timeTracker);
                timeTracker = 0;
            }
        }
        if (timesArray.length != 0) statTracker.avgTimeBeforeInteraction = getAvg(timesArray);
    }

    function setupViz(sessions) {
        if(!shouldAllow){return;}
        let graphsToMake = [];
        let sessionInputs = DOM.viz_session_select_ul.getElementsByTagName("INPUT");
        let chartInputs = DOM.viz_chart_select_ul.getElementsByTagName("INPUT");

        DOM.viz_session_select.classList.add("d-none");
        DOM.viz_refresh.classList.remove("d-none")
        DOM.visualizations.classList.remove("d-none");

        for (let chart of chartInputs){
            if(chart.checked) { graphsToMake.push(chart.id); }
        }
        let sessionIDArray = [];
        for (let sesh of sessionInputs) {
            if(sesh.checked){sessionIDArray.push(sesh.dataset.index);}
        }
        // Initialize our stat tracker
        let statTracker = new StatTracker(0,0,0,0,0,0,0,0,0,0,0, 0, 0, 0, 0);
        for (let seshID of sessionIDArray){
            let currSesh = sessions[seshID];
            currSesh.timeline = createTimeline(currSesh, statTracker);
            timelines = timelines.concat(currSesh.timeline);
            if (graphsToMake.includes('cb_sankey')) {
                DOM.sankey_container.classList.remove('d-none');
                createSankey(sankeyJSONFromSession(currSesh));
            }
        }
        if(graphsToMake.includes('cb_pie') || graphsToMake.includes('cb_timeline')) {
            preparePieAndTimeline(graphsToMake.includes('cb_pie'), graphsToMake.includes('cb_timeline'));
        }
        // Get the average time before interactions for the viz stat tracker
        timeB4Interaction(timelines, statTracker);
        appendStatTracker(DOM.session_facts, statTracker);
    }

    function bindListeners(sessions) {
        $(DOM.viz_select_btn).click(function() {
            setupViz(sessions);
        });
        $(DOM.viz_refresh).click(function() {
            location.reload();
        });
        $('.cb-custom').click(checkSelectionStatus);
    }

    return {
        fillSessionSelector: fillSessionSelector,
        bindListeners: bindListeners,
        setUseDefaultSchema: setUseDefaultSchema
    }
})();