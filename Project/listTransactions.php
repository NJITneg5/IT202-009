<?php

require_once(__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

if(isset($_GET["id"])){
    $acctId = $_GET["id"];
}
$page = 1;
$perPage = 10;
$action = null;
$startDate = null;
$endDate = null;
$allSet = false;

if(isset($_GET["page"])){
    try{
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}

if(isset($_GET["action"])){
    try{
        $action = $_GET["action"];
    }
    catch(Exception $e){

    }
}

if(isset($_GET["startDate"])){
    try{
        $startDate = $_GET["startDate"];
        $startDate = date('Y-m-d H:i:s', strtotime($startDate));
    }
    catch(Exception $e){

    }
}

if(isset($_GET["endDate"])){
    try{
        $endDate = $_GET["endDate"];
        $endDate = date('Y-m-d H:i:s', strtotime($endDate));
    }
    catch(Exception $e){

    }
}

if(isset($action,$startDate,$endDate)){
    $allSet = true;
}


$db = getDB();
$userID = get_user_id();

if(isset($acctId)) {    //To get info on the account
    $stmt = $db->prepare("SELECT account_number, balance FROM TPAccounts WHERE id = :id AND user_id = :userID");
    $r = $stmt->execute([
            ":id" => $acctId,
            ":userID" => $userID
    ]);

    if($r){
        $acctResults = $stmt->fetch(PDO::FETCH_ASSOC);
        $acctNum = $acctResults["account_number"];
        $balance = $acctResults["balance"];
    }else {
        $e = $stmt->errorInfo();
        flash("Error fetching account information, likely from trying to access an account that is not yours. Please refrain from doing that.");
    }
}

    if (isset($acctId) && isset($acctNum) && isset($balance)) {
        $stmt = $db->prepare("SELECT COUNT(*) AS total FROM TPTransactions WHERE act_src_id = :acctID");
        $stmt->execute([":acctID" => $acctId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = 0;
        if ($result) {
            $total = (int)$result["total"];
        }
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
    }

    /*if (isset($acctId) && isset($acctNum) && isset($balance)) {
        $stmt = $db->prepare("SELECT amount, action_type, memo, created FROM TPTransactions WHERE act_src_id = :acctID ORDER BY created LIMIT :offset, :count");
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindValue(":count", $perPage, PDO::PARAM_INT);
        $stmt->bindValue(":acctID", $acctId);
        $r = $stmt->execute();
        if ($r) {
            $transResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $e = $stmt->errorInfo();
            flash("There was an error fetching your transactions. Please contact a bank representative and relay the following error code. " . var_export($e, true));
        }
    } else {
        flash("Account not Found Error. Please contact your bank representative.");
        die(header("Location: listAccounts.php"));
    }*/


    if(isset($_POST["submit"])) {
        $action = $_POST["actionType"];

        $startDate = $_POST["startDate"];
        $startDate = date('Y-m-d H:i:s', strtotime($startDate));

        $endDate = $_POST["endDate"];
        $endDate = date('Y-m-d H:i:s', strtotime($endDate));
    }

    if (isset($acctId) && isset($action)) {
        $stmt = $db->prepare("SELECT COUNT(*) AS total FROM TPTransactions WHERE act_src_id = :acctID AND action_type = :action");
        $stmt->execute([":acctID" => $acctId,
                        ":action" => $action]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = 0;
        if ($result) {
            $total = (int)$result["total"];
        }
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
    }

    $query = "SELECT amount, action_type, memo, created FROM TPTransactions WHERE act_src_id = :id";
    $params[":id"] = $userID;


    if(isset($startDate) && isset($endDate)){
        $query .= " AND created BETWEEN :start AND :end";
        $params[":start"] = $startDate;
        $params[":end"] = $endDate;
    }

    if(isset($action)){
        $query .= " AND action_type = :action";
        $params[":action"] = $action;
    }

    $query .= " ORDER BY created LIMIT :offset, :count";
    $params[":offset"] = $offset;
    $params[":count"] = $perPage;

    $stmt = $db->prepare($query);
    foreach ($params as $key=>$val) {
        if ($key == ":offset" || $key == ":count") {
            $stmt->bindValue($key, $val, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $val);
        }
    }
    $r = $stmt->execute();
        if ($r) {
            $transResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $e = $stmt->errorInfo();
            flash("There was an error fetching your transactions. Please contact a bank representative and relay the following error code. " . var_export($e, true));
        }


?>

<div class="bodyMain">

    <h1>Recent Transactions on this Account</h1>

    <h4>Account Number: <?php safer_echo($acctNum);?></h4>
    <h4>Balance: $<?php safer_echo($balance);?></h4>

    <h6><strong>List Filters:</strong></h6>
    <form method="POST">
        <label>Action Type:
            <select name="actionType">
                <option value="">Select an option</option>
                <option value="deposit">Deposit</option>
                <option value="withdraw">Withdraw</option>
                <option value="transfer">Personal Transfer</option>
                <option value="ext-transfer">Transfer</option>
                <option value="">No Preference</option>
            </select>
        </label>

        <label>Start Date:
            <input type="date" name="startDate">
        </label>

        <label>End Date:
            <input type="date" name="endDate">
        </label>

        <input type="submit" name="submit" value="Submit">
        <input type="reset" value="Reset">
    </form>

    <?php if(count($transResults) > 0): ?>
        <table class="listTable">
            <thead>
            <tr class="listHead">
                <th>Transaction Type</th>
                <th>Amount</th>
                <th>Memo</th>
                <th>Occurred on:</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($transResults as $r):?>
                <tr class="listRow">
                    <td><?php safer_echo($r["action_type"]);?></td>
                    <td>$<?php safer_echo($r["amount"]);?></td>
                    <td><?php safer_echo($r["memo"]);?></td>
                    <td><?php safer_echo($r["created"]);?></td>
                    </tr>
            <?php endforeach;?>
            </tbody>
        </table>
    <?php else: ?>
        <p>There are no transactions for this account. (Which is bad, because there should at least be a "Initial Deposit")</p>
    <?php endif; ?>
    <br>
    <div class="listNav">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                <a class="page-link" href="?id=<?php echo $acctId?>&page=<?php echo $page-1;?>&action=<?php echo $action?>&startDate=<?php echo $startDate?>&endDate=<?php echo $endDate?>" tabindex="-1">Previous</a>
            </li>
            <?php for($i = 0; $i < $totalPages; $i++):?>
                <li class="page-item <?php echo ($page-1) == $i?"active":"";?>">
                    <a class="page-link" href="?id=<?php echo $acctId?>&page=<?php echo ($i+1);?>&action=<?php echo $action?>&startDate=<?php echo $startDate?>&endDate=<?php echo $endDate?>"><?php echo ($i+1);?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $totalPages?"disabled":"";?>">
                <a class="page-link" href="?id=<?php echo $acctId?>&page=<?php echo $page+1;?>&action=<?php echo $action?>&startDate=<?php echo $startDate?>&endDate=<?php echo $endDate?>">Next</a>
            </li>
        </ul>
    </div>

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