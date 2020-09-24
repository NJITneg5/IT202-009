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
	<link rel="icon" href="bankIcon.jpg" type="image/gif" sizes="16x16">

<style>
body {
    font-family: 'Average';
}
</style>
</head>
<body>
	<h1>Please Log in with your Email and password.</h1>
	<form method = "POST">
		<label for= "email">Email</label><br>
		<input type= "email" id= "email" name= "email" required/><br>
		<label for= "pw">Password</label><br>
		<input type= "password" id= "pw" name= "pw" required/><br>
		<input type= "submit" name = "login" value= "Log in"/><br>
	</form>
	
	
	<hr>
	
	<address>
	Page made by Nate Gile <br>
	for Internet Applications Final Project. <br>
	Created September 2020<br>
	</address>
	
	
	<!--PHP Shenanigans -->
	
<?php
	if(isset($_POST["login"])){
		$email = null;
		$password = null;
		if(isset($_POST["email"])){
			$email = $_POST["email"];
		}
		if(isset($_POST["password"])){
			$password = $_POST["password"];
		}
		$isValid = true;
		if(!isset($email) || !isset($password)){
			$isValid = false; 
		}
		//TODO other validation as desired, remember this is the last line of defense
		//here you'd probably want some email validation, for sake of example let's do a super basic one
		if(!strpos($email, "@")){
			$isValid = false;
			echo "<br>Invalid email<br>";
		}
		if($isValid){
			//for password matching, we can't use this, every time it's ran it'll be a different value
			//so will never log us in!
			//$hash = password_hash($password, PASSWORD_BCRYPT);
			//instead we'll want to run password_verify
			//TODO pretend we got our use from the DB
			//make sure if you're pasting a sample hash here that you use single quotes
			//if you use double quotes it'll try to parse values with $ as a php variable
			//and the sample won't work
			$password_hash_from_db = '';//placeholder, you can copy/paste a hash generated from sample_reg.php if you want to test it
			//otherwise it'll always be false
    
			//note it's raw password, saved hash as the parameters
			if(password_verify($password, $password_hash_from_db)){
				echo "<br>Welcome! You're logged in!<br>"; 
			}
			else{
				echo "<br>Invalid password, get out!<br>"; 
			}
		}
		else{
			echo "There was a validation issue"; 
		}
	}
?>
</body>
</html>