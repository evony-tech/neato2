<? 
// MODULE_NAME: NEATOlert
// MODULE_DESC: Send alerts to Skype
// MODULE_STATUS: Released
// MODULE_VERSION: 1
// NEATOlert.php (c)2013 SumRandomTechGuys
include_once "StandardIncludes.php";
include "inc/rain.tpl.class.php";

raintpl::configure("base_url", null);
raintpl::configure("tpl_dir", "tpl/");
raintpl::configure("cache_dir", "tmp/");

$tpl = new RainTPL;
$tpl->assign("NEATOVersion", $NEATO_VERSION);
$tpl->assign("Title", "NEATOlert");
$tpl->assign("Header", "NEATO Skype Alerter");
$tpl->assign("NEATO_HTTPURL", $NEATO_HTTPURL);

$command='..\NEATOlert.exe';
$chatidfile='..\chatid.txt';
$chatid='';

if (isset($_REQUEST['msg'])) {
	$message = $_REQUEST['msg'];
	if (isset($_REQUEST['cid'])) {	
		$chatid=urldecode($_REQUEST['cid']);
		if(file_exists($chatidfile) == TRUE){ unlink($chatidfile); }
		file_put_contents($chatidfile,$chatid);
	} else { 
		$chatid = trim(file_get_contents($chatidfile));
	}
	$command .= ' '.$chatid.' "'.$message.'"';
	$output=shell_exec($command);
	echo $output;
	exit();
} else {
	$output=shell_exec($command);
	list($junk, $chatid) = explode(':', $output);
	$chatid=trim($chatid);
	if(substr($chatid, -1, 1) == ',') $chatid = substr($chatid, 0, -1);
	if(file_exists($chatidfile) == TRUE) unlink($chatidfile);
	file_put_contents($chatidfile,$chatid);
}
$chatidlen = strlen($chatid);
$tpl->assign("chatid", $chatid);
$tpl->assign("message", $message);
$tpl->draw("NEATOlertTemplate");
?>