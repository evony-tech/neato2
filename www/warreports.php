<?php

// warreports.php

include_once "StandardIncludes.php";

$DBPATH="../db/WarReports.db3";

$NEATOlert='..\NEATOlert.exe';
//$chatid='#wildfire-ss48/$5d8f2a1f28ac0f0';
$chatid="#evony.tech/$44750d86584caa69";


$Alliance = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['Alliance']);
$Server = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['Server']);
$Neutrals = $_POST['Neutrals'];
$Reports = preg_split("/\r\n|\n|\r/", $_POST['Reports']);
$countRead=0;
$lastTimestamp = false;

try {
	$File_DB = new PDO('sqlite:'.$DBPATH);
	$result= $File_DB->query ("select timestamp,url from 'defenseReports' where server ='$Server' and toAlliance='$Alliance' order by timestamp desc limit 1");
	if ($result != null) {
	foreach($result as $row) {
		if (isset($row['timestamp'])) {
			$lastTimestamp = $row['timestamp'];
			$lastURL = $row['url'];
		}
	}
	}
	foreach ($Reports as $rpt) {

		if (strpos($rpt,"event:")) {
			
			list($junk,$line) = explode("a href='",$rpt);
			$line=str_replace("event:","",$line);
			$line=str_replace("</font></u></a>","",$line);	
			$line=str_replace(" New city"," NewCity",$line);
			$line=str_replace(" City name"," CityName",$line);
			$line=str_replace(" on "," ",$line);
			$line=str_replace(" from "," ",$line);
			$line=str_replace(" to "," ",$line);
			$line=str_replace("  "," ",$line);
			$line=str_replace("'><u><font color='#4377F9'>","|",$line);
			list($url,$data) = explode("|",$line);
			$url=filter_var($url, FILTER_SANITIZE_URL);
			$data= str_replace("'",'',$data); 
			$data= str_replace('"','',$data);
			list($d1,$d2)= explode(" Attack ",$data);	
			list($ATTorDEF,$ally,$a) = explode(" ",$d1);	
			list($target,$day,$month,$date,$year,$time,$AMorPM,$from,$attacker,$t,$defender) = explode(" ",$d2);
			list($hours,$minutes,$seconds)=explode(":",$time);
			if (($AMorPM == "PM")&&($hours<>12)) $hours=$hours+12;
			$timestamp = "$year-$month-$date $hours:$minutes:$seconds";
			
			if ($lastTimestamp) {
				if (($lastTimestamp==$timestamp)&&($lastURL==$url)) {
					$File_DB = null;
					die("All reports have been logged!");
					exit();
				}
			}
			
			if ($a<>$attacker) {

			$attacker=$a;
			$ally="NONE"; 
			}
			

echo "
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--==--=-=-=-=-=- 
DEBUG OUTPUT... 
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--==--=-=-=-=-=- 

rpt\t=$rpt

-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--==--=-=-=-=-=-
";
/*
line\t=$line
url\t=$url
data\t=$data
d1\t=$d1
d2\t=$d2
ATTorDEF\t=$ATTorDEF
ally\t\t=$ally
a\t\t=$a
target\t=$target
day\t\t=$day
month\t=$month
date\t\t=$date
year\t\t$year
time\t\t=$time
AMorPM\t=$AMorPM
from\t\t=$from
attacker\t=$attacker
t\t\t=$t
defender\t=$defender
*/

			if ($ATTorDEF=="DEF") {
				if (strpos($Neutrals, '"'.$ally.'"') >= 0) {
					
					echo "attack from ally.\n";
					// attack is from ally
					// discard this report

				} else {
					// attack is from enemy... save to db	
					$query = "insert into defenseReports (server, timestamp, url, fromAlliance, fromPlayer, fromLocation, toAlliance, toPlayer, toLocation )
								values (:server, :timestamp, :url, :fromAlliance, :fromPlayer, :fromLocation, :toAlliance, :toPlayer, :toLocation)";	
					$dbObject = $File_DB->prepare($query);
					$dbObject->bindParam(':server',$Server);
					$dbObject->bindParam(':timestamp',$timestamp);
					$dbObject->bindParam(':url',$url);
					$dbObject->bindParam(':fromAlliance',$ally);
					$dbObject->bindParam(':fromPlayer',$attacker);
					$dbObject->bindParam(':fromLocation',$from);
					$dbObject->bindParam(':toAlliance',$Alliance);
					$dbObject->bindParam(':toPlayer',$defender);
					$dbObject->bindParam(':toLocation',$target);
					$dbObject->execute();		

					$message = "$day $time $AMorPM $attacker $ally @ $from Attacked $defender ($Alliance) @ $target $url";
					if (strtotime("$date $month $year $time -0500") > strtotime("now-4 hours")) {
						if (checkReport("defenseReports",$url,$File_DB)==0){
							$command = "$NEATOlert $chatid \"$message\"";
							$output=shell_exec($command);
						} 
					} else {

							die("All reports have been logged!");

					}				
				}
			} else {
				// attack report.... save maybe ?? (  if its not attack on neutrals)
			}
			
		} else {
			list($type,$desc) = explode(':',$rpt);
			if ($type=="Info") {
				$loyalty=str_replace(" The Loyalty of this city is ","",$desc);
				$loyalty=str_replace(".","",$loyalty);
				if ($loyalty < 60) {
					if (strpos($Neutrals, '"'.$ally.'"') >= 0) {
						if ($ATTorDEF=="ATT") {
							
						// farm city is getting low on loyalty, send report to  db for farm alts needing to be logged
						
						$query = "insert into farmingReports (server, timestamp, url, fromAlliance, fromPlayer, fromLocation, toAlliance, toPlayer, toLocation, loyalty ) values (:server, :timestamp, :url, :fromAlliance, :fromPlayer, :fromLocation, :toAlliance, :toPlayer, :toLocation, :loyalty)";
						$dbObject = $File_DB->prepare($query);
						$dbObject->bindParam(':server', $Server);
						$dbObject->bindParam(':timestamp',$timestamp);
						$dbObject->bindParam(':url',$url);
						$dbObject->bindParam(':fromAlliance',$Alliance);
						$dbObject->bindParam(':fromPlayer',$attacker);
						$dbObject->bindParam(':fromLocation',$from);
						$dbObject->bindParam(':toAlliance',$ally);
						$dbObject->bindParam(':toPlayer',$defender);
						$dbObject->bindParam(':toLocation',$target);
						$dbObject->bindParam(':loyalty',$loyalty);
						$dbObject->execute();					
						$message = "LOW LOYALTY ($loyalty) on $defender $ally @ $target $day $time $AMorPM $month-$date";
						if (strtotime("$date $month $year $time -0500") > strtotime("now-1 days")) {
							if (checkReport("farmingReports",$url,$File_DB)==0){
								$command = "$NEATOlert $chatid \"$message\"";
								$output=shell_exec($command);
							} 
						} else {
							die("All reports have been logged!");
							exit();	
						}
						}
					}
				}
			}
		}
	}
// Clean up after ourselves, close the database
$File_DB = null;
} catch(PDOException $e) {
	// Print PDOException message
	echo "Uh oh, Scooby! ".$e->getMessage();
}


function checkReport($table,$url,$DB){
	$result= $DB->query("select skypeAlertSent from '$table' where url ='$url'");
	$skypeAlertSent=0;
	foreach($result as $row) {
		if (isset($row['skypeAlertSent'])) 	$skypeAlertSent = $row['skypeAlertSent'];
		}
	if ($skypeAlertSent==0) {
		$query="update '$table' set skypeAlertSent = 1 where url = '$url'";
		$dbObject = $DB->prepare($query);
		$dbObject->execute();
	}
	return $skypeAlertSent;
}
