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
	//starts/loads a session, basically tells php to do its magic
	session_start();
	// remove all session variables
	session_unset();
	// destroy the session
	session_destroy();
	echo "You're logged out (proof by dumping the session)<br>";
	echo "<pre>" . var_export($_SESSION, true) . "</pre>";
?>
	<a href="home.php">Link back to the Home page</a>
	
	<hr>
	
	<address>
	Page made by Nate Gile <br>
	for Internet Applications Final Project. <br>
	Created October 2020<br>
	</address>

</body>
</html>