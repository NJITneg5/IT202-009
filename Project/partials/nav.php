<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
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
                <li ><a href="<?php echo getURL("listAccounts.php");?>">Query Accounts</a></li>
            <?php endif; ?>
            <?php if (has_role("Admin")): ?>
            <!--<li style = "padding-left: 10%"><a href="<?php //echo getURL("testFiles/testCreateAccounts.php");?>">Create Accounts</a></li>-->
            <!--<li ><a href="<?php //echo getURL("testFiles/testListAccounts.php");?>">Query Accounts</a></li>-->
            <li ><a href="<?php echo getURL("testFiles/testCreateTransactions.php");?>">Create Transactions</a></li>
            <li style = "padding: 0"><a href="<?php echo getURL("testFiles/testListTransactions.php");?>">Query Transactions</a></li>
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