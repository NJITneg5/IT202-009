<?php require_once(__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
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
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Average' rel='stylesheet'>
    <link rel="icon" href="bankIcon.jpg" type="image/gif" sizes="16x16">

    <style>
        body {
            font-family: 'Average', serif;
        }
    </style>
</head>
<body>
<div class="bodyMain">
    <h1>Please Create An Account With Our Bank</h1>

    <form method="POST">
        <label>Account Type:<br>
            <!--TODO Add other account types such as savings when time comes-->
            <select name="accountType">
                <option value="checking">Checking</option>
            </select> <br><br>
        </label>
        <label>Initial Deposit: (Minimum of $5.00 is needed.)<br>
            <input name="initBalance" type="text" placeholder="00.00"><br><br>
        </label>
        <input type="submit" name="submit" value="Create">
        <input type="reset" value="Reset">

    </form>

    <hr>

    <address>
        Page made by Nate Gile
        for Internet Applications Final Project.
        Created November 2020
    </address>
</div>

<!--PHP Shenanigans -->

<?php
if(isset($_POST["submit"])){
    $db = getDB();
    $isValid = false;       //Check for inserts
    $uniqueNum = false;     //Check to make sure account number is available
    $uniqueCount = 0;       //Makes sure that the unique account check only runs a certain amount of times

    $accountType = $_POST["accountType"];
    $initBalance = $_POST["initBalance"];
    $user = get_user_id();

    if((float)$initBalance >= 5.0){ //Checks to make sure that the initial balance variable can be stripped to float and is greater than or equal to 5.0
        $isValid = true;
    }else{
        flash("You did not enter a valid initial deposit. Please Try again.");
    }

    while(!$uniqueNum && $uniqueCount < 10 && $isValid) {    //Loop to generate a unique account number
        $newActNum = rand(100000000000, 999999999999);
        str_pad($newActNum,12,"0",STR_PAD_LEFT);
        $stmt = $db->prepare("SELECT account_number from TPAccounts WHERE account_number = :num");
        $r = $stmt->execute([
            ":num"=>$newActNum
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(empty($result)){
            $uniqueNum = true;
            break;
        }
        $uniqueCount++;
    }
    if($uniqueCount == 10 || !$uniqueNum){
        $e = $stmt->errorInfo();
        $isValid = false;
        flash("There was an error creating unique account number. Please try again." . var_export($e, true));
    }

    if($isValid) {  //Creates the account
        $stmt = $db->prepare("INSERT INTO TPAccounts (account_number, account_type, balance, user_id) VALUES(:accountNum, :accountType, :initBalance, :userID)");
        $r = $stmt->execute([
            ":accountNum" => $newActNum,
            ":accountType" => $accountType,
            ":initBalance" => 0,
            ":userID" => $user
        ]);
        if ($r) {
            flash("Account created successfully with Account Number: " . $newActNum);
        } else {
            $e = $stmt->errorInfo();
            $isValid = false;
            flash("There was an error creating the account. Please try again." . var_export($e, true));
        }
    }

    if($isValid){

        //Get id of new Account
        $stmt = $db->prepare("SELECT id FROM TPAccounts WHERE account_number = :newActNum");
        $r = $stmt->execute([":newActNum" => $newActNum]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $actID = $result["id"];

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Getting id for new account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Get expected total for world account
        $stmt = $db->prepare("SELECT balance FROM TPAccounts WHERE id = :id");
        $r = $stmt->execute([":id" => 3]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $worldTotal = $result["balance"];

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Getting balance for world account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Create Transaction, to pull from world account
        $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:world, :newAct, :amount, :action, :memo, :total)");
        $r = $stmt->execute([
            ":world" => 3,
            ":newAct" => $actID,
            ":amount" => (float)$initBalance * -1,
            ":action" => "deposit",
            ":memo" => "Initial Deposit",
            ":total" => ($worldTotal - $initBalance)
        ]);

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error writing transaction from World account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Create Transaction, to put into new account
        $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:newAct, :world, :amount, :action, :memo, :total)");
        $r = $stmt->execute([
            ":newAct" => $actID,
            ":world" => 3,
            ":amount" => (float)$initBalance,
            ":action" => "deposit",
            ":memo" => "Initial Deposit",
            ":total" => (float)$initBalance
        ]);

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error writing transaction into new account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Sums the new account's expected balance
        $stmt = $db->prepare("SELECT SUM(amount) AS total FROM TPTransactions WHERE act_src_id = :actID");
        $r = $stmt->execute([":actID" => $actID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $balance = $result["total"];

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error SUMming total for new account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Takes SUMmed balance and updates the account's actual balance
        $stmt = $db->prepare("UPDATE TPAccounts SET balance = :balance WHERE id = :id");
        $r = $stmt->execute([
            ":balance" => $balance,
            ":id" => $actID
        ]);
        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error updating balance. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Sums the world account's expected balance
        $stmt = $db->prepare("SELECT SUM(amount) AS total FROM TPTransactions WHERE act_src_id = :world");
        $r = $stmt->execute([":world" => 3]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $balance = $result["total"];

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error SUMming total for world account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Takes SUMmed balance and updates the account's actual balance
        $stmt = $db->prepare("UPDATE TPAccounts SET balance = :balance WHERE id = :id");
        $r = $stmt->execute([
            ":balance" => $balance,
            ":id" => 3
        ]);
        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error updating world balance. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Final Success Message that redirects user to the new account's page.
        flash("Initial Deposit has been successfully processed.");
        //header("Location: ViewAccount.php?id=" . $actID); TODO Implement View Account Page
    }

}
?>
<?php require(__DIR__ . "/partials/flash.php");?>
</body>
</html>
