<?php
/**
 * Created by PhpStorm.
 * User: Serere
 * Date: 6/22/2019
 * Time: 11:11 AM
 */

include 'constants.php';

// initialize variables
$first_name = "";
$last_name = "";
$email = "";
$phone_number = "";
$company_name = "";
$location = "";
$occupation = "";
$medium = "";
$pal_registration = false;

// Create connection
$conn = mysqli_connect($HOST, $USERNAME, $PASSWORD, $DATABASE);

// Check connection
if (!$conn) {
  file_put_contents('log.txt',
     'SQL CONN ERROR ' . date('Y-m-d H:i:s') . mysqli_connect_errno() . PHP_EOL,
     FILE_APPEND | LOCK_EX);
  die("Connection failed: ");
}


// Check if user is returning
$returning = $_POST['returning'];

$errors = '';

if ($returning === 'true') {
  if (isset($_POST['email']) and !empty($_POST['email'])) {
    $email = $_POST['email'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors .= 'Invalid email <br>';
    }
  }
} else {
  if (isset($_POST['first_name']) and !empty($_POST['first_name'])) {
    $first_name = $_POST['first_name'];
//   echo $first_name;
  } else {
    $errors .= 'First Name field is required <br>';
  }
  if (isset($_POST['last_name']) and !empty($_POST['last_name'])) {
    $last_name = $_POST['last_name'];
  } else {
    $errors .= 'Last Name field is required <br>';
  }
  if (isset($_POST['phone_number']) and !empty($_POST['phone_number'])) {
    $phone_number = $_POST['phone_number'];
  } else {
    $errors .= 'Phone Number field is required <br>';
  }
  if (isset($_POST['email']) and !empty($_POST['email'])) {
    $email = $_POST['email'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors .= 'Invalid email <br>';
    }
  }

  if (isset($_POST['company_name']) and !empty($_POST['email'])) {
    $company_name = $_POST['company_name'];
  }
  if (isset($_POST['location']) and !empty($_POST['location'])) {
    $location = $_POST['location'];
  }
  if (isset($_POST['medium']) and !empty($_POST['medium'])) {
    $medium = $_POST['medium'];
  }
  if (isset($_POST['occupation']) and !empty($_POST['occupation'])) {
    $occupation = $_POST['occupation'];
  }
}

if (isset($_POST['pal_first_name']) and !empty($_POST['pal_first_name']) and
   isset($_POST['pal_email']) and !empty($_POST['pal_email']) and
   isset($_POST['pal_phone']) and !empty($_POST['pal_phone']))
{
  $pal_registration = true;
  $pal_first_name = $_POST['pal_first_name'];
  $pal_last_name = $_POST['pal_last_name'];
  $pal_email = $_POST['pal_email'];
  $pal_phone = $_POST['pal_phone'];
  $pal_company_name = $_POST['pal_company_name'];
  $pal_location = $_POST['pal_location'];
  $pal_medium = $_POST['pal_medium'];
  $pal_occupation = $_POST['pal_occupation'];

  $pal_first_name = mysqli_real_escape_string($conn, $pal_first_name);
  $pal_last_name = mysqli_real_escape_string($conn, $pal_last_name);
  $pal_email = mysqli_real_escape_string($conn, $pal_email);
  $pal_phone = mysqli_real_escape_string($conn, $pal_phone);
  $pal_company_name = mysqli_real_escape_string($conn, $pal_company_name);
  $pal_location = mysqli_real_escape_string($conn, $pal_location);
  $pal_medium = mysqli_real_escape_string($conn, $pal_medium);
  $pal_occupation = mysqli_real_escape_string($conn, $pal_occupation);
}

if (strlen($errors) > 0) {
  echo json_encode([
     'message' => $errors,
     'status' => 404
  ]);
  return;
}

// echo $first_name;


