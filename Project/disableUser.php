<?php
require_once(__DIR__ . "/lib/helpers.php");
if(!has_role("Admin")) {
    flash("You do not have permission to access this page.");
    die(header("Location: login.php"));
}

if(isset($_GET["userID"])){
    $id = $_GET["userID"];
} else {
    flash("Cannot get user id");
    die(header("Location: admin.php"));
}
if(isset($_GET["enable"])){
    $enabled = $_GET["enable"];
} else {
    flash("Cannot get enabled string");
    die(header("Location: admin.php"));
}

$db = getDB();

if(strcmp($enabled, "true") == 0) {

    $stmt = $db->prepare("SELECT * FROM TPUsers WHERE id = :id AND enabled = 'true'");
    $r = $stmt->execute([":id" => $id]);
    if (!empty($r)) {
        $stmt = $db->prepare("UPDATE TPUsers SET enabled = 'false' WHERE id = :id");
        $r = $stmt->execute([":id" => $id]);
        flash("Successfully changed the User's status.");
        die(header("Location: admin.php"));
    } else {
        $e = $stmt->errorInfo();
        flash("There was an error. " . var_export($e, true));
        die(header("Location: admin.php"));
    }
} elseif (strcmp($enabled, "false") == 0) {

    $stmt = $db->prepare("SELECT * FROM TPUsers WHERE id = :id AND enabled = 'false'");
    $r = $stmt->execute([":id" => $id]);
    if (!empty($r)) {
        $stmt = $db->prepare("UPDATE TPUsers SET enabled = 'true' WHERE id = :id");
        $r = $stmt->execute([":id" => $id]);
        flash("Successfully changed the User's status.");
        die(header("Location: admin.php"));
    } else {
        $e = $stmt->errorInfo();
        flash("There was an error. " . var_export($e, true));
        die(header("Location: admin.php"));
    }
} else {
    flash("There was an error, seems like enabled was set to something other than true/false");
    die(header("Location: admin.php"));
}