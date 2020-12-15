<?php
require_once(__DIR__ . "/lib/helpers.php");

if(isset($_GET["id"])){
    $id = $_GET["id"];
} else {
    flash("Cannot access account");
    die(header("Location: listAccounts.php"));
}

$db = getDB();
$userID = get_user_id();

$stmt = $db->prepare("SELECT balance FROM TPAccounts WHERE id = :id AND user_id = :user");
$r = $stmt->execute([":id" => $id, ":user" => $userID]);
if($r){
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = (float)$result["balance"];
} else {
    $e = $stmt->errorInfo();
    flash("There was an error fetching your account. Please contact a bank representative and relay the following error code. " . var_export($e, true));
    die(header("Location: listAccounts.php"));
}

if($balance == 0.0){
    $stmt = $db->prepare("UPDATE TPAccounts SET active = 'false' WHERE id = :id");
    $r = $stmt->execute([":id" => $id]);
    flash("Successfully closed the Account.");
    die(header("Location: listAccounts.php"));
} else {
    flash("There is still funds in this account. Please remove them to continue.");
    die(header("Location: listAccounts.php"));
}
