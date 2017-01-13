<?
// MODULE_NAME: Player Informatics
// MODULE_DESC: Detailed player information and stats
// MODULE_STATUS: Released
// MODULE_VERSION: 1
include_once "StandardIncludes.php";
include "inc/rain.tpl.class.php";

$DBPATH=$NEATO_DBDIR."PlayerInformatics.db3";

raintpl::configure("base_url", null);
raintpl::configure("tpl_dir", "tpl/");
raintpl::configure("cache_dir", "tmp/");

$tpl = new RainTPL;
$tpl->assign("NEATOVersion", $NEATO_VERSION);
$tpl->assign("Title", "Player Informatics");
$tpl->assign("Header", "Player Informatics");
// Do $tpl->draw("TemplateName"); -- TemplateName.html
// optional: 
// $content = $tpl->draw("MinFormat");

// If we can't even open the database, let's bail out.
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
	saveFunct($_GET);
} if ($_GET['action'] == 'view') {
	viewFunct($_GET);
} else {
	informationFunct($_GET);
}

$File_DB = null;

//---------------------
// Functions below here
//---------------------
function saveFunct($TheGET) {

}

function showOverall($TheGET) {
	global $tpl, $File_DB;
	$query = "select * from PlayerInfo; ";
	$dbObject = $File_DB->prepare($query);
	$dbObject->execute();
	$allData = $dbObject->fetchAll();
	$tpl->assign("PlayerInfo", $allData);
	$tpl->draw("PlayerInfo.overall");	
}

function getPlayerInfo($piID) {
	global $File_DB;
	$query = "select * from PlayerInfo where piID = :piID ";
	$dbObject = $File_DB->prepare($query);
	$dbObject->bindParam(":piID", $piID);
	$dbObject->execute();
	$row = $dbObject->fetch();
	return $row;
}

function showAccount($TheGET) {
	global $tpl, $File_DB;
	$query  = "select pi.*, ci.*, ri.* ";
	$query .= "  from PlayerInfo pi ";
	$query .= "       left join CityInfo as ci on (ci.piID = pi.piID) ";
	$query .= "       left join ResInfo as ri on (ri.ciID = ci.ciID) ";
	$query .= " where pi.piID = :piid ";
	$dbObject = $File_DB->prepare($query);

	// TODO: Get this from the GET stuff
	$tid = $TheGET['piid'];
	$dbObject->bindParam(':piid', $tid);
	$dbObject->execute();
	$tpl->assign("playerinfo", getPlayerInfo($tid));

	$tpl->assign("server", $row['piServer']);
	$tpl->assign("lordname", $row['piLordname']);
	$tpl->assign("alliance", $row['piAlliance']);
	$tpl->assign("prestige", $row['piPrestige']);
	$tpl->assign("honor", $row['piHonor']);
	$tpl->assign("lastupdate", $row['piTimestamp']);
	
	$allData = $dbObject->fetchAll();
	$tpl->assign("accountinfo", $allData);
	$tpl->draw("PlayerInfo.account");	
}

function showCity($TheGET) {
	global $tpl, $File_DB;
	
	$tpl->draw("PlayerInfo.city");	
}

function viewFunct($TheGET) {
	if ($TheGET['level'] == 'overall') {
		showOverall($TheGET);
	} elseif ($TheGET['level'] == 'account') {
		showAccount($TheGET);
	} elseif ($TheGET['level'] == 'city') {
		showCity($TheGET);
	} else {
		showOverall($TheGET);
	}
}

function informationFunct($TheGET) {
	global $tpl;
	$tpl->draw("PlayerInfo.general");
}


?>