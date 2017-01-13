<?
// MODULE_NAME: Goal Sets
// MODULE_DESC: Global goalsets for server, account and city
// MODULE_STATUS: Released
// MODULE_VERSION: 1
include_once "StandardIncludes.php";
include "inc/rain.tpl.class.php";

$DBPATH=$NEATO_DBDIR."/GoalSets.db3";

raintpl::configure("base_url", null);
raintpl::configure("tpl_dir", "tpl/");
raintpl::configure("cache_dir", "tmp/");

$tpl = new RainTPL;
$tpl->assign("NEATOVersion", $NEATO_VERSION);
$tpl->assign("Title", "Global Goal Sets");
$tpl->assign("Header", "NEATO Global Goal Sets");
$tpl->assign("NEATO_HTTPURL", $NEATO_HTTPURL);

try {
	$File_DB = new PDO('sqlite:'.$DBPATH);
} catch(PDOException $e) {
	echo "Uh oh, Scooby! " . $e->getMessage();
	exit();
}

if ($_GET['action'] == 'save') {
	SaveGoals($_GET);
} elseif ($_GET['action'] == 'saveupdate') {
	SaveUpdatedGoals($_GET);
} elseif ($_GET['action'] == 'get') {
	GetGoals($_GET);
} elseif ($_GET['action'] == 'edit') {
	EditGoals($_GET);
} elseif ($_GET['action'] == 'delete') {
	DeleteGoals($_GET);
} else {
	GoalForm();
}

$File_DB = null;

function SaveGoals($TheGET) {
	global $File_DB;

	$query  = "insert into GoalSets (gsServer, gsUsername, gsCity, gsGoals) ";
	$query .= "       values (:server, :username, :city, :goals); ";
	$dbObject = $File_DB->prepare($query);
	$dbObject->bindParam(':server',   filter_var($TheGET['server'],   FILTER_SANITIZE_STRING));
	$dbObject->bindParam(':username', filter_var($TheGET['username'], FILTER_SANITIZE_EMAIL));
	$dbObject->bindParam(':city',     filter_var($TheGET['city'],     FILTER_SANITIZE_STRING));
	$dbObject->bindParam(':goals',    filter_var($TheGET['goals'],    FILTER_SANITIZE_FULL_SPECIAL_CHARS));
	$dbObject->execute();
	echo "Saved";
	echo "<a href=\"GoalSets.php?action=edit\">Click here to go back to the list</a>";
}

function DeleteGoals($TheGET) {
	global $File_DB;

	$query  = "delete from GoalSets where gsID = :rowid ";
	$dbObject = $File_DB->prepare($query);
	$dbObject->bindParam(':rowid',    filter_var($TheGET['rowid'],    FILTER_SANITIZE_NUMBER_INT));
	$dbObject->execute();
	echo "DELETED";
	echo "<a href=\"GoalSets.php?action=edit\">Click here to go back to the list</a>";
}

function SaveUpdatedGoals($TheGET) {
	global $File_DB;

	$query  = "update GoalSets set gsServer = :server, gsUsername = :username, gsCity = :city, gsGoals = :goals ";
	$query .= " where gsID = :rowid ";

	$dbObject = $File_DB->prepare($query);
	$dbObject->bindParam(':server',   filter_var($TheGET['server'],   FILTER_SANITIZE_STRING));
	$dbObject->bindParam(':username', filter_var($TheGET['username'], FILTER_SANITIZE_EMAIL));
	$dbObject->bindParam(':city',     filter_var($TheGET['city'],     FILTER_SANITIZE_STRING));
	$dbObject->bindParam(':goals',    filter_var($TheGET['goals'],    FILTER_SANITIZE_FULL_SPECIAL_CHARS));
	$dbObject->bindParam(':rowid',    filter_var($TheGET['rowid'],    FILTER_SANITIZE_NUMBER_INT));
	if ($dbObject->execute()) {
		echo "Saved ";
	} else {
		echo "Error encountered, sorry. ";
	}
	echo "<a href=\"GoalSets.php?action=edit\">Click here to go back to the list</a>";
}

function GetGoals($TheGET) {
	global $File_DB;
	$query  = "select gsGoals, 1 as TheOrder from GoalSets ";
	$query .= " where gsServer = '' ";
	$query .= "   and gsUsername = '' ";
	$query .= "   and gsCity = '' ";
	$query .= " union ";
	$query .= "select gsGoals, 2 as TheOrder from GoalSets ";
	$query .= " where gsServer = :server ";
	$query .= "   and gsUsername = '' ";
	$query .= "   and gsCity = '' ";
	$query .= " union ";
	$query .= "select gsGoals, 3 as TheOrder from GoalSets ";
	$query .= " where gsServer = :server ";
	$query .= "   and gsUsername = :username ";
	$query .= "   and gsCity = '' ";
	$query .= " union ";
	$query .= "select gsGoals, 4 as TheOrder from GoalSets ";
	$query .= " where gsServer = :server ";
	$query .= "   and gsUsername = :username ";
	$query .= "   and gsCity = :city ";
	$query .= " order by TheOrder";

	$dbObject = $File_DB->prepare($query);
	$dbObject->bindParam(':server',   filter_var($TheGET['server'],   FILTER_SANITIZE_STRING));
	$dbObject->bindParam(':username', filter_var($TheGET['username'], FILTER_SANITIZE_EMAIL));
	$dbObject->bindParam(':city',     filter_var($TheGET['city'],     FILTER_SANITIZE_STRING));
	$result = $dbObject->execute();
	while ($row = $dbObject->fetch()) {
		echo $row['gsGoals']."\n";
	}
}

function EditGoals($TheGET) {
	global $tpl, $File_DB;

	if ($TheGET['rowid'] == "") {
		$query = "select * from GoalSets;";
		$dbObject = $File_DB->prepare($query);
		$dbObject->execute();
		$allData = $dbObject->fetchAll();
		$tpl->assign("goalsetlist", $allData);
		$tpl->draw("GoalSets.viewtable");
	} else {
		$query = "select * from GoalSets where gsID = :theID;";
		$dbObject = $File_DB->prepare($query);
		$dbObject->bindParam(':theID', filter_var($TheGET['rowid'], FILTER_SANITIZE_NUMBER_INT));
		$dbObject->execute();
		$row = $dbObject->fetch();
		$tpl->assign("GoalSet", $row);
		$tpl->draw("GoalSets.editdata");
	}
}	

function GoalForm() {
	global $tpl;
	$tpl->draw("GoalSets.general");
}	

?>