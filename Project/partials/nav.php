<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<div class = "navMain">
    <h3 id="title">A Simple Man's Bank</h3>
    <nav>
        <ul id="genNav">
            <li><a href="home.php">Home</a></li>
            <?php if (!is_logged_in()): ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="registration.php">Register</a></li>
           <?php endif; ?>
        </ul>

        <ul id="loggedNav">
            <?php if (is_logged_in()): ?>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<div class="underline"></div>