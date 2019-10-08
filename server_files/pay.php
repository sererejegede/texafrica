<?php
/**
 * Created by PhpStorm.
 * User: Serere
 * Date: 6/23/2019
 * Time: 12:11 PM
 */
include 'constants.php';

// Create connection
$conn = mysqli_connect($HOST, $USERNAME, $PASSWORD, $DATABASE);

// Check connection
if (!$conn) {
  file_put_contents('log.txt', date('Y-m-d H:i:s'). mysqli_connect_errno(). PHP_EOL, FILE_APPEND | LOCK_EX);
  die("Connection failed: ");
}

file_put_contents('log.txt', date('Y-m-d H:i:s'). json_encode($_POST). PHP_EOL, FILE_APPEND | LOCK_EX);

$paystack_response = json_encode($_POST);
$errors = '';
if (isset($_POST['success']) and !empty($_POST['success'])) {
  $status = $_POST['success'];
}
// Paystack
if (isset($_POST['reference']) and !empty($_POST['reference'])) {
  $reference = $_POST['reference'];
}
// Rave
if (isset($_POST['tx']) and !empty($_POST['tx'])) {
  $reference = $_POST['tx']['txRef'];
}
if (isset($_POST['email']) and !empty($_POST['email'])) {
  $email = $_POST['email'];
  $get_user_query = "SELECT users.id FROM users WHERE email = '$email'";
  if ($resource = mysqli_query($conn, $get_user_query)) {
    $result = mysqli_fetch_array($resource);
    if(isset($result) and !empty($result)) {
      $user_id = json_decode(json_encode($result))->id;

      if (isset($reference) and !empty($reference)) {
        $insert_trans_query =
           "INSERT INTO transactions (transaction_id, user_id, paystack_response, status, verify_status, created_at, updated_at)
                              VALUES ('$reference', '$user_id', '$paystack_response', '$status', 0, NOW(), NOW())";
        if (mysqli_query($conn, $insert_trans_query)) {
          echo 'Transaction Saved';
        } else {
          file_put_contents('log.txt',
             date('Y-m-d H:i:s'). ' SQL Error ' . $insert_trans_query . mysqli_error($conn). PHP_EOL,
             FILE_APPEND | LOCK_EX);
          echo 'An error occurred';
          die();
        }
      }

    }
  } else {
    file_put_contents('log.txt',
       date('Y-m-d H:i:s'). mysqli_error($conn). PHP_EOL,
       FILE_APPEND | LOCK_EX);
  }
}


