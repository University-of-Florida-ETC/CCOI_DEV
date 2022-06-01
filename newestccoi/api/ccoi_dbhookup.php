<?php

$db=mysqli_connect("localhost","ccoi_editor","8rW4jpfs67bD",'newerccoi');
if(!$db){$err=mysqli_error();		exit ("Error: could not connect to the CCOI Database! -- $access -- $err");}

?>