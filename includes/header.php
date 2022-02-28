<?php
//CCOI header file
session_start();
$includeroot= $_SERVER['DOCUMENT_ROOT'];			$devprodroot='';			$serverroot=$_SERVER['SERVER_NAME'];		// this is DEV =====================  $devprodroot='/newccoi'; for prod
include $includeroot.$devprodroot.'/api/ccoi_session.php';
if (substr_count($_SERVER[‘HTTP_ACCEPT_ENCODING’], ‘gzip’))
    ob_start(“ob_gzhandler”);		// this may start throwing an error soon...  Use of undefined constant \xe2\x80\x98gzip\xe2\x80\x99 - assumed '\xe2\x80\x98gzip\xe2\x80\x99'
else ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- GA Disabled on DEV-->
    <!-- Global site tag (gtag.js) - Google Analytics
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-8082624-12"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-8082624-12');
    </script>
    -->
    <!-- Google Tag Manager
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-WJLDQ3B');</script>
     End Google Tag Manager -->

    <meta charset="UTF-8">
    <title>C-COI | <?php echo ucfirst($page)?></title>
    <link rel="icon" href="/assets/images/cropped-CTRL_Icon-32x32.png" sizes="32x32" />
    <link rel="icon" href="/assets/images/cropped-CTRL_Icon-192x192.png" sizes="192x192" />
    <link rel="apple-touch-icon-precomposed" href="/assets/images/cropped-CTRL_Icon-180x180.png" />
    <meta name="msapplication-TileImage" content="/assets/images/cropped-CTRL_Icon-270x270.png" />
    <link rel="stylesheet" href="<?php echo $devprodroot; ?>/css/app.css">
    <?php if (!empty($jsUserVars)){ echo $jsUserVars; } ?>
    <?php if (!empty($extraCSS)){ echo $extraCSS; } ?>
    <?php if (!empty($extraJS)){ echo $extraJS; } ?>
</head>

<body data-spy="<?php echo $dataSpy?>" data-target="<?php echo $dataTarget?>" data-offset="<?php echo $dataOffset?>">
<!-- GA Disabled on DEV-->
<!-- Google Tag Manager (noscript)
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WJLDQ3B"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
 End Google Tag Manager (noscript) -->
<nav class="navbar navbar-expand-lg navbar-light px-md-5">
    <a class="navbar-brand" href="/">C-COI Dev</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="nav navbar-nav px-md-5">
            <li class="nav-item <?php echo ($page == "home" ? "active" : "")?>">
                <a class="nav-link" href="/">Home</a>
            </li>
            <li class="nav-item <?php echo ($page == "about" ? "active" : "")?>">
                <a class="nav-link" href="/about">About</a>
            </li>
            <li class="nav-item <?php echo ($page == "demo" ? "active" : "")?>">
                <a class="nav-link" href="/demo">Demo</a>
            </li>
            <li class="nav-item <?php echo ($page == "contact" ? "active" : "")?>">
                <a class="nav-link" href="/contact">Contact</a>
            </li>
            <li class="nav-item <?php echo ($page == "dashboard" ? "active" : "")?>">
                <a class="nav-link" href="/ZPB/dashboard">New</a>
            </li>
        </ul>
        <ul class="nav navbar-nav ml-auto">
            <?php if(isset($_SESSION['pid']) === false) : ?>
                <span><?php echo $_SESSION['pid']; ?></span>
                <a class="btn btn-outline-darkblue my-2 mx-sm-2" href="/register" type="button">Register</a>
                <a class="btn btn-gold my-2 mx-sm-2" href="/login" type="button">Log In</a>
            <?php endif; ?>
            <?php if(isset($_SESSION['pid'])) : ?>
                <a class="btn btn-gold my-2 mx-sm-2" href="/api/ccoi_logout" type="button">Log Out</a>
            <?php endif; ?>
        </ul>
    </div>
</nav>
