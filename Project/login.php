<?php require_once(__DIR__ . "/partials/nav.php"); ?>
    <div class="bodyMain">
	<h1>Please Log in with your Email or Username and password.</h1>
	<form method = "POST" id ="loginForm">
		<label for= "userEmail">Username/Email:</label><br>
		<input type= "text" id= "userEmail" name= "userEmail" required/><br>

		<label for= "pw">Password:</label><br>
		<input type= "password" id= "pw" name= "pw" required/><br>

		<input type= "submit" name = "login" value= "Login"/><br>
	</form>
	
	
	<hr>
	
	<address>
	Page made by Nate Gile
	for Internet Applications Final Project.
	Created October 2020
	</address>
    </div>
	
	<!--PHP Shenanigans -->

    <?php
    if (isset($_POST["login"])) {
        $userEmail = null;
        $user = null;
        $email = null;
        $password = null;
        $stmt = null;
        $params = null;
        $endings = [".com", ".org", ".net", ".int",".edu",".gov",".mil"];

        if (isset($_POST["userEmail"])) {
            $userEmail = $_POST["userEmail"];
        }

        if (isset($_POST["pw"])) {
            $password = $_POST["pw"];
        }

        $isValid = true;

        if (!isset($userEmail) || !isset($password)) {
            $isValid = false;
        }

        foreach($endings as $end) {
            if (strpos($userEmail, "@") && strpos($userEmail, $end)) {
                $email = $userEmail;
                break;
            }
        }

        if (!isset($email)) {
            $user = $userEmail;
        }

        if(!isset($email) && !isset($user)){
            $isValid = false;
        }

        if ($isValid) {

            $db = getDB();

            if (isset($db)) {
                if($email != null) {
                    $email = $userEmail;
                    $stmt = $db->prepare("SELECT id, email, username, password, enabled from TPUsers WHERE email = :email LIMIT 1");
                    $params = array(":email" => $email);
                }
                elseif ($user != null) {
                    $user = $userEmail;
                    $stmt = $db->prepare("SELECT id, email, username, password, enabled from TPUsers WHERE username = :user LIMIT 1");
                    $params = array(":user" => $user);
                }

                $r = $stmt->execute($params);
                //echo "db returned: " . var_export($r, true);
                $e = $stmt->errorInfo();

                if ($e[0] != "00000") {
                    //echo "uh oh something went wrong: " . var_export($e, true);
                    flash("Something went wrong. Please try again.");
                }

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result && isset($result["password"])) {
                    if(strcmp($result["enabled"], "true") == 0) {
                        $password_hash_from_db = $result["password"];
                        if (password_verify($password, $password_hash_from_db)) {
                            $stmt = $db->prepare("SELECT TPRoles.name FROM TPRoles JOIN TPUserRoles on TPRoles.id = TPUserRoles.role_id where TPUserRoles.user_id = :user_id and TPRoles.is_active = 1 and TPUserRoles.is_active = 1");
                            $stmt->execute([":user_id" => $result["id"]]);
                            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            unset($result["pw"]);//remove password so we don't leak it beyond this page
                            //let's create a session for our user based on the other data we pulled from the table
                            $_SESSION["user"] = $result;//we can save the entire result array since we removed password
                            if ($roles) {
                                $_SESSION["user"]["roles"] = $roles;
                            } else {
                                $_SESSION["user"]["roles"] = [];
                            }
                            //on successful login let's serve-side redirect the user to the home page.
                            flash("Log in Successful.");

                            calcSavingsAPY();

                            calcLoanAPY();

                            die(header("Location: home.php"));
                        } else {
                            flash("Invalid password, try again");
                        }
                    }
                    else {
                        flash("This user has been disabled by an admin");
                    }
                }
                else {
                    flash("Invalid username or email");
                }
            }
        }
        else {
            flash("There was a validation issue");
        }
    }

    require(__DIR__ . "/partials/flash.php");
    ?>
</body>
</html>