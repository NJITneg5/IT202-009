<?php

require_once(__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

if(isset($_GET["id"])){
    $acctId = $_GET["id"];
}
if(isset($_GET["actNum"])){
    $acctNum = $_GET["actNum"];
}
if(isset($_GET["balance"])){
    $balance = $_GET["balance"];
}

$db = getDB();

if(isset($acctId) && isset($acctNum) && isset($balance)) {
    $stmt = $db->prepare("SELECT amount, action_type, memo, created FROM TPTransactions WHERE act_dest_id = :acctID ORDER BY created LIMIT 10");
    $r = $stmt->execute(["acctID" => $acctId]);
    if ($r) {
        $transResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $e = $stmt->errorInfo();
        flash("There was an error fetching your transactions. Please contact a bank representative and relay the following error code. " . var_export($e, true));
    }
} else {
    flash("Account not Found Error. Please contact your bank representative.");
    die(header("Location: listAccounts.php"));
}

?>

<div class="bodyMain">

    <h1>Recent Transactions on this Account</h1>

    <h4>Account Number: <?php safer_echo($acctNum);?></h4>
    <h4>Balance: <?php safer_echo($balance);?></h4>

    <?php if(count($transResults) > 0): ?>
        <table class="listTable">
            <thead>
            <tr class="listHead">
                <th>Transaction Type</th>
                <th>Amount</th>
                <th>Memo</th>
                <th>Occurred on:</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($transResults as $r):?>
                <tr class="listRow">
                    <td><?php safer_echo($r["action_type"]);?></td>
                    <td>$<?php safer_echo($r["amount"]);?></td>
                    <td><?php safer_echo($r["memo"]);?></td>
                    <td><?php safer_echo($r["created"]);?></td>
                    </tr>
            <?php endforeach;?>
            </tbody>
        </table>
    <?php else: ?>
        <p>There are no transactions for this account. (Which is bad, because there should at least be a "Initial Deposit")</p>
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