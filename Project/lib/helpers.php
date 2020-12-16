<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in(){
    return isset($_SESSION["user"]);
}
function has_role($role){
    if(is_logged_in() && isset($_SESSION["user"]["roles"])){
        foreach($_SESSION["user"]["roles"] as $r){
            if($r["name"] == $role){
                return true;
            }
        }
    }
    return false;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

//end flash

//Nav Bar function for better pathing
function getURL($path){
    if(substr($path, 0, 1) == "/"){
        return $path;
    }
    return $_SERVER["CONTEXT_PREFIX"] . "/IT202repo/Project/$path";
}

//Function to get World Account ID
function getWorldID(){
    global $worldID;
    if(!isset($worldID) || empty($worldID)){
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM TPAccounts WHERE account_number = '000000000000'");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if($r){
            $worldID = (int)$r["id"];
        }
    }
    return $worldID;
}

function getWorldBalance(){
    global $worldBalance;
    if(!isset($worldBalance) || empty($worldBalance)){
        $db = getDB();
        $stmt = $db->prepare("SELECT balance FROM TPAccounts WHERE account_number = '000000000000'");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if($r){
            $worldBalance = (int)$r["balance"];
        }
    }
    return $worldBalance;
}

function calcSavingsAPY(){
    $db = getDB();
    $numMonths = 1;
    $stmt = $db->prepare("SELECT id, apy, balance FROM TPAccounts WHERE account_type = 'savings' AND IFNULL(nextApy, DATE_ADD(nextApy, INTERVAL 1 MONTH)) <= current_date");
    $r = $stmt-> execute([":months" => $numMonths]);
    if($r){
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $worldID = getWorldID();
        $worldBalance = getWorldBalance();
        foreach($accounts as $account) {
            $apy = $account["apy"];
            $apy /= 1200; //Gets the monthly and turns the .05% is 0.0005. 12 * 100 = 1200
            $balance = (float)$account["balance"];
            $change = $balance * $apy;

            $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:world, :acct, :amount, :action, :memo, :total)");
            $r = $stmt->execute([
                ":world" => $worldID,
                ":acct" => $account["id"],
                ":amount" => $change * -1,
                ":action" => "deposit",
                ":memo" => "Interest",
                ":total" => ($worldBalance - $change)
            ]);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error writing transaction from World account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }

            $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:acct, :world, :amount, :action, :memo, :total)");
            $r = $stmt->execute([
                ":newAct" => $account["id"],
                ":world" => $worldID,
                ":amount" => $change,
                ":action" => "deposit",
                ":memo" => "Interest",
                ":total" => ($account["balance"] + $change)
            ]);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error writing transaction into new account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }


            //Sums the account's expected balance
            $stmt = $db->prepare("SELECT SUM(amount) AS total FROM TPTransactions WHERE act_src_id = :actID");
            $r = $stmt->execute([":actID" => $account["id"]]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $balance = $result["total"];

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error SUMming total for new account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }


            //Takes SUMmed balance and updates the account's actual balance and next date check for interest
            $stmt = $db->prepare("UPDATE TPAccounts SET balance = :balance, nextApy = DATE_ADD(nextApy, INTERVAL 1 MONTH) WHERE id = :id");
            $r = $stmt->execute([
                ":balance" => $balance,
                ":id" => $account["id"]
            ]);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error updating balance. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }


            //Sums the world account's expected balance
            $stmt = $db->prepare("SELECT SUM(amount) AS total FROM TPTransactions WHERE act_src_id = :world");
            $r = $stmt->execute([":world" => $worldID]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $balance = $result["total"];

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error SUMming total for world account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }

            //Takes SUMmed balance and updates the account's actual balance
            $stmt = $db->prepare("UPDATE TPAccounts SET balance = :balance WHERE id = :id");
            $r = $stmt->execute([
                ":balance" => $balance,
                ":id" => $worldID
            ]);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error updating world balance. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }
        }
    }
}

function calcLoanAPY(){
    $db = getDB();
    $numMonths = 1;
    $stmt = $db->prepare("SELECT id, apy, balance FROM TPAccounts WHERE account_type = 'loan' AND IFNULL(nextApy, DATE_ADD(nextApy, INTERVAL 1 MONTH)) <= current_date");
    $r = $stmt-> execute([":months" => $numMonths]);
    if($r){
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $worldID = getWorldID();
        $worldBalance = getWorldBalance();
        foreach($accounts as $account) {
            $apy = $account["apy"];
            $apy /= 1200; //Gets the monthly and turns the 9% is 0.09. 12 * 100 = 1200
            $balance = (float)$account["balance"];
            $change = $balance * $apy;

            $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:world, :acct, :amount, :action, :memo, :total)");
            $r = $stmt->execute([
                ":world" => $worldID,
                ":acct" => $account["id"],
                ":amount" => $change * -1,
                ":action" => "deposit",
                ":memo" => "Interest",
                ":total" => ($worldBalance - $change)
            ]);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error writing transaction from World account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }

            $stmt = $db->prepare("INSERT INTO TPTransactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:acct, :world, :amount, :action, :memo, :total)");
            $r = $stmt->execute([
                ":newAct" => $account["id"],
                ":world" => $worldID,
                ":amount" => $change,
                ":action" => "deposit",
                ":memo" => "Interest",
                ":total" => ($account["balance"] + $change)
            ]);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error writing transaction into new account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }


            //Sums the account's expected balance
            $stmt = $db->prepare("SELECT SUM(amount) AS total FROM TPTransactions WHERE act_src_id = :actID");
            $r = $stmt->execute([":actID" => $account["id"]]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $balance = $result["total"];

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error SUMming total for new account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }


            //Takes SUMmed balance and updates the account's actual balance and next date check for interest
            $stmt = $db->prepare("UPDATE TPAccounts SET balance = :balance, nextApy = DATE_ADD(nextApy, INTERVAL 1 MONTH) WHERE id = :id");
            $r = $stmt->execute([
                ":balance" => $balance,
                ":id" => $account["id"]
            ]);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error updating balance. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }


            //Sums the world account's expected balance
            $stmt = $db->prepare("SELECT SUM(amount) AS total FROM TPTransactions WHERE act_src_id = :world");
            $r = $stmt->execute([":world" => $worldID]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $balance = $result["total"];

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error SUMming total for world account. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }

            //Takes SUMmed balance and updates the account's actual balance
            $stmt = $db->prepare("UPDATE TPAccounts SET balance = :balance WHERE id = :id");
            $r = $stmt->execute([
                ":balance" => $balance,
                ":id" => $worldID
            ]);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error updating world balance. Please contact your bank representative and relay the following error code. " . var_export($e, true));
            }
        }
    }
}
