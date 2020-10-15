<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
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
            font-family: 'Average', serif;
        }
    </style>
</head>
<body>
    <?php require_once(__DIR__ . "/partials/nav.php"); ?>
    <div class="bodyMain">
        <h1>Simple Bank Homepage</h1>
        <p>Welcome, <?php echo get_email(); ?></p>

        <hr>

        <address>
        Page made by Nate Gile
        for Internet Applications Final Project.
        Created October 2020
        </address>
    </div>
</body>
</html>