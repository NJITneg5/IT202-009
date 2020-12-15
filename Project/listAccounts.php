<?php

require_once(__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
        flash("You must be logged in to access this page");
        die(header("Location: login.php"));
}

$userID = get_user_id();
$db = getDB();
$results = 0;

$page = 1;
$perPage = 10;

if(isset($_GET["page"])){
    try{
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}
$stmt = $db->prepare("SELECT COUNT(*) as total from TPAccounts WHERE user_id = :id AND active = 'true'");
$r = $stmt->execute([":id" => $userID]);
if($r){
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = 0;
    $total = (int)$result["total"];

    $totalPages = ceil($total/ $perPage);
    $offset = ($page - 1) * $perPage;
}

$stmt = $db->prepare("SELECT id, account_number, account_type, IFNULL(balance,'0.00') AS balance, IFNULL(apy, 'N/A') as apy, frozen FROM TPAccounts WHERE user_id = :id AND active = 'true' ORDER BY opened_date LIMIT :offset, :count");
$stmt->bindValue(":id",$userID);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $perPage, PDO::PARAM_INT);
$r = $stmt->execute();
if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $e = $stmt->errorInfo();
    flash("There was an error fetching your accounts. Please contact a bank representative and relay the following error code. " . var_export($e, true));
}
?>

<div class="bodyMain">

    <h1>List of Your Accounts</h1>
    <?php if(count($results) > 0): ?>
        <table class="listTable">
            <thead>
                <tr class="listHead">
                    <th>Account Number</th>
                    <th>Account Type</th>
                    <th>Account Balance</th>
                    <th>Interest Rate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r):?>
                    <tr class="listRow">
                        <td><?php safer_echo($r["account_number"]);?></td>
                        <td><?php safer_echo($r["account_type"]);?></td>
                        <td>$<?php if(strcmp($r["account_type"], 'loan') == 0 && (float)$r["balance"] != 0){
                                safer_echo((float)$r["balance"] * -1);
                            }else {
                                safer_echo($r["balance"]);
                            }
                            ?></td>
                        <td><?php if(strcmp($r["apy"], "N/A") == 0) {
                                echo "N/A";
                            } else {
                                safer_echo(rtrim((float)$r["apy"], '0') . "%");
                            }
                        ?></td>
                        <td><a href="<?php echo getURL("listTransactions.php?id=" . $r["id"]);?>">View Transactions</a>, <a href="<?php echo getURL("closeAccount.php?id=" . $r["id"]);?>">Close Account</a>
                        <?php if(strcmp($r["frozen"], "true") == 0) :?><br>This account is currently frozen. <?php endif; ?></td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You do not have any accounts with our bank. Click here to create one: </p>
        <a href="<?php echo getURL("createAccount.php")?>">Create Account</a>
    <?php endif; ?>

    <div class="listNav">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                <a class="page-link" href="?page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
            </li>
            <?php for($i = 0; $i < $totalPages; $i++):?>
                <li class="page-item <?php echo ($page-1) == $i?"active":"";?>">
                    <a class="page-link" href="?page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $totalPages?"disabled":"";?>">
                <a class="page-link" href="?page=<?php echo $page+1;?>">Next</a>
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