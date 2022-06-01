<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '/var/www/html/ccoi.education.ufl.edu/PHPMailer/src/Exception.php';
require '/var/www/html/ccoi.education.ufl.edu/PHPMailer/src/PHPMailer.php';
require '/var/www/html/ccoi.education.ufl.edu/PHPMailer/src/SMTP.php';

function ccoiSendEmail($targetemail,$targetname,$subject,$content,$isHTML){
		$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
		try {
			//Server settings
	//		$mail->SMTPDebug = 2;										// Enable verbose debug output
			$mail->isSMTP();													// Set mailer to use SMTP
			$mail->Host = 'email-smtp.us-east-1.amazonaws.com';					// Specify main and backup SMTP servers
			$mail->SMTPAuth = true;										// Enable SMTP authentication
			$mail->Username = 'AKIA2P65IBWTGFDMLREL';		// SMTP username
			$mail->Password = 'BAjezojCVmIhl1YKb1cG695NI78/AHtgliuPrQAnswEi';									// SMTP password
			$mail->SMTPSecure = 'tls';									// Enable TLS encryption, `ssl` also accepted
			$mail->Port = 587;												// TCP port to connect to

			//Recipients
			$mail->setFrom('admin@ccoi-dev.education.ufl.edu', 'CCOI Admin');          //This is the email your form sends From
			$mail->addAddress($targetemail, $targetname); // Add a recipient address
			//$mail->addAddress('contact@example.com');               // Name is optional
			//$mail->addReplyTo('info@example.com', 'Information');
			//$mail->addCC('cc@example.com');
			//$mail->addBCC('bcc@example.com');

			//Attachments
			//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

			//Content
			$mail->isHTML($isHTML);                                  // Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $content;
			//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			$mail->send();
			echo 'Message has been sent';										// SUCCESS
		} catch (Exception $e) {
			echo 'Message could not be sent.';								// FAILURE
		//	echo '               Mailer Error: ' . $mail->ErrorInfo;
		}
}
//$headers = 'From: admin@ccoi-dev.education.ufl.edu' . "\r\n" .'Reply-To: admin@ccoi-dev.education.ufl.edu' . "\r\n";
//$headers='';
//if(mail('awumba@gmail.com','Mail Test',$mail_content,$headers,'-Fadmin@ccoi-dev.education.ufl.edu -fadmin@ccoi-dev.education.ufl.edu')){echo 'On Its way!';}else{echo 'no love';} 

?>
