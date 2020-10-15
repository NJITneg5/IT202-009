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
    <?php
        session_start();
        // remove all session variables
        session_unset();
        // destroy the session
        session_destroy();
    ?>
    <?php require_once(__DIR__ . "/partials/nav.php");?>
    <?php
        flash("You have been logged out");
        die(header("Location: login.php"));
    ?>
</body>
</html>