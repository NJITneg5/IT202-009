<?php
require_once (__DIR__ . "/../partials/nav.php");

if(!has_role("Admin")){
    flash("You do not have permission to access this page.");
    dir(header("Location: ../login.php"));
}

if(isset($_GET["id"])){
    $id = $_GET["id"];
}

$result = [];
if(isset($id)){
    $db = getDB();
    $stmt = $db->prepare("SELECT TPA.id, account_number, account_type, opened_date, last_updated, balance, TPUsers.username FROM TPAccounts as TPA JOIN TPUsers on TPA.user_id = TPUsers.id WHERE TPA.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
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
<div class="bodyMain">
    <h1><strong>TEST PAGE</strong></h1>
    <h3>This page is used to view details of an account</h3>

<?php if(isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="cardTitle">
            <?php safer_echo($result["account_number"]); ?>
        </div>
        <div class="cardBody">
            <div>
                <p>Account Details</p>
                <div>Owner Username: <?php safer_echo($result["TPUsers.username"]); ?></div>
                <div>Account Type: <?php safer_echo($result["account_type"]); ?></div>
                <div>Account Balance: <?php safer_echo($result["balance"]); ?></div>
                <div>Account Opened: <?php safer_echo($result["opened_date"]); ?></div>
                <div>Last Activity Date: <?php safer_echo($result["last_updated"]); ?></div>
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
