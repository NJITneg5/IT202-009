<?php
require_once(__DIR__ . "/lib/helpers.php");

if(!has_role("Admin")) {
    flash("You do not have permission to access this page.");
    die(header("Location: login.php"));
}

if(isset($_GET["userID"])){
    $userID = $_GET["userID"];
}
else {
    flash("Can't get user ID.");
    die(header("Location: admin.php"));
}

$db = getDB();

$isValid = true;       //Check for inserts
$uniqueNum = false;     //Check to make sure account number is available
$uniqueCount = 0;       //Makes sure that the unique account check only runs a certain amount of times

while(!$uniqueNum && $uniqueCount < 10 && $isValid) {    //Loop to generate a unique account number
    $newActNum = rand(100000000000, 999999999999);
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
if($uniqueCount == 10 && !$uniqueNum){
    $isValid = false;
    flash("There was an error creating unique account number. Please try again.");
    die(header("Location: admin.php"));
}

if($isValid) {  //Creates the account
    $stmt = $db->prepare("INSERT INTO TPAccounts (account_number, account_type, balance, user_id) VALUES(:accountNum, :accountType, :initBalance, :userID)");
    $r = $stmt->execute([
        ":accountNum" => $newActNum,
        ":accountType" => "checking",
        ":initBalance" => 0,
        ":userID" => $userID
    ]);
    if ($r) {
        flash("Account created successfully with Account Number: " . $newActNum);
        die(header("Location: admin.php"));
    } else {
        $e = $stmt->errorInfo();
        flash("There was an error creating the account. Please try again." . var_export($e, true));
        die(header("Location: admin.php"));
    }
}