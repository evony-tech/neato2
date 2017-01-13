<?php
// MODULE_NAME: TECH's Global Scripts
// MODULE_DESC: Loads scripts which can be overridden at server/alliance/player/city level and creates text files to easily set things up.
// MODULE_STATUS: Released
// MODULE_VERSION: 1.0
// loadGlobals.php - to be used as call NEATOURL+"loadGlobals.php" {server:Config.server, lordname:player.playerInfo.userName, alliance:player.playerInfo.alliance, cityname:city.name, cityid:city.id, neatokey:Config.neatokey}
// TECH 2014.08.21 
//

$path = 'c:\neato\includes'; // change this if you dont have neato in standard location. this directory will be created if it does not exist

if (isset($_REQUEST['server'])) $server = filter_var($_REQUEST['server'],FILTER_SANITIZE_STRING);
if (isset($_REQUEST['lordname'])) $lordname = filter_var($_REQUEST['lordname'],FILTER_SANITIZE_STRING);
if (isset($_REQUEST['alliance'])) $alliance = filter_var($_REQUEST['alliance'],FILTER_SANITIZE_STRING);
if (isset($_REQUEST['cityname'])) $cityname = filter_var($_REQUEST['cityname'],FILTER_SANITIZE_STRING);
if (isset($_REQUEST['cityid'])) $cityid = filter_var($_REQUEST['cityid'],FILTER_SANITIZE_STRING);
if (isset($_REQUEST['neatokey'])) $neatokey = filter_var($_REQUEST['neatokey'],FILTER_SANITIZE_STRING); // need to enforce this

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header('Content-Type: text/plain'); // make it plain text rather than html so linebreaks show correctly.

if ( isset( $server)) {
	// create directory for that server if it doesnt exist at ..\includes\$server\
	$path .= DIRECTORY_SEPARATOR.$server;
	if (!is_dir($path.DIRECTORY_SEPARATOR.$lordname)) {
		mkdir($path.DIRECTORY_SEPARATOR.$lordname, 0777, true);
	}
	// create text-file for that server if it doesn't exist at ..\includes\$server\serverConfig.txt, containing standard configs.....
	if(!file_exists($path.DIRECTORY_SEPARATOR.'serverConfig.txt')) { 
		$fp = fopen($path.DIRECTORY_SEPARATOR.'serverConfig.txt',"w");  
		fwrite($fp,'ALLIANCE_ALERT = true // post messages to alliance chat?
AUDIO_ALERT = true // Enable Text to Speech message alerts
SKYPE_ALERT = false // enable posting messages to skype?
CHAT_ID = "yourSkypeChatIdGoesHere" // skype chat room to send alerts to
EMAIL_ALERT = false // send alerts to email
EMAIL_TO = "joeShmoe@gmail.com" // email address to send alerts to
WHISPER_ALERT = false // if set to true, will whisper all the players listed below
WHISPER_TO = ["bigdog", "killer", "JoeShmoe","Host","ViceHost"] // names of players to whisper to
MIN_FOOD = 5  // number of hours of food to issue warning at
SPAM_HEROES = "any:pol<best,att<best,int<best,lvl<100,base<80" // use any hero except your best poly, attack and intel heroes, and no heroes over lvl 100 and no high base heroes
KEEP_RESOURCES = "f:100m,w:10m,s:10m,i:10m 100m"
EXCLUDE_LIST = false // this can be set to a list of coords, like so:
//EXCLUDE_LIST = "123,456 123,457 0,0 666,666"
RES_CITY = false // change this to coords (inside quotes), this can be overridden on playerConfig.txt
');  
		fclose($fp); 
	}  

	if ( isset( $alliance)) {
		if ($alliance != "null" ) {
			// create text-file for alliance if it doesnt exist at ..\includes\$server\$alliance-Config.txt
			if(!file_exists($path.DIRECTORY_SEPARATOR.$alliance.'-Config.txt')) { 
				$fp = fopen($path.DIRECTORY_SEPARATOR.$alliance.'-Config.txt',"w");  
				fwrite($fp,'//RES_CITY = "123,456"
');  
				fclose($fp); 
		}
		}
	}
	if ( isset( $lordname)) {	
		// create text file for lordname if it doesnt exist at ..\includes\$server\$lordname\playerConfig.txt
		if(!file_exists($path.DIRECTORY_SEPARATOR.$lordname.DIRECTORY_SEPARATOR.'playerConfig.txt')) { 
			$fp = fopen($path.DIRECTORY_SEPARATOR.$lordname.DIRECTORY_SEPARATOR.'playerConfig.txt',"w");  
			fwrite($fp,'//enter any overrides here
');  
			fclose($fp); 
		}
	}
	if ( isset( $cityname)) {	
		// create text file for cityname if it doesnt exist at ..\includes\$server\$lordname\cityId-$cityId-$cityname-config.txt
		if(!file_exists($path.DIRECTORY_SEPARATOR.$lordname.DIRECTORY_SEPARATOR.'cityId-'.$cityid.'-'.$cityname.'-Config.txt')) { 
			$fp = fopen($path.DIRECTORY_SEPARATOR.$lordname.DIRECTORY_SEPARATOR.'cityId-'.$cityid.'-'.$cityname.'-Config.txt',"w");  
			fwrite($fp,'//enter any overrides here
');  
			fclose($fp); 
		}
	}	
} else {
	echo 'this file should not be called directly, it should be used in script like so:
call NEATOURL+"loadGlobals.php" {server:Config.server, lordname:player.playerInfo.userName, alliance:player.playerInfo.alliance, cityname:city.name, cityid:city.id, neatokey:Config.neatokey}
';
	exit();
}

// above section created all the config files, now for the magic

// following code loads all the different config files allowing each to be overridden on server level, alliance level, player level, and city level

echo file_get_contents($path.DIRECTORY_SEPARATOR.'serverConfig.txt').PHP_EOL;
if ($alliance != "null" ) {
	echo file_get_contents($path.DIRECTORY_SEPARATOR.$alliance.'-Config.txt').PHP_EOL;
	}
echo file_get_contents($path.DIRECTORY_SEPARATOR.$lordname.DIRECTORY_SEPARATOR.'playerConfig.txt').PHP_EOL;
echo file_get_contents($path.DIRECTORY_SEPARATOR.$lordname.DIRECTORY_SEPARATOR.'cityId-'.$cityid.'-'.$cityname.'-Config.txt').PHP_EOL;

// below are some standard script lines that will be run in ALL cities. This is like the APPEND GOALS but its actually an APPEND SCRIPT :)
?>

if city.getBuildingLevel(21) > 1 goal "build inn:2:0" // if the inn is higher level than 1 set building goals to demo it

if RES_CITY if city.coords != RES_CITY goal "keepresources {RES_CITY} {KEEP_RESOURCES}"
if EXCLUDE_LIST goal "excludelist {EXCLUDE_LIST}"
goal "spamheroes {SPAM_HEROES}"

if player.buffs.FurloughBuff != null resetgoals // if player is in holiday, cancel goals
