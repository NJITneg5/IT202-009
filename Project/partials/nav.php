<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<!DOCTYPE HTML>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="Author" content="Nate Gile">
    <meta name="date" content="9/24/2020">
    <meta name="keywords" content="">
    <title>Gile Family Bank</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

    <!-- jQuery and JS bundle w/ Popper.js -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
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
<div class = "navMain">
    <h1 id="title">A Simple Man's Bank</h1>
    <nav>
        <ul id="genNav">
            <li style = "padding: 0"><a href="<?php echo getURL("home.php"); ?>">Home</a></li>
            <?php if (!is_logged_in()): ?>
            <li style = "padding-left: 10%"><a href="<?php echo getURL("login.php"); ?>">Login</a></li>
            <li style = "padding: 0"><a href="<?php echo getURL("registration.php");?>">Register</a></li>
            <?php endif; ?>
            <?php if (is_logged_in()): ?>
                <li style = "padding-left: 10%"><a href="<?php echo getURL("createAccount.php");?>">Create Accounts</a></li>
                <li><a href="<?php echo getURL("listAccounts.php");?>">My Accounts</a></li>
                <li><a href="<?php echo getURL("createTransaction.php");?>">Create Transaction</a></li>
            <?php endif; ?>
            <?php if (has_role("Admin")): //This is for later later test pages that I may need. ?>
                <li><a href="<?php echo getURL("admin.php");?>">Admin</a></li>
            <?php endif; ?>
        </ul>

        <ul id="loggedNav">
            <?php if (is_logged_in()): ?>
                <li><a href="<?php echo getURL("profile.php");?>">Profile</a></li>
                <li><a href="<?php echo getURL("logout.php");?>">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<div id ="underline"></div>