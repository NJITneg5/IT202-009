<?php
    require_once(__DIR__ . "/partials/nav.php");
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$db = getDB();

$stmt = $db->prepare("SELECT firstName, lastName, visible FROM TPUsers WHERE id = :id");
$stmt->execute([":id" => get_user_id()]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$qFirst = $result["firstName"];
$qLast = $result["lastName"];
$qVisible = $result["visible"];

//save data if we submitted the form
if (isset($_POST["saved"])) {
    $isValid = true;
    //check if our email changed
    $newEmail = get_email();
    if (get_email() != $_POST["email"]) {
        //TODO we'll need to check if the email is available
        $email = $_POST["email"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from TPUsers where email = :email");
        $stmt->execute([":email" => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Email is already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newEmail = $email;
        }
    }
    $newUsername = get_username();
    if (get_username() != $_POST["username"]) {
        $username = $_POST["username"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from TPUsers where username = :username");
        $stmt->execute([":username" => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Username is already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newUsername = $username;
        }
    }
    if ($isValid) {
        $stmt = $db->prepare("UPDATE TPUsers set email = :email, username= :username where id = :id");
        $r = $stmt->execute([":email" => $newEmail, ":username" => $newUsername, ":id" => get_user_id()]);
        if ($r) {
            flash("Updated profile");
        }
        else {
            flash("Error updating profile");
        }
        //password is optional, so check if it's even set
        //if so, then check if it's a valid reset request
        if (!empty($_POST["password"]) && !empty($_POST["confirm"])) {
            if ($_POST["password"] == $_POST["confirm"]) {
                $password = $_POST["password"];
                $hash = password_hash($password, PASSWORD_BCRYPT);
                //this one we'll do separate
                $stmt = $db->prepare("UPDATE TPUsers set password = :password where id = :id");
                $r = $stmt->execute([":id" => get_user_id(), ":password" => $hash]);
                if ($r) {
                    flash("Password has been reset.");
                }
                else {
                    flash("Error resetting password");
                }
            }
        }
//fetch/select fresh data in case anything changed
        $stmt = $db->prepare("SELECT email, username from TPUsers WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => get_user_id()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $email = $result["email"];
            $username = $result["username"];
            //let's update our session too
            $_SESSION["user"]["email"] = $email;
            $_SESSION["user"]["username"] = $username;
        }
    }
    if($isValid){
        $formFirst = $_POST["firstName"];
        $formLast = $_POST["lastName"];
        $formVisible = $qVisible;
        if(isset($_POST["public"])){
            $formVisible = $_POST["public"];
        } else {
            $formVisible = "private";
        }


        if(strcmp($qFirst,$formFirst) != 0){
            $stmt = $db->prepare("UPDATE TPUsers set firstName = :first WHERE id = :id");
            $stmt->execute([":first" => $formFirst, ":id" => get_user_id()]);
        }

        if(strcmp($qLast,$formLast) != 0){
            $stmt = $db->prepare("UPDATE TPUsers set lastName = :last WHERE id = :id");
            $stmt->execute([":last" => $formLast, ":id" => get_user_id()]);
        }

        if(strcmp($qVisible, $formVisible) != 0){
            $stmt = $db->prepare("UPDATE TPUsers set visible = :vis WHERE id = :id");
            $stmt->execute([":vis" => $formVisible, ":id" => get_user_id()]);
        }
    }
}
?>

    <div class="bodyMain">
    <h1>Simple Bank Profile</h1>

    <h3><?php safer_echo(get_email()); ?>'s Profile</h3>

    <p>Change your info?</p>
    <form method="POST" id = "profileForm">
        <label>First Name: <br>
        <input type="text" name="firstName" value="<?php echo $qFirst ?>" placeholder="John">
        </label><br><br>

        <label>Last Name: <br>
        <input type="text" name="lastName" value="<?php echo $qLast ?>" placeholder="Doe">
        </label><br><br>

        <label>Public Account:
            <input type="checkbox" name="public" value="public" <?php echo ($qVisible == "public"?'selected="selected"':'');?>>
        </label><br>

        <label>Email:<br>
        <input type="email" name="email" value="<?php safer_echo(get_email()); ?>"/>
        </label><br><br>

        <label>Username:<br>
        <input type="text" maxlength="60" name="username" value="<?php safer_echo(get_username()); ?>"/>
        </label><br><br>

        <!-- DO NOT PRELOAD PASSWORD-->
        <label>Password:<br>
        <input type="password" name="password"/>
        </label><br><br>

        <label>Confirm Password:<br>
        <input type="password" name="confirm"/>
        </label><br><br>

        <input type="submit" name="saved" value="Save Profile"/>
    </form>

    <hr>

    <address>
    Page made by Nate Gile
    for Internet Applications Final Project.
    Created October 2020
    </address>
    </div>
    <?php require(__DIR__ . "/partials/flash.php");?>
</body>
</html>
