<?php
// MODULE_NAME: pokeNEATO
// MODULE_DESC: maintain information about currently monitored bots and act as a method of communication between TheDirector and NEATO
// MODULE_STATUS: In development
// MODULE_VERSION: 3.333c
// pokeNEATO.php - monitors bot to server to director to neato communication/ health/ neatonote
// last update Jan 14 2017 - TECH
define("VERSION","3.333c");
/*

PokeNEATO API as of Jan 2017

==================================
To NEATO
==================================
t.server      := ProfileInfo.Server
t.username    := ProfileInfo.UserName
t.title       := ProfileInfo.Title
t.cmdline     := CmdLine
t.exe         := TheCommand
t.dirver      := DirectorVer
t.dirbeta     := DirectorBeta
t.botver      := BotVer
t.notice      := "launching" 
t.directory   := RunDirectory
t.profilename := ProfileName
t.compname    := A_ComputerName

notice is one of:
launching - the Director is about to launch - NEATO may return deny
launched - the Director ran the executable
failedlaunch - the Director tried but failed to launch
debug - Debug info to NEATO - includes msg
checkserver - Check to see if the server has been reporting problems
checkbots - Check for status of the bots (attack, foodlow, etc??)
schedulepoke - Optional regularly scheduled poke, recommend 900 or more for time, default 0
requestedpoke - Requested re-poke
running - the Director noticed the bot is now running
stopped - the Director noticed the bot is now stopped
directorexit - the Director is exiting
directorstart - the Director is starting

==================================
From NEATO (JSON)
==================================
{ 
  "action":{SOMEACTION},
  "profile":{SOMEPROFILE},
  "time":{SOMETIME},
  "msg":{MSG TO USER},
  "msgtimeout":{SOMETIME},
  "traytipmsg":{MSG TO USER},
  "field":{SOMEFIELDNAME},
  "fields":[{"profile":"xyz","field":"MaintainOn","value":"Yes"},{"profile":"xyz","field":"Alert","value":"1234"},{"profile":"xyz","field":"Cities","value":"9"}],
  "addparam":{SOMEPARAMETER},
  "neatonote":{NOTE TO ADD TO DIRECTOR}
} 

{SOMEACTION} - The ONLY required parameter
deny - denies the launch - only valid 
pause - disable the scheduler for SOMETIME
offline - NEATO is going offline - just stop poking NEATO for SOMETIME
serverhold - Don't run anything for that server for SOMETIME
pokefreq - Change the poke frequency to SOMETIME seconds
launch - Launches the profile SOMEPROFILE
stop - Stops the profile SOMEPROFILE
restart - Restarts the profile SOMEPROFILE
deleteprofile - Deletes the profile SOMEPROFILE
createprofile - Saves the data passed as an XML line

msg can be passed in to be presented to the user for SOMETIME amount of seconds

addparam can be passed if the notice is launching, this will be added (useful for proxies)
	addparam is ignored for most other notices

neatonote - must be used in conjunction with profile, adds a note to the NEATONote column

if action is neatofield:
  profile should be set to the name of the profile
  field should be set to the name of the field
  addparam should be set to the value of the field
	
if action is createprofile:
  Pass in an XML line in addparam in the format supported in the DB backup
    The universe may explode if you pass in an existing profile.  Might not, though. Don't know; don't care.

==================================
NOTES
==================================
If the Director tries but fails to poke NEATO, it will stop trying until 
the Director is restarted or the DirectorConfig is reread (iow, it changes
the object value in the Director but NOT the DB).

Scheduled NEATOPoke allows a message queue to be built up in NEATO which gets received
by the Director and processed.  Only one message may be passed per poke, though.


*/
include_once "StandardIncludes.php";
$DBPATH=$NEATO_DBDIR."/pokeNEATO3.db3";

if ($_SERVER['HTTP_HOST'] != "localhost:82") {
	echo "PokeNEATO3 is intended for use only running as localhost:82 on same computer as director is on.";
	exit();
}

$url='http://localhost:82/pokeNEATO.php';

$server = ""; // from bot and director
if (isset($_REQUEST['server'])) $server = (strtolower(filter_var($_REQUEST['server'],FILTER_SANITIZE_STRING)));

