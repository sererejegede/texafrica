<?php
include "constants.php";

// function getUser($reference = "T887106180416808", $HOST, $USERNAME, $PASSWORD, $DATABASE) {
//   $conn2 = mysqli_connect($HOST, $USERNAME, $PASSWORD, $DATABASE);
//   if (!$conn2) {
//     file_put_contents('log.txt',
//       date('Y-m-d H:i:s'). ' '. mysqli_connect_errno(). PHP_EOL,
//       FILE_APPEND | LOCK_EX);
//     die("Connection failed: ");
//   }
//   $get_user_id_query = "SELECT * FROM users JOIN transactions ON transactions.user_id = users.id WHERE transactions.transaction_id = '$reference'";
//   if ($resource = mysqli_query($conn2, $get_user_id_query)) {
//     $result = mysqli_fetch_array($resource);
//     $data = json_encode($result);
//     try {
//       postToEventsRail($data);
//     } catch (Exception $exception) {
//       file_put_contents('log.txt',
//          date('Y-m-d H:i:s'). ' Events rail error: '. $exception. PHP_EOL,
//          FILE_APPEND | LOCK_EX);
//     }
//     return json_decode(json_encode($result));
//   }
// }

$conn2 = mysqli_connect($HOST, $USERNAME, $PASSWORD, $DATABASE);
  if (!$conn2) {
    file_put_contents('log.txt',
       date('Y-m-d H:i:s'). ' '. mysqli_connect_errno(). PHP_EOL,
       FILE_APPEND | LOCK_EX);
    die("Connection failed: ");
  }
  $data = array();
  $get_user_query = "SELECT * FROM users JOIN transactions ON transactions.user_id = users.id WHERE transactions.transaction_id = 'T225418873596489'";
  if ($resource = mysqli_query($conn2, $get_user_query)) {
      
    while ($result2 = mysqli_fetch_assoc($resource)) {
        $data[] = $result2;
    }
    $answer = json_decode(json_encode($data))[0];
    var_dump($answer->first_name);
    echo '<br>';
    echo '<br>';
    echo $answer->first_name;
    
    
//     $l_firstname  = $data->first_name;
//     $l_lastname = $data->last_name;
//     $l_email = $data->email;
//     $l_mobile = $data->phone_no;
//     $l_org = $data->company;
//     $l_amount = '100000';
//     $l_pay_status = $data->verify_status === '1' ? 'PAID' : '';
    
//     //API URL
// $url = 'https://www.eventsrail.com/er_livereg_api_process'; //REMOTE SERVER URI


// $l_ev_id = '242';  /////PLEASE DO NOT MODIFY

// //create a new cURL resource
// $ch = curl_init($url);

// //setup request to send json via POST
// $regdata = array(
//     'l_urn' => $l_urn,
//     'l_firstname' => $l_firstname,
//     'l_lastname' => $l_lastname,
//     'l_email' => $l_email,
//     'l_mobile' => $l_mobile,
//     'l_org' => $l_org,
//     'l_att_type' => $l_att_type,
//     'l_category' => $l_category,
//     'l_description' => $l_description,
//     'l_amount' => $l_amount,
//     'l_pay_status' => $l_pay_status,
//     'l_comment' => $l_comment,
//     'l_ev_id' => $l_ev_id
// );

//     $payload = json_encode(array("reguser" => $regdata));
    
//     ///NEW
//     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//     //attach encoded JSON string to the POST fields
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
//     curl_setopt($ch, CURLOPT_HEADER, true);
    
//     curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
//     # Return response instead of printing.
//     curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
//     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    
    
//     //execute the POST request
//     $result = curl_exec($ch);
//     echo $result;
//     //close cURL resource
//     curl_close($ch);
  }