<?php require_once(__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$userID = get_user_id();
$db=getDB();

$stmt = $db->prepare("SELECT id, account_number FROM TPAccounts WHERE user_id = :userID AND active = 'true'");
$r = $stmt->execute([":userID" => $userID]);
$acctResults = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<div class="bodyMain">
    <h1>Transfer Between Outside Accounts</h1>

    <form method="POST">
        <label>From Account Selection:<br>
            <select name="srcSelect">
                <?php foreach($acctResults as $r ): ?>
                    <option value="<?php echo $r["id"]?>"><?php echo $r["account_number"]?></option>
                <?php endforeach;?>
            </select>
        </label> <br><br>

        <label>To Account Owner's Last Name<br>
            <input type="text" name="last" placeholder="Smith">
        </label> <br><br>

        <label>Last 4 digits of Account<br>
            <input type="text" name="fourDig" placeholder="i.e 1234" maxlength="4">
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
    $lastName = $_POST["last"];
    $searchNum = $_POST["fourDig"];
    $actionType = "ext-transfer";
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
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $srcBalance = (float)$result["balance"];
    }

    if($isValid && $amount > $srcBalance){    //Checks if the account has enough to transfer the asked amount
        flash("You do not have enough money in your source account to complete this action. You have $" . $srcBalance . ". Please try again.");
        $isValid = false;
    }

    if($isValid){
        $stmt = $db->prepare("SELECT TPAccounts.id AS newID, account_number, balance FROM `TPUsers` JOIN `TPAccounts` ON TPUsers.id=TPAccounts.user_id WHERE lastName=:last AND account_number LIKE :digits LIMIT 1");
        $r = $stmt->execute([":last" => $lastName, ":digits" => "%$searchNum"]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $destID = $result["newID"];
        $acctNum = $result["account_number"];
        $destBalance = $result["balance"];

        if (!$r) {
            flash("Error, we could not find an account matching the credentials given. Please Try again.");
            $isValid = false;
        }
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
        flash("Transaction has been successfully processed. Funds transferred to " . $acctNum . ".");
        die(header("Location: listAccounts.php"));
    }
}

require(__DIR__ . "/partials/flash.php");
?>
</body>
</html>
