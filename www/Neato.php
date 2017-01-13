<?php
// MODULE_NAME: NEATO 3
// MODULE_DESC: MySQL based NEATO
// MODULE_STATUS: Development Alpha
// MODULE_VERSION: 3.0
// 
// TECH 2016.10.22 
// 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header('Content-Type: text/plain'); 
// make it plain text rather than html so linebreaks show correctly.

//include_once "../includes/db.php"; // could relocate hardcoded db connect info below...

// below settings work for default xampp
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "neato";

// comes with ALL requests:
$server = isset($_REQUEST['server']) ? strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $_REQUEST['server'])):"";

// comes with info update from client
$info = isset($_POST['info']) ? json_decode($_POST['info'],true) : [];
$alliance = isset($_POST['alliance']) ? filter_var($_POST['alliance'],FILTER_SANITIZE_STRING) : "";
$lordName = isset($_POST['lordName']) ? filter_var($_POST['lordName'],FILTER_SANITIZE_STRING) : "";

$titles = ["","Civilian","Knight","Baronet","Baron","Viscount","Earl","Marquis","Duke","Furstin","Prinzessin"];

// below inputs only come with mapscan and war reports
$state = isset($_POST['state']) ? ucwords($_POST['state']) : "";
// only passed in along with map scan data	
$neutrals = isset($_POST['neutrals']) ? json_decode($_POST['neutrals']) : [];
// passed in with map scan data and with war reports

if (isset($_REQUEST['action'])) {
	$action = filter_var($_REQUEST['action'],FILTER_SANITIZE_STRING);
	
	if ($action == 'createDb') {
		echo "creating database for $server";
		createDb();
		die();
	}
	
	if ($action == 'launch') {
		echo "launching $server-$lordName";
		launchBot();
		die();
	}
	
}

if (isset($_POST['reports'])) {
	$reports =  explode("\n<a href='event:http://",$_POST['reports']);
	array_shift($reports);
	
	// warreports
	while ( count($reports) ) {
		$thisReport = array_shift($reports);
		/*
	ss78.evony.com/default.html?logfile/20161022/7e/d7/7ed72ef594b7dae95b41fe488e2f2032.xml'><u><font color='#4377F9'>DEF WildFire Daenerys Attack 2Winky(599,757) on Sat Oct 22 2016 10:14:16 PM from BeerBar(599,755) Daenerys to 2Winky(599,757) Mr Winky</font></u></a>
	Info: The Loyalty of this city is 97.
	attackers: s:1/1, t:2/99000
	defenders: dt:49/49, ab:0/10000
	*/
		preg_match('/(.*.xml)\'.*\>(ATT|DEF) (\w+) .* Attack (.*) on (.*) from (.*) (.*) to .*\) (.*)\<\/f.*\n(Info: The Loyalty of this city is (?<loy>.*).\n)?(attackers: (?<att>.*)\n)?(defenders: (?<def>.*))?/', $thisReport, $matches);
		
		if (isset($matches)) {
			//print_r($matches);					
			// $matches[1] = url
			// $matches[2] = type
			// $matches[3] = other alliance
			// $matches[4] = defending city
			// $matches[5] = date / time
			// $matches[6] = attacking city
			// $matches[7] = attacking lord
			// $matches[8] = defending lord	
			// $matches['loy'] = loyalty
			// $matches['att'] = attackers
			// $matches['def'] = defenders		
			if ($matches[2] == "ATT"){
				$defAlly = $matches[3];
				$attAlly = $alliance;
			} else {
				$defAlly = $alliance;
				$attAlly = $matches[3];
			}
			$troopCount=0;
			if (isset ($matches['att'])) { 
				$troops = explode(", ",$matches['att']);
				foreach($troops as $x) { $troopCount=$troopCount+$x; }
			}		
			if (in_array($matches[3],$neutrals)) {
			// its neutral - we do not need to record this, but we should probably report low loyalty....		
			echo "neutral. loyalty ".$matches['loy']."\n";
			} else {
				// it is not neutral.
				$url = matches[1];
				// only get the evonyurl link if the troop count is > 21k
				if ($troopCount > 21000) {
					$url =$matches[1];
					$result=json_decode(file_get_contents('http://ww.evonyurl.com/battle?url='.$url),true);		
					if (isset ($result['token'])) {
						$url = $result['token'];
						$exp = "";
						
					}
				}	
			}	
		}
	}
}

