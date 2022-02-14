<?php
$page = "";
include 'includes/header.php';
?>
        <main role="main">
            <div id="no_sesh" class="container-fluid d-none" style="height: calc(100vh - 180px);">
                <div class="container" style="height: 100%">
                    <div class="row h-100">
                        <div class="col-md-6 col-12 mx-auto align-self-center">
                            <span style="font-size: 3rem; display: block; margin: 1rem auto;" class="oi oi-warning viz-error-icon"></span>
                            <p style="font-size: 1.5rem; text-align: center;">Oops... You have not created a demo observation yet, so there are no visualizations to display.</p>
                            <div class="text-center"><a class="btn btn-blue" href="/demo">Create Observation</a></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="viz_container" class="container-fluid d-none">
                <div class="container">
                    <div class="row pt-3 pb-5">
                        <div class="col">
                            <a class="underlined-btn" href="/demo"><span class="oi oi-arrow-thick-left mr-2"></span><span class="btn-text">Back to Observation</span></a>
                        </div>
                    </div>
                    <div class="row pb-5">
                        <div class="col-sm-12 col-md-9">
                            <h1 class="red-font">Visualizations</h1>
                            <h5 id="viz_session_list">Demo Session</h5>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <button type="button" id="viz_refresh" class="btn btn-blue d-none float-md-right">Back to Selection</button>
                        </div>
                    </div>
                    <div id="viz_session_select" class="row pb-5 d-none">
                        <div class="col-md-8 col-12 mx-auto">
                            <h4 class="text-center pb-3">Select Session(s)</h4>
                            <form id="viz_chart_select_form" action="javascript:void(0)">
                                <ul id="viz_session_select_ul" class="row"></ul>
                                <h4 class="text-center pb-3">Select Desired Visualizations</h4>
                                <ul id="viz_chart_select_ul" class="row">
                                    <li class="col"><input type="checkbox" id="cb_pie" class="cb-custom" />
                                        <label for="cb_pie"><img src="/assets/images/pie-chart.png" />Pie Chart</label>
                                    </li>
                                    <li class="col"><input type="checkbox" id="cb_timeline" class="cb-custom" />
                                        <label for="cb_timeline"><img src="/assets/images/timeline-graph.png" />Timeline</label>
                                    </li>
                                    <li class="col"><input type="checkbox" id="cb_sankey" class="cb-custom" />
                                        <label for="cb_sankey"><img src="/assets/images/sankey-diagram.png" />Sankey Diagram</label>
                                    </li>
                                </ul>
                                <div class="row">
                                    <div class="col"><button id="viz_select_btn" class="btn btn-blue float-right disabled" type="button">Create Charts</button></div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div id="visualizations" class="row pb-5 d-none">
                        <div id="session_facts" class="col-12 pb-5">

                        </div>
                        <div id='timeline_container' class='col-md-8 pb-5 d-none'>
                            <h4 class="pb-3">Session(s) Timeline</h4>
                        </div>
                        <div id="timeline_info_box" class='col-md-4 pb-5 d-none'>
                            <div id="timeline_info">
                                <div class="timeline-instructions">
                                    <span class="oi oi-clock"></span>
                                    <span id="timeline_instruction_text">Click on a section of the timeline for further information.</span>
                                </div>
                            </div>
                        </div>
                        <div id='pie_container' class='col-md-8 pb-md-0 pb-sm-5 d-none'>
                            <h4>Path Breakdown</h4>
                        </div>
                        <div id="pie_info_box" class='col-md-4 pb-md-0 pb-sm-5 d-none'>
                            <div id="pie_info">
                                <div class="pie-instructions">
                                    <span class="oi oi-pie-chart"></span>
                                    <span id="pie_instruction_text">Click on a section of the pie chart for further information.</span>
                                </div>
                            </div>
                        </div>
                        <div id="sankey_container" class="col-12 pb-5 d-none">
                            <h4 class="pb-3">Sankey Diagram</h4>
                            <div id="sankey_info">
                                <div class="sankey-instructions">
                                    <span class="oi oi-graph"></span>
                                    <span id="sankey_instruction_text">Click on a path or node to show further details.</span>
                                </div>
                            </div>
                            <!-- TODO: Change this, so legend is creating with JS from the JSON object -->
                            <div class="legend-area">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5 class="pt-3">CCOI Sankey Node Legend</h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span style="color: #06bf1b; font-size: 20px;">&#9724;</span><span> Start</span>
                                        </div>
                                        <div class="col-md-4">
                                            <span style="color: #c4414f; font-size: 20px;">&#9724;</span><span> Independent</span>
                                        </div>
                                        <div class="col-md-4">
                                            <span style="color: #aaaaaa; font-size: 20px;">&#9724;</span><span> Interactive</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <!--<span style="color: #0676be; font-size: 20px;">&#9724;</span><span> Interactive, Computing Problem Solving</span>-->
                                            <span style="color: #0676be; font-size: 20px;">&#9724;</span><span>Collaborative Problem Solving</span>
                                        </div>
                                        <div class="col-md-4">
                                            <!--<span style="color: #db8409; font-size: 20px;">&#9724;</span><span> Interactive, Computing Discussion</span>-->
                                            <span style="color: #db8409; font-size: 20px;">&#9724;</span><span>Computing Discussion (Non-Problem Solving)</span>
                                        </div>
                                        <div class="col-md-4">
                                            <!--<span style="color: #320ead; font-size: 20px;">&#9724;</span><span> Interactive, Non-Computing Interaction</span>-->
                                            <span style="color: #320ead; font-size: 20px;">&#9724;</span><span>Non-Computing Interaction</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id='sankey_area' class="col"></div>
                            <div class="color-timeline-container">
                                <div id="color_timeline">
                                    <div id="time_start"></div>
                                    <div id="time_end"></div>
                                </div>
                                <p style="text-align: center;">Video Timestamp</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
        <script src="./js/jquery-3.4.1.min.js"></script>
        <script src="./js/bootstrap.min.js"></script>
        <script src="https://d3js.org/d3.v4.min.js" charset="utf-8"></script>
        <script src="https://cdn.rawgit.com/eligrey/canvas-toBlob.js/f1a01896135ab378aa5c0118eadd81da55e698d8/canvas-toBlob.js"></script>
        <script src="https://cdn.rawgit.com/eligrey/FileSaver.js/e9d941381475b5df8b7d7691013401e171014e89/FileSaver.min.js"></script>
        <script type="text/javascript" src="./js/libraries/d3/d3pie.min.js" defer></script>
        <script type="text/javascript" src="./js/libraries/d3/d3-timelines.js" defer></script>
        <script type="text/javascript" src="./js/libraries/d3/d3-sankey-circular.js"></script>
        <script type="text/javascript" src="./js/libraries/d3/d3-path-arrows.js"></script>
        <script type="text/javascript" src="./js/ccoi.js"></script>
        <script type="text/javascript" src="./js/utility.js"></script>
        <script type="text/javascript" src="./js/visualization-generator.js"></script>
        <script type="text/javascript" src="./js/visualization.js"></script>
    </body>
</html>