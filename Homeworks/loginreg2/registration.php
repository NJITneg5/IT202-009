<!DOCTYPE HTML>

<html lang="en">

<head>
    <meta charset="utf-8">
	<meta name="Author" content="Nate Gile">
	<meta name="date" content="9/24/2020">
	<meta name="keywords" content="">
    <title>Gile Family Bank</title>
    <!--<link rel="stylesheet" type="text/css" href="style.css">-->
	<link href='https://fonts.googleapis.com/css?family=Average' rel='stylesheet'>
	<link rel="icon" href="../../Project/bankIcon.jpg" type="image/gif" sizes="16x16">

    <style>
    body {
        font-family: 'Average';
    }
    </style>
</head>
<body>
	<h1>Please enter your the email and password you would like to use to Register.</h1>
	<form method = "POST">
		<label for= "email">Email</label><br>
		<input type= "email" id= "email" name= "email" required/><br>
		<label for= "pw">Password</label><br>
		<input type= "password" id= "pw" name= "pw" required/><br>
		<label for= "confirmPw">Confirm Password</label><br>
		<input type= "password" id= "confirmPw" name= "confirmPw" required/><br>
		<input type= "submit" name= "register" value= "Register"/>
	</form>
	
	
	<hr>
	
	<address>
	Page made by Nate Gile <br>
	for Internet Applications Final Project. <br>
	Created September 2020<br>
	</address>
	
	
	<!--PHP Shenanigans -->
	
<?php
	if(isset($_POST["register"])){
		$email = null;
		$password = null;
		$confirm = null;
		if(isset($_POST["email"])){
			$email = $_POST["email"];
		}
		if(isset($_POST["pw"])){
			$password = $_POST["pw"];
		}
		if(isset($_POST["confirmPw"])){
			$confirm = $_POST["confirmPw"];
		}
		$isValid = true;
		//check if passwords match on the server side
		if($password == $confirm){
			echo "Passwords match <br>"; 
		}
		else{
			echo "Passwords don't match<br>";
			$isValid = false;
		}
		if(!isset($email) || !isset($password) || !isset($confirm)){
			$isValid = false; 
		}
		//TODO other validation as desired, remember this is the last line of defense
		if($isValid){
			$hash = password_hash($password, PASSWORD_BCRYPT);
			require_once("../db.php");
			$db = getDB();
			if(isset($db)){
				//here we'll use placeholders to let PDO map and sanitize our data
				$stmt = $db->prepare("INSERT INTO TPUsers(email, password) VALUES(:email, :password)");
				//here's the data map for the parameter to data
				$params = array(":email"=>$email, ":password"=>$hash);
				$r = $stmt->execute($params);
				//let's just see what's returned
				echo "db returned: " . var_export($r, true);
				$e = $stmt->errorInfo();
				if($e[0] == "00000"){
					echo "<br>Welcome! You successfully registered, please login.";
				}
				else{
					echo "uh oh something went wrong: " . var_export($e, true);
				}
			}
		}
		else{
			echo "There was a validation issue"; 
		}
	}
?>
</body>
</html>