$email = ""; // from bot and director
if (isset($_REQUEST['email'])) $email = (strtolower(filter_var($_REQUEST['email'],FILTER_SANITIZE_STRING)));
if (isset($_REQUEST['username'])) $email = (strtolower(filter_var($_REQUEST['username'],FILTER_SANITIZE_STRING)));

$hash = sha1(utf8_encode($server.$email)); // hash made of server and email (actually same as each account's hash on forums)

$lordName = ""; // from bot to pokeDirector
if (isset($_REQUEST['lordName'])) $lordName = (filter_var($_REQUEST['lordName'],FILTER_SANITIZE_STRING));

$notice = ""; // from director to pokeNEATO
if (isset($_REQUEST['notice'])) $notice = (strtolower(filter_var($_REQUEST['notice'],FILTER_SANITIZE_STRING)));

$action = ""; // from bot to pokeDirector
if (isset($_REQUEST['action'])) $action = (strtolower(filter_var($_REQUEST['action'],FILTER_SANITIZE_STRING)));

$alert = ""; // from bot to pokeDirector (replaces neatonote field, sends to Alert field.)
if (isset($_REQUEST['alert'])) $alert = (filter_var($_REQUEST['alert'],FILTER_SANITIZE_STRING));

$neatonote = ""; // from bot to pokeDirector
if (isset($_REQUEST['neatonote'])) $neatonote = (filter_var($_REQUEST['neatonote'],FILTER_SANITIZE_STRING));

$time = null; // from bot to pokeDirector
if (isset($_REQUEST['time'])) $time = (filter_var($_REQUEST['time'],FILTER_SANITIZE_STRING));

$msg = null; // from bot to pokeDirector
if (isset($_REQUEST['msg'])) $msg = (filter_var($_REQUEST['msg'],FILTER_SANITIZE_STRING));

$msgtimeout = null; // from bot to pokeDirector
if (isset($_REQUEST['msgtimeout'])) $msgtimeout = (filter_var($_REQUEST['msgtimeout'],FILTER_SANITIZE_STRING));

$traytipmsg = ""; // from bot to pokeDirector
if (isset($_REQUEST['traytipmsg'])) $traytipmsg = (filter_var($_REQUEST['traytipmsg'],FILTER_SANITIZE_STRING));

$addparam = null; // from bot to pokeDirector
if (isset($_REQUEST['addparam'])) $addparam = $_REQUEST['addparam'];

$PID = ""; // the bot's processId (from director on launched) - could be usefull with bring to focus exe
if (isset($_REQUEST['pid'])) $PID = (filter_var($_REQUEST['pid'],FILTER_SANITIZE_STRING));

$profile = ""; // the director profile name for the account
if (isset($_REQUEST['profile'])) $profile= (filter_var($_REQUEST['profile'],FILTER_SANITIZE_STRING));// from bot
if (isset($_REQUEST['profilename'])) $profile= (filter_var($_REQUEST['profilename'],FILTER_SANITIZE_STRING)); // from director

		
if(!file_exists($DBPATH)) { 
// database doesn't exist, create new database
	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);
		$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); 
		$File_DB->exec("CREATE TABLE pokeNEATO(
		hash TEXT NULL PRIMARY KEY,
		profile TEXT NOT NULL,
		server TEXT NULL,
		lordName TEXT NULL,
		pid TEXT NULL,
		launched TIMESTAMP NULL,
		login TIMESTAMP NULL,
		lastUpdate TIMESTAMP NULL,
		alert TEXT NULL
		);

		CREATE TABLE pokeDirector(
		msgid INTEGER PRIMARY KEY,
		received TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		profile TEXT NULL,
		action TEXT NULL,
		fields TEXT NULL,
		time TEXT NULL,
		msg TEXT NULL,
		msgtimeout TEXT NULL,
		traytipmsg TEXT NULL,
		addparam TEXT NULL,
		neatonote TEXT NULL
		);
		
		CREATE TABLE lastMaintenance(
		server TEXT NULL PRIMARY KEY,
		lastTimestamp TIMESTAMP NULL
		);
		");
		// Clean up after ourselves, close the database
		$File_DB = null;
		} 
	catch(PDOException $e) {
		 // Print PDOException message
		echo "Uh oh, Scooby! Cannot create the database. ".$e->getMessage();
	}
}

