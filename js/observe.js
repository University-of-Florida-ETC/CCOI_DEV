"use strict";

$(function() {
    ccoi.callToAPI('/storage/ccoi.json').then(function(result) {
        ccoi.ccoiSchema = ccoi.parseCCOI(result);
    }).then(function(){
        setUpObservation();
        $('#coder_name').text(jsUserVars['first'] + " " + jsUserVars['last'] + "'s Sessions");
    }, function(error) {
        console.log(error)
    })

    function setUpObservation() {
        ccoiObservation.setDemoBool(false);
        ccoiObservation.getSessions();
        ccoiObservation.bindListeners();
    }
});