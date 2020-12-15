<?php require_once(__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$initAction = 0;

if(isset($_GET["action"])){
    $initAction = $_GET["action"];
}

$userID = get_user_id();
$db=getDB();

$stmt = $db->prepare("SELECT id, account_number FROM TPAccounts WHERE user_id = :userID AND active = 'true' AND frozen = 'false'");
$r = $stmt->execute([":userID" => $userID]);
$acctResults = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<div class="bodyMain">
    <h1>Transaction Creation</h1>

    <form method="POST">
        <label>Account Selection:<br>
            <select name="acctSelect">
                <?php foreach($acctResults as $r ): ?>
                    <option value="<?php echo $r["id"]?>"><?php echo $r["account_number"]?></option>
                <?php endforeach;?>
            </select>
        </label> <br><br>

        <label>Action:<br>
            <select name="actionType">
                <option value="deposit" <?php echo ($initAction == "0"?'selected="selected"':'');?>>Deposit</option>
                <option value="withdraw" <?php echo ($initAction == "1"?'selected="selected"':'');?>>Withdraw</option>
            </select>
        </label> <br><br>

        <label>Amount:<br>
            <input type="text" name="amount" placeholder="00.00">
        </label> <br><br>

        <label>Memo:<br>
            <input type="text" name="memo" placeholder="e.g Paycheck or Car Payment">
        </label> <br><br>

        <input type="submit" value="Submit" name="submit">
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
    $db= getDB();
    $worldID = getWorldID();

    $isValid = false;

    $actID = $_POST["acctSelect"];
    $actionType = $_POST["actionType"];
    $amount = (float)$_POST["amount"];
    $memo = $_POST["memo"];
    $balance = 0.0;

    if($amount >= 0.0){ //Checks if amount is valid
        $isValid = true;
    }else{
        flash("You did not enter a valid amount. Please try again.");
    }

    if($isValid){   //Gets the user's selected account balance
        $stmt = $db->prepare("SELECT balance FROM TPAccounts WHERE id = :id");
        $r = $stmt->execute([":id" => $actID]);
        $results = $stmt->fetch(PDO::FETCH_ASSOC);
        $balance = (float)$results["balance"];
    }

    if($isValid && $actionType == "withdraw" && $amount > $balance){    //Checks if the account has enough to withdraw the asked amount
        flash("You do not have enough money in your account to complete this action. You have $" . $balance . ". Please try again.");
        $isValid = false;
    }

    if($isValid) {  //Sets the world amount and amount to correct values for insertion into Transaction table
        switch ($actionType) {
            case "deposit":
                $worldAmount = $amount * -1;
                break;
            case "withdraw":
                $worldAmount = $amount;
                $amount *= -1;
                break;
        }
    }

    if($isValid){
        //Get expected total for world account
        $stmt = $db->prepare("SELECT balance FROM TPAccounts WHERE id = :id");
        $r = $stmt->execute([":id" => $worldID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $worldTotal = $result["balance"];

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error getting balance for world account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Create Transaction, for world account
        $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:world, :newAct, :amount, :action, :memo, :total)");
        $r = $stmt->execute([
            ":world" => $worldID,
            ":newAct" => $actID,
            ":amount" => $worldAmount,
            ":action" => $actionType,
            ":memo" => $memo,
            ":total" => ($worldTotal + $worldAmount)
        ]);

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error writing transaction for World account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Create Transaction, for user account
        $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:newAct, :world, :amount, :action, :memo, :total)");
        $r = $stmt->execute([
            ":newAct" => $actID,
            ":world" => $worldID,
            ":amount" => $amount,
            ":action" => $actionType,
            ":memo" => $memo,
            ":total" => ($balance + $amount)
        ]);

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error writing transaction into new account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Sums the user's account expected balance
        $stmt = $db->prepare("SELECT SUM(amount) AS total FROM TPTransactions WHERE act_src_id = :actID");
        $r = $stmt->execute([":actID" => $actID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $newBalance = $result["total"];

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
            ":balance" => $newBalance,
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
        $r = $stmt->execute([":world" => $worldID]);
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
            ":id" => $worldID
        ]);

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error updating world balance. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Final Success Message that redirects user to the user's account page.
        flash("Transaction has been successfully processed.");
        die(header("Location: listAccounts.php"));
    }
}

require(__DIR__ . "/partials/flash.php");
?>
</body>
</html>
