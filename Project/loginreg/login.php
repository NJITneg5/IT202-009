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
	<h1>Please Log in with your Email and password.</h1>
	<form method = "POST">
		<label for= "email">Email</label><br>
		<input type= "email" id= "email" name= "email" required/><br>
		<label for= "pw">Password</label><br>
		<input type= "password" id= "pw" name= "pw" required/><br>
		<input type= "submit" name = "login" value= "Log in"/><br>
	</form>
	
	
	<hr>
	
	<address>
	Page made by Nate Gile <br>
	for Internet Applications Final Project. <br>
	Created September 2020<br>
	</address>
	
	
	<!--PHP Shenanigans -->
	
<?php
	if(isset($_POST["login"])){
		$email = null;
		$password = null;
		if(isset($_POST["email"])){
			$email = $_POST["email"];
		}
		if(isset($_POST["pw"])){
			$password = $_POST["pw"];
		}
		$isValid = true;
		if(!isset($email) || !isset($password)){
			$isValid = false; 
		}
		//TODO other validation as desired, remember this is the last line of defense
		//here you'd probably want some email validation, for sake of example let's do a super basic one
		if(!strpos($email, "@")){
			$isValid = false;
			echo "<br>Invalid email<br>";
		}
		 if($isValid){
            require_once("db.php");
            $db = getDB();
            if(isset($db)){
                $stmt = $db->prepare("SELECT email, password from TPUsers WHERE email = :email LIMIT 1");
                $params = array(":email"=>$email);
                $r = $stmt->execute($params);

                echo "db returned: " . var_export($r, true);
                $e = $stmt->errorInfo();
                if($e[0] != "00000"){
                    echo "uh oh something went wrong: " . var_export($e, true);
                }
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if($result && isset($result["password"])){
                    $passwordHash = $result["password"];
                    if(password_verify($password, $passwordHash)){
                        echo "<br>Welcome! You're logged in!<br>";
                    }
                    else{
                        echo "<br>Invalid password, try again<br>";
                    }
                }
                else{
                    echo "<br>Invalid user<br>";
                }
            }
        }
        else{
            echo "There was a validation issue";
        }
    }
?>
</body>
</html>