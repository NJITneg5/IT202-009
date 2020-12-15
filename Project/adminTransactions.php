<?php

require_once(__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
if(!has_role("Admin")) {
    flash("You do not have permission to access this page.");
    die(header("Location: login.php"));
}

if(isset($_GET["id"])){
    $acctId = $_GET["id"];
}

$page = 1;
$perPage = 10;
$action = null;

$startDate = null;
$prefillStart = null;

$endDate = null;
$prefillEnd = null;

$epochDay= "1970-01-01 00:00:00";

if(isset($_GET["page"])){
    try{
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}

if(isset($_SESSION["actionType"])){
    $action = $_SESSION["actionType"];
}

if(isset($_SESSION["startDate"])){
    $startDate = $_SESSION["startDate"];
    $prefillStart = $_SESSION["prefillStart"];
}

if(isset($_SESSION["endDate"])){
    $endDate = $_SESSION["endDate"];
    $prefillEnd = $_SESSION["prefillEnd"];
}

$db = getDB();

if(isset($acctId)) {    //To get info on the account
    $stmt = $db->prepare("SELECT account_number, balance, account_type, IFNULL(apy, 'none') as apy FROM TPAccounts WHERE id = :id");
    $r = $stmt->execute([":id" => $acctId]);

    if($r){
        $acctResults = $stmt->fetch(PDO::FETCH_ASSOC);
        $acctNum = $acctResults["account_number"];
        $balance = $acctResults["balance"];
        $acctType = $acctResults["account_type"];
        $apy = $acctResults["apy"];
    }else {
        $e = $stmt->errorInfo();
        flash("An error has occurred" . var_export($e, true));
    }
}

if(isset($_POST["submit"])) {
    $action = $_POST["actionType"];
    $_SESSION["actionType"] = $action;

    if (isset($_POST["startDate"])) {
        $date = $_POST["startDate"];
        $date = date('Y-m-d H:i:s', strtotime($date));
        if($date > $epochDay){
            $startDate = $date;
            $_SESSION["startDate"] = $startDate;
            $_SESSION["prefillStart"] = $_POST["startDate"];
        }
    }

    if (isset($_POST["endDate"])) {
        $date = $_POST["endDate"];
        $date = date('Y-m-d H:i:s', strtotime($date));
        if($date > $epochDay){
            $endDate = $date;
            $_SESSION["endDate"] = $endDate;
            $_SESSION["prefillEnd"] = $_POST["endDate"];
        }
    }
}

$countQuery = "SELECT COUNT(*) as total FROM TPTransactions WHERE act_src_id = :id";
$countParams[":id"] = $acctId;

if(isset($startDate) && isset($endDate)) {
    $countQuery .= " AND created BETWEEN :start AND :end";
    $countParams[":start"] = $startDate;
    $countParams[":end"] = $endDate;
}

if(isset($action)) {
    if (strcmp($action, "") != 0) {
        $countQuery .= " AND action_type = :action";
        $countParams[":action"] = $action;
    }
}

$stmt = $db->prepare($countQuery);
foreach ($countParams as $key=>$val) {
    $stmt->bindValue($key, $val);
}
$r = $stmt->execute();
if($r){
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
$total = 0;
if ($result) {
    $total = (int)$result["total"];
}
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

$query = "SELECT amount, action_type, memo, created FROM TPTransactions WHERE act_src_id = :id";
$params[":id"] = $acctId;


if(isset($startDate) && isset($endDate)){
    $query .= " AND created BETWEEN :start AND :end";
    $params[":start"] = $startDate;
    $params[":end"] = $endDate;
}

if(isset($action)){
    if(strcmp($action, "") != 0){
        $query .= " AND action_type = :action";
        $params[":action"] = $action;
    }
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

    <h4>Balance: $<?php if(strcmp($acctType, "loan") == 0 && (float)$balance != 0) {
            safer_echo((float)$balance * -1);
        } else {
            safer_echo($balance);
        };?></h4>

    <?php if(strcmp($apy, "none") != 0): ?>
        <h4>APY: <?php echo rtrim((float)$apy, '0') . "%";?></h4>
    <?php endif; ?>
    <h6><strong>List Filters:</strong></h6>
    <form method="POST">
        <label>Action Type:
            <select name="actionType">
                <option value="">Select an option</option>
                <option value="deposit" <?php echo ($action == "deposit"?'selected="selected"':'');?>>Deposit</option>
                <option value="withdraw" <?php echo ($action == "withdraw"?'selected="selected"':'');?>>Withdraw</option>
                <option value="transfer" <?php echo ($action == "transfer"?'selected="selected"':'');?>>Personal Transfer</option>
                <option value="ext-transfer" <?php echo ($action == "ext-transfer"?'selected="selected"':'');?>>Transfer</option>
                <option value="" <?php echo ($action == ""?'selected="selected"':'');?>>No Preference</option>
            </select>
        </label>

        <label>Start Date:
            <input type="date" name="startDate" value="<?php echo $prefillStart?>">
        </label>

        <label>End Date:
            <input type="date" name="endDate" value="<?php echo $prefillEnd?>">
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
        <p>There are no transactions for this account.</p>
    <?php endif; ?>
    <br>
    <div class="listNav">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                <a class="page-link" href="?id=<?php echo $acctId?>&page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
            </li>
            <?php for($i = 0; $i < $totalPages; $i++):?>
                <li class="page-item <?php echo ($page-1) == $i?"active":"";?>">
                    <a class="page-link" href="?id=<?php echo $acctId?>&page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $totalPages?"disabled":"";?>">
                <a class="page-link" href="?id=<?php echo $acctId?>&page=<?php echo $page+1;?>">Next</a>
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