<?php

// warreports.php


$alliance = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['alliance']);
//$Server = preg_replace("/[^a-zA-Z0-9]+/", "", $_POST['Server']);
$neutrals = json_decode($_POST['neutrals']);

//print_r($neutrals);

$reports =  explode("\n<a href='event:http://",$_POST['reports']);
array_shift($reports);

while ( count($reports) ) {
	$thisReport = array_shift($reports);
	preg_match('/(.*.xml)\'.*\>(ATT|DEF) (\w+) .* Attack (.*) on (.*) from (.*) (.*) to .*\) (.*)\<\/f.*\n(Info: The Loyalty of this city is (?<loy>.*).\n)?(attackers: (?<att>.*)\n)?(defenders: (?<def>.*))?/', $thisReport, $matches);
	if (isset($matches)) {
		print_r($matches);
					
		// $matches[1] = url
		// $matches[2] = type
		// $matches[3] = other alliance
		// $matches[4] = defending city
		// $matches[5] = date / time
		// $matches[6] = attacking city
		// $matches[7] = attacking lord
		// $matches[8] = defending lord
		
		// $matches['loy'] = loyalty
		// $matches['att'] = attackers
		// $matches['def'] = defenders
		//echo $matches[1];
		$url ='http://ww.evonyurl.com/battle?url='.$matches[1];
		
		$result=json_decode(file_get_contents($url),true);		

//		var_dump($result);
		echo 'http://ww.evonyurl.com/'.$result['token'];
		
	}
}