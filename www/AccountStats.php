<?
// MODULE_NAME: Account Statistics
// MODULE_DESC: Keep track of items in your accounts
// MODULE_STATUS: In development
// MODULE_VERSION: 1
include_once "StandardIncludes.php";
include "inc/rain.tpl.class.php";

$DBPATH=$NEATO_DBDIR."/AccountStats.db3";

raintpl::configure("base_url", null);
raintpl::configure("tpl_dir", "tpl/");
raintpl::configure("cache_dir", "tmp/");

$tpl = new RainTPL;
$tpl->assign("NEATOVersion", $NEATO_VERSION);
$tpl->assign("Title", "Account Stats");
$tpl->assign("NEATO_HTTPURL", $NEATO_HTTPURL);
$tpl->assign("Header", "<a href=".$NEATO_HTTPURL." style='text-decoration: none'>NEATO</a>  <a href=".$NEATO_HTTPURL."AccountStats.php style='text-decoration: none'>Account Stats</a>");
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
	Save($_GET);
} elseif ($_GET['action'] == 'edit') {
	Edit($_GET);
} elseif ($_GET['action'] == 'delete') {
	Delete($_GET);
} elseif ($_GET['action'] == 'export') {
	Export();
} else {
	Display();
}

$File_DB = null;

//---------------------
// Functions below here
//---------------------
function Save($TheGET) {
	global $File_DB;

	$query  = "insert or replace into AccountStats (asServer, asLordName, asTitle, asCities, asCents, asAmulets, asMars, asAdvanceTeleports, asRandomTeleports, asWarTeleports, asBrokenGates, asFleetFeet, asAlchemist, asAnabasis, asEpitome, asOnWars, asExcal, asWON, asNationMedals) ";
	$query .= "values (:server, :lordname, :title, :cities, :cents, :ammies, :mars, :aport, :rport, :wport, :brokengates, :fleetfeet, :alchemist, :anabasis, :epitome, :onwars, :xcal, :won, :nationmedals) ";
	$dbObject = $File_DB->prepare($query);
	$dbObject->bindParam(':server',   filter_var($TheGET['server'],   FILTER_SANITIZE_STRING));
	$dbObject->bindParam(':lordname',   filter_var($TheGET['lordname'],   FILTER_SANITIZE_STRING));
	$dbObject->bindParam(':title', filter_var($TheGET['title'], FILTER_SANITIZE_STRING));
	$dbObject->bindParam(':cities', filter_var($TheGET['cities'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':cents', filter_var($TheGET['cents'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':ammies', filter_var($TheGET['ammies'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':mars', filter_var($TheGET['mars'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':aport', filter_var($TheGET['aport'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':rport', filter_var($TheGET['rport'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':wport', filter_var($TheGET['wport'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':brokengates', filter_var($TheGET['brokengates'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':fleetfeet', filter_var($TheGET['fleetfeet'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':alchemist', filter_var($TheGET['alchemist'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':anabasis', filter_var($TheGET['anabasis'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':epitome', filter_var($TheGET['epitome'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':onwars', filter_var($TheGET['onwars'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':xcal', filter_var($TheGET['xcal'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':won', filter_var($TheGET['won'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->bindParam(':nationmedals', filter_var($TheGET['nationmedals'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->execute();
	
	echo "<p>".$TheGET['lordname']." (".$TheGET['server'].") Saved</p>";

	if ($TheGET['rowid'] != "") {
		header( 'Location: '.$NEATO_HTTPURL.'AccountStats.php?action=edit' );
	}
}	
function Edit($TheGET) {
	global $File_DB, $tpl;

	if ($TheGET['rowid'] == "") {
		$query = "select *, rowid from AccountStats order by upper(asLordName) asc";
		$dbObject = $File_DB->prepare($query);
		$result = $dbObject->execute();
		$allData = $dbObject->fetchAll();
		$tpl->assign("accountlist", $allData);
		$tpl->draw("AccountStats.viewtable");
	} else {
		$query = "select *, rowid from AccountStats where rowid = :rowid";
		$dbObject = $File_DB->prepare($query);
		$dbObject->bindParam(':rowid', filter_var($TheGET['rowid'], FILTER_SANITIZE_STRING));
		$result = $dbObject->execute();
		$row = $dbObject->fetch();
		$tpl->assign("AccountStats", $row);
		$tpl->draw("AccountStats.editdata");
	}
}

function Delete($TheGET) {
	global $File_DB;

	$query  = "delete from AccountStats where rowid = :rowid ";
	$dbObject = $File_DB->prepare($query);
	$dbObject->bindParam(':rowid', filter_var($TheGET['rowid'], FILTER_SANITIZE_NUMBER_INT));
	$dbObject->execute();
	
	echo "<p>".$TheGET['name']." (".$TheGET['id'].") Deleted</p>";

	if ($TheGET['rowid'] != "") {
		header( 'Location: '.$NEATO_HTTPURL.'AccountStats.php?action=edit' );
	}
}

function Export() {
	global $File_DB;

	$query = "select * from AccountStats";
	$dbObject = $File_DB->prepare($query);
	$result = $dbObject->execute();
	echo "<pre>\n";
	echo "Server,LordName,ID,HeroName,Level,Politics,Attack,Intelligence\n";
	while ($row = $dbObject->fetch()) {
		echo $row['hsServer'].",".$row['hsUsername'].",".$row['hsId'].",".$row['hsName'].",".$row['hsLevel'].",".$row['hsPolitics'].",".$row['hsAttack'].",".$row['hsIntelligence']."\n";
	}
	echo "</pre>\n";
	echo "<br />";
}	

function Display() {
	global $tpl;
	$tpl->draw("AccountStats.general");
}	
?>
