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
    <!--<link rel="stylesheet" type="text/css" href="style.css">-->
    <link href='https://fonts.googleapis.com/css?family=Average' rel='stylesheet'>
    <link rel="icon" href="bankIcon.jpg" type="image/gif" sizes="16x16">

    <style>
        body {
            font-family: 'Average', serif;
        }
    </style>
</head>
<body>
    <nav>
        <?php require_once(__DIR__ . "/partials/nav.php"); ?>
    </nav>

    <p>Welcome, <?php echo $email; ?></p>

    <hr>

    <address>
    Page made by Nate Gile <br>
    for Internet Applications Final Project. <br>
    Created September 2020<br>
    </address>

</body>
</html>