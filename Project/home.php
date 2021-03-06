<?php require_once(__DIR__ . "/partials/nav.php"); ?>
    <div class="bodyMain">
        <h1>Simple Bank Dashboard</h1>
        <h4>Welcome, <?php echo get_email(); ?></h4>
        <p>How would you like to conduct business with us today:</p>
        <ul class="dashLinks">
            <li><a href="<?php echo getURL("createAccount.php")?>">Create Account</a></li>
            <li><a href="<?php echo getURL("createLoan.php")?>">Create Loan Account</a></li>
            <li><a href="<?php echo getURL("listAccounts.php")?>">My Accounts</a></li>
            <li><a href="<?php echo getURL("createTransaction.php?action=0")?>">Deposit</a></li>
            <li><a href="<?php echo getURL("createTransaction.php?action=1")?>">Withdrawal</a></li>
            <li><a href="<?php echo getURL("createTransfer.php")?>">Personal Transfer</a></li>
            <li><a href="<?php echo getURL("createExternalTransfer.php")?>">Transfer to another person</a></li>
            <li><a href="<?php echo getURL("profile.php")?>">Profile</a></li>
        </ul>
        <hr>

        <address>
        Page made by Nate Gile
        for Internet Applications Final Project.
        Created October 2020
        </address>
    </div>
    <?php require(__DIR__ . "/partials/flash.php");?>
</body>
</html>