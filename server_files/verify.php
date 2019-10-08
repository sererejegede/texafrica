<?php
/**
 * Created by PhpStorm.
 * User: TOSIN
 * Date: 6/23/2019
 * Time: 12:48 PM
 */

//require 'Paystack.php';
require_once 'Unirest.php';
//$paystack = new Paystack('sk_live_cd829129a22527cba7294cc74a01e250d24912ca'); // sk_test_988f227099c3a263e8ddc4d02dac38d37cbd59fc
$reference = $_POST['reference'];


// include constants
include 'constants.php';

$data = array('txref' => $reference,
   'SECKEY' => 'FLWSECK_TEST-f425d330865e25223c6f0c6d454e1623-X' //
);


// make request to endpoint using unirest.
$headers = array('Content-Type' => 'application/json');
$body = Unirest\Request\Body::json($data);
$url = "https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/v2/verify"; //please make sure to change this to production url when you go live

// Make `POST` request and handle response with unirest
$response = Unirest\Request::post($url, $headers, $body);
// check the status is successful
if ($response->body->data->status === "successful" && $response->body->data->chargecode === "00") {
  // confirm that the amount is the amount you wanted to charge
//  var_dump($response->body);
//  if ($response->body->data->amount === 100) {
    give_value($reference, $HOST, $USERNAME, $PASSWORD, $DATABASE, $response);
//  }
}



/*if('success' == $trx->data->status){
  give_value($reference, $HOST, $USERNAME, $PASSWORD, $DATABASE, $trx);
//  perform_success();
}*/


function give_value($reference, $host, $username, $password, $database, $transaction_response_from_paystack){

  file_put_contents('response_logs.txt',
     date('Y-m-d H:i:s'). json_encode($transaction_response_from_paystack). PHP_EOL,
     FILE_APPEND | LOCK_EX);
  $conn = mysqli_connect($host, $username, $password, $database);
  if (!$conn) {
    file_put_contents('log.txt',
       date('Y-m-d H:i:s'). ' '. mysqli_connect_errno(). PHP_EOL,
       FILE_APPEND | LOCK_EX);
    die("Connection failed: ");
  }
//  $reference = mysqli_real_escape_string($conn, $reference);
  $update_query = "UPDATE transactions SET verify_status = 1, updated_at = NOW() WHERE transaction_id = '$reference'";
//  echo $update_query;
  if ($update = mysqli_query($conn, $update_query)) {
    $data = getUser($reference, $host, $username, $password, $database);

//    die();
    perform_success($data);
  } else {
    file_put_contents('log.txt',
       date('Y-m-d H:i:s'). mysqli_error($conn). PHP_EOL,
       FILE_APPEND | LOCK_EX);
    echo 'An error occurred';
    die();
  }
}

function getUser($reference, $host, $username, $password, $database) {
  $conn2 = mysqli_connect($host, $username, $password, $database);
  if (!$conn2) {
    file_put_contents('log.txt',
       date('Y-m-d H:i:s'). ' '. mysqli_connect_errno(). PHP_EOL,
       FILE_APPEND | LOCK_EX);
    die("Connection failed: ");
  }
  $data_array = array();
  $get_user_id_query = "SELECT * FROM users JOIN transactions ON transactions.user_id = users.id WHERE transactions.transaction_id = '$reference'";
  if ($_POST['is_pal'] == 1) {
    $get_user_id_query = "SELECT * FROM users JOIN transactions ON transactions.user_id = users.id JOIN pals ON pals.user_id = users.id WHERE transactions.transaction_id = '$reference'";
  }
//   die($get_user_id_query);
  if ($resource = mysqli_query($conn2, $get_user_id_query)) {
      while ($result2 = mysqli_fetch_assoc($resource)) {
        $data_array[] = $result2;
      }

    $answer = json_decode(json_encode($data_array))[0];

    return $answer;
  }
}

function perform_success($data){
  $first_name = $data->first_name;
  $is_sent = sendReceipt($first_name);
   try {
      postToEventsRail($data);
    } catch (Exception $exception) {
      file_put_contents('log.txt',
         date('Y-m-d H:i:s'). ' Events rail error: '. $exception. PHP_EOL,
         FILE_APPEND | LOCK_EX);
    }
  notifyTexa($data);
  // inline
  echo json_encode([
     'verified' => true,
     'mail_sent' => $is_sent
  ]);
  // standard
//  header('Location: /success.php');
  exit();
}

function sendReceipt($first_name) {
  $to = $_POST['email'];
  $email_subject = "Thank you for registering ";
  $email_body = "
  <html>
  <head>
    <meta name='viewport' content='width=device-width, initial-scale=1.0' />
    <style>
        .signature{font-size: 20pt;}
        .container{width: 450px;
            font-size:14pt;
            line-height: 180%;}
        @media (max-width: 400px){
            .signature{font-size: 16pt;}
            .container{width: 80%;
                font-size:11pt;
                line-height: 150%;}
            
        }
        @font-face {font-family: Allison_Script;src: url('http://texafrica.com/Allison_Script.otf') format('opentype');}
    </style>
   </head>
   <body>
    <div class='container' style='font-family:Roboto; margin: 0 auto;font-family:Georgia;padding: 25px;border: 8px solid lightgrey;'>
        <div>
            <img src='http://texafrica.com/images/TEXA-LOGO-300X300.png' style='height: 120px; width: auto; padding-left: 20px'>
        </div>
        <div style='padding-top: 40px'>
            Hi {$first_name},<br><br>
            
            Congratulations, you are duly registered for The Event Xperience Africa. We can't wait to see you in January.<br><br>
            
            The Attendee support team will be sending you detailed information in the coming weeks.
            Do not hesitate to get in touch if you have any questions, we'll always get back to you.<br><br>
            
            Thanks.
        </div>
        <div class='signature' style='padding-top: 40px; font-family: Allison_Script, cursive'>
            Funke Bucknor-Obruthe
        </div>
    </div>
  </body>
  </html>";
  $headers = "From: support@texafrica.com \r\n";
  $headers .= "Reply-To: support@texafrica.com \r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

  return mail($to, $email_subject, $email_body, $headers);
}

