<?php
$page = "Register";
include 'includes/header.php';
?>
        <main role="main">
            <div class="container-fluid">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-7 py-5">
                            <h1>Request Access</h1>
                            <p>Please complete the inquiry form below to gain access to the C-COI tool.</p>
                            <form id="access_request_form" action="register-form.php" method="post" role="form">
                                <div class="messages"></div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="first_name">First Name</label>
                                            <input type="text" class="form-control required" id="first_name" name="first_name" placeholder="Jane" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="last_name">Last Name</label>
                                            <input type="text" class="form-control required" id="last_name" name="last_name" placeholder="Doe" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="institution">Institution</label>
                                            <input type="text" class="form-control required" id="institution" name="institution" placeholder="Example University" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="email_address">Email Address</label>
                                            <input type="email" class="form-control required" id="email_address" name="email_address" placeholder="jane.doe@example.edu" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="phone_number">Phone Number</label>
                                            <input type="tel" class="form-control required" id="phone_number" name="phone_number" placeholder="123-456-7890" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="about_project">Tell Us About Your Project</label>
                                            <textarea class="form-control required" id="about_project" name="about_project" placeholder="Description of research project" rows="5" required></textarea>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row pt-sm-2">
                                    <div class="col">
                                        <input id="request_submit" class="btn btn-blue float-right" type="submit">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-5 py-sm-5 pl-md-5">
                            <div class="row">
                                <div class="col">
                                    <iframe style="display: block; margin: auto;" src="https://player.vimeo.com/video/437840908" width="400" height="225" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                                </div>
                            </div>
                            <div class="row py-3">
                                <div class="col">
                                    <div class="md-boxed-content light-blue-background">
                                        <h4>Analyze Computational Behaviors with the C-COI</h4>
                                        <p>The C-COI allows researchers to study the learning processes that K-12 students engage in while doing computational thinking and programming activities.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
        <script src="./js/jquery-3.4.1.min.js"></script>
        <script src="./js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/1000hz-bootstrap-validator/0.11.9/validator.min.js"></script>
        <script src="./js/request-access.js"></script>
    </body> 
</html>