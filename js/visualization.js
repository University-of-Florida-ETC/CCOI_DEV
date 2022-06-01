"use strict";

$(function() {

ccoi.callToAPI('/storage/ccoi.json').then(function(result) {
    ccoi.ccoiSchema = ccoi.parseCCOI(result);
    // TODO: Change this if user has created a custom path schema
    ccoiVizGenerator.setUseDefaultSchema(true);
}).then(function(){
    const queryString = window.location.search;
    const urlParms = new URLSearchParams(queryString);
    if (urlParms.has('demo')) {
        let sessions = localStorage.getItem("sessions");
        if (sessions == "" || sessions == undefined ) { document.getElementById('no_sesh').classList.remove('d-none'); }
        else {
            DOM.viz_container.classList.remove('d-none');
            sessions = JSON.parse(sessions);
            ccoiVizGenerator.fillSessionSelector(sessions);
            document.getElementById('viz_session_select').classList.remove('d-none');
            ccoiVizGenerator.bindListeners(sessions);
        }
    } else if (urlParms.has('session')) {
        // hit api for session id
        // then show graph select modal
    } else {
        // show session select modal
        // then show graph select
    }
}, function(error) {
    console.log(error)
})
});