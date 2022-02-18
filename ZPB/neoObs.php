<?php
$page = "dashboard";
$dataTarget = "#demo_help_box";
$dataOffset = "10";
$dataSpy = "scroll";
include '../includes/header.php';
$sessionTitle = "sessionTitle";
?>
<script src="/js/jquery-3.4.1.min.js"></script>
<script src="/js/utility.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/zpbccoi.js"></script>

<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Hello, world!</title>
</head>

<body>
    <div class="jumbotron text-center">
        <h1>My First Bootstrap Page</h1>
        <p>Resize this responsive page to see the effect!</p>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-sm-4">
                <h3>Column 1</h3>
                <p>Lorem ipsum dolor..</p>
            </div>
            <div class="col-sm-4">
                <h3>Column 2</h3>
                <p>Lorem ipsum dolor..</p>
            </div>
            <div class="col-sm-4">
                <h3>Column 3</h3>
                <p>Lorem ipsum dolor..</p>
            </div>
        </div>
    </div>
</body>

</html>