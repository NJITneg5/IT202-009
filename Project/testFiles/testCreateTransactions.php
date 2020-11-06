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
    require_once(__DIR__ . "/../partials/nav.php");

    if(!has_role("Admin")) {
        flash("You do not have permission to access this page");
        die(header("Location: login.php"));
    }

    //Getting account IDs for dropdowns
    $db = getDB();
    $stmt = $db->prepare("SELECT id,account_number FROM TPAccounts LIMIT 10");
    $r = $stmt->execute();
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="bodyMain">
    <h1><strong>TEST PAGE</strong></h1>
    <h3>Please Create a Transaction With Our Bank</h3>

    <form method="POST">
        <label>Memo<br>
            <input name="memo" type="text" placeholder="i.e Paycheck Deposit"> <br><br>
        </label>
        <label>Transaction Type <br>
            <select name="actionType">
                <option value="deposit">Deposit</option>
                <option value="withdrawal">Withdrawal</option>
                <option value="transfer">Transfer</option>
            </select> <br><br>
        </label>
        <label> Source Account<br>
            <select name="srcID">
                <?php foreach ($accounts as $account): ?>
                    <option value="<?php safer_echo($account["id"]); ?>"
                    ><?php safer_echo($account["account_number"]); ?></option>
                <?php endforeach;?>
            </select> <br><br>
        </label>
        <label> Destination Account<br>
            <select name="destID">
                <?php foreach ($accounts as $account): ?>
                    <option value="<?php safer_echo($account["id"]); ?>"
                    ><?php safer_echo($account["account_number"]); ?></option>
                <?php endforeach;?>
            </select> <br><br>
        </label>
        <label>Amount<br>
            <!--TODO Change type from number to text and add proper validation for currency-->
            <input name="amount" type="number" min="0" placeholder="00.00"><br><br>
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
    if(isset($_POST["submit"])) {   //Setting values from Form
        $memo = $_POST["memo"];
        $actionType = $_POST["actionType"];
        $srcID = $_POST["srcID"];
        $destID = $_POST["destID"];
        $amount = $_POST["amount"];

        //Initializing Variables for source and destination accounts
        $srcBalance = 0;
        $srcExpect = 0;
        $srcAmt = 0;

        $destBalance = 0;
        $destExpect = 0;
        $destAmt = 0;

        //Bool to make sure that if something breaks, it doesn't cause extra queries and insert errors
        $failsafe = true;

        $db = getDB();


        //Query to fetch balance to set expected_balance value for source account
        if ($failsafe) {
            $stmt = $db->prepare("SELECT balance FROM TPAccounts WHERE id = :srcAcct");
            $r = $stmt->execute([":srcAcct" => $srcID]);
            $srcBalance = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error accessing the Source Account: " . var_export($e, true));
                $failsafe = false;
            }
        }

        //Query to fetch balance to set expected_balance value for destination account
        if ($failsafe) {
            $stmt = $db->prepare("SELECT balance FROM TPAccounts WHERE id = :destAcct");
            $r = $stmt->execute([":destAcct" => $destID]);
            $destBalance = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error accessing the Destination Account: " . var_export($e, true));
                $failsafe = false;
            }
        }

        //Casting the strings to ints
        $srcBalance = (int)$srcBalance;
        $destBalance = (int)$destBalance;
        $amount = (int)$amount;

        //Set of if statements to set expected_balance according to actionType
        if ($actionType == "deposit") {
            $srcExpect = $srcBalance - $amount;
            $srcAmt = $amount * -1;

            $destExpect = $destBalance + $amount;
            $destAmt = $amount;
        } elseif ($actionType == "withdrawal") {
            $srcExpect = $srcBalance + $amount;
            $srcAmt = $amount;

            $destExpect = $destBalance - $amount;
            $destAmt = $amount * -1;
        } elseif ($actionType == "transfer") {
            $srcExpect = $srcBalance - $amount;
            $srcAmt = $amount * -1;

            $destExpect = $destBalance + $amount;
            $destAmt = $amount;
        }

        //Query to insert source side of transaction
        if ($failsafe) {
            $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:acctSrc, :acctDest, :amount, :actionType, :memo, :expected)");
            $r = $stmt->execute([
                ":acctSrc" => $srcID,
                ":acctDest" => $destID,
                ":amount" => $srcAmt,
                ":actionType" => $actionType,
                ":memo" => $memo,
                ":expected" => $srcExpect
            ]);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error writing transaction for Source account: " . var_export($e, true));
                $failsafe = false;
            }
        }

        //Query to insert source side of transaction. The source and dest are flipped so that the transactions properly mirror each other.
        if ($failsafe) {
            $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:acctSrc, :destDrc, :amount, :actionType, :memo, :expected)");
            $r = $stmt->execute([
                ":acctSrc" => $destID,
                ":acctDest" => $srcID,
                ":amount" => $destAmt,
                ":actionType" => $actionType,
                ":memo" => $memo,
                ":expected" => $destExpect
            ]);
            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error writing transaction for Destination Account: " . var_export($e, true));
                $failsafe = false;
            }
        }
        if($r) {
            flash("Successfully processed transaction!");
        }
    }
    require (__DIR__ . "/../partials/flash.php");
?>
</body>
</html>