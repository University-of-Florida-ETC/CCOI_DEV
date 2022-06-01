<?php
$page = "about";
include 'includes/header.php';
?>
        <main role="main">
            <div class="container-fluid">
                <div class="container">
                    <div class="row pt-5">
                        <div class="col">
                            <h1>About the C-COI</h1>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 py-md-2 pb-5">
                            <p>The C-COI allows researchers to study the learning processes that K-12 students engage in while doing computational thinking and programming activities.  It has undergone content and construct validation over the course of three years (Israel et al., 2016).</p>
                            <p>The C-COI is used to analyze video screen capture data from Screencastify, video capture software for Google Chrome. </p>
                            <a class="btn btn-outline-gold my-sm-2 mr-sm-2" href="/assets/files/CCOI_Code_Book.pdf" target="_blank" type="button">Code Book</a>
                            <a class="btn btn-outline-red my-sm-2 mr-sm-2" href="#learn" type="button">Need Help?</a>
                        </div>
                        <div class="col-lg-6 pt-md-2 pl-md-5">
                            <iframe style="display: block; margin: auto;" src="https://player.vimeo.com/video/437840908" width="480" height="270" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                        </div>
                    </div>
                    <div class="row pt-5">
                        <div class="col"><h4 class="blue-font">C-COI videos are analyzed for:</h4></div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 col-sm-4 py-sm-3 text-center">
                            <img src="./assets/images/time-on-task.png" style="width: 100%;" alt="clock icon">
                            <h5>Time on Task</h5>
                        </div>
                        <div class="col-md-2 col-sm-4 py-sm-3 text-center">
                            <img src="./assets/images/persistence.png" style="width: 100%;" alt="person running icon">
                            <h5>Persistence</h5>
                        </div>
                        <div class=" col-md-2 col-sm-4 py-sm-3 text-center">
                            <img src="./assets/images/help.png" style="width: 100%;" alt="question mark icon">
                            <h5>Help Seeking or Giving</h5>
                        </div>
                        <div class="col-md-2 col-sm-4 py-sm-3 text-center">
                            <img src="./assets/images/collaborative.png" style="width: 100%;" alt="two people talking icon">
                            <h5>Collaborative Problem Solving</h5>
                        </div>
                        <div class="col-md-2 col-sm-4 py-sm-3 text-center">
                            <img src="./assets/images/problems.png" style="width: 100%;" alt="coding brackets icon">
                            <h5>Student Problems</h5>
                        </div>
                        <div class="col-md-2 col-sm-4 pt-sm-3 text-center">
                            <img src="./assets/images/social-behaviors.png" style="width: 100%;" alt="thumbs up icon">
                            <h5>Social Behaviors</h5>
                        </div>
                    </div>
                    <div class="row py-5">
                        <div class="col">
                            <div class="card-group">
                                <div class="card md-boxed-content grey-background">
                                    <div class="card-block">
                                        <h4 class="card-title">History</h4>
                                        <p class="card-text">This instrument is the result of a multi-year, collaborative effort between university faculty, computer scientists, graduate students, classroom teachers, and staff across the University of Florida and the University of Illinois. It started as a proof of concept on white boards, to spreadsheets, and now to a fully-developed video analysis instrument that allows us to study how students both independently and collaboratively engage in computational behaviors.</p>
                                    </div>
                                </div>
                                <div class="card md-boxed-content grey-background">
                                    <div class="card-block">
                                        <h4 class="card-title">Citation</h4>
                                        <p class="card-text">Creative Technology Research Lab Development Team. (2020). Collaborative Computing Observation Instrument (C-COI 4th Ed.). https://ccoi.education.ufl.edu/</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="learn" class="container-fluid light-blue-background">
                <div class="container">
                    <div class="row pt-5">
                        <div class="col">
                            <h2>C-COI Help Center</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 py-3 pb-5">
                            <div class="accordion" id="help_accordion">
                                <div class="card mb-3">
                                    <div class="card-header" id="headingOne">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                What is the C-COI?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#help_accordion">
                                        <div class="card-body">
                                            <p>The Collaborative Computing Observation Instrument (C-COI) allows researchers to study the learning processes and behaviors that K-12 students engage in while doing computational thinking and programming activities.</p>

                                            <p>It has undergone content and construct validation over the course of four years (Israel et al., 2016). The C-COI is used to analyze video screen capture data from Screencastify, video capture software for Google Chrome. This video is analyzed for:</p>
                                            <ul>
                                                <li>Time on task</li>
                                                <li>Persistence</li>
                                                <li>Help seeking/help giving</li>
                                                <li>Collaborative problem solving</li>
                                                <li>Problems students face while engaged in computing</li>
                                                <li>Social behaviors of students engaged in computing</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card  mb-3">
                                    <div class="card-header" id="headingTwo">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                How can I access the C-COI?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#help_accordion">
                                        <div class="card-body">
                                            <p>To request access for the C-COI, please use the request form located <a href="/register">here</a>. After we have reviewed your request, we will contact you to ask further questions about the nature of your research, the number of researchers who will need access and whether you will be using the <a href="/assets/files/CCOI_Code_Book.pdf" target="_blank">default C-COI path structure</a> or importing your own.</p>

                                            <p>If you wish to import your own structure, we will do our best to ensure that our system and accompanying data analysis tools can manage your data; however, we cannot guarantee that the system will operate the same with imported path structures.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mb-3">
                                    <div class="card-header" id="headingThree">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                                How will I know which codes to assign within the C-COI?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#help_accordion">
                                        <div class="card-body">
                                            <p>A detailed <a href="/assets/files/CCOI_Code_Book.pdf" target="_blank">code book</a> has been developed for use with the current set of C-COI codes. It was developed collaboratively by a team of experienced C-COI users and iterated on over the course of several years as a result of extensive research in schools. </p>

                                            <p>Part of the coding process involves immersing oneself in the video screen recording to become familiar with the types of student behaviors that occur during programming sessions. As one becomes more familiar with the video data and the instrument itself, code selection becomes easier. That said, it is advised that researchers  check their codes with other raters over the course of multiple coding sessions as preparation for data analysis. Standard qualitative rater comparison procedures are prudent.</p>
                                        <p>To establish reliability, an algorithm was created which establishes percent agreement between two raters.  Percent agreement was used for this purpose as opposed to Cohen’s Kappa due to the vast number of possible combinations of nodes that may occur. To assess agreement of video analysis, a rigorous, multi-level protocol was established.</p>
                                        </div>
                                    </div>
                                </div>
                                <!--<div class="card mb-3">
                                    <div class="card-header" id="headingFour">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                                Why do researchers use the C-COI?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#help_accordion">
                                        <div class="card-body">
                                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>

                                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                        </div>
                                    </div>
                                </div>-->
                                <div class="card mb-3">
                                    <div class="card-header" id="headingFive">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                                What can I do with the Demo site for the C-COI?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#help_accordion">
                                        <div class="card-body">
                                            <p>Using the Demo site for the C-COI, you will be able to analyze and code our demo video just as you would in the live application. Although you will not be able to upload your own video into the Demo site, you can still use the tool while viewing your own videos on another screen. In addition, you will also be able to use our visualizations to analyze the data that you code. The demo sessions that you code will be stored in your browser, so you can come back and edit your session later.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mb-3">
                                    <div class="card-header" id="headingSix">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                                How are codes extracted to make the visualizations in the C-COI?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseSix" class="collapse" aria-labelledby="headingSix" data-parent="#help_accordion">
                                        <div class="card-body">
                                            <p>The C-COI is equipped with visualization software to help you analyze the data that you code. Currently, the tool offers pie charts, timelines and circular sankey visualizations. In addition, if you choose to use the default C-COI schema, the system will give you a holistic snapshot of key data.</p>
                                            <p>In the future, the C-COI will allow a more open spectrum of options for users to generate custom statistics based on their unique data.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mb-3">
                                    <div class="card-header" id="headingSeven">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                                What kind of data is used with the C-COI?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseSeven" class="collapse" aria-labelledby="headingSeven" data-parent="#help_accordion">
                                        <div class="card-body">
                                            <p>Currently the C-COI uses observational data of students’ computer screens as its main input. Our vision is to expand the input data to include eye-tracking and other innovative technologies.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mb-3">
                                    <div class="card-header" id="headingEight">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                                How do I import data into the C-COI so that I can use it?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseEight" class="collapse" aria-labelledby="headingEight" data-parent="#help_accordion">
                                        <div class="card-body">
                                            <p>If your request for access to the C-COI is approved, we will reach out to your research team to discuss your onboarding. In the event that you need to use your own path structure, you can upload a JSON file containing the structure directly to our website. If the path structure you upload passes testing on our system, you will be able to select it as your default schema.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mb-3">
                                    <div class="card-header" id="headingNine">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
                                                What are some considerations when collecting video screen capture data?
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseNine" class="collapse" aria-labelledby="headingNine" data-parent="#help_accordion">
                                        <div class="card-body">
                                            <p>Depending the hardware and software you use to collect screen capture data, you may need to consider certain issues like logging students into the screencast software, navigating issues related to district security or access protocols, and intersections between the software and any other classroom software that may be in use (e.g., Google Classroom). Selecting a secure storage site for data and making sure that students do not have access to it or the screencast account is prudent.</p>
                                            <p>It may be difficult to ascertain who is speaking during video screencast recording sessions due to normal classroom noise and activity. Having the participant state their identifier and speak for approximately 15- 20 seconds allows those analyzing the data to become familiar with their voice. We recommend that this process is repeated at the end of the screen cast recording as well. For example, the student may be asked to repeat the following script, “My ID number is _____. I am working on_______”  </p>
                                            <p>Depending on the software used, students may be able to turn off the video screen recording during data collection. Therefore, it is advisable to monitor students’ computers and periodically check that recording is still active. </p>
                                            <p>In addition to video screen recordings, it is also helpful to have concurrent classroom observations to note things such as whether the students leave their computers, move to collaborate with peers, etc. Some data cannot be captured with video screen recordings, so this additional data is sometimes crucial to contextualize what is happening on students’ computer screens.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--
                        <div class="col-lg-5 pt-md-3 pb-5 pl-md-5">
                            <div class="row">
                                <div class="col-md-12">
                                        <img src="./assets/images/youtube-image.jpg" style="width: 100%;" alt="Video icon">
                                </div>
                                <div class="col-md-12 pt-3">
                                    <img src="./assets/images/youtube-image.jpg" style="width: 100%;" alt="Video icon">
                                </div>
                            </div>
                        </div>-->
                    </div>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
        <script src="./js/jquery-3.4.1.min.js"></script>
        <script src="./js/bootstrap.min.js"></script>
    </body> 
</html>