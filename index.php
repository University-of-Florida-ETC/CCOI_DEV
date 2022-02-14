<?php
$page = "home";
include 'includes/header.php';
?>
        <main role="main">
            <div class="container-fluid">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6 py-5">
                            <h1>Collaborative Computing Observation Instrument</h1>
                            <p>The Collaborative Computing Observation Instrument (C-COI) allows researchers to study the learning processes that K-12 students engage in while doing computational thinking and programming activities.</p>
                            <a class="btn btn-blue" href="/about" role="button">About C-COI</a>
                        </div>
                        <div class="col-lg py-md-5 pt-3 pb-5">
                            <iframe style="display: block; margin: auto;" src="https://player.vimeo.com/video/437840908" width="480" height="270" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid grey-background">
                <div class="row">
                    <div class="col-lg full-width-image home-cta-students">
                        <div class="full-width-image-text">
                            <h2 class="pb-3">C-COI Demo</h2>
                            <a id="home_demo_cta" class="btn btn-gold" href="/demo" role="button">Start Observing</a>
                        </div>
                    </div>
                    <div class="col-lg full-width-image home-cta-researchers">
                        <div class="full-width-image-text">
                            <h2 class="pb-3">Researcher Access</h2>
                            <a id="home_access_cta" class="btn btn-gold" href="/register" role="button">Request Access</a>
                        </div>
                    </div>
                </div>
            </div>
            <!--
            <div class="container-fluid grey-background">
                <div class="container">
                    <div class="row">
                        <div class="col-lg py-5">
                            <img src="./assets/images/students-working.jpg" style="width: 100%;" alt="Two female students working on computer project">
                        </div>
                        <div class="col-lg-9 pt-md-5 pt-2 pb-5 pl-md-5">
                            <h2>Testimonials</h2>
                            <p class="testimonial">In hac habitasse platea dictumst. In hendrerit mi sem, vehicula vulputate quam congue vitae. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Praesent gravida placerat risus. In hac habitasse platea dictumst. In hendrerit mi sem, vehicula vulputate quam congue vitae. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Praesent gravida placerat risus.</p>
                            <div class="attribution float-right">
                                <p>This is a person's name</p>
                                <h5 class="blue-font">This is their institution</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            -->
        </main>
        <?php include 'includes/footer.php'; ?>
        <script src="./js/jquery-3.4.1.min.js"></script>
        <script src="./js/bootstrap.min.js"></script>
    </body> 
</html>