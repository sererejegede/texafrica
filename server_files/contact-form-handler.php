<?php
//echo 'Here';
$errors = '';
$mail_response = '';
//var_dump($_POST['name']);
$my_email = 'info@texafrica.com';//<-----Put Your email address here.
if (!isset($_POST['name']) ||
   !isset($_POST['email']) ||
   !isset($_POST['subject']) ||
   !isset($_POST['message'])
) {
  $errors .= "\n Error: all fields are required";
}
if (isset($_POST['name'])) {
  $name = $_POST['name'];
}
if (isset($_POST['email'])) {
  $email = $_POST['email'];
}
if (isset($_POST['email']) and !empty($_POST['email'])) {
  $email = $_POST['email'];
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors .= 'Invalid email address<br>';
  }
}
if (isset($_POST['subject'])) {
  $subject = $_POST['subject'];
}
if (isset($_POST['message'])) {
  $message = $_POST['message'];
}


if (empty($errors)) {

  /*try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 465;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Username = 'serere007@gmail.com';
    $mail->Password = 'anonymous1997';

    $mail->SMTPOptions = array(
       'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
       )
    );

    $mail->setFrom($email, $name);
    $mail->addAddress($email);
    $mail->addReplyTo($my_email, $name);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = '<h3> Name: ' . $name . '<br>'
       . 'Email: ' . $email . '<br>' .
       'Body: <br></h3>' . '<p>' . $message . '</p>';

    if (!$mail->send()) {
      $mail_response = 'An error occurred while sending mail';
    } else {
      $mail_response = 'Thanks for getting in touch with us. We\'ll get back to you shortly';
    }
    echo $mail_response;
  } catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }*/


  $to = $my_email;
  $email_subject = "Contact form submission: $name";
  $email_body = '<h3> Name: ' . $name . '<br>'
     . 'Email: ' . $email . '<br>' .
     'Body: <br></h3>' . '<p>' . $message . '</p>';

  $headers = "From: ". strip_tags($email) . "\r\n";
  $headers .= "Reply-To: ". strip_tags($email) . "\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

  if(mail($to, $email_subject, $email_body, $headers)){
    echo 'Your mail has been sent successfully.';
  } else{
    echo 'Unable to send email. Please try again.';
  }
  //redirect to the 'thank you' page
//  header('Location: contact-form-thank-you.html');
}
