<!--PHP Shenanigans -->

<?php require_once(__DIR__ . "/../partials/nav.php");

if(!has_role("Admin")) {
    flash("You do not have permission to access this page.");
    die(header("Location: ../login.php"));
}
if(isset($_GET["id"])){
    $id = $_GET["id"];
}

if(isset($_POST["edit"])){
    //TODO Proper Validations
    $accountNum = $_POST["accountNum"];
    $accountType = $_POST["accountType"];
    $balance = $_POST["balance"];
    $user = get_user_id();
    $db = getDB();
    if(isset($id)) {
        $stmt = $db->prepare("UPDATE TPAccounts set account_number=:accountNum, account_type=:accountType, balance=:balance WHERE id=:id");
        $r = $stmt->execute([
            ":accountNum" => $accountNum,
            ":accountType" => $accountType,
            ":balance" => $balance,
            ":userID" => $user
        ]);
        if ($r) {
            flash("Account created successfully with Account Number: " . $accountNum);
        } else {
            $e = $stmt->errorInfo();
            flash("There was an error!" . var_export($e, true));
        }
    }
    else{
        flash("ID isn't set. You need to put an ID in the address.");
    }
}

$result = [];
if(isset($id)){
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM TPAccounts WHERE id = :id");
    $r = $stmt->execute([":id"=>$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <h3>This page is used to edit an Account</h3>

    <form method="POST">
        <label>Account Number <br>
            <input name="accountNum" type="number" min="100000000000" max="999999999999" placeholder="000000000000" value="<?php echo $result["name"];?>"> <br><br>
        </label>
        <label>Account Type <br>
            <select name="accountType" value="<?php echo $result["account_type"]?>">
                <option value="checking" <?php echo ($result["account_type"] == "checking"?'selected="selected"':'');?>>Checking</option>
                <option value="savings" <?php echo ($result["account_type"] == "savings"?'selected="selected"':'');?>>Savings</option>
                <option value="loan" <?php echo ($result["account_type"] == "loan"?'selected="selected"':'');?>>Loan</option>
            </select> <br><br>
        </label>
        <label>Initial Balance<br>
            <input name="balance" type="number" placeholder="00.00" value="<?php echo $result["balance"];?>"><br><br>
        </label>
        <input type="submit" name="edit" value="Create">
        <input type="reset" value="Reset">

    </form>

    <hr>

    <address>
        Page made by Nate Gile
        for Internet Applications Final Project.
        Created October 2020
    </address>
</div>

<?php require(__DIR__ . "/../partials/flash.php");?>
</body>
</html>