function notifyTexa($data) {
  $to = "info@texafrica.com";
  $email_subject = "TEXA registration ";
  $pal_details = "";
  if ($data->pal_first_name) {
    $pal_details = "
      <tr>
          <td colspan='2' style='text-align: center'><h3>Pal's Details</h3></td>
      </tr>
      <tr>
          <th>Name:</th>
          <td>{$data->pal_first_name} {$data->pal_last_name}</td>
      </tr>
      <tr>
          <th>Email:</th>
          <td>{$data->pal_email}</td>
      </tr>
      <tr>
          <th>Phone Number:</th>
          <td>{$data->pal_phone}</td>
      </tr>
      <tr>
          <th>Company:</th>
          <td>{$data->pal_company_name}</td>
      </tr>
      <tr>
          <th>Location:</th>
          <td>{$data->pal_location}</td>
      </tr>
      <tr>
          <th>Occupation:</th>
          <td>{$data->pal_occupation}</td>
      </tr>
      <tr>
          <th>How did you hear about us:</th>
          <td>{$data->pal_medium}</td>
      </tr>
    ";
  }
  $email_body = "
  <html>
  <head>
    <meta name='viewport' content='width=device-width, initial-scale=1.0' />
    <style>
        .signature{font-size: 20pt;}
        .container{width: 450px;
            font-size:14pt;
            line-height: 180%;}
        @media (max-width: 400px){
            .signature{font-size: 16pt;}
            .container{width: 80%;
                font-size:11pt;
                line-height: 150%;}
            
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
        }
            table th, table td {
            padding: 1em;
            border: 1px solid black;
        }
    
        @font-face {font-family: Allison_Script;src: url('http://texafrica.com/Allison_Script.otf') format('opentype');}
    </style>
   </head>
   <body>
    <div class='container' style='font-family:Roboto; margin: 0 auto;font-family:Georgia;padding: 25px;border: 8px solid lightgrey;'>
        <div>
            <img src='http://texafrica.com/images/TEXA-LOGO-300X300.png' style='height: 120px; width: auto; padding-left: 20px'>
        </div>
        <div style='padding-top: 40px'>
            <table>
                <tr>
                    <th>Name:</th>
                    <td>{$data->first_name} {$data->last_name}</td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>{$data->email}</td>
                </tr>
                <tr>
                    <th>Phone Number:</th>
                    <td>{$data->phone_number}</td>
                </tr>
                <tr>
                    <th>Company:</th>
                    <td>{$data->company_name}</td>
                </tr>
                <tr>
                    <th>Location:</th>
                    <td>{$data->location}</td>
                </tr>
                <tr>
                    <th>Occupation:</th>
                    <td>{$data->occupation}</td>
                </tr>
                <tr>
                    <th>How did you hear about us:</th>
                    <td>{$data->medium}</td>
                </tr>
                {$pal_details}
            </table>
        </div>
    </div>
  </body>
  </html>";
  $headers = "From: support@texafrica.com \r\n";
  $headers .= "Reply-To: support@texafrica.com \r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

  return mail($to, $email_subject, $email_body, $headers);

}

function postToEventsRail($data) {
  $l_firstname  = $data->first_name;
  $l_lastname = $data->last_name;
  $l_email = $data->email;
  $l_mobile = $data->phone_number;
  $l_org = $data->company_name;
  $l_amount = '100000';
  $l_att_type = $data->occupation;
  $l_pay_status = $data->verify_status === '1' ? 'PAID' : '';

  $url = 'https://www.eventsrail.com/er_livereg_api_process'; //REMOTE SERVER URI


  $l_ev_id = '242';  /////PLEASE DO NOT MODIFY
  $l_owner = '14942';  /////PLEASE DO NOT MODIFY

  $l_urn = 'D8281CD4';
//create a new cURL resource
  $ch = curl_init($url);

//setup request to send json via POST
  $regdata = array(
     'l_urn' => $l_urn,
    'l_firstname' => $l_firstname,
    'l_lastname' => $l_lastname,
    'l_email' => $l_email,
    'l_mobile' => $l_mobile,
    'l_org' => $l_org,
    'l_att_type' => $l_att_type,
    // 'l_category' => $l_category,
    // 'l_category_id' => $l_category_id,
    // 'l_description' => $l_description,
    'l_amount' => $l_amount,
    'l_pay_status' => $l_pay_status,
    // 'l_comment' => $l_comment,
    'l_owner' => $l_owner,
    'l_ev_id' => $l_ev_id
  );

  $payload = json_encode(array("reguser" => $regdata));

  ///NEW
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  //attach encoded JSON string to the POST fields
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_HEADER, true);

  curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
  # Return response instead of printing.
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);


  //execute the POST request
  $result = curl_exec($ch);
  file_put_contents('log.txt',
     date('Y-m-d H:i:s'). ' Events rail response'. $result. PHP_EOL,
     FILE_APPEND | LOCK_EX);
//  echo $result;
  //close cURL resource
  curl_close($ch);
}