////////////////////////////////////////
// Process requests from TheDirector ///
// and prepare JSON reply to pokes   ///
////////////////////////////////////////
$reply = array();
$reply['dbug'] = VERSION;

if (isset($profile)) $reply['profile'] = $profile;

if($notice == 'launching') {
	// this is director asking if its ok to launch a bot
	// section removed for performance 
	
	$reply['action'] = 'Launching'; // sent to let director know message was received.	
	// encode the array as JSON and send to TheDirector
	echo json_encode($reply);
	exit();	
}

if ($notice == 'launched') {
	// request coming from TheDirector (or any other bot Starter) to indicate time bot was launched
	try {
		// open the PDO database
		$File_DB = new PDO('sqlite:'.$DBPATH);	
		$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		$query = "replace into pokeNEATO ( hash, profile, server, pid, launched, login, lastUpdate  ) values 
		( :hash, :profile, :server, :pid, CURRENT_TIMESTAMP, NULL, NULL );";
		
		$dbObject = $File_DB->prepare($query);
		$dbObject->bindParam(':hash', $hash);
		$dbObject->bindParam(':profile', $profile);
		$dbObject->bindParam(':server', $server);
		$dbObject->bindParam(':pid', $PID);
		$dbObject->execute();
		
		// Clean up after ourselves, close the database
		$File_DB = null;

		// Prepare JSON reply to send to TheDirector
		//$reply['traytipmsg'] = $profile.' launched.';
		$reply['neatonote'] = 'Launched';
		
		// encode the array as JSON and send to TheDirector
		echo json_encode($reply);
		
	} catch(PDOException $e) {
	    // Print PDOException message
	    echo "Uh oh, Scooby! problem on action = 'launched' ".$e->getMessage();
	}
	exit();	
}
if ($notice == "stopping") {
	$reply['reply']="Go ahead, make my day!";
	echo json_encode($reply);
	exit();
}

if ($notice == "stopped") {
	// request coming from director, indicating director closed the bot.
	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);		
		$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		
		$dbObject = $File_DB->prepare("update pokeNEATO set pid = NULL, launched = NULL, login = NULL, lastUpdate = CURRENT_TIMESTAMP where hash = :hash");
		$dbObject->bindParam(':hash', $hash);
		$dbObject->execute();

		// Prepare reply to send to TheDirector
		$reply['neatonote'] = 'Offline';
		$reply['traytipmsg'] = $profile.' offline';
		
		// Clean up after ourselves, close the database
		$File_DB = null;	
		// encode the array as JSON and send to TheDirector
		echo json_encode($reply);
		
	} catch(PDOException $e) {
	    // Print PDOException message
	    echo "Uh oh, Scooby! problem on action 'stopping' ".$e->getMessage();
	}
	exit();	
}

if (($notice == 'schedulepoke')||($notice == 'requestedpoke')) {
	// request coming from director, requesting next unread message in the pokeDirector message queue
	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);		
		$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		
		$query = "SELECT * FROM pokeDirector ORDER BY received ASC;";
		$dbObject = $File_DB->prepare($query);
		$dbObject->execute();
		$reply = $dbObject->fetch(PDO::FETCH_ASSOC); // gets the first row of results
		
		if ($dbObject->fetch()!==false) {
			// there are more messages in the pokeDirector queue
			$reply['pokeagain']= true;
			$reply['avoidrefreshgrid']="true";
		}
		
		if ($reply['msgid'] != null) {
			$query = "delete from pokeDirector WHERE msgid = :msgid;";
			$dbObject = $File_DB->prepare($query);
			$dbObject->bindParam(':msgid', $reply['msgid']);
			$dbObject->execute();
		}
		// Clean up after ourselves, close the database
		$File_DB = null;	
		// encode the array as JSON and send to TheDirector

		if (isset($reply['fields'])){
			$reply['fields'] = json_decode($reply['fields']);
		}
		$reply = (object) array_filter((array) $reply); // strip null fields.
		
		header('Content-Type: text/plain'); // make it plain text rather than html so linebreaks show correctly.
		
		echo json_encode($reply); //
		
	} catch(PDOException $e) {
	    // Print PDOException message
	    echo "Uh oh, Scooby! problem on action 'schdeuled' or 'requested' 'poke' ".$e->getMessage();
	}
	exit();	
}

