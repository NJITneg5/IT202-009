<!DOCTYPE HTML>

<html lang="en">

<head>
    <meta charset="utf-8">
	<meta name="Author" content="Nate Gile">
	<meta name="date" content="10/4/18">
	<meta name="keywords" content="Nathaniel's, Computer, Repair, fixing, building, home">
    <title>Gile Family Bank</title>
    <!--<link rel="stylesheet" type="text/css" href="style.css">-->
	<link href='https://fonts.googleapis.com/css?family=Average' rel='stylesheet'>
	<link rel="icon" href="bankIcon.jpg" type="image/gif" sizes="16x16">

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
		<input type= "email" id= "email" name= "email" required/>
		<label for= "pw">Password</label><br>
		<input type= "password" id= "pw" name= "pw" required/>
		<label for= "confirmPw">Confirm Password</label><br>
		<input type= "password" id= "confirmPw" name= "ConfirmPw" required/>
		<input type= "submit" id= "register" value= "Register"/>
	</form>
	
	
	<hr>
	
	<address>
	Page made by Nate Gile 
	for Internet Applications Final Project. 
	Created September 2020
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
				//for password security we'll generate a hash that'll be saved to the DB instead of the raw password
				//for this sample we'll show it instead
				$hash = password_hash($password, PASSWORD_BCRYPT);
				echo "<br>Our hash: $hash<br>";
				echo "User registered (not really since we don't have a database setup yet)"; 
			}
			else{
				echo "There was a validation issue"; 
			}
		}
	?>
</body>
</html>