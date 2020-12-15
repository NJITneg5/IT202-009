<?php

require_once(__DIR__ . "/partials/nav.php");
if(!has_role("Admin")) {
    flash("You do not have permission to access this page.");
    die(header("Location: login.php"));
}

$outside = true;
$searchResults = array();
$params = array();

if(isset($_POST["searchSub"])){

    $db = getDB();
    $outside = false;

    $query = "SELECT * FROM TPAccounts account JOIN TPUsers users on user_id = users.id";

    if(isset($_POST["firstName"])){
        $firstName = $_POST["firstName"];
        if(!empty($firstName)) {
            $query .= " WHERE firstName = :firstName";
            $params[":firstName"] = $firstName;
        }
    }
    if(isset($_POST["lastName"])){
        $lastName = $_POST["lastName"];
        if(!empty($lastName)) {
            $query .= " AND lastName = :lastName";
            $params[":lastName"] = $lastName;
        }
    }
    if(isset($_POST["partialAct"])) {
        $partialAct = (float)$_POST["partialAct"];
        if ($partialAct != 0) {
            $query .= " AND account_number LIKE '%:num%'";
            $params[":num"] = $partialAct;
        }
    }

    $query .= " ORDER BY lastName";

    $stmt = $db->prepare($query);

    foreach($params as $key=>$val){
        $stmt->bindValue($key, $val);
    }
    $r = $stmt->execute();
    if($r){
        $searchResults = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $e = $stmt->errorInfo();
        flash("Something went wrong " . var_export($e, true));
    }
}
?>
<div class="bodyMain">
    <h1>Simple Bank Admin Dashboard</h1>

    <h4>Search Form</h4>
    <form method="POST">
        <label>First Name: <br>
            <input type="text" name="firstName" placeholder="John">
        </label><br><br>
        <label>Last Name: <br>
            <input type="text" name="lastName" placeholder="Doe">
        </label><br><br>
        <label>Partial Account #:<br>
            <input type="text" name="partialAct" placeholder="1234"
        </label><br><br>
        <input type="submit" name="searchSub" value="Submit">
        <input type="reset" value="Reset">
    </form>

    <?php if(count($searchResults) >0): ?>
        <table class="listTable">
            <thead>
            <tr class="listHead">
                <th>Last Name</th>
                <th>First Name</th>
                <th>User Disabled</th>
                <th>Account Number</th>
                <th>Account Type</th>
                <th>Balance</th>
                <th>Closed</th>
                <th>Frozen</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($searchResults as $r):?>
                <?php
                    $lastNameOut = $r["users.lastName"];
                    $firstNameOut = $r["users.firstName"];
                    $enabledOut = $r["users.enabled"];
                    $accountNumOut = $r["account.account_number"];
                    $accountTypeOut = $r["account.account_type"];
                    $balanceOut = $r["account.balance"];
                    $activeOut = $r["account.active"];
                    $frozenOut = $r["account.frozen"];
                ?>
                <tr class="listRow">
                    <td><?php safer_echo($lastNameOut);?></td>
                    <td><?php safer_echo($firstNameOut);?></td>
                    <td><?php safer_echo($enabledOut);?></td>
                    <td><?php safer_echo($accountNumOut);?></td>
                    <td><?php safer_echo($accountTypeOut);?></td>
                    <td>$<?php safer_echo($balanceOut);?></td>
                    <td><?php safer_echo($activeOut);?></td>
                    <td><?php safer_echo($frozenOut);?></td>
                    <td><a href="adminCreate.php?userID=<?php echo $r["users.id"]?>">Create Checking Account for User</a>, <a href="disableUser.php?userID=<?php echo $r["users.id"]?>&enable=<?php echo $r["users.enabled"]?>">Enable/Disable User</a><br>
                        <a href="freezeAccount.php?id=<?php echo $r["account.id"]?>&frozen=<?php echo $r["frozen"]?>">(Un)Freeze Account</a><a href="adminTransactions.php?id= <?php echo $r["account.id"]?>">View Account's Transactions</a></td>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
    <?php elseif($outside): ?>

    <?php else: ?>
        <p>There are no Results.</p>
    <?php endif; ?>

    <hr>

    <address>
        Page made by Nate Gile
        for Internet Applications Final Project.
        Created November 2020
    </address>
</div>
<?php require(__DIR__ . "/partials/flash.php");?>
</body>
</html>