<?
// NEATO  (c)2013 SumRandomTechGuys
include_once "StandardIncludes.php";
include "inc/rain.tpl.class.php";

$DBPATH=$NEATO_DBDIR."/GoalSets.db3";

raintpl::configure("base_url", null);
raintpl::configure("tpl_dir", "tpl/");
raintpl::configure("cache_dir", "tmp/");

$tpl = new RainTPL;
$tpl->assign("NEATOVersion", $NEATO_VERSION);
$tpl->assign("Title", "Welcome to NEATO!");
$tpl->assign("Header", "NEATO!");
$tpl->assign("NEATO_HTTPURL", $NEATO_HTTPURL);

if (isset($_GET['phpinfo'])) {
	phpinfo();
	exit();
}

$NEATO_Modules = [];

if ($handle = opendir('.')) {

    while (false !== ($entry = readdir($handle))) {
    	if (right($entry, 4) == ".php") {
    		$file_handle = fopen($entry, "r");
    		if ($file_handle) {
    			while (($buffer = fgets($file_handle, 1024)) !== false) {
    				if (preg_match("/^\/\/ MODULE_NAME: (.*)$/", $buffer, $matches) == 1) {
                        $NEATO_Modules[$entry]['name']        = $entry;
                        $NEATO_Modules[$entry]['title']       = $matches[1];
                    } elseif (preg_match("/^\/\/ MODULE_DESC: (.*)$/", $buffer, $matches) == 1) {
                        $NEATO_Modules[$entry]['description'] = $matches[1];
                    } elseif (preg_match("/^\/\/ MODULE_STATUS: (.*)$/", $buffer, $matches) == 1) {
                        $NEATO_Modules[$entry]['status'] = $matches[1];
                    } elseif (preg_match("/^\/\/ MODULE_VERSION: (.*)$/", $buffer, $matches) == 1) {
                        $NEATO_Modules[$entry]['version'] = $matches[1];
                    }
    			}
    			if (!feof($file_handle)) {
    				echo "Error: unexpected fgets() fail\n";
    			}
    			fclose($file_handle);
    		} else {
    			echo "Could not open file: " . $entry . "<br />";
    		}
    	}
    }

    closedir($handle);
}

$tpl->assign("modulelist", $NEATO_Modules);
$tpl->draw("IndexTemplate");

//------------------
// --- FUNCTIONS ---
//------------------
function right($str, $length) {
     return substr($str, -$length);
}
?>