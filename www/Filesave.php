<?php
// MODULE_NAME: FILESAVE
// MODULE_DESC: Save data to a file
// MODULE_STATUS: Released
// MODULE_VERSION: 1.3
// filesave.php - saves your NEAT data into a file on your computer. example:
// post "http://localhost:82/filesave.php" {type:csv|log|json|xml, filename:"whatToNameIt", data:variable}
// optional arguments: server, lordname, alliance, which will effect the folder location saved to... ie:
// post "http://localhost:82/filesave.php" {type:txt|csv|json|xml, filename:"whatToNameIt", data:variable, server:Config.server, lordname:player.playerInfo.username}
// would create needed directories as well as file at http://localhost:82/{$server}/{$lordname}/{$filename}.{$type}
// TECH 2014.09.30
// updated 2015.01.22 - adding in append to file and filedate as well as xml and log file types.
// post "http://localhost:82/filesave.php" {type:"txt", filename:"worldChat", data:chatlog, server:Config.server, filedate:"now"}
// would create needed directories as well as file at http://localhost:82/{$server}/{$filedate}worldChat.{$type}

$scriptpath = 'c:\neato\www'; 
// change this if you dont have neato in standard location. this should be the web root folder, where this script is located

$types = array('csv', 'log', 'json', 'txt', 'xml');
// allowable filename extensions

$filepath = $scriptpath;
$filedate = '';
// this will be sent in as variable if so desired.

if (isset($_POST['type'])) {
	if(in_array($_POST["type"],$types)){
	$type = $_POST["type"];
	}  else {
	echo "ERROR: You must specify a valid file type: csv, txt or json.";
	exit();
	}
} else {
	echo "ERROR: You must specify the type of file you are wanting to save.";
	exit();
}
if (isset($_POST['filedate'])) {
	$filedate = date("Ymd");
	}
	
if (isset($_POST['filename'])) {
	$filename = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['filename']);
} else {
	echo "ERROR: You must specify the name of file you are wanting to save, and it must be alphanumeric.";
	exit();
}

if (isset($_POST['server'])) {
	$server = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['server']);
} else {
	$server = false;
}

if (isset($_POST['lordname'])) {
	$lordname = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['lordname']);
} else {
	$lordname = false;
}

if (isset($_POST['alliance'])) {
	$alliance = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['alliance']);
} else {
	$alliance = false;
}

if ($server) $filepath .= DIRECTORY_SEPARATOR.$server;
// if server was passed in, use a sub-folder for it

if ($filedate) $filepath .= DIRECTORY_SEPARATOR.$filedate;
// if filedate was passed in, use a sub-folder for it
// example... c:\neato\www\$server\20150131\

if ($alliance) $filepath .= DIRECTORY_SEPARATOR.$alliance;
// if alliance was passed in, use a sub-folder for it
// example... c:\neato\www\$server\20150131\$alliance\

if ($lordname) $filepath .= DIRECTORY_SEPARATOR.$lordname;
// if lordname was passed in, use a sub-folder for it 
// example... c:\neato\www\$server\20150131\$lordname\
// *NOTE* do not use BOTH alliance and lordname unless you understand the implications. 
// files saved should pertain to either lordname or alliance, not both.
// use either lordname OR alliance but not BOTH.

if (!is_dir($filepath)) {
	// the subdirectory doesn't exist, so create it...
	mkdir($filepath, 0777, true);
}

$filepath .= DIRECTORY_SEPARATOR.$filename.'.'.$type;
//  this is the full path of the filename it will save your data to

if (isset($_POST['data'])) {
	$data = $_POST['data'];
} else {
	echo "ERROR: You did not send any data.";
	exit();
}

if (isset($_POST['append'])) {
	file_put_contents($filepath,$data, FILE_APPEND | LOCK_EX);
	// saves the data to the file in append mode with exclusive file lock.
	} else {
	if(file_exists($filepath) == TRUE){ unlink($filepath); }
	// deletes the file if it already exists
	file_put_contents($filepath,$data, LOCK_EX);
	// saves the data to the file with exclusive file lock.
}

$htmlpath = str_replace($scriptpath, "", $filepath);

$htmlpath = str_replace("\\", "/", $htmlpath);

echo 'SUCCESS: Data saved to http://'.$_SERVER['HTTP_HOST'].$htmlpath;

