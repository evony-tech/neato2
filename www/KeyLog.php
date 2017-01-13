<?
// MODULE_NAME: Account Login Tracker
// MODULE_DESC: Keep track of your login details locally
// MODULE_STATUS: Released
// MODULE_VERSION: 1
include_once "StandardIncludes.php";
include "inc/rain.tpl.class.php";

$DBPATH=$NEATO_DBDIR."/KeyLog.db3";

raintpl::configure("base_url", null);
raintpl::configure("tpl_dir", "tpl/");
raintpl::configure("cache_dir", "tmp/");

$tpl = new RainTPL;
$tpl->assign("NEATOVersion", $NEATO_VERSION);
$tpl->assign("Title", "Key Log");
$tpl->assign("Header", "NEATO Key Logging");
$tpl->assign("NEATO_HTTPURL", $NEATO_HTTPURL);
//$tpl->draw("MinFormat");

try {
	$File_DB = new PDO('sqlite:'.$DBPATH);
} catch(PDOException $e) {
	echo "Uh oh, Scooby! " . $e->getMessage();
	exit();
}

//---------------------
// Dispatcher
//---------------------
if ($_GET['action'] == 'save') {
	SaveKeys($_GET);
} elseif ($_GET['action'] == 'edit') {
	EditKeys($_GET);
} elseif ($_GET['action'] == 'delete') {
	DeleteKeys($_GET);
} elseif ($_GET['action'] == 'export') {
	ExportKeys($_GET);
} else {
	ShowInstructions();
}

$File_DB = null;

//---------------------
// Functions below here
//---------------------
function SaveKeys($TheGET) {
	global $File_DB;

	$query  = "insert or replace into KeyLog (klServer, klUsername, klPassword, klLordname) ";
	$query .= "       values (:server, :username, :password, :lordname) ";
	$dbObject = $File_DB->prepare($query);
	$dbObject->bindParam(':server',   filter_var($TheGET['server'],   FILTER_SANITIZE_STRING));
	$dbObject->bindParam(':username', filter_var($TheGET['username'], FILTER_SANITIZE_EMAIL));
	$dbObject->bindParam(':password', filter_var($TheGET['password'], FILTER_SANITIZE_STRING));
	$dbObject->bindParam(':lordname', filter_var($TheGET['lordname'], FILTER_SANITIZE_STRING));
	$dbObject->execute();
	echo "<p>Saved</p>";
}	

function EditKeys($TheGET) {
	global $File_DB, $tpl;

	if ($TheGET['rowid'] == "") {
		$query = "select * from KeyLog;";
		$dbObject = $File_DB->prepare($query);
		$result = $dbObject->execute();
		$allData = $dbObject->fetchAll();
		$tpl->assign("keylist", $allData);
		$tpl->draw("KeyLog.viewtable");
	} else {
		$query = "select * from KeyLog where klID = :rowid";
		$dbObject = $File_DB->prepare($query);
		$dbObject->bindParam(':rowid', filter_var($TheGET['rowid'], FILTER_SANITIZE_EMAIL));
		$result = $dbObject->execute();
		$row = $dbObject->fetch();
		$tpl->assign("KeyLog", $row);
		$tpl->draw("KeyLog.editdata");
	}
}	

function DeleteKeys($TheGET) {
	global $File_DB;

	$query  = "delete from KeyLog where klID = :rowid ";
	$dbObject = $File_DB->prepare($query);
	$dbObject->bindParam(':rowid', filter_var($TheGET['rowid'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->execute();
	echo "DELETED<br />";
}

function ShowInstructions() {
	global $tpl;
	$tpl->draw("KeyLog.general");
}	

function ExportKeys($TheGET) {
	global $File_DB;

	$query = "select * from KeyLog;";
	$dbObject = $File_DB->prepare($query);
	$result = $dbObject->execute();
	echo "<pre>\n";
	echo "Server,LordName,Username,Password\n";
	while ($row = $dbObject->fetch()) {
		echo $row['klServer'].",".$row['klLordname'].",".$row['klUsername'].",".$row['klPassword']."\n";
	}
	echo "</pre>\n";
	echo "<br />";
}	

?>
