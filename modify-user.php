<?php session_start(); ?>
<?php require_once('inc/connection.php'); ?>
<?php require_once('inc/functions.php'); ?>
<?php
	
    // checking if a user is logged in 
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
    }

    $errors = array();
    $first_name = ''; 
    $last_name = ''; 
    $email = ''; 
    $password = '';
    
    if (isset($_GET['user_id'])) {
        // getting the user information
        $user_id = mysqli_real_escape_string($connection, $_GET['user_id']);
        $query = "SELECT * FROM user WHERE id ={$user_id} LIMIT 1";

        $result_set = mysqli_query($connection, $query);

        if ($result_set) {
            if (mysqli_num_rows($result_set) ==1) {
                // user found
                $result = mysqli_fetch_assoc($result_set);
                $first_name = $result['first_name']; 
                $last_name = $result['last_name']; 
                $email = $result['email']; 
               
            } else {
                // user not found
                header('Location: users.php?err=user_not_found');
            }
        } else {
            //  query unsuccessful
            header('Location: users.php?err=query_failed');
        }
    }

    if (isset($_POST['submit'])) {
        $first_name = $_POST['first_name']; 
        $last_name = $_POST['last_name']; 
        $email = $_POST['email']; 
        $password = $_POST['password'];


        // checking required fields
        $req_fields = array('first_name' , 'last_name' , 'email' , 'password');

        // call function for check required fields ("array_merge" key word used to marge 2 arrays in a 1 array)
        $errors = array_merge($errors, check_req_fields($req_fields));

        // foreach ($req_fields as $field){
            // if (empty(trim($_POST[$field]))) {
               // $errors[] = $field . ' is required';
            //}    
        //}

        // checking max length
        $max_len_fields = array('first_name' => 50, 'last_name' => 100, 'email' => 100, 'password' => 40);

        // foreach ($max_len_fields as $field => $max_len) {
            // if (strlen(trim($_POST[$field])) > $max_len) {
               // $errors[] = $field . ' must be less than ' . $max_len . ' characters ';
           //}    
        //}
        $errors = array_merge($errors, check_max_len($max_len_fields));
        
        // checking email address
         
        if(!is_email($_POST['email'])){
            $errors [] = 'Email address is invalid.';

        }

        // checking if email address already exists
        $email = mysqli_real_escape_string($connection, $_POST['email']);
        $query = "SELECT * FROM user WHERE email = '{$email}' LIMIT 1";

        $result_set = mysqli_query($connection, $query);

        if ($result_set) {
            if (mysqli_num_rows($result_set) ==1) {
                $errors[] = 'Email address already exists';

            }
        }

        if (empty($errors)) {
            // no errors found... adding new record
            $first_name = mysqli_real_escape_string($connection, $_POST['first_name']);
            $last_name = mysqli_real_escape_string($connection, $_POST['last_name']);
            $password = mysqli_real_escape_string($connection, $_POST['password']);
            // email address is already sanitized
           // get the current time from a php function which return current date and time.  

            $hashed_password = sha1($password); 

            $query = "INSERT INTO user ( ";
            $query .= " first_name, last_name, email, password, last_login ,is_deleted";
            $query .= ") VALUES (";
            $query .= "'{$first_name}', '{$last_name}', '{$email}', '{$hashed_password}', NOW(), 0";
            $query .= ")";

           // echo $query;
           // die();

            $result = mysqli_query($connection, $query);

            if ($result) {
                // query successful... redirecting to users page
                header('Location: users.php?user_added=true');
            } else {
                $errors[] = 'Failed to add the new record.';
            }
        }


    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Viwe / Modify User</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <header>
        <div class="appname">User Management System</div>
        <div class="loggedin">Welcome <?php echo $_SESSION['first_name']; ?>! <a href="logout.php">Log Out</a></div>
    </header>
    <main>
        <h1>Viwe / Modify User<span><a href="users.php"> < Back to User List</a></span></h1>

        <?php 
        
            if (!empty($errors)) {
                display_errors($errors);
                // echo '<div class="errmsg">';
                // echo '<b>There were error(s) on your form.</b><br>'; 
                // foreach ($errors as $error) {
                   // echo '- ' . $error . '<br>';
                // }
                // echo '</div>';
            }
        
         ?>

        <form action="modify-user.php" method="POST" class="userform">

            <p>
                <label for=""> First Name: </label>
                <input type="text" name="first_name" <?php echo 'value="' . $first_name . '"'; ?>>
            </p>

            <p>
                <label for=""> Last Name: </label>
                <input type="text" name="last_name" <?php echo 'value="' . $last_name . '"'; ?>>
            </p>

            <p>
                <label for=""> Email Address: </label>
                <input type="text" name="email" <?php echo 'value="' . $email . '"'; ?>>
            </p>

            <p>
                <label for=""> New Password: </label>
                <input type="password" name="password">
            </p>

            <p>
                <label for="">&nbsp;</label>
                <button type="submit" name="submit">Save</button>
            </p>

        </form>
        
    </main>
   
</body> 
</html>