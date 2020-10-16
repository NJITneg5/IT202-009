<!--PHP Shenanigans-->
<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if(isset($_POST["register"])){
    $email = null;
    $password = null;
    $confirm = null;
    $username = null;

    if(isset($_POST["email"])){
        $email = $_POST["email"];
    }

    if(isset($_POST["pw"])){
        $password = $_POST["pw"];
    }

    if(isset($_POST["confirmPw"])){
        $confirm = $_POST["confirmPw"];
    }

    if (isset($_POST["username"])) {
        $username = $_POST["username"];
    }

    $isValid = true;
    //check if passwords match on the server side
    if($password == $confirm){
        //flash("Passwords match");
    }
    else{
        flash("Passwords don't match");
        $isValid = false;
    }

    if(!isset($email) || !isset($password) || !isset($confirm)){
        $isValid = false;
    }

    //TODO other validation as desired, remember this is the last line of defense
    if($isValid){
        $hash = password_hash($password, PASSWORD_BCRYPT);


        $db = getDB();
        if(isset($db)){
            //here we'll use placeholders to let PDO map and sanitize our data
            $stmt = $db->prepare("INSERT INTO TPUsers(email, username, password) VALUES(:email, :username, :password)");
            //here's the data map for the parameter to data
            $params = array(":email"=>$email, ":username" => $username, ":password"=>$hash);
            $r = $stmt->execute($params);
            //let's just see what's returned

            $e = $stmt->errorInfo();
            if($e[0] == "00000"){
                flash("Welcome! You successfully registered, please login.");
            }
            else{
                if ($e[0] == "23000"){ //Duplicate entry code.
                    flash("Username and/or email is already registered, please choose a different one.");
                }
                else {
                    flash("An error has occurred, please try again");
                }
            }
        }
    }
    else{
        flash("There was a validation issue");
    }
}

if (!isset($email)) {
    $email = "";
}

if(!isset($username)) {
    $username = "";
}
?>

<!DOCTYPE HTML>

<html lang="en">

<head>
    <meta charset="utf-8">
	<meta name="Author" content="Nate Gile">
	<meta name="date" content="9/24/2020">
	<meta name="keywords" content="">
    <title>Gile Family Bank</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Average' rel='stylesheet'>
	<link rel="icon" href="bankIcon.jpg" type="image/gif" sizes="16x16">

    <style>
    body {
        font-family: 'Average';
    }
    </style>
</head>
<body>
    <div class="bodyMain">
	<h1>Please enter your the email and password you would like to use to Register.</h1>
	<form method = "POST" id = "regForm">
		<label for= "email">Email</label><br>
		<input type= "email" id= "email" name= "email" required/><br>

        <label for= "user">Username:</label><br>
        <input type="text" id="user" name="username" required maxlength="60" value="<?php safer_echo($username);?>"/><br>

		<label for= "pw">Password</label><br>
		<input type= "password" id= "pw" name= "pw" required/><br>

		<label for= "confirmPw">Confirm Password</label><br>
		<input type= "password" id= "confirmPw" name= "confirmPw" required/><br>

		<input type= "submit" name= "register" value= "Register"/>
	</form>
	
	
	<hr>
	
	<address>
	Page made by Nate Gile
	for Internet Applications Final Project.
	Created October 2020
	</address>
    </div>
    <?php require(__DIR__ . "/partials/flash.php");?>
</body>
</html>