<?
// GetPricing.php - Logs market prices
// SumRandomGuy 20130701

header("content-type: application/json");
setlocale(LC_ALL,'');
date_default_timezone_set("America/Chicago");

include_once "StandardIncludes.php";

$res=filter_var($_GET['res'],FILTER_SANITIZE_STRING);
$server=filter_var($_GET['server'],FILTER_SANITIZE_STRING);

$DBPATH=$NEATO_DBDIR."/MarketPrices.db3";
$url='http://'.$_SERVER['HTTP_HOST'].'/MarketPrices.php';

$timeoffset = -5 * 60 * 60;
//(timezone is -5hrs GMT * 60 mins in an hour * 60 seconds in a minute )


try {
	$File_DB = new PDO('sqlite:'.$DBPATH);
	$SQLStatement = "select strftime('%s', mpTimestamp) EpochTime, mp".$res."Buy, mp".$res."Sell from MarketPrices where mpServer = '".$server."'";
	$result = $File_DB->query($SQLStatement);
	$output ="";
	while ($row = $result->fetch()) {
		$output .= "[".($row[0]+$timeoffset)."000,".round((($row[1]+$row[2])/2),2)."],\n";
	}
	$output = substr($output, 0, -2);

	echo $_GET['callback']. "([\n". $output."\n]);";
	
	// Clean up after ourselves, close the database
	$File_DB = null;
} catch(PDOException $e) {
    // Print PDOException message
    echo "Uh oh, Scooby! ".$e->getMessage();
}
?>