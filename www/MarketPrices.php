<? 
// MODULE_NAME: Market Prices
// MODULE_DESC: Receive and save the market prices from your bots
// MODULE_STATUS: Released
// MODULE_VERSION: 1
// MarketPrices.php - Logs market prices
// SumRandomGuy 20130701
include_once "StandardIncludes.php";

$DBPATH=$NEATO_DBDIR."/MarketPrices.db3";
$url='http://'.$_SERVER['HTTP_HOST'].'/MarketPrices.php';

if ($_GET['action'] == 'save') {
	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);
		$query = "insert into MarketPrices (mpServer, mpFoodBuy, mpFoodSell, mpLumberBuy, mpLumberSell, mpStoneBuy, mpStoneSell, mpIronBuy, mpIronSell) values (:mpServer, :mpFoodBuy, :mpFoodSell, :mpLumberBuy, :mpLumberSell, :mpStoneBuy, :mpStoneSell, :mpIronBuy, :mpIronSell)";
		$dbObject = $File_DB->prepare($query);
		$dbObject->bindParam(':mpServer',     filter_var($_GET['server'],     FILTER_SANITIZE_STRING));
		$dbObject->bindParam(':mpFoodBuy',    filter_var($_GET['foodbuy'],    FILTER_VALIDATE_FLOAT));
		$dbObject->bindParam(':mpFoodSell',   filter_var($_GET['foodsell'],   FILTER_VALIDATE_FLOAT));
		$dbObject->bindParam(':mpLumberBuy',  filter_var($_GET['lumberbuy'],  FILTER_VALIDATE_FLOAT));
		$dbObject->bindParam(':mpLumberSell', filter_var($_GET['lumbersell'], FILTER_VALIDATE_FLOAT));
		$dbObject->bindParam(':mpStoneBuy',   filter_var($_GET['stonebuy'],   FILTER_VALIDATE_FLOAT));
		$dbObject->bindParam(':mpStoneSell',  filter_var($_GET['stonesell'],  FILTER_VALIDATE_FLOAT));
		$dbObject->bindParam(':mpIronBuy',    filter_var($_GET['ironbuy'],    FILTER_VALIDATE_FLOAT));
		$dbObject->bindParam(':mpIronSell',   filter_var($_GET['ironsell'],   FILTER_VALIDATE_FLOAT));
		$dbObject->execute();
		
		if ($_GET['returnaverages'] == "yes") {
			outputAverages($File_DB, $_GET);
		} else {
			echo "Saved";
		}
		// Clean up after ourselves, close the database
		$File_DB = null;
	} catch(PDOException $e) {
	    // Print PDOException message
	    echo "Uh oh, Scooby! ".$e->getMessage();
	}
	exit();	
}

alterDatabase();

if ($_GET['type'] <> "stats" && $_GET['type'] <> "averages") {
	?><html>
	<head>
		<title>Market Prices</title>
		<link href="<?=$NEATO_CSSDIR;?>/neato.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
	<div id="header"><h1>MarketPrices</h1>
	</div>
	<div id="main">
	<p>Provided by SumRandomTechGuys<br>
	MarketPrices.php version: 20130701<br>
	NEATO Version: <?=$NEATO_VERSION;?></p>
	<?
}

if($_GET['action'] == 'read') {
	
	readInfo($_GET);

	} else {

?>
<p>To save prices, execute the following script:</p>
<pre>label autorun
// How often should this be updated (in seconds)
RefreshRate = 30

label ST

marketupdate food
marketupdate wood
marketupdate stone
marketupdate iron

if !m_context.marketReady() goto ST

url = "<?=$url;?>"
data = { action:"save", type:"stats", server:Config.server, foodbuy:BuyPrice(0), foodsell:SellPrice(0), lumberbuy:BuyPrice(1), lumbersell:SellPrice(1), stonebuy:BuyPrice(2), stonesell:SellPrice(2), ironbuy:BuyPrice(3), ironsell:SellPrice(3) }
get url data
echo $result
execute "sleep " + RefreshRate
loop ST</pre>
<p>To view the data in fantastic graphs click here:<br/>
<a href="MarketDisplay.php"><?=$NEATO_HTTPURL;?>MarketDisplay.php</a></p>
<p>To view the data in a table click here:<br/>
<a href="<?=$url;?>?action=read&type=table"><?=$url;?>?action=read&type=table</a></p>
<p>To view the data as a stat list:<br/>
<?
if (isset($_COOKIE['server'])) {
	$cookieServer=strtolower($_COOKIE['server']);
} else {
	$cookieServer = "ss69";
}
?>
<a href="<?=$url;?>?action=read&type=stats&restype=lumber&server=<?=$cookieServer?>&maxmeasures=2&groupby=%M" target="_blank"><?=$url;?>?action=read&type=stats&restype=lumber&server=<?=$cookieServer?>&maxmeasures=2&groupby=%M</a><br/>
where the groupby param follows the format of: <br/>
 %d		 day of month: 00<br/>
 %H		 hour: 00-24<br/>
 %j		 day of year: 001-366<br/>
 %J		 Julian day number<br/>
 %m		 month: 01-12<br/>
 %M		 minute: 00-59<br/>
 %w		 day of week 0-6 with Sunday==0<br/>
 %W		 week of year: 00-53<br/>
 %Y		 year: 0000-9999</p>
</div>
</body>
</html><?
}

