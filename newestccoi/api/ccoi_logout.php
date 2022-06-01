<?php

session_start();
$_SESSION=array();
header("Location: https://{$_SERVER['SERVER_NAME']}"); session_write_close(); exit;
?>