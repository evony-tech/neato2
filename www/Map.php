<?php
setlocale(LC_ALL,'');
date_default_timezone_set("America/Chicago");

@$State = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['State']);
@$Server = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['Server']);
@$neutrals = $_POST['Neutrals'];
@$Cas = $_POST['Cas'];
@$GetDate = strtotime(Now);

$States = array("FRIESLAND","SAXONY","NORTH MARCH","BOHEMIA","LOWER LORRAINE","FRANCONIA","THURINGIA","MORAVIA","UPPER LORRAINE","SWABIA","BAVARIA","CARINTHIA","BURGUNDY","LOMBARDY","TUSCANY","ROMAGNA");

$St = array("","Peace","Truce","Barbarian","Dream Truce","Holiday");
$Diplo = array("ALLY","BLUE","GREY","RED","UNALLIED","NEUTRAL");
$timestamp = date('YmdHi',$GetDate);

$filename = "server-".$Server."_".$States[$State]."_".$timestamp;
$mapFile = 'maps/'.$filename.'.html';
$csvFile = 'csvdata/'.$filename.'.csv';
$AddData = "X,Y,State,Player,Castle,Alliance,Flag,Status,Prestige,Honor,Diplo,Picure\r\n";	
file_put_contents($csvFile,$AddData);
if(file_exists($csvFile) == False) { exit("An error occured writing to ".$csvFile."\n"); }
$headerData = '<!DOCTYPE html>
<html>
<head>
<title>Server '.$Server.' Data as of '.date('jS F Y h:i:s A (T)', $GetDate).'</title>
<style type="text/css">@import "../css/table.css";</style>
<script type="text/javascript" src="../js/table.js"></script>
</head>
<body>
<div>
<h1>Server '.$Server.' Data as of '.date('jS F Y h:i:s A (T)', $GetDate).'</h1>
<table id="mapdata" class="mapdata altstripe sort01 table-autostripe table-autosort:10 table-stripeclass:alternate2"> 
<thead> 
<tr> 
    <th>X</th> 
    <th>Y</th> 
    <th>State</th> 
    <th class="table-filterable table-sortable:default table-sortable " title="Click to sort">Player</th> 
    <th class="table-filterable table-sortable:default table-sortable " title="Click to sort">City Name</th>
	<th class="table-filterable table-sortable:default table-sortable " title="Click to sort">Alliance</th>
	<th class="table-filterable table-sortable:default table-sortable " title="Click to sort">Flag</th>
	<th class="table-filterable table-sortable:default table-sortable " title="Click to sort">Status</th>
	<th class="table-filterable table-sortable:default table-sortable " title="Click to sort">Holiday</th>
	<th class="table-filterable table-sortable:numeric table-sortable " title="Click to sort">Prestige</th>
	<th class="table-filterable table-sortable:numeric table-sortable " title="Click to sort">Honor</th>
	<th class="table-filterable table-sortable:default table-sortable table-sorted-desc" title="Click to sort">Diplo</th>
</tr> 

<tr>
    <th></th> 
    <th></th> 
    <th></th> 
	<th><input name="filter" size="10" onkeyup="Table.filter(this,this)"></th>
	<th><input name="filter" size="10" onkeyup="Table.filter(this,this)"></th>
	<th><input name="filter" size="8" onkeyup="Table.filter(this,this)"></th>
	<th><input name="filter" size="4" onkeyup="Table.filter(this,this)"></th>
	<th><select onchange="Table.filter(this,this)"><option value="">All</option><option value="Peace">Peace</option><option value="Truce">Truce</option></th>
	<th></th>
	<th></th>
	<th></th>
	<th><select onchange="Table.filter(this,this)"><option value="">All</option><option value="ALLY">ALLY</option>
	<option value="BLUE">BLUE</option>
	<option value="RED">RED</option>
	<option value="GREY">GREY</option>
	<option value="NEUTRAL">NEUTRAL</option>
	<option value="UNALLIED">UNALLIED</option>
	</th>
