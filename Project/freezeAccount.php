<?php
require_once(__DIR__ . "/lib/helpers.php");

if(!has_role("Admin")) {
    flash("You do not have permission to access this page.");
    die(header("Location: login.php"));
}

if(isset($_GET["id"])){
    $id = $_GET["id"];
} else {
    flash("Cannot access account id.");
    die(header("Location: listAccounts.php"));
}
if(isset($_GET["frozen"])){
    $frozen = $_GET["frozen"];
} else {
    flash("Cannot access account id.");
    die(header("Location: listAccounts.php"));
}

$db = getDB();

if(strcmp($frozen, "false") == 0) {
    $stmt = $db->prepare("SELECT * FROM TPAccounts WHERE id = :id AND frozen = 'false'");
    $r = $stmt->execute([":id" => $id]);
    if (!empty($r)) {
        $stmt = $db->prepare("UPDATE TPAccounts SET frozen = 'true' WHERE id = :id");
        $r = $stmt->execute([":id" => $id]);
        flash("Successfully changed the Account's status.");
        die(header("Location: admin.php"));
    } else {
        $e = $stmt->errorInfo();
        flash("There was an error. " . var_export($e, true));
        die(header("Location: admin.php"));
    }
} elseif (strcmp($frozen, "true") == 0) {

    $stmt = $db->prepare("SELECT * FROM TPUsers WHERE id = :id AND frozen = 'true'");
    $r = $stmt->execute([":id" => $id]);
    if (!empty($r)) {
        $stmt = $db->prepare("UPDATE TPUsers SET frozen = 'false' WHERE id = :id");
        $r = $stmt->execute([":id" => $id]);
        flash("Successfully changed the Account's status.");
        die(header("Location: admin.php"));
    } else {
        $e = $stmt->errorInfo();
        flash("There was an error. " . var_export($e, true));
        die(header("Location: admin.php"));
    }
} else {
    flash("There was an error, seems like frozen was set to something other than true/false");
    die(header("Location: admin.php"));
}