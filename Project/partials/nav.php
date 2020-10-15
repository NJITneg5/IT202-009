<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<div class = "navMain">
    <h1 id="title">A Simple Man's Bank</h1>
    <nav>
        <ul id="genNav">
            <li><a href="home.php" style = "padding: 0px">Home</a></li>
            <?php if (!is_logged_in()): ?>
            <li><a href="login.php"style = "padding-left: 10%">Login</a></li>
            <li><a href="registration.php" style = "padding: 0px">Register</a></li>
           <?php endif; ?>
        </ul>

        <ul id="loggedNav">
            <?php if (is_logged_in()): ?>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php" style = "padding: 0px">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<div class="underline"></div>