</tr>


</thead> 
<tbody>
';
file_put_contents($mapFile,$headerData);
if(file_exists($mapFile) == False) { exit("An error occured writing to ".$mapFile."\n"); }

$Cas = json_decode($Cas, true);
$total=0;
$allies=0;
$blues=0;
$reds=0;
$neuts=0;
$greys=0;
$unallied=0;
$holiday=0;

foreach($Cas as $key => $Ca){
	$total++;
	$hol = "FALSE";
	if ($Ca['furlough']==1) {
		$hol="TRUE";
		$holiday++;
		}
	$relation = $Ca['relation'];
	if ($relation==0) $allies++;
	if ($relation==1) $blues++;
	if ($relation==3) $reds++;

	$alliance = $Ca['allianceName'];
	if ($alliance == null) {
		// UNALLIED
		$alliance = "---"; 
		$relation = 4;
		$unallied++;
	}
	if ($relation==2) {
		if (strpos($neutrals, '"'.$Ca['allianceName'].'"')) {
			// NEUTRAL
			$relation = 5;
			$neuts++;
			} else {
			// NO DIPLO
			$greys++;
			}
	}

	
	$OutData = "<tr class>
	<td>".($Ca['id'] % 800)."</td>
	<td>".(int)($Ca['id'] / 800)."</td>
	<td>".$Ca['zoneName']."</td>
	<td>".$Ca['userName']."</td>
	<td>".$Ca['name']."</td>
	<td>".$alliance."</td>
	<td>".$Ca['flag']."</td>
	<td>".$St[$Ca['state']]."</td>
	<td>".$hol."</td>
	<td>".$Ca['prestige']."</td>
	<td>".$Ca['honor']."</td>
	<td>".$Diplo[$relation]."</td>
</tr>
";
	file_put_contents($mapFile,$OutData,FILE_APPEND);
	
	$OutData = ($Ca['id'] % 800).",";
	$OutData .= (int)($Ca['id'] / 800).",";
	$OutData .= $Ca['zoneName'].",";
	$OutData .= $Ca['userName'].",";
	$OutData .= $Ca['name'].",";
	$OutData .= $alliance.",";	
	$OutData .= $Ca['flag'].",";
	$OutData .= $St[$Ca['state']].",";
	$OutData .= $Ca['prestige'].",";
	$OutData .= $Ca['honor'].",";
	$OutData .= $Diplo[$relation].",";
	$OutData .= str_replace ("images/icon/player/","",$Ca['playerLogoUrl'])."\r\n";
	file_put_contents($csvFile,$OutData,FILE_APPEND);	
}
$footerData = "</tbody>
</table>
<p>Total Cities = $total, Allies = $allies (".round(($allies/$total)*100)."%), Blues = $blues (".round(($blues/$total)*100)."%), Reds = $reds (".round(($reds/$total)*100)."%), Neutrals = $neuts (".round(($neuts/$total)*100)."%), No-Diplo = $greys (".round(($greys/$total)*100)."%), Unallied = $unallied (".round(($unallied/$total)*100)."%), Cities in Holiday = $holiday</p>
</div>
</body>
</html>";
file_put_contents($mapFile,$footerData,FILE_APPEND);
echo "Server $Server ".$States[$State]." Map scan saved.
Total Cities = $total,
Allies = $allies (".round(($allies/$total)*100)."%),
Blues = $blues (".round(($blues/$total)*100)."%),
Reds = $reds (".round(($reds/$total)*100)."%),
Neutrals = $neuts (".round(($neuts/$total)*100)."%),
No-Diplo = $greys (".round(($greys/$total)*100)."%),
Unallied = $unallied (".round(($unallied/$total)*100)."%),
Holiday = $holiday (".round(($holiday/$total)*100)."%)\r\n";
?>