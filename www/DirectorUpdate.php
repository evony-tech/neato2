<?php
// MODULE_NAME: DirectorUpdate
// MODULE_DESC: Update Director Database for PokeNEATO
// MODULE_STATUS: In development
// MODULE_VERSION: 0.1
// 

$directorPath = 'c:\neato\db\DirectorConfig.db';
$title = "Director Update";
$body = "<p>This module will update your director database for use with PokeNEATO. This means adding some columns to the profile table, as well as a few other modifications. To use this function, place a COPY of your DirectorConfig.db file in c:\\neato\\db and refresh this page.</p>";

if(file_exists($directorPath)){
	
	$body = "Beginning update process....";
	
	$pdo = new PDO('sqlite:'.$directorPath);
	$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); 
	try {   

	/* 
	
	not essential:
	
		$tableList = array();
        $query = "SELECT name FROM sqlite_master WHERE type='table';";
		$result = $pdo->query($query);
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tableList[] = $row[0];
        }
        $body = "<p>tables:".json_encode($tableList)."</p>";   

		// below loads settings into array //
		$query = "SELECT SettingName, Value FROM settings";
		$settings = $pdo->query($query)->fetchAll(PDO::FETCH_KEY_PAIR);	
		$body .= "<p>Current director settings:".str_replace(",",", ",json_encode($settings))."</p>";
		// above was for debugging. shows all $settings are imported to array... you can access like $settings['PokeNEATO']
*/
		
		// basic stuff - drop the debug db and rebuild it, and turn debugging off
		$query = "drop table if exists debuginfo;";	
		$query.="CREATE TABLE debuginfo (debug_id INTEGER PRIMARY KEY, logdatetime, message);";
		$query.="update settings set Value = 0 where SettingName = 'DebugToDB';";

		// enable pokeNEATO
		$query.= "update settings set Value='http://localhost:82/pokeNEATO.php' where SettingName = 'LauncherNEATOPokeURL';";
		$query.= "update settings set Value=1 where SettingName = 'PokeNEATO';";		
	
		// makes director easier to use (for me):
		$query.= "update settings set Value=1 where SettingName = 'BringToForeMaximized';"; 
		
		// this boosts performance by disabling the constant memory and cpu checks
		$query.= "update settings set Value=0 where SettingName = 'WantCPUAndMemoryStatus';"; 

		$pdo->exec($query);
		$body .= $query;
		
		$query = "SELECT SettingName, Value FROM settings";
		$settings = $pdo->query($query)->fetchAll(PDO::FETCH_KEY_PAIR);		
		$body .= "<p>New director settings:".str_replace(",",", ",json_encode($settings))."</p>";

		$query = "SELECT * FROM profiles LIMIT 1;"; // this fails if the profiles table is empty.
		$result = $pdo->query($query);
		for ($i = 0; $i < $result->columnCount(); $i++) {
			$col = $result->getColumnMeta($i);
			$profileFields[] = $col['name'];
		}
		$body.="<p>ProfileFields:".str_replace(",",", ",json_encode($profileFields))."</p>";
		
		$query="";
		if (!array_search("AdvPorts",$profileFields)) {
			$body.="<p>No AdvPorts field found. Adding.</p>";
			$query.="alter table profiles add column AdvPorts;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('AdvPorts','40','center text','AdvPorts');";
			}
			
		if (!array_search("Alert",$profileFields)) {
			$body.="<p>No Alert field found. Adding.</p>";
			$query.="alter table profiles add column Alert;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Alert','240','left text','Alert');";
			}
			
		if (!array_search("Alliance",$profileFields)) {
			$body.="<p>No Alliance field found. Adding.</p>";
			$query.="alter table profiles add column Alliance;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Alliance','40','center text','Alliance');";
			}

		if (!array_search("Amps",$profileFields)) {
			$body.="<p>No Amps field found. Adding.</p>";
			$query.="alter table profiles add column Amps;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Amps','40','center text','Amps');";
			}
			
		if (!array_search("Ammys",$profileFields)) {
			$body.="<p>No Ammys field found. Adding.</p>";
			$query.="alter table profiles add column Ammys;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Ammys','40','center text','Ammys');";
			}	
			
		if (!array_search("Banners",$profileFields)) {
			$body.="<p>No Banners field found. Adding.</p>";
			$query.="alter table profiles add column Banners;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Banners','40','center text','Banners');";
			}				
			
		if (!array_search("BG",$profileFields)) {
			$body.="<p>No BG (Broken Gates) field found. Adding.</p>";
			$query.="alter table profiles add column BG;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('BG','40','center text','BG');";
			}
			
		if (!array_search("Chests",$profileFields)) {
			$body.="<p>No Chests field found. Adding.</p>";
			$query.="alter table profiles add column Chests;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Chests','40','center text','Chests');";
			}
			
		if (!array_search("Cities",$profileFields)) {
			$body.="<p>No Cities field found. Adding.</p>";
			$query.="alter table profiles add column Cities;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Cities','40','center text','Cities');";
			}	
									
		if (!array_search("Coins",$profileFields)) {
			$body.="<p>No Coins field found. Adding.</p>";
			$query.="alter table profiles add column Coins;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Coins','40','center text','Coins');";
			}	

		if (!array_search("EOTI",$profileFields)) {
			$body.="<p>No EOTI (Endurance of the Immortals) field found. Adding.</p>";
			$query.="alter table profiles add column EOTI;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('EOTI','40','center text','EOTI');";
			}	
			
		if (!array_search("FF",$profileFields)) {
			$body.="<p>No FF (Fleet Feet) field found. Adding.</p>";
			$query.="alter table profiles add column FF;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('FF','40','center text','FF');";
			}
			
		if (!array_search("Keys",$profileFields)) {
			$body.="<p>No Keys field found. Adding.</p>";
			$query.="alter table profiles add column Keys;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Keys','40','center text','Keys');";
			}
			
		if (!array_search("LordName",$profileFields)) {
			$body.="<p>No LordName field found. Adding.</p>";
			$query.="alter table profiles add column LordName;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('LordName','40','center text','LordName');";
			}
			
		if (!array_search("Lost",$profileFields)) {
			$body.="<p>No Lost field found. Adding.</p>";
			$query.="alter table profiles add column Lost;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Lost','40','center text','Lost');";
			}
			
		if (!array_search("Nations",$profileFields)) {
			$body.="<p>No Nations field found. Adding.</p>";
			$query.="alter table profiles add column Nations;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Nations','40','center text','Nations');";
			}	
			
		if (!array_search("OnWars",$profileFields)) {
			$body.="<p>No OnWars field found. Adding.</p>";
			$query.="alter table profiles add column OnWars;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('OnWars','40','center text','OnWars');";
			}	
			
		if (!array_search("Poison",$profileFields)) {
			$body.="<p>No Poison field found. Adding.</p>";
			$query.="alter table profiles add column Poison;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Poison','40','center text','Poison');";
			}
			
		if (!array_search("Randoms",$profileFields)) {
			$body.="<p>No Randoms field found. Adding.</p>";
			$query.="alter table profiles add column Randoms;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Randoms','40','center text','Randoms');";
			}
			
		if (!array_search("Stones",$profileFields)) {
			$body.="<p>No Stones field found. Adding.</p>";
			$query.="alter table profiles add column Stones;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Stones','40','center text','Stones');";
			}
			
		if (!array_search("TotalBurn",$profileFields)) {
			$body.="<p>No TotalBurn field found. Adding.</p>";
			$query.="alter table profiles add column TotalBurn;	insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('TotalBurn','40','center text','TotalBurn');";
			}	
		
		if (!array_search("TotalRes",$profileFields)) {
			$body.="<p>No TotalRes field found. Adding.</p>";
			$query.="alter table profiles add column TotalRes;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('TotalRes','40','center text','TotalRes');";
			}	

		if (!array_search("WarPorts",$profileFields)) {
			$body.="<p>No WarPorts field found. Adding.</p>";
			$query.="alter table profiles add column WarPorts;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('WarPorts','40','center text','WarPorts');";
			}	
			
		if (!array_search("Wealth",$profileFields)) {
			$body.="<p>No Wealth field found. Adding.</p>";
			$query.="alter table profiles add column Wealth;insert into DirColSettings (colName, colWidth, colAttrs, colTitle) values ('Wealth','40','center text','Wealth');";
			}	

		if ($query == "") {
			$body .= "<p>No missing fields found.</p>";
		} else {
			$pdo->exec($query);
			$body .= $query;
		}
		
		
		// close db connection
		$pdo = null;

		$body .= "<p>All done! Your Director database has been updated. Please close TheDirector, then rename the existing DirectorConfig.db that is in TheDirector's folder to DirectorConfig-Backup.db and replace with the new updated one in c:\\neato\\db and restart TheDirector!</p>";
	
	}
	
    catch (PDOException $e) {
        $body.="<font color='#ff0000'>".$query."<br/>".$e->getMessage()."</font>";
    }
} 

?><html>
<head>
<title><?=$title;?></title>

<style>
body {    font-family: "Courier New",
Courier, monospace;
font-size: 14px;
color: #CCC;
background-color: #000;}
div {width:80%;padding:20px;}
h1   {color: #00ff00;font-size:2em;}
p    {color: #00ff00;}
</style>
</head>
<body>
<div><h1><?=$title;?></h1></div>
<div>
<?=$body;?>
</div>
</body>
</html>