if ($returning === 'true') {
  $email = mysqli_real_escape_string($conn, $email);
  $check_query = "SELECT email FROM users WHERE email = '$email'";
  if ($resource = mysqli_query($conn, $check_query)) {
    if ($resource->num_rows > 0) {
      echo json_encode([
         'message' => 'User found!',
         'status' => 200,
         'email' => $email
      ]);
    } else {
      echo json_encode([
         'message' => 'User not found!',
         'status' => 404
      ]);
    }
  } else {
    file_put_contents('log.txt',
       date('Y-m-d H:i:s') . ' SQL Error ' . $check_query . mysqli_error($conn) . PHP_EOL,
       FILE_APPEND | LOCK_EX);
    echo json_encode([
       'message' => 'An error occurred',
       'status' => 500
    ]);
    die();
  }
} else {
  $first_name = mysqli_real_escape_string($conn, $first_name);
  $last_name = mysqli_real_escape_string($conn, $last_name);
  $email = mysqli_real_escape_string($conn, $email);
  $phone_number = mysqli_real_escape_string($conn, $phone_number);
  $company_name = mysqli_real_escape_string($conn, $company_name);
  $location = mysqli_real_escape_string($conn, $location);
  $occupation = mysqli_real_escape_string($conn, $occupation);
  $medium = mysqli_real_escape_string($conn, $medium);
  if (isset($first_name) && !empty($first_name) && isset($last_name) && !empty($last_name)
     && isset($email) && !empty($email) && isset($phone_number) && !empty($phone_number)
     && isset($company_name) && !empty($company_name) && isset($location) && !empty($location)
     && isset($occupation) && !empty($occupation) && isset($medium) && !empty($medium)
  ) {
    $query = "INSERT INTO users (first_name, last_name, email, phone_number, company_name, location, occupation, created_at, updated_at, medium)
    VALUES ('$first_name', '$last_name', '$email', '$phone_number', '$company_name', '$location', '$occupation', NOW(), NOW(), '$medium')";

    if (!mysqli_query($conn, $query)) {
      $query_err = mysqli_error($conn);
      file_put_contents('log.txt', date('Y-m-d H:i:s') . $query_err . PHP_EOL, FILE_APPEND | LOCK_EX);
      if (strpos($query_err, 'Duplicate entry') !== false) {
        $errors = ["status" => 422, "message" => ""];
        if (strpos($query_err, 'email') !== false) {
          $errors["message"] = "Email already taken\r\n";
        }
        if (strpos($query_err, 'phone_number') !== false) {
          $errors += ["phone_number" => "Phone number already taken"];
          $errors["message"] = "Phone number already taken";
        }
        echo json_encode($errors);
      }
      // echo 'An error occurred';
      die();
    }

    if ($pal_registration) {
      $pal_query = "INSERT INTO pals (pal_first_name, pal_last_name, pal_email, pal_phone, pal_company_name, pal_location, pal_occupation, pal_medium, user_id) 
                    VALUES ('$pal_first_name', '$pal_last_name', '$pal_email', '$pal_phone', '$pal_company_name', '$pal_location', '$pal_occupation', '$pal_medium',
                    (SELECT users.id FROM users WHERE users.email = '$email'))";

      if (!mysqli_query($conn, $pal_query)) {
        $pal_query_err = mysqli_error($conn);
        file_put_contents('log.txt', date('Y-m-d H:i:s') . $pal_query_err . PHP_EOL, FILE_APPEND | LOCK_EX);
        if (strpos($pal_query_err, 'Duplicate entry') !== false) {
          $errors = ["status" => 422, "message" => ""];
          if (strpos($pal_query_err, 'user_id') !== false) {
            $errors["message"] = "". $first_name. " ". $last_name. " already has a pal\r\n";
          }
          if (strpos($pal_query_err, 'pal_email') !== false) {
            $errors["message"] = "Email already taken\r\n";
          }
          echo json_encode($errors);
        }
        // echo 'An error occurred';
        die();
      }
    }

    echo json_encode([
       'message' => 'Created successfully',
       'email' => $email,
       'status' => 200
    ]);
  } else {
    echo json_encode([
       "status" => 400,
       "message" => "Unknown error"
    ]);
  }
}

