<? 
// MODULE_NAME: CityStats
// MODULE_DESC: save city stats and hero stats to database
// MODULE_STATUS: Released
// MODULE_VERSION: 1.0
// 
// TECH 2015.10.16 
// 
include_once "StandardIncludes.php";

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1

header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

header('Content-Type: text/plain'); 

// make it plain text rather than html so linebreaks show correctly.

if (!isset($_SERVER['PHP_AUTH_USER'])) {
	// no user was specified, so send
    header('WWW-Authenticate: Basic realm="My NEATO"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'no PHP_AUTH_USER sent.';
    exit;
} else {
	//build your username/password check here, right now as long as a username was specified, access to the page is fully functional
	list($server,$lordName) = explode(".", $_SERVER['PHP_AUTH_USER']);
	$password = $_SERVER['PHP_AUTH_PW'];
	$DBPATH=$NEATO_DBDIR."/{$server}.db3";

	session_start();

	$_SESSION['server']=$server;
	$_SESSION['lordname']=$lordName;
	$_SESSION['password']=$password;

	if (!isset($_SESSION['count'])) {
	  $_SESSION['count'] = 0;
	} else {
	  $_SESSION['count']++;
	}

	if (!isset($_POST["city"])) {
		$city = [];
	} else {
		$city = json_decode($_POST["city"]);
	}

	

	if (!isset($_POST["items"])) {

		$items = [];

	} else {

		$items = json_decode($_POST["items"]);

	}



	if (!isset($_POST["playerInfo"])) {

		$playerInfo = [];

	} else {

		$playerInfo = json_decode($_POST["playerInfo"]);

	}

	

	if (!isset($_POST["goals"])) {

		$goals = null;

	} else {

		$goals = $_POST["goals"];

	}

	

	if (!isset($_POST["state"])) {

		$state = "?";

	} else {

		$state = $_POST["state"];

	}	

	

	if (!isset($_POST["neutrals"])) {

		$neutrals = [];

	} else {

		$neutrals = explode(",",$_POST["neutrals"]);

	}

	

	if(!file_exists($DBPATH)) {

	// database doesn't exist, create new database

		try {

			$File_DB = new PDO('sqlite:'.$DBPATH);

			$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); 

			$File_DB->exec("CREATE TABLE CityStats( id INTEGER PRIMARY KEY, name TEXT, owner TEXT, alliance TEXT, prestige INTEGER, fieldId INTEGER, coords TEXT, state TEXT, abs INTEGER DEFAULT 0, tra INTEGER DEFAULT 0, at INTEGER DEFAULT 0, rl INTEGER DEFAULT 0, tre INTEGER DEFAULT 0, wo INTEGER DEFAULT 0, w INTEGER DEFAULT 0, s INTEGER DEFAULT 0, p INTEGER DEFAULT 0, sw INTEGER DEFAULT 0, a INTEGER DEFAULT 0, c INTEGER DEFAULT 0, cata INTEGER DEFAULT 0, t INTEGER DEFAULT 0, b INTEGER DEFAULT 0, r INTEGER DEFAULT 0, cp INTEGER DEFAULT 0, burn INTEGER DEFAULT 0, gold NUMERIC DEFAULT 0, food NUMERIC DEFAULT 0, wood NUMERIC DEFAULT 0, stone NUMERIC DEFAULT 0, iron NUMERIC DEFAULT 0, lastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP );

			CREATE TABLE Goals( cityId INTEGER PRIMARY KEY, goals TEXT, lastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP);

			CREATE TABLE Heroes( id INTEGER PRIMARY KEY, name TEXT, owner TEXT, cityName TEXT, lvl INTEGER, ulvl INTEGER, pol INTEGER, att INTEGER, int INTEGER, buffEnds TEXT, lastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP );

			CREATE TABLE Items( lordName TEXT PRIMARY KEY, coins INTEGER, ardeeHeroes INTEGER DEFAULT 0, crystalHeroes INTEGER DEFAULT 0, marsHeroes INTEGER DEFAULT 0, amplifier INTEGER DEFAULT 0, brokenGates INTEGER DEFAULT 0, endurance INTEGER DEFAULT 0, fleetFeet INTEGER DEFAULT 0, lost INTEGER DEFAULT 0, poison INTEGER DEFAULT 0, romanKit INTEGER DEFAULT 0, advPort INTEGER DEFAULT 0, randomPort INTEGER DEFAULT 0, warPort INTEGER DEFAULT 0, artOfWar INTEGER DEFAULT 0, excal INTEGER DEFAULT 0, wealth INTEGER DEFAULT 0, nations INTEGER DEFAULT 0, onWarLvls FLOAT DEFAULT 0, lastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP );

			CREATE TABLE Map( fieldId INTEGER PRIMARY KEY, coords TEXT, state TEXT, lordName TEXT, cityName TEXT, alliance TEXT, diplo TEXT, flag TEXT, status TEXT, prestige INTEGER, honor INTEGER, lastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP );

			");

			// Clean up after ourselves, close the database

			$File_DB = null;

			} 

		catch(PDOException $e) {

			 // Print PDOException message

			echo "Uh oh, Scooby! Cannot create the database. ".$e->getMessage();

		}

	}



	try {

		// open the PDO database

		$File_DB = new PDO('sqlite:'.$DBPATH);	

		$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

		

		$alliance = $playerInfo->alliance; // figure this out later

		$prestige = $playerInfo->prestige; // figure this out later

		$coords = ($city->fieldId % 800).",".(int)($city->fieldId / 800);

		

		$query = "replace into CityStats ( id, name, owner, alliance, prestige, fieldId, coords, state, abs, tra, at, rl, tre, wo, w, s, p, sw, a, c, cata, t, b, r, cp, burn, gold, food, wood, stone, iron ) values ( :id, :name, :owner, :alliance, :prestige, :fieldId, :coords, :state, :abs, :tra, :at, :rl, :tre, :wo, :w, :s, :p, :sw, :a, :c, :cata, :t, :b, :r, :cp, :burn, :gold, :food, :wood, :stone, :iron );";

		
		$dbObject = $File_DB->prepare($query);

		
		$dbObject->bindParam(':id', ($city->id));
		$dbObject->bindParam(':name', ($city->name));
		$dbObject->bindParam(':owner', ($lordName));
		$dbObject->bindParam(':alliance', $alliance);
		$dbObject->bindParam(':prestige', $prestige);

		$dbObject->bindParam(':fieldId', ($city->fieldId));
		$dbObject->bindParam(':coords', $coords);
		$dbObject->bindParam(':state', $state);
		
		$dbObject->bindParam(':abs', ($city->fortification->abatis));
		$dbObject->bindParam(':tra', ($city->fortification->trap));
		$dbObject->bindParam(':at', ($city->fortification->arrowTower));
		$dbObject->bindParam(':rl', ($city->fortification->rollingLogs));
		$dbObject->bindParam(':tre', ($city->fortification->rockfall));		

		$dbObject->bindParam(':wo', ($city->troop->peasants));
		$dbObject->bindParam(':w', ($city->troop->militia));
		$dbObject->bindParam(':s', ($city->troop->scouter));
		$dbObject->bindParam(':p', ($city->troop->pikemen));
		$dbObject->bindParam(':sw', ($city->troop->swordsmen));
		$dbObject->bindParam(':a', ($city->troop->archer));
		$dbObject->bindParam(':c', ($city->troop->lightCavalry));
		$dbObject->bindParam(':cata', ($city->troop->heavyCavalry));
		$dbObject->bindParam(':t', ($city->troop->carriage));
		$dbObject->bindParam(':b', ($city->troop->ballista));
		$dbObject->bindParam(':r', ($city->troop->batteringRam));
		$dbObject->bindParam(':cp', ($city->troop->catapult));
		$dbObject->bindParam(':burn', ($city->troop->foodConsumeRate));
		
		$gold = floor($city->resource->gold);
		$food = floor($city->resource->food->amount);
		$wood = floor($city->resource->wood->amount);
		$stone = floor($city->resource->stone->amount);
		$iron = floor($city->resource->iron->amount);
		
		$dbObject->bindParam(':gold', $gold);
		$dbObject->bindParam(':food', $food);
		$dbObject->bindParam(':wood', $wood);
		$dbObject->bindParam(':stone', $stone);
		$dbObject->bindParam(':iron', $iron);

		$dbObject->execute();



		foreach ($city->herosArray as $hero) {

			$exp = $hero->experience;

			$lvl = $hero->level;
			
			$unappliedLevels = max(floor((pow((sqrt(243*pow((($lvl-1)*(($lvl-1)+1)*(2*($lvl-1)+1)/6*100)+$exp,2)-625)/(200*pow(3,(3/2)))+3*((($lvl-1)*(($lvl-1)+1)*(2*($lvl-1)+1)/6*100)+$exp)/200),(1/3))+1/(12*pow((sqrt(243*pow((($lvl-1)*(($lvl-1)+1)*(2*($lvl-1)+1)/6*100)+$exp,2)-625)/(200*pow(3,(3/2)))+3*((($lvl-1)*(($lvl-1)+1)*(2*($lvl-1)+1)/6*100)+$exp)/200),(1/3)))-1/2)+1)-$lvl,0);

			if (is_nan($unappliedLevels)) $unappliedLevels = 0;

			$buffEnds = "";

			if (isset($hero->buffs->HeroPowerBuff)) $buffEnds .= "Xcal ends ".date_format(new DateTime("@".(int)($hero->buffs->HeroPowerBuff->endTime/1000)), 'M jS Y')." ";

			if (isset($hero->buffs->HeroStratagemBuff)) $buffEnds .= "AoW ends ".date_format(new DateTime("@".(int)($hero->buffs->HeroStratagemBuff->endTime/1000)), 'M jS Y')." ";

			if (isset($hero->buffs->HeroManagementBuff)) $buffEnds .= "WoN ends ".date_format(new DateTime("@".(int)($hero->buffs->HeroManagementBuff->endTime/1000)), 'M jS Y')." ";
	
			$query ="replace into Heroes (id, name, owner, cityName, lvl, ulvl, pol, att, int, buffEnds) values (:id, :name, :owner, :cityName, :lvl, :ulvl, :pol, :att, :int, :buffEnds);";
			
			$dbObject = $File_DB->prepare($query);
		
			$dbObject->bindParam(':id', ($hero->id));

			$dbObject->bindParam(':name', ($hero->name));

			$dbObject->bindParam(':owner', ($lordName));

			$dbObject->bindParam(':cityName', ($city->name));				

			$dbObject->bindParam(':lvl', ($lvl));		

			$dbObject->bindParam(':ulvl',($unappliedLevels));				

			$dbObject->bindParam(':pol', ($hero->managementWithBuffAdded));	

			$dbObject->bindParam(':att', ($hero->powerWithBuffAdded));	

			$dbObject->bindParam(':int', ($hero->stratagemWithBuffAdded));	

			$dbObject->bindParam(':buffEnds', ($buffEnds));					

			$dbObject->execute();	

		}

		

		if (isset($items)){

					

			$onWarLvls = 0;

			$ardeeHeroes = 0;

			$crystalHeroes = 0;

			$marsHeroes = 0;

			$amplifier = 0;

			$brokenGates = 0;

			$endurance = 0;

			$fleetFeet = 0;

			$lost = 0;

			$poison = 0;

			$romanKit = 0;

			$advPort = 0;

			$randomPort = 0;

			$warPort = 0;

			$artOfWar = 0;

			$excal = 0;

			$wealth = 0;

			$nations = 0;

					

			foreach($items as $item){

				//echo "item: {$item->id} {$item->count}".PHP_EOL;

				if ($item->id == "player.box.hero.f") $ardeeHeroes = $item->count;

				if ($item->id == "player.box.hero.e") $crystalHeroes = $item->count;

				if ($item->id == "player.box.hero.d") $marsHeroes = $item->count;

				if ($item->id == "player.box.present.money.72") $amplifier = $item->count;

				if ($item->id == "player.box.present.money.77") $brokenGates = $item->count;

				if ($item->id == "player.box.present.money.71") $endurance = $item->count;

				if ($item->id == "player.box.present.money.70") $fleetFeet = $item->count;

				if ($item->id == "player.box.present.money.75") $lost = $item->count;

				if ($item->id == "player.box.present.money.76") $poison = $item->count;

				if ($item->id == "player.box.romanbuildingkit") $romanKit = $item->count;

				if ($item->id == "consume.move.1.a") $advPort = $item->count;

				if ($item->id == "consume.move.1") $randomPort = $item->count;

				if ($item->id == "consume.move.1.c") $warPort = $item->count;

				if ($item->id == "hero.intelligence.1") $artOfWar = $item->count;

				if ($item->id == "hero.power.1") $excal = $item->count;

				if ($item->id == "hero.management.1") $wealth = $item->count;

				if ($item->id == "hero.loyalty.9") $nations = $item->count;

				if ($item->id == "player.experience.1.a") $onWarLvls += (0.08)*((int)$item->count);

				if ($item->id == "player.experience.1.b") $onWarLvls += (0.3)*((int)$item->count);

				if ($item->id == "player.experience.1.c") $onWarLvls += $item->count;

			}

			

			$query ="replace into Items ( lordName, coins, ardeeHeroes, crystalHeroes, marsHeroes, amplifier, brokenGates, endurance, fleetFeet, lost, poison, romanKit, advPort, randomPort, warPort, artOfWar, excal, wealth, nations, onWarLvls ) values ( :lordName, :coins, :ardeeHeroes, :crystalHeroes, :marsHeroes, :amplifier, :brokenGates, :endurance, :fleetFeet, :lost, :poison, :romanKit, :advPort, :randomPort, :warPort, :artOfWar, :excal, :wealth, :nations, :onWarLvls );";

			

			$dbObject = $File_DB->prepare($query);

			

			$dbObject->bindParam(':lordName', $lordName);

			$dbObject->bindParam(':coins', ($playerInfo->medal));	

			$dbObject->bindParam(':ardeeHeroes',$ardeeHeroes);

			$dbObject->bindParam(':crystalHeroes',$crystalHeroes);

			$dbObject->bindParam(':marsHeroes',$marsHeroes);

			$dbObject->bindParam(':amplifier',$amplifier);

			$dbObject->bindParam(':brokenGates',$brokenGates);

			$dbObject->bindParam(':endurance',$endurance);

			$dbObject->bindParam(':fleetFeet',$fleetFeet);

			$dbObject->bindParam(':lost',$lost);

			$dbObject->bindParam(':poison',$poison);

			$dbObject->bindParam(':romanKit',$romanKit);

			$dbObject->bindParam(':advPort',$advPort);

			$dbObject->bindParam(':randomPort',$randomPort);

			$dbObject->bindParam(':warPort',$warPort);

			$dbObject->bindParam(':artOfWar',$artOfWar);

			$dbObject->bindParam(':excal',$excal);

			$dbObject->bindParam(':wealth',$wealth);

			$dbObject->bindParam(':nations',$nations);

			$dbObject->bindParam(':onWarLvls',$onWarLvls);



			$dbObject->execute();

			

		}

		

		if (isset($goals)){

					

			$query ="replace into Goals ( cityId, goals ) values ( :cityId, :goals );";

			

			$dbObject = $File_DB->prepare($query);

			

			$dbObject->bindParam(':cityId', ($city->id));

			$dbObject->bindParam(':goals', $goals);

			

			$dbObject->execute();

			

		}		

		// Clean up after ourselves, close the database

		$File_DB = null;

		

	} catch(PDOException $e) {

	    // Print PDOException message

	    echo "PDOException: {$e->getMessage()}";



		// print all the available keys for the arrays of variables

		print_r(get_defined_vars());



		exit();



	}



echo "{$lordName} ({$server}) City: {$city->name} Saved to CityStats".PHP_EOL;

}