function outputListRow($row) {
	echo "Server:      " . $row['mpServer']     . "<br>\n";
	echo "DateTime:    " . $row['mpTimestamp']  . "<br>\n";
	echo "Food Buy:    " . $row['mpFoodBuy']    . "<br>\n";
	echo "Food Sell:   " . $row['mpFoodSell']   . "<br>\n";
	echo "Lumber Buy:  " . $row['mpLumberBuy']  . "<br>\n";
	echo "Lumber Sell: " . $row['mpLumberSell'] . "<br>\n";
	echo "Stone Buy:   " . $row['mpIronBuy']    . "<br>\n";
	echo "Stone Sell:  " . $row['mpIronSell']   . "<br>\n";
	echo "Iron Buy:    " . $row['mpStoneBuy']   . "<br>\n";
	echo "Iron Sell:   " . $row['mpStoneSell']  . "<br>\n";
	echo "<br>\n";
}

function outputTableRow($row) {
	echo "<tr>";
	echo "<td>" . $row['mpServer']     . "</td>";
	echo "<td>" . $row['mpTimestamp']  . "</td>";
	echo "<td>" . $row['mpFoodBuy']    . "</td>";
	echo "<td>" . $row['mpFoodSell']   . "</td>";
	echo "<td>" . $row['mpLumberBuy']  . "</td>";
	echo "<td>" . $row['mpLumberSell'] . "</td>";
	echo "<td>" . $row['mpIronBuy']    . "</td>";
	echo "<td>" . $row['mpIronSell']   . "</td>";
	echo "<td>" . $row['mpStoneBuy']   . "</td>";
	echo "<td>" . $row['mpStoneSell']  . "</td>";
	echo "</tr>\n";
}

function outputStats($DB, $PARAMS) {

	if (strtolower($PARAMS['restype']) == "food") {
		$res = "Food";
	} elseif (strtolower($PARAMS['restype']) == "lumber" || strtolower($PARAMS['restype']) == "wood") {
		$res = "Lumber";
	} elseif (strtolower($PARAMS['restype']) == "stone") {
		$res = "Stone";
	} elseif (strtolower($PARAMS['restype']) == "iron") {
		$res = "Iron";
	} else {
		$res = "Food";
	}

	$measures = $PARAMS['maxmeasures'] == null ? 1000 : $PARAMS['maxmeasures'];

	$groupby = $PARAMS['groupby'] == null ? "%H" : $PARAMS['groupby'];

	// If they groupby is minute, I have to add a group by clause
	$query  = " SELECT date(mpTimestamp) as mpDate, ";
	$query .= $groupby == "%M" ? " strftime(\"%H:%M\", mpTimestamp), " : "";
	$query .= "        strftime(\"".$groupby."\", mpTimestamp) as Grouping, ";
	$query .= "        round(min(mp".$res."Buy), 3) as MinBuy,  ";
	$query .= "        round(max(mp".$res."Buy), 3) as MaxBuy, ";
	$query .= "        round(avg(mp".$res."Buy), 3) as AvgBuy, ";
	$query .= "        round(min(mp".$res."Sell), 3) as MinSell,  ";
	$query .= "        round(max(mp".$res."Sell), 3) as MaxSell, ";
	$query .= "        round(avg(mp".$res."Sell), 3) as AvgSell, ";
	$query .= "        count(1) as NumberMeasures ";
	$query .= "   FROM MarketPrices ";
	$query .= "  where lower(mpServer) = \"".$PARAMS['server']."\" ";
	$query .= "  group by date(mpTimestamp), ";
	$query .= $groupby == "%M" ? " strftime(\"%H:%M\", mpTimestamp), " : "";
	$query .= "        strftime(\"".$groupby."\", mpTimestamp)";
	$query .= "  order by mpTimestamp DESC";
	$query .= "  limit " . $measures;
	$query .= ";";

	$result = $DB->query($query);

	foreach ($result as $row) {
		echo $row['mpDate']. "|";
		echo $row['Grouping']. "|";
		echo $row['MinBuy']. "|";
		echo $row['MaxBuy']. "|";
		echo $row['AvgBuy']. "|";
		echo $row['MinSell']. "|";
		echo $row['MaxSell']. "|";
		echo $row['AvgSell']. "|";
		echo $row['NumberMeasures'] . "<br />\n";
	}
	echo "Date|GroupValue|MinBuy|MaxBuy|AvgBuy|MinSell|MaxSell|AvgSell|NumMeasures<br />\n";
}

