<?
$NEATO_VERSION="00.01.00.12";
$NEATO_UPDATEURL="http://sumrandomguy.com/NEATO_update";
$NEATO_DBDIR="../db/";
$NEATO_TMPDIR="../tmp";
$NEATO_CSSDIR="./css";
$NEATO_NEATSCRIPTSDIR="./scripts";
$NEATO_DB=$NEATO_DBDIR."\SumRandomTechGuys.db3";
$NEATO_HTTPURL='http://'.$_SERVER['HTTP_HOST']."/";

function ShowHeader($TheTitle) {
	global $NEATO_VERSION;
?>	<html>
	<head>
		<title><?=$TheTitle;?></title>
		<link href="./css/neato.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<div id="header">
			<h1><?=$TheTitle;?></h1>
		</div>
		<div id="version">
		<p>Provided by SumRandomTechGuys<br>
		MarketPrices.php version: 20130701<br>
		NEATO Version: <?=$NEATO_VERSION;?></p>
		</div>
		<div id="main">
<?
}

function ShowFooter() {
	global $NEATO_VERSION;
?>	<div id="footer">
		<p>Visit <a href="http://sumrandomguy.com">SumRandomGuy's website</a></p>
		<p><a href="index.php">Go back to the main page</a></p>
		<p>NEATO Version: <?=$NEATO_VERSION?></p>
	</div>
	</body>
	</html>
<?	
}	

?>