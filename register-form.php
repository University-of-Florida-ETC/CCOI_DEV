<?php
require '/var/www/html/ccoi.education.ufl.edu/api/ccoi_mail.php';
$from = 'admin@ccoi-dev.education.ufl.edu';
$sendTo = 'mbeutt@ufl.edu';
$subject = 'New C-COI Access Request';
$fields = array('first_name' => 'First Name', 'last_name' => 'Last Name', 'institution' => 'Institution', 'phone_number' => 'Phone Number', 'email_address' => 'Email', 'about_project' => 'Project Description');

$okMessage = 'Your form successfully submitted. Thank you, we will review your message and get back to you shortly.';
$errorMessage = 'There was an error while submitting the form. Please try again later';

error_reporting(0);

try
{

    if(count($_POST) == 0) throw new \Exception('Form is empty');

    $emailText = "You have a new message from the C-COI Access Request Form\n=============================\n";

    foreach ($_POST as $key => $value) {
        // If the field exists in the $fields array, include it in the email
        if (isset($fields[$key])) {
            $emailText .= "$fields[$key]: $value\n";
        }
    }

    ccoiSendEmail($sendTo,'C-COI Access Form',$subject,$emailText,false);

    //$responseArray = array('type' => 'success', 'message' => $okMessage);
}
catch (\Exception $e)
{
    //$responseArray = array('type' => 'danger', 'message' => $errorMessage);
}