if (isset($_POST['info'])) {
	$info = json_decode($_POST['info'],true);
	try{
		$conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		// prepare sql and bind parameters (makes the inputs safe for database)
		$stmt = $conn->prepare("REPLACE INTO `".$server."_accounts`(email, password, lordName, title, alliance, prestige, coins, advPort, randomPort, warPort, artOfWar, excal, wealth, ardeeHero, crystalHero, marsHero, amplifier, brokenGates, endurance, fleetFeet, horde, lost, poison, romanKit, nations, onWarLvls, totalBurn, totalRes) VALUES (:email, :password, :lordName, :title, :alliance, :prestige, :coins, :advPort, :randomPort, :warPort, :artOfWar, :excal, :wealth, :ardeeHero, :crystalHero, :marsHero, :amplifier, :brokenGates, :endurance, :fleetFeet, :horde, :lost, :poison, :romanKit, :nations, :onWarLvls, :totalBurn, :totalRes);");
		$email = strtolower($info['email']);
		$stmt->bindParam(':email', $email );	
		$stmt->bindParam(':password', $info['password']);
		$stmt->bindParam(':lordName', $info['lordName']);	
		$stmt->bindParam(':title', $titles[(int)$info['title']]);	
		$stmt->bindParam(':alliance', $info['alliance']);
		$stmt->bindParam(':prestige', $info['prestige']);	
		$stmt->bindParam(':coins', $info['items']['coins']);		
		$stmt->bindParam(':advPort', $info['items']['advPort']);
		$stmt->bindParam(':randomPort', $info['items']['randomPort']);
		$stmt->bindParam(':warPort', $info['items']['warPort']);	
		$stmt->bindParam(':artOfWar', $info['items']['artOfWar']);
		$stmt->bindParam(':excal', $info['items']['excal']);	
		$stmt->bindParam(':wealth', $info['items']['wealth']);	
		$stmt->bindParam(':ardeeHero', $info['items']['ardeeHero']);		
		$stmt->bindParam(':crystalHero', $info['items']['crystalHero']);	
		$stmt->bindParam(':marsHero', $info['items']['marsHero']);
		$stmt->bindParam(':amplifier', $info['items']['amplifier']);	
		$stmt->bindParam(':brokenGates', $info['items']['brokenGates']);
		$stmt->bindParam(':endurance', $info['items']['endurance']);	
		$stmt->bindParam(':fleetFeet', $info['items']['fleetFeet']);
		$stmt->bindParam(':horde', $info['items']['horde']);		
		$stmt->bindParam(':lost', $info['items']['lost']);	
		$stmt->bindParam(':poison', $info['items']['poison']);
		$stmt->bindParam(':romanKit', $info['items']['romanKit']);	
		$stmt->bindParam(':nations', $info['items']['nations']);
		$stmt->bindParam(':onWarLvls', $info['items']['onWarLvls']);	
		$stmt->bindParam(':totalBurn', $info['totalBurn']);	
		$stmt->bindParam(':totalRes', $info['totalRes']);			
		$stmt->execute();

		//for debugging:
		echo $info['lordName']." updated to database.\n";
	}
	catch(PDOException $e)
	{
		print_r($stmt);
		echo $e->getMessage();
	}

	while ( count($info['heroes']) ) {
		$thisHero = array_shift($info['heroes']);
		try{
			$conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			// prepare sql and bind parameters (makes the inputs safe for database)
			$stmt = $conn->prepare("REPLACE INTO `".$server."_heroes`( heroId, heroName, owner, alliance, cityName, cityFieldId, lvl, ulvl, pol, att , intel, won, aow, exc ) VALUES ( :heroId, :heroName, :owner, :alliance, :cityName, :cityFieldId, :lvl, :ulvl, :pol, :att, :intel, :won, :aow, :exc);");
			
			$stmt->bindParam(':heroId', $thisHero['id']);	
			$stmt->bindParam(':heroName', $thisHero['name']);
			$stmt->bindParam(':owner', $info['lordName']);
			$stmt->bindParam(':alliance', $info['alliance']);
			$stmt->bindParam(':cityName', $thisHero['cityName']);
			$stmt->bindParam(':cityFieldId', $thisHero['cityFieldId']);
			$stmt->bindParam(':lvl', $thisHero['lvl']);
			$stmt->bindParam(':ulvl', $thisHero['ulvl']);
			$stmt->bindParam(':pol', $thisHero['pol']);
			$stmt->bindParam(':att', $thisHero['att']);
			$stmt->bindParam(':intel', $thisHero['int']);
			$stmt->bindParam(':won', $thisHero['won']);
			$stmt->bindParam(':aow', $thisHero['aow']);
			$stmt->bindParam(':exc', $thisHero['exc']);
			
			$stmt->execute();
			
			//for debugging:
			echo $thisHero['name']." hero updated to database.\n";

		}
		catch(PDOException $e)
		{
			print_r($stmt);
			echo $e->getMessage();
		}
	}

	while ( count($info['cities']) ) {
		$thisCity = array_shift($info['cities']);
		try{
			$conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			// prepare sql and bind parameters (makes the inputs safe for database)
			$stmt = $conn->prepare("REPLACE INTO `".$server."_cityStats`( fieldId, timeSlot, name, owner, alliance, prestige, coords, state, abs, tra, at, rl, tre, wo, w, s, p, sw, a, c, cata, t, b, r, cp, burn, gold, food, wood, stone, iron ) VALUES ( :fieldId, :timeSlot, :name, :owner, :alliance, :prestige, :coords, :state, :abs, :tra, :at, :rl, :tre, :wo, :w, :s, :p, :sw, :a, :c, :cata, :t, :b, :r, :cp, :burn, :gold, :food, :wood, :stone, :iron);");
			
			$stmt->bindParam(':fieldId', $thisCity['fieldId']);	
			$stmt->bindParam(':timeSlot', $thisCity['timeSlot']);	
			$stmt->bindParam(':name', $thisCity['name']);
			$stmt->bindParam(':owner', $info['lordName']);	
			$stmt->bindParam(':alliance', $info['alliance']);
			$stmt->bindParam(':prestige', $info['prestige']);	
			$stmt->bindParam(':coords', $thisCity['coords']);		
			$stmt->bindParam(':state', $thisCity['state']);	
			$stmt->bindParam(':abs', $thisCity['abs']);
			$stmt->bindParam(':tra', $thisCity['tra']);	
			$stmt->bindParam(':at', $thisCity['at']);
			$stmt->bindParam(':rl', $thisCity['rl']);	
			$stmt->bindParam(':tre', $thisCity['tre']);	
			$stmt->bindParam(':wo', $thisCity['wo']);		
			$stmt->bindParam(':w', $thisCity['w']);	
			$stmt->bindParam(':s', $thisCity['s']);
			$stmt->bindParam(':p', $thisCity['p']);	
			$stmt->bindParam(':sw', $thisCity['sw']);
			$stmt->bindParam(':a', $thisCity['a']);	
			$stmt->bindParam(':c', $thisCity['c']);
			$stmt->bindParam(':cata', $thisCity['cata']);		
			$stmt->bindParam(':t', $thisCity['t']);	
			$stmt->bindParam(':b', $thisCity['b']);
			$stmt->bindParam(':r', $thisCity['r']);	
			$stmt->bindParam(':cp', $thisCity['cp']);
			$stmt->bindParam(':burn', $thisCity['burn']);	
			$stmt->bindParam(':gold', $thisCity['gold']);
			$stmt->bindParam(':food', $thisCity['food']);	
			$stmt->bindParam(':wood', $thisCity['wood']);
			$stmt->bindParam(':stone', $thisCity['stone']);	
			$stmt->bindParam(':iron', $thisCity['iron']);			
			$stmt->execute();
			
			//for debugging:
			echo $thisCity['name']." city updated to database.\n";

		}
		catch(PDOException $e)
		{
			print_r($stmt);
			echo $e->getMessage();
		}
	}
}
function createDb() {
	try {
		global $dbhost,$dbuser,$dbpass,$dbname,$server;
		$conn = new PDO("mysql:host=$dbhost", $dbuser, $dbpass);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$sql = "CREATE DATABASE IF NOT EXISTS `".$dbname."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
		$conn->exec($sql);
		
		$sql = "use `".$dbname."`;";
		$conn->exec($sql);
		
		$sql = "CREATE TABLE IF NOT EXISTS `".$server."_accounts`( email varchar(64) PRIMARY KEY, password TEXT, lordName TEXT, title TEXT, alliance TEXT, prestige INTEGER UNSIGNED DEFAULT 0, coins INTEGER UNSIGNED DEFAULT 0, nations INTEGER UNSIGNED DEFAULT 0, onWarLvls DECIMAL(6,2) UNSIGNED DEFAULT 0, totalBurn DECIMAL(10,2) UNSIGNED DEFAULT 0, totalRes DECIMAL(10,2) UNSIGNED DEFAULT 0, advPort INTEGER UNSIGNED DEFAULT 0, randomPort INTEGER UNSIGNED DEFAULT 0, warPort INTEGER UNSIGNED DEFAULT 0, artOfWar INTEGER UNSIGNED DEFAULT 0, excal INTEGER UNSIGNED DEFAULT 0, wealth INTEGER UNSIGNED DEFAULT 0, ardeeHero INTEGER UNSIGNED DEFAULT 0, crystalHero INTEGER UNSIGNED DEFAULT 0, marsHero INTEGER UNSIGNED DEFAULT 0, amplifier INTEGER UNSIGNED DEFAULT 0, brokenGates INTEGER UNSIGNED DEFAULT 0, endurance INTEGER UNSIGNED DEFAULT 0, fleetFeet INTEGER UNSIGNED DEFAULT 0, horde INTEGER UNSIGNED DEFAULT 0, lost INTEGER UNSIGNED DEFAULT 0, poison INTEGER UNSIGNED DEFAULT 0, romanKit INTEGER UNSIGNED DEFAULT 0, stone INTEGER UNSIGNED DEFAULT 0,lastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP);";
		$conn->exec($sql);
		
		$sql = "CREATE TABLE IF NOT EXISTS `".$server."_cityStats`( fieldId INTEGER UNSIGNED PRIMARY KEY, timeSlot INTEGER UNSIGNED DEFAULT 0, name TEXT, owner TEXT, alliance TEXT, prestige INTEGER UNSIGNED DEFAULT 0, coords TEXT, state TEXT, abs INTEGER UNSIGNED DEFAULT 0, tra INTEGER UNSIGNED DEFAULT 0, at INTEGER UNSIGNED DEFAULT 0, rl INTEGER UNSIGNED DEFAULT 0, tre INTEGER UNSIGNED DEFAULT 0, wo INTEGER UNSIGNED DEFAULT 0, w INTEGER UNSIGNED DEFAULT 0, s INTEGER UNSIGNED DEFAULT 0, p INTEGER UNSIGNED DEFAULT 0, sw INTEGER UNSIGNED DEFAULT 0, a INTEGER UNSIGNED DEFAULT 0, c INTEGER UNSIGNED DEFAULT 0, cata INTEGER UNSIGNED DEFAULT 0, t INTEGER UNSIGNED DEFAULT 0, b INTEGER UNSIGNED DEFAULT 0, r INTEGER UNSIGNED DEFAULT 0, cp INTEGER UNSIGNED DEFAULT 0, burn INTEGER UNSIGNED DEFAULT 0, gold DECIMAL(13,0) UNSIGNED DEFAULT 0, food DECIMAL(13,0) UNSIGNED DEFAULT 0, wood DECIMAL(13,0) UNSIGNED DEFAULT 0, stone DECIMAL(13,0) UNSIGNED DEFAULT 0, iron DECIMAL(13,0) UNSIGNED DEFAULT 0, lastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP );";
		$conn->exec($sql);
		
		$sql = "CREATE TABLE IF NOT EXISTS `".$server."_heroes`( heroId INTEGER PRIMARY KEY, heroName TEXT, owner TEXT, alliance TEXT, cityName TEXT, cityFieldId INTEGER, lvl INTEGER, ulvl INTEGER, pol INTEGER, att INTEGER, intel INTEGER, won INTEGER, aow INTEGER, exc INTEGER, lastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP);";
		$conn->exec($sql);
		
		$sql = "CREATE TABLE IF NOT EXISTS `".$server."_map`( fieldId INTEGER PRIMARY KEY, coords TEXT, state TEXT, lordName TEXT, cityName TEXT, alliance TEXT, diplo TEXT, flag TEXT, status TEXT, prestige INTEGER, honor INTEGER, lastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP);";
		$conn->exec($sql);
		
		$sql = "CREATE TABLE IF NOT EXISTS `".$server."_reports`( url VARCHAR(64) PRIMARY KEY, attAlly TEXT, attCity TEXT, attLord TEXT, d TEXT, defAlly TEXT, defCity TEXT, defLord TEXT, eUrl TEXT, exp INTEGER, troopKills INTEGER, plunderedRes INTEGER, dateTime TIMESTAMP);";		
		$conn->exec($sql);		
		echo " success!";
		
	}

	catch(PDOException $e)
	{
		echo $sql . "\n" . $e->getMessage();
	}
	
}

// close the database connection.
$conn = null;

// for debugging:
//print_r(get_defined_vars());