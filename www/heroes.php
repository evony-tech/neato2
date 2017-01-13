<?php
setlocale(LC_ALL,'');
date_default_timezone_set("America/Chicago");

@$Page = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['Page']);
@$Server = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['Server']);

@$Heroes = str_replace(array("\r\n", "\r"), "\n", $_POST['Heroes']);
$Heroes = str_replace('"','',$Heroes);
$Heroes = array_slice(explode("\n", $Heroes),2);

@$GetDate = strtotime(Now);
$timestamp = date('Y-m-d',$GetDate);

$filename = "server-".$Server."_Heroes_".$timestamp;
$heroFile = 'maps/'.$filename.'.html';

$headerData = '<!DOCTYPE html>
<html>
<head>
<title>Server '.$Server.' Hero Data as of '.date('jS F Y h:i:s A (T)', $GetDate).'</title>
<style type="text/css">@import "../css/table.css";</style>
<script type="text/javascript" src="../js/table.js"></script>
</head>
<body>
<div>
<h1>Server '.$Server.' Hero Data as of '.date('jS F Y h:i:s A (T)', $GetDate).'</h1>
<table id="mapdata" class="mapdata altstripe table-autosort table-autostripe table-stripeclass:alternate2"> 
<thead> 
<tr>
	<th class="table-filterable table-sortable:numeric table-sortable" title="Click to sort">Rank</th>
	<th class="table-filterable table-sortable:default table-sortable" title="Click to sort">Name</th>
	<th class="table-filterable table-sortable:default table-sortable" title="Click to sort">Owner</th>
	<th class="table-filterable table-sortable:numeric table-sortable" title="Click to sort">Level</th>	
	<th class="table-filterable table-sortable:numeric table-sortable" title="Click to sort">Pol</th>
	<th class="table-filterable table-sortable:numeric table-sortable" title="Click to sort">Atk</th>
	<th class="table-filterable table-sortable:numeric table-sortable" title="Click to sort">Int</th>
</tr> 
<tr>
    <th></th>
    <th></th>
	<th><input name="filter" size="10" onkeyup="Table.filter(this,this)"></th>
	<th></th>
	<th></th>
	<th></th>
	<th></th>
</tr>
</thead> 
<tbody>
';

$OutData = "";

if ($Page == "1") {
	if(file_exists($heroFile)) unlink($heroFile);
	file_put_contents($heroFile,$headerData);
}
if(file_exists($heroFile) == False) { exit("An error occured writing to ".$heroFile."\n"); }

foreach($Heroes as $hero) {
	$thisHero = explode(",",$hero);
	$OutData .= "<tr><td>".$thisHero[0]."</td><td>".$thisHero[1]."</td><td>".$thisHero[2]."</td><td>".$thisHero[3]."</td><td>".$thisHero[6]."</td><td>".$thisHero[4]."</td><td>".$thisHero[5]."</td></tr>\r\n";
}
file_put_contents($heroFile,$OutData,FILE_APPEND);
if ($Page == "10") {
	$footerData = "</tbody></table></div></body></html>";
	file_put_contents($heroFile,$footerData,FILE_APPEND);
	echo "Server $Server Heroes saved.\r\n";
}
?>