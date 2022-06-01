"use strict";

$(function() {
    ccoi.callToAPI('/storage/ccoi.json').then(function(result) {
        ccoi.ccoiSchema = ccoi.parseCCOI(result);
    }).then(function(){
        setUpObservation();
    }, function(error) {
        console.log(error)
    })

    function setUpObservation() {
        ccoiObservation.setDemoBool(true);
        ccoiObservation.getSessions();
        ccoiObservation.bindListeners();
    }
});

