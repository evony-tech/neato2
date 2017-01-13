<?
// NEATO/www/GetMarket.php - displays high low and avg prices over specified time span
// requires "server" to be specified in GET
// takes optional "days" to be specified in GET
// (defaults to 1 day)
setlocale(LC_ALL,'');
date_default_timezone_set("America/Chicago");
include_once "StandardIncludes.php";
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header('Content-Type: text/plain'); // make it plain text rather than html so linebreaks show correctly.

if (isset($_GET['server'])) {
$server=filter_var($_GET['server'],FILTER_SANITIZE_STRING);
} else {
echo "You need to call this and specify the server as a variable. example: 

call \"{$NEATO_HTTPURL}GetMarket.php\" {server:Config.server}

or if you are trying to view this in browser, {$NEATO_HTTPURL}GetMarket.php?server=???
";
exit();
}
if (isset($_GET['days'])) {
	$days=filter_var($_GET['days'],FILTER_SANITIZE_NUMBER_INT);
	} else {
	$days = 1;
}	
$DBPATH=$NEATO_DBDIR."/MarketPrices.db3";


try {
	$File_DB = new PDO('sqlite:'.$DBPATH);
	$output ="";
    $resources = array("Food","Lumber","Stone","Iron");
	$counter = 0;
	$hiPrice = array();
	$loPrice = array();
	$avgPrice = array();
	
    foreach ($resources as $res) {
		$SQL = 'select avg(mp'.$res.'Buy), avg(mp'.$res.'Sell), max(mp'.$res.'Buy), min(mp'.$res.'Sell) from MarketPrices where mpServer = "'.$server.'" and mpTimestamp > date("now","-'.$days.' day"); ';
		$result = $File_DB->query($SQL);
		//echo $SQL."<br/>";
		//print_r(result); // for debugging
		//echo "<br/>";
		
		while ($row = $result->fetch()) {
			$avgPrice[$counter] = round((($row[0]+$row[1])/2),2);
			$hiPrice[$counter] = round($row[2],2);
			$loPrice[$counter] = round($row[3],2);
			$counter++;
		}
	}
	// Clean up after ourselves, close the database
	$File_DB = null;
	
	echo "avgPrice=[$avgPrice[0],$avgPrice[1],$avgPrice[2],$avgPrice[3]]\n";
	echo "hiPrice=[$hiPrice[0],$hiPrice[1],$hiPrice[2],$hiPrice[3]]\n";
	echo "loPrice=[$loPrice[0],$loPrice[1],$loPrice[2],$loPrice[3]]\n";
	
} catch(PDOException $e) {
    // Print PDOException message
    echo "Uh oh, Scooby! ".$e->getMessage();
}
?>