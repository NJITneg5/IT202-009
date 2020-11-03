<?php
require_once (__DIR__ . "../partials/nav.php");
if (!has_role("Admin")){
    flash("You do not have permission to access this page.");
    die(header("Location: ../login.php"));
}

$query = "";
$results = [];

if(isset($_POST["query"])){
    $query = $_POST["query"];
}

if(isset($_POST["search"]) && !empty($query)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, account_number, user_id, account_type, opened_date, last_updated, balance FROM TPAccounts WHERE account_number LIKE :q LIMIT 10");
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        flash("There was a problem fetching the results.");
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
    <h3>This page is used to query accounts</h3>

    <form method="POST">
        <label> Partial Account Number <br>
            <input name="query" placeholder="Search" value="<?php safer_echo($query);?>"/>
        </label>
        <input type="submit" value="Search" name="search"/>
        <input type="reset"/>
    </form>

    <div class="results">
        <?php if(count($results) > 0): ?>
            <div class="list-group">
                <?php foreach ($results as $r): ?>
                    <div class="list-group-item">
                        <div>
                            <div>Account Number:</div>
                            <div><?php safer_echo($r["account_number"]); ?></div>
                        </div>
                        <div>
                            <div>Account Type:</div>
                            <div><?php safer_echo($r["account_type"]); ?></div>
                        </div>
                        <div>
                            <div>Account Opened:</div>
                            <div><?php safer_echo($r["opened_date"]); ?></div>
                        </div>
                        <div>
                            <a type="button" href="testEditAccounts.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                            <a type="button" href="testViewAccounts.php?id=<?php safer_echo($r['id']); ?>">View</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No Results</p>
        <?php endif; ?>
    </div>

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
