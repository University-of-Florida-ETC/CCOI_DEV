<?php
$page = "";
require 'newccoi/includes/header.php';
?>
        <main role="main">
            <div id="login_container" class="container-fluid" style="height: calc(100vh - 180px);">
                <div class="container" style="height: 100%">
                    <div class="row h-100">
                        <div class="col-md-6 col-12 mx-auto align-self-center">
                            <h1>C-COI Beta Login</h1>
                            <form id="#login_form" action="/newccoi/api/ccoi_login.php" method="post">
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="useremail">Email Address</label>
                                            <input type="email" class="form-control" id="useremail" name="useremail" placeholder="jane.doe@example.edu" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row pt-sm-2">
                                    <div class="col">
                                        <button class="btn btn-blue" type="submit">Login</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'newccoi/includes/footer.php'; ?>
        <script src="./js/jquery-3.4.1.min.js"></script>
        <script src="./js/bootstrap.min.js"></script>
    </body> 
</html>