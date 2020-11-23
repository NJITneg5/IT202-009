<?php

require_once(__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
        flash("You must be logged in to access this page");
        die(header("Location: login.php"));
}

$userID = get_user_id();
$db = getDB();
$results = 0;

$stmt = $db->prepare("SELECT id, account_number, account_type, IFNULL(balance,'0.00') AS balance FROM TPAccounts WHERE user_id = :id LIMIT 5");
$r = $stmt->execute([":id" => $userID]);
if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $e = $stmt->errorInfo();
    flash("There was an error fetching your accounts. Please contact a bank representative and relay the following error code. " . var_export($e, true));
}
?>

<div class="bodyMain">

    <h1>List of Your Accounts</h1>
    <?php if(count($results) > 0): ?>
        <table class="listTable">
            <thead>
                <tr class="listHead">
                    <th>Account Number</th>
                    <th>Account Type</th>
                    <th>Account Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r):?>
                    <tr class="listRow">
                        <td><?php safer_echo($r["account_number"]);?></td>
                        <td><?php safer_echo($r["account_type"]);?></td>
                        <td>$<?php safer_echo($r["balance"]);?></td>
                        <td><a href="<?php echo getURL("listTransactions.php") . "?id=" . safer_echo($r["id"]) . "&actNum=" . safer_echo($r["account_number"]) . "&balance=" . safer_echo($r["balance"]);?>">View Transactions</a></td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You do not have any accounts with our bank. Click here to create one: </p>
        <a href="<?php echo getURL("createAccount.php")?>">Create Account</a>
    <?php endif; ?>
    <hr>

    <address>
        Page made by Nate Gile
        for Internet Applications Final Project.
        Created November 2020
    </address>
</div>
<?php require(__DIR__ . "/partials/flash.php");?>
</body>
</html>