if($notice == 'running') {
	// section disabled for performance
	
	$reply['reply'] = 'Running';
		
	// encode the array as JSON and send to TheDirector
	echo json_encode($reply);
	exit();	
}

if($notice == 'directorexit') {
	//Director just poked me to say it stopped

	$reply['traytipmsg'] = 'Goodbye Director';
	echo json_encode($reply);
	exit();			
}

if($notice == 'directorstart') {
	//Director just poked me to say it started

	$reply['traytipmsg'] = 'PokeNEATO '.VERSION.' says Hi Director!';
	echo json_encode($reply);
	exit();	
}

/////////////////////////////////
/// REQUESTS COMING FROM NEAT ///
/////////////////////////////////

if ($action == 'login') {
	// request coming from bot, time bot actually logged in is updated here.
	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);		
		$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		
		$query ="select profile from pokeNEATO where hash = :hash;";
		$dbObject = $File_DB->prepare($query);	
		$dbObject->bindParam(':hash', $hash);
		$dbObject->execute();		
		$row = $dbObject->fetch();
		$profile = $row[0];
				
		$query = "update pokeNEATO set lordname = :lordName, login = CURRENT_TIMESTAMP, lastUpdate = CURRENT_TIMESTAMP where hash = :hash;";
		$dbObject = $File_DB->prepare($query);
		$dbObject->bindParam(':lordName', $lordName);
		$dbObject->bindParam(':hash', $hash);
		$dbObject->execute();
		
		$fixedProfile = $lordName.' - '.$server;
		$traytip = $fixedProfile.' now On-line.';
		
		$query ="insert into pokeDirector ( profile, neatonote, traytipmsg ) values ( :profile, 'On-line', :traytip );";
		$dbObject = $File_DB->prepare($query);
		$dbObject->bindParam(':profile', $profile);
		$dbObject->bindParam(':traytip', $traytip);
		$dbObject->execute();
					
		// Clean up after ourselves, close the database
		$File_DB = null;
		echo "PokeNEATO ".VERSION." : SUCCESSFULL LOGIN to $server for account $lordName";
	} catch(PDOException $e) {
	    // Print PDOException message
	    echo "PokeNEATO ".VERSION." : Uh oh, Scooby! problem on action = $action
		profile:$profile
		query:$query
		".$e->getMessage();
	}
	exit();	
}

if ($action == 'update') {
	// request coming from bot - old method supplies neatonote - still works this way but gets appended to the Alert field
	// if you send "alert":"blah" it will replace current alert message.
	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);		
		$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		
		if ($email == "") {
			
			// email not passed in, attempt to find director profile based on lord name and server.
				
			$query ="select profile, alert from pokeNEATO where lordName = :lordname and server = :server;";
			$dbObject = $File_DB->prepare($query);	
			$dbObject->bindParam(':lordname', $lordName);
			$dbObject->bindParam(':server', $server);
			$dbObject->execute();
			
			$row = $dbObject->fetch();
			$profile = $row[0];
			$alert = $row[1];

			} else {			
		
			$query ="select profile, alert from pokeNEATO where hash = :hash;";
			$dbObject = $File_DB->prepare($query);	
			$dbObject->bindParam(':hash', $hash);
			$dbObject->execute();
		
			$row = $dbObject->fetch();
			$profile = $row[0];
			$alert = $row[1];
		}
		if ($alert == "") {
			if (substr($neatonote,0,1)==':'){
				// the string begins with : so we will erase what was in the neatonote field completely with new information minus the leading colon.
				$neatonote = substr($neatonote,1); // trim the colon off.
			} else {
				// append the incoming update to existing alert
				$neatonote = $alert.' '.$neatonote;
			}
		} else {
			$neatonote = $alert; // if alert is passed in rather than 
		}
		$query = "update pokeNEATO set lastUpdate = CURRENT_TIMESTAMP, alert = :alert where hash = :hash";
		$dbObject = $File_DB->prepare($query);
		$dbObject->bindParam(':hash', $hash);
		$dbObject->bindParam(':alert', $neatonote);
		$dbObject->execute();	
		
		$action = "updateprofile";
		$addparam = "Alert=".$neatonote;
		
		// Clean up after ourselves, close the database
		$File_DB = null;
	
	} catch(PDOException $e) {
		// Print PDOException message
		echo "PokeNEATO ".VERSION." : Uh oh, Scooby! problem on action = $action
		profile:$profile
		query:$query
		".$e->getMessage();
		exit();
	}
}	

