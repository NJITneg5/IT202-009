<!DOCTYPE HTML>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="Author" content="Nate Gile">
    <meta name="date" content="9/24/2020">
    <meta name="keywords" content="">
    <title>Gile Family Bank</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Average' rel='stylesheet'>
    <link rel="icon" href="../bankIcon.jpg" type="image/gif" sizes="16x16">

    <style>
        body {
            font-family: 'Average', serif;
        }
    </style>
</head>
<body>
<?php
require_once (__DIR__ . "/../partials/nav.php");

if(!has_role("Admin")){
    flash("You do not have permission to access this page.");
    dir(header("Location: ../login.php"));
}

if(isset($_GET["id"])){
    $transId = $_GET["id"];
}

$transResult = [];
$srcResult = [];
$destResult = [];
if(isset($transId)){
    $db = getDB();
    $stmt = $db->prepare("SELECT id, act_src_id, act_dest_id, action_type, amount, memo, created FROM TPTransactions WHERE  id = :id");
    $r = $stmt->execute([":id" => $transId]);
    $transResult = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$transResult) {
        $e = $stmt->errorInfo();
        flash("Error fetching transaction info: " . var_export($e, true));
    }

    $stmt = $db->prepare("SELECT TPA.id, account_number, TPUsers.username FROM TPAccounts as TPA JOIN TPUsers on TPA.user_id = TPUsers.id WHERE TPA.id = :number");
    $r = $stmt->execute([":number" => $transResult["act_src_id"]]);
    $srcResult = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$srcResult) {
        $e = $stmt->errorInfo();
        flash("Error fetching source info: " . var_export($e, true));
    }

    $stmt = $db->prepare("SELECT TPA.id, account_number, TPUsers.username FROM TPAccounts as TPA JOIN TPUsers on TPA.user_id = TPUsers.id WHERE TPA.id = :number");
    $r = $stmt->execute([":number" => $transResult["act_dest_id"]]);
    $destResult = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$destResult) {
        $e = $stmt->errorInfo();
        flash("Error fetching destination info: " . var_export($e, true));
    }
}
?>
<div class="bodyMain">
    <h1><strong>TEST PAGE</strong></h1>
    <h3>This page is used to view details of an transaction</h3>

    <?php if(isset($destResult) && !empty($destResult)): ?>
        <div class="card">
            <div class="cardTitle">
                <p>Details for Transaction:</p>
            </div>
            <div class="cardBody">
                <div>
                    <div>Source Account: <?php safer_echo($transResult["act_src_id"]); ?></div>
                    <div>Belonging to: <?php safer_echo($srcResult["TPUsers.username"]); ?></div> <br>
                    <div>Destination Account: <?php safer_echo($transResult["act_dest_id"]); ?></div>
                    <div>Belonging to: <?php safer_echo($destResult["TPUsers.username"]); ?></div> <br>
                    <div>Action Type: <?php safer_echo($transResult["action_type"]); ?></div>
                    <div>Amount Moved: <?php safer_echo($transResult["amount"]); ?></div>
                    <div>Memo: <?php safer_echo($transResult["memo"]); ?></div>
                    <div>Date/Time Occurred: <?php safer_echo($transResult["created"]); ?></div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p>Error looking up id.</p>
    <?php endif; ?>

    <hr>

    <address>
        Page made by Nate Gile
        for Internet Applications Final Project.
        Created November 2020
    </address>
</div>

<?php require(__DIR__ . "/../partials/flash.php");?>
</body>
</html>

