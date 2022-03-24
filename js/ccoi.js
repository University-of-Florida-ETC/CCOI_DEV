'use strict';

var DOM = {};
(function () {
    var IDs = [
        'launch_video_button',
        'go_to_session_select',
        'save_session_button',
        'session_list',
        'session_meta_form',
        'session_video_url',
        'session_notes',
        'new_session_button',
        'session_submit_button',
        'add_path_button',
        'reorder_paths_button',
        'finish_reorder_button',
        //Brandon's addition of node_preview_list
        'node_preview_list',
        'path_start',
        'path_choices',
        'path_select',
        'path_input',
        'path_list',
        'path_listing',
        'path_preview',
        'path_preview_list',
        'path_preview_heading',
        'path_title',
        'path_label',
        'path_label_button',
        'proceed_button',
        'proceed_and_play_button',
        'branch_form',
        'notes_input',
        'timestamp_input_minutes',
        'timestamp_input_seconds',
        'irr_button',
        'dom_group_1',
        'path_go_back',
        'session_go_back',
        'visualizations',
        'viz_container',
        'viz_refresh',
        'viz_session_select',
        'viz_chart_select_form',
        'viz_session_select_ul',
        'viz_chart_select_ul',
        'demo_no_sesh',
        'viz_select_btn',
        'session_facts',
        'sankey_container',
        'csvImportShow',
        'exportHumanReadable',
        'exportCSV',
        'prepareGVall',
        'goToMainMenu',
        'pathSelectTitle',
        'pathSelectList',
        'sessionLabel',
        'sessionTitle',
        'sessionDate',
        'sessionStudent',
        'sessionPrompted',
        'sessionSelect',
        'timestampInputMinutes',
        'timestampInputSeconds',
        'notesInputLabel',
        'csvImportDialog',
        'csvImportFileInput',
        'gvExportDialog',
        'returnFromGV',
        'gvForm',
        'gvSelectGraphType',
        'gvSelectEdgeType',
        'gvShowEnd',
        'gvAcyclic',
        'gvSelectSessions',
        'rawOutput',
        'exportTitle',
        'returnFromExport',
        'exportDownload',
        'exportOut'
    ];

    var numIDs = IDs.length;
    for (var i = 0; i < numIDs; ++i) {
        var ID = IDs[i];
        DOM[ID] = document.getElementById(ID);
    }
}
)();

var ccoi = (function (){
    var ccoiSchema;

    function callToAPI(url, data= "", method = 'POST') {
        var xhr = new XMLHttpRequest();
        return new Promise(function(resolve, reject) {
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) return;

                if (xhr.readyState == 4) {
                    if (xhr.status >= 300) {
                        reject("Error, status code = " + xhr.status);
                    } else {
                        resolve(xhr.responseText);
                    }
                }
            }
            xhr.open(method, url, true);
            xhr.send(data);
        });
    }
    
    function parseCCOI(response) {
	
        var schema = JSON.parse(response);
        console.log(schema);

        var nodeArray = schema.nodes;
        var nodesByID = {};
        var branchesByID = {};

        for(var i = 0; i < nodeArray.length; i++){
            var currentNode = nodeArray[i];
            if(nodesByID[currentNode.node_id]) {
                throw "Error: collision on node id "+currentNode.node_id;
            }
            nodesByID[currentNode.node_id] = currentNode;
            var pretty_counter = 0;
            for(var j = 0; j < currentNode.branches.length; j++){
                for(var k = 0; k < currentNode.branches[j].length; k++){
                    var currentBranch = currentNode.branches[j][k];
                    currentBranch.parent_node = currentNode.node_id;
                    currentBranch.pretty_id = pretty_counter++;
                    if(branchesByID[currentBranch.branch_new_id]) {
                        throw "Error: collision on branch id "+currentBranch.branch_new_id;
                    }
                    branchesByID[currentBranch.branch_new_id] = currentBranch;
                }
            }
        }
        schema.nodes = nodesByID;
        schema.branches = branchesByID;

        // Returns the node for a given node ID, either new integer ID or old hex ID
        schema.getNode = function(id) {
            // New ID system that Mark setup
            if(Number.isInteger(id)){
                console.log("Passed is integer\n \n");
                for(var key in schema.nodes){
                    if(schema.nodes[key].node_id == id) {
                        return schema.nodes[key]
                    }
                }
                throw "Error: no node found for node id "+id;
            }

            // Old hex-id numbering system
            if(schema.nodes[id]) return schema.nodes[id];

            throw "Error: no node found for node id "+id;
        }

        // Returns the choice for a given branch ID (hex IDs only)
        schema.getChoiceFromID = function(id) {		
            console.log("Here's the id we handed to getchoicefromID:" + id);		
            // New hex-id numbering system
            if(schema.branches[id]) return schema.branches[id];

            throw "Error: no choice found for branch id "+id;
        }

        schema.getChoiceFromNodeAndID = function(node_id, choice_id){
            if(schema.branches[choice_id]) return schema.branches[choice_id];

            if(Number.isInteger(choice_id) && choice_id <= 100000){
                let node = schema.getNode(node_id);
                //console.log(node);

                for(var i = 0; i < node.branches.length; i++){
                    if(node.should_group_choices){
                        for(var j = 0; j < node.branches[i].length; j++){
                            if(node.branches[i][j].pretty_id == choice_id)
                                return node.branches[i][j];
                        }
                    }
                    else if(node.branches[i].pretty_id == choice_id)
                        return node.branches[i];
                }
            }

            throw "Error: no choice found for node id "+node_id+" and branch id "+choice_id;
        }

        return schema;
    }
    
    return {
        parseCCOI: parseCCOI,
        callToAPI: callToAPI,
        ccoiSchema: ccoiSchema
    }
    
})();