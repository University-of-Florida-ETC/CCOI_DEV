<?php

 $content="This is another mail test!\n\n";
 $subject='Still More mail test!';
 $isHTML=true;

require '/var/www/html/ccoi.education.ufl.edu/api/ccoi_mail.php';

ccoiSendEmail('awumba@gmail.com','Bocky',$subject,$content,true);

?>