function outputAverages($DB, $PARAMS) {
	global $DBPATH;
	if ($PARAMS['seconds'] == "") {
		$theSeconds = "-86400 seconds";
	} else {
		$theSeconds = filter_var($PARAMS['seconds'], FILTER_SANITIZE_NUMBER_INT);
		$theSeconds = $theSeconds * -1 . " seconds";
	}

	if ($PARAMS['server'] == "") {
		if (isset($_COOKIE['server'])) {
			$theServer=strtolower($_COOKIE['server']);
		} else {
			$theServer = "ss69";
		}
	} else {
		$theServer = filter_var($PARAMS['server'], FILTER_SANITIZE_STRING);
	}

	$theSQL  = "select round(avg(mpFoodBuy+mpFoodSell)/2, 2) as FoodAvg,  ";
	$theSQL .= "       round(avg(mpLumberBuy+mpLumberSell)/2, 2) as LumberAvg,  ";
	$theSQL .= "       round(avg(mpIronBuy+mpIronSell)/2, 2) as IronAvg,  ";
	$theSQL .= "       round(avg(mpStoneBuy+mpStoneSell)/2, 2) as StoneAvg  ";
	$theSQL .= "  from MarketPrices indexed by idx_mp_server_nocase ";
	$theSQL .= " where mpServer = :theServer collate nocase ";
	$theSQL .= "   and mpTimestamp >= datetime('now', :theSeconds); ";
	
	$statement = $DB->prepare($theSQL);

	$statement->bindParam(':theServer', $theServer);
	$statement->bindParam(':theSeconds', $theSeconds);

	$statement->execute();

	while ($row = $statement->fetch()) {
		echo $row['FoodAvg'].",".$row['LumberAvg'].",".$row['IronAvg'].",".$row['StoneAvg'];
	}
}

function readInfo($PARAMS) {
	global $DBPATH;

	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);
		// If we're outputing a chart, we don't need to do the row-by-row stuff so we'll bail out after
		if ($PARAMS['type']  == "chart") {
			createChart($File_DB);
			return;
		}

		if ($PARAMS['type'] == "stats") {
			outputStats($File_DB, $PARAMS);
			return;
		}

		if ($PARAMS['type'] == "averages") {
			outputAverages($File_DB, $PARAMS);
			return;
		}

		if ($PARAMS['type'] == "table") {
			echo '<table border="1">';
			echo '<tr>';
			echo '<th>Server</th>';
			echo '<th>Timestamp</th>';
			echo '<th>Food Buy</th>';
			echo '<th>Food Sell</th>';
			echo '<th>Lumber Buy</th>';
			echo '<th>Lumber Sell</th>';
			echo '<th>Iron Buy</th>';
			echo '<th>Iron Sell</th>';
			echo '<th>Stone Buy</th>';
			echo '<th>Stone Sell</th>';
			echo '</tr>';
		}

		$result = $File_DB->query('select * from MarketPrices order by mpTimestamp DESC limit 100');
		foreach ($result as $row) {
			if ($PARAMS['type'] == "table")	outputTableRow($row);
			else outputListRow($row);
		}

		if ($outputType == "table") {
			echo "</table>";
		}
		echo "</div></body></html>";
		$File_DB = null;

	} catch(PDOException $e) {
	    // Print PDOException message
	    echo "Uh oh, Scooby!<br>";
	    echo $e->getMessage();
	}
}

function alterDatabase() {
	global $DBPATH;

	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);
		$theSQL = "create index if not exists idx_mp_server on MarketPrices(mpServer); ";
		$File_DB->exec($theSQL);
		$theSQL = "create index if not exists idx_mp_server_nocase on MarketPrices(mpServer collate nocase, mpTimestamp); ";
		$File_DB->exec($theSQL);
		$theSQL = "create index if not exists idx_mp_timestamp on MarketPrices(mpTimestamp); ";
		$File_DB->exec($theSQL);
		$File_DB = null;
	} catch(PDOException $e) {
	    echo "Uh oh, Scooby!<br>";
	    echo $e->getMessage();
	}	

}
?>
