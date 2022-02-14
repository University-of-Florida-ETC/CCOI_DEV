<?php
$page = "contact";
include 'includes/header.php';
?>
        <main role="main">
            <div class="container-fluid">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-7 py-5">
                            <h1>Contact Us</h1>
                            <p>Please contact us for more information about the Collaborative Computing Observation Instrument.</p>
                            <form id="contact_form" action="contact-form.php" method="post" role="form">
                                <div class="messages"></div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="first_name">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Jane" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="last_name">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Doe" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="institution">Institution</label>
                                            <input type="text" class="form-control" id="institution" name="institution" placeholder="Example University" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="email_address">Email Address</label>
                                            <input type="email" class="form-control" id="email_address" name="email_address" placeholder="jane.doe@example.edu" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="message">Your Message</label>
                                            <textarea class="form-control" id="message" name="message" placeholder="Your message to us" rows="5" required></textarea>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row pt-sm-2">
                                    <div class="col">
                                        <button id="contact_submit" class="btn btn-blue float-right" type="submit">Send Message</button>
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
        <script src="./js/contact.js"></script>
    </body> 
</html>