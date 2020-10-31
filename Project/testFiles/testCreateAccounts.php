<?php require_once(__DIR__ . "../partials/nav.php");

if(!has_role("Admin")) {
    flash("You do not have permission to access this page.");
    die(header("Location: ../login.php"));
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
    <h3>Please Create An Account With Our Bank</h3>

    <form method="POST">
        <label>Account Number <br>
            <input name="accountNum" type="number" min="100000000000" max="999999999999" placeholder="000000000000"> <br><br>
        </label>
        <label>Account Type <br>
            <select name="accountType">
                <option value="checking">Checking</option>
                <option value="savings">Savings</option>
                <option value="loan">Loan</option>
            </select> <br><br>
        </label>
        <label>Initial Balance<br>
            <input name="initBalance" type="number" placeholder="00.00"><br><br>
        </label>
        <input type="submit" name="submit" value="Create">
        <input type="reset" value="Reset">

    </form>

    <hr>

    <address>
        Page made by Nate Gile
        for Internet Applications Final Project.
        Created October 2020
    </address>
</div>

    <!--PHP Shenanigans -->

<?php
if(isset($_POST["submit"])){
    //TODO Proper Validations
    $accountNum = $_POST["accountNum"];
    $accountType = $_POST["accountType"];
    $initBalance = $_POST["initBalance"];
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO TPACCOUNTS (account_number, account_type, balance, user_id) VALUES(:accountNum, :accountType, :initBalance, :userID)");
    $r = $stmt ->execute([
        ":accountNum"=>$accountNum,
        ":accountType"=>$accountType,
        ":initBalance"=>$initBalance,
        ":userID"=>$user
    ]);
    if($r){
        flash("Account created successfully with Account Number: " . $accountNum);
    }
    else{
        $e = $stmt->errorInfo();
        flash("There was an error!" . var_export($e, true));
    }
}
?>
<?php require(__DIR__ . "../partials/flash.php");?>
</body>
</html>