if ($action == 'updateprofile') {
	// request coming from bot, indicating neato wants director to update multiple field in specified
	// server and lord name. requires "addparam" variable specifying fields in following format:
	
	// {server:"123",lordName:"TECH",action:"updateprofile",addparam:"Notes=1 bad mofo,MaintainOn=Yes,NEATONote=TECH,anotherColumn=whatever",t:date().time}
	
	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);		
		$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);		
	
		if ($profile == "") {
			// find director profile based on lord name and server.
		
			$query ="select profile from pokeNEATO where lordName = :lordname and server = :server;";
			$dbObject = $File_DB->prepare($query);	
			$dbObject->bindParam(':lordname', $lordName);
			$dbObject->bindParam(':server', $server);
			$dbObject->execute();			
			$row = $dbObject->fetch();
			$profile = $row[0];
		}
		
		$stuff = explode(",",$addparam);
		
		$fields = "[";
		
		foreach ($stuff as $field){
			$thisField=explode("=",$field);
			if (isset($thisField[0])&&isset($thisField[1])) $fields.='{"profile":"'.$profile.'", "field": "'.$thisField[0].'", "value": "'.$thisField[1].'"},';
		}
		
		$fields = substr($fields, 0, -1)."]"; // trim off the last comma and close the array brace

		$query ="insert into pokeDirector ( profile, action, fields) values ( :profile, 'neatofield', :fields);";
		$dbObject = $File_DB->prepare($query);
		$dbObject->bindParam(':profile', $profile);
		$dbObject->bindParam(':fields', $fields);
		$dbObject->execute();

		// Clean up after ourselves, close the database
		$File_DB = null;
		
		// let calling script know everything is fine.
		echo "PokeNEATO ".VERSION." : SUCCESSFULLY POSTED $profile $action $fields to pokeDirector!";

	} catch(PDOException $e) {
	    // Print PDOException message
	    echo "PokeNEATO ".VERSION." : Uh oh, Scooby! problem on action = $action
		profile:$profile
		query:$query
		".$e->getMessage();
	}
	exit();	
}

if ($action == 'createprofile') {
	// request coming from bot - requires action createprofile and addparam containing xml account profile import line.
	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);		
		$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);		
		
		$query ="insert into pokeDirector ( action, addparam) values ( 'createprofile', :addparam);";
		$dbObject = $File_DB->prepare($query);

		$dbObject->bindParam(':addparam', $addparam);
		$dbObject->execute();
		
		// Clean up after ourselves, close the database
		$File_DB = null;
		
		// let calling script know everything is fine.
		echo "PokeNEATO ".VERSION." : SUCCESSFULLY POSTED $action $addparam to pokeDirector!";
		
	} catch(PDOException $e) {
	    // Print PDOException message
	    echo "PokeNEATO ".VERSION." : Uh oh, Scooby! problem on action = $action
		profile:$profile
		query:$query
		".$e->getMessage();
	}
	exit();	
}

if (($action == 'launch')||($action == 'stop')||($action == 'restart')||($action == 'deleteprofile' )) {
	// request coming from bot to stop, start, restart or delete a profile - requires lordname and server
	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);		
		$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);			

		// find director profile based on lord name and server.			
		$query ="select profile from pokeNEATO where lordName = :lordname and server = :server;";
		$dbObject = $File_DB->prepare($query);	
		$dbObject->bindParam(':lordname', $lordName);
		$dbObject->bindParam(':server', $server);
		$dbObject->execute();		
		$row = $dbObject->fetch();
		$profile = $row[0];	
			
		$query ="insert into pokeDirector (profile, action) values (:profile, :action);";
		$dbObject = $File_DB->prepare($query);
		$dbObject->bindParam(':profile', $profile);
		$dbObject->bindParam(':action', $action);
		$dbObject->execute();
	
		// Clean up after ourselves, close the database
		$File_DB = null;
		
		// let calling script know everything is fine.
		echo "PokeNEATO ".VERSION." : SUCCESSFULLY POSTED $action $profile to pokeDirector! ";
		//" (lordName: $lordName server: $server)";

	} catch(PDOException $e) {
	    // Print PDOException message
	    echo "PokeNEATO ".VERSION." : Uh oh, Scooby! problem on action = $action
		profile:$profile
		query:$query
		".$e->getMessage();
	}
	exit();	
}


