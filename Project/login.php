<!DOCTYPE HTML>

<html lang="en">

<head>
    <meta charset="utf-8">
	<meta name="Author" content="Nate Gile">
	<meta name="date" content="9/24/2020">
	<meta name="keywords" content="">
    <title>Gile Family Bank</title>
    <!--<link rel="stylesheet" type="text/css" href="style.css">-->
	<link href='https://fonts.googleapis.com/css?family=Average' rel='stylesheet'>
	<link rel="icon" href="bankIcon.jpg" type="image/gif" sizes="16x16">

<style>
body {
    font-family: 'Average';
}
</style>
</head>
<body>
    <nav><?php require_once(__DIR__ . "/partials/nav.php"); ?></nav>
	<h1>Please Log in with your Email and password.</h1>
	<form method = "POST">
		<label for= "email">Email</label><br>
		<input type= "email" id= "email" name= "email" required/><br>
		<label for= "pw">Password</label><br>
		<input type= "password" id= "pw" name= "pw" required/><br>
		<input type= "submit" name = "login" value= "Login"/><br>
	</form>
	
	
	<hr>
	
	<address>
	Page made by Nate Gile <br>
	for Internet Applications Final Project. <br>
	Created September 2020<br>
	</address>
	
	
	<!--PHP Shenanigans -->

    <?php
    if (isset($_POST["login"])) {
        $email = null;
        $password = null;
        if (isset($_POST["email"])) {
            $email = $_POST["email"];
        }
        if (isset($_POST["password"])) {
            $password = $_POST["password"];
        }
        $isValid = true;
        if (!isset($email) || !isset($password)) {
            $isValid = false;
        }
        if (!strpos($email, "@")) {
            $isValid = false;
            echo "<br>Invalid email<br>";
        }
        if ($isValid) {
            $db = getDB();
            if (isset($db)) {
                $stmt = $db->prepare("SELECT id, email, password from TPUsers WHERE email = :email LIMIT 1");

                $params = array(":email" => $email);
                $r = $stmt->execute($params);
                echo "db returned: " . var_export($r, true);
                $e = $stmt->errorInfo();
                if ($e[0] != "00000") {
                    echo "uh oh something went wrong: " . var_export($e, true);
                }
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result && isset($result["pw"])) {
                    $password_hash_from_db = $result["pw"];
                    if (password_verify($password, $password_hash_from_db)) {
                        $stmt = $db->prepare("
SELECT TPRoles.name FROM TPRoles JOIN TPUserRoles on TPRoles.id = TPUserRoles.role_id where TPUserRoles.user_id = :user_id and TPRoles.is_active = 1 and TPUserRoles.is_active = 1");
                        $stmt->execute([":user_id" => $result["id"]]);
                        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        unset($result["password"]);//remove password so we don't leak it beyond this page
                        //let's create a session for our user based on the other data we pulled from the table
                        $_SESSION["user"] = $result;//we can save the entire result array since we removed password
                        if ($roles) {
                            $_SESSION["user"]["roles"] = $roles;
                        }
                        else {
                            $_SESSION["user"]["roles"] = [];
                        }
                        //on successful login let's serve-side redirect the user to the home page.
                        header("Location: home.php");
                    }
                    else {
                        echo "<br>Invalid password, get out!<br>";
                    }
                }
                else {
                    echo "<br>Invalid user<br>";
                }
            }
        }
        else {
            echo "There was a validation issue";
        }
    }
    ?>
</body>
</html>