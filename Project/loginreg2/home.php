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
<?php
	session_start();
?>
	<h1>This page is a Home page for my IT202 project.</h1>
	<ul>
		<li><a href= "../loginreg/registration.php">Registration page</a></li>
		<li><a href= "../loginreg/login.php">Login page</a></li>
		<li><a href= "../dbTest.php">Database Test page</a></li><br>
		<li><a href= "/registration.php">Registration page for loginreg part 3</a></li>
		<li><a href= "/login.php">Login page for loginreg part 3</a></li>
		<li><a href= "/logout.php">Logout page for loginreg part 3</a></li>

	</ul>
<?php
	//we use this to safely get the email to display
	$email = "";
	if(isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])){
		$email = $_SESSION["user"]["email"]; 
	}
?>
	<p>Welcome, <?php echo $email;?></p><br>
	
	<hr>
	
	<address>
	Page made by Nate Gile <br>
	for Internet Applications Final Project. <br>
	Created September 2020<br>
	</address>

</body>
</html>