if ($action == 'pause') {
	// request coming from bot - requires action createprofile and addparam containing xml account profile import line.
	try {
		$File_DB = new PDO('sqlite:'.$DBPATH);		
		$File_DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);		
		
		$query ="insert into pokeDirector ( action, time) values ( 'pause', :time);";
		$dbObject = $File_DB->prepare($query);

		$dbObject->bindParam(':time', $time);
		$dbObject->execute();
		
		// Clean up after ourselves, close the database
		$File_DB = null;
		
		// let calling script know everything is fine.
		echo "PokeNEATO ".VERSION." : SUCCESSFULLY POSTED $action $time to pokeDirector!";
		
	} catch(PDOException $e) {
	    // Print PDOException message
	    echo "PokeNEATO ".VERSION." : Uh oh, Scooby! problem on action = $action
		time:$time
		query:$query
		".$e->getMessage();
	}
	exit();	
}


////////////////////////////////////
/// REQUESTS COMING FROM BROWSER ///
////////////////////////////////////

?><html>
<head>
<title>pokeNEATO <?php echo VERSION;?></title>
<link href="./css/neato.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div><h1>pokeNEATO  <?php echo VERSION;?></h1>
<p>To utilize pokeNEATO you need to have the following line in your AutoRunScript.txt file:</p>
<pre>if !city.timeSlot get "<?=$url;?>" {action:"login",server:Config.server,email:player.playerInfo.accountName,t:date().time}</pre>

<p>The message in the "NEATONote" column can be whatever you want it to be. These are just examples. To send an update message back to NEATO/Director, use the following parameters:  </p>
<pre>get "<?=$url;?>" {action:"update",server:Config.server,email:player.playerInfo.accountName,neatonote:"Incoming Attacks",t:date().time}</pre>
<pre>get "<?=$url;?>" {action:"update",server:Config.server,email:player.playerInfo.accountName,neatonote:"Broken Gates",t:date().time}</pre>
<p>If you prefix the neatonote value with a colon like ":WARNING" it will erase the contents of the NEATONote column, otherwise it appends to it. It will get wiped out when director marks bot as closed or launches it again.</p>

<pre>get "<?=$url;?>" {action:"pause",time:900,t:date().time}</pre>
<p>Above tells Director to pause launching bots for specified amount of time in seconds.</p>

<pre>get "<?=$url;?>" {action:"stop",server:"123",lordName:"TECH",t:date().time}</pre>
<p>Above uses action:"stop" and requires server and lord name. Tells director to kill that bot instance (it may relaunch it if the account is set to MaintainOn = Yes). You can issue the following "action" requests to pokeNEATO which it will relay to TheDirector: "stop","start","restart","deleteprofile" all require server and lordName arguments.</p>

<p>You can use action:"createprofile" and pass in "addparam" variable with an xml string like so: 
<pre>get "<?=$url;?>" {action:"createplayer",addparam:'< Account Name="ABC - 123" MaintainOn="Yes" UserName="abc123@gmail.com" Password="passw0rd" Server="123" Title="ABC - 123" WindowState="Restored" SortOrder="123" Notes="Blah Blah" CreatedDate="2017-01-10 04:20:00" SpecificExe="" ProfileParams="-password passw0rd" />',t:date().time}</pre>

<pre>get "<?=$url;?>" {action:"updateprofile",server:Config.server,lordName:"TECH",addparam:"MaintainOn=On,Alert=Blah Blah Blah",t:date().time}</pre>
<p>If "action" is "updateprofile", you must also specify "server" and "lordName" of the profile in director that you want to modify, as well as "addparam" which should contain "field=value,field2=value,etc".</p>

</div>
</body>
</html>
