<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<div class = "navMain">
    <h1 id="title">A Simple Man's Bank</h1>
    <nav>
        <ul id="genNav">
            <li style = "padding: 0"><a href="home.php">Home</a></li>
            <?php if (!is_logged_in()): ?>
            <li style = "padding-left: 10%"><a href="login.php">Login</a></li>
            <li style = "padding: 0"><a href="registration.php">Register</a></li>
            <?php endif; ?>
            <!--
            <?php if (has_role("Admin")): ?>
            <li style = "padding-left: 10%"><a href="testFiles/testCreateAccounts.php">Create Accounts</a></li>
            <li style = "padding: 0"><a href="testFiles/testListAccounts.php">Query Accounts</a></li>
            <?php endif; ?>
            -->
        </ul>

        <ul id="loggedNav">
            <?php if (is_logged_in()): ?>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<div id ="underline"></div>