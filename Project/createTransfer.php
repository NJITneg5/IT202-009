<?php require_once(__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$userID = get_user_id();
$db=getDB();

$stmt = $db->prepare("SELECT id, account_number FROM TPAccounts WHERE user_id = :userID");
$r = $stmt->execute([":userID" => $userID]);
$acctResults = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<div class="bodyMain">
    <h1>Transfer Between My Accounts</h1>

    <form method="POST">
        <label>From Account Selection:<br>
            <select name="scrSelect">
                <?php foreach($acctResults as $r ): ?>
                    <option value="<?php echo $r["id"]?>"><?php echo $r["account_number"]?></option>
                <?php endforeach;?>
            </select>
        </label> <br><br>

        <label>To Account Selection<br>
            <select name="destSelect">
                <?php foreach($acctResults as $r ): ?>
                    <option value="<?php echo $r["id"]?>"><?php echo $r["account_number"]?></option>
                <?php endforeach;?>
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

    $isValid = false;

    $srcID = $_POST["srcSelect"];
    $destID = $_POST["destSelect"];
    $actionType = "transfer";
    $amount = (float)$_POST["amount"];
    $memo = $_POST["memo"];
    $srcBalance = 0.0;

    if($amount >= 0.0){ //Checks if amount is valid
        $isValid = true;
    }else{
        flash("You did not enter a valid amount. Please try again.");
    }

    if($isValid){   //Gets the user's selected account balance
        $stmt = $db->prepare("SELECT balance FROM TPAccounts WHERE id = :id");
        $r = $stmt->execute([":id" => $srcID]);
        $results = $stmt->fetch(PDO::FETCH_ASSOC);
        $srcBalance = (float)$results["balance"];
    }

    if($isValid && $amount > $srcBalance){    //Checks if the account has enough to transfer the asked amount
        flash("You do not have enough money in your source account to complete this action. You have $" . $srcBalance . ". Please try again.");
        $isValid = false;
    }

    if($isValid) {
        $srcAmount = $amount * -1;
        $destAmount = $amount;
    }

    if($isValid){
        //Create Transaction, for source account
        $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:src, :dest, :amount, :action, :memo, :total)");
        $r = $stmt->execute([
            ":src" => $srcID,
            ":dest" => $destID,
            ":amount" => $srcAmount,
            ":action" => $actionType,
            ":memo" => $memo,
            ":total" => ($srcBalance + $srcAmount)
        ]);

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error writing transaction for source account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){   //Gets the user's selected account balance
        $stmt = $db->prepare("SELECT balance FROM TPAccounts WHERE id = :id");
        $r = $stmt->execute([":id" => $destID]);
        $results = $stmt->fetch(PDO::FETCH_ASSOC);
        $destBalance = (float)$results["balance"];
    }

    if($isValid){
        //Create Transaction, for dest account
        $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:dest, :src, :amount, :action, :memo, :total)");
        $r = $stmt->execute([
            ":dest" => $destID,
            ":src" => $srcID,
            ":amount" => $amount,
            ":action" => $actionType,
            ":memo" => $memo,
            ":total" => ($destBalance + $amount)
        ]);

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error writing transaction into dest account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Sums the source account expected balance
        $stmt = $db->prepare("SELECT SUM(amount) AS total FROM TPTransactions WHERE act_src_id = :actID");
        $r = $stmt->execute([":actID" => $srcID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $newBalance = $result["total"];

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error SUMming total for source account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Takes SUMmed balance and updates the account's actual balance
        $stmt = $db->prepare("UPDATE TPAccounts SET balance = :balance WHERE id = :id");
        $r = $stmt->execute([
            ":balance" => $newBalance,
            ":id" => $srcID
        ]);

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error updating balance. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Sums the world account's expected balance
        $stmt = $db->prepare("SELECT SUM(amount) AS total FROM TPTransactions WHERE act_src_id = :destID");
        $r = $stmt->execute([":destID" => $destID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $balance = $result["total"];

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error SUMming total for destination account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            $isValid = false;
        }
    }

    if($isValid){
        //Takes SUMmed balance and updates the account's actual balance
        $stmt = $db->prepare("UPDATE TPAccounts SET balance = :balance WHERE id = :id");
        $r = $stmt->execute([
            ":balance" => $balance,
            ":id" => $destID
        ]);

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error updating destination balance. Please contact your bank representative and relay the following error code. " . var_export($e, true));
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
