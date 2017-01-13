<?
// MODULE_NAME: Variable Persistence
// MODULE_DESC: Save and load Neatbot script variables
// MODULE_STATUS: Released
// MODULE_VERSION: 1
// varsave.php (c)2013 SumRandomTechGuys
// saves your NEAT variables to a text file 
include_once "StandardIncludes.php";
$url='http://'.$_SERVER['HTTP_HOST'].'/';
$filename="./vars/varsave";
$fileout="";
foreach ($_REQUEST as $key => $value ) {
	if ($_COOKIE[$key]<>$value){
		switch($key) {
			case 'Server';
			case 'User';
				$filename .= "_$value";
				break;
			default;
				$fileout .= "$key=$value\r\n";
				break;
		}
	}
}
$filename.=".txt";
if($fileout<>"") {
//variables were passed in
	if(file_exists($filename) == TRUE){ unlink($filename); }
	// deletes the file if it already exists
	file_put_contents($filename,$fileout);
	// saves the data to the file
	?>Variables saved to <?=$filename;
	exit();
}?>
<? echo ShowHeader("VarSave");?>
<pre>a=1
b=2
c=3
d=4
post "<?=$url;?>VarSave.php" {a:a,b:b,c:c,d:d}
echo $result</pre>
<p>this will save your variables to a file called "varsave.txt" located in the "vars" folder, off the web server's www root (<a href="<?=$url;?>vars/" target="_blank"><?=$url;?>vars/</a>)</p>
<p><b>VarSave</b> Script Example 2:</p>
<pre>lows = [BuyPrice(0), BuyPrice(1), BuyPrice(2), BuyPrice(3)]
highs = [SellPrice(0), SellPrice(1), SellPrice(2), SellPrice(3)]

post "<?=$url;?>VarSave.php" {Server:Config.server,lows:json_encode(lows),highs:json_encode(highs)}
echo $result</pre>
<p>because the variable "Server" was passed in, it will name this file "varsave_176.txt" (example)</p>
<p><b>NOTE:</b><br/>If you pass in a string, you have to use stringName:json_encode(stringName) to pass it in.<br/>
If you pass in an array, you have to use arrayName:json_encode(arrayName) to pass it in.<br/>
There are 2 special variables you can pass in that will effect the name of the file the variables are saved in: "Server" and "User" - These are *not* required.</p>
<p>Special variable usage examples:</p>
<pre>post url {Server:Config.server,data:json_encode(data)}
post url {Player:player.playerInfo.userName,data:data:json_encode(data)}
post url {Server:Config.server,Player:player.playerInfo.userName,data:json_encode(data)}</pre>
<p>For these examples, assume that the current server is 176 and the Lord name is "JohnDoe". The 1st example passes in the variable "Server" with the value of the current server (176) to VarSave.php, which saves the array called "data" to "varsave_176.txt" in your /vars folder. The second example passes in the variable "User" with the value being "JohnDoe" as well as the array "data", and that is saved as "varsave_JohnDoe.txt". The third example passes in both "Server" and "User" as well as "data", and the data is saved as "varsave_176_JohnDoe.txt".</p>
<p>the variables are stored in a text file, one variable per line... loading them back into your bot is relatively simple. You just need to know the filename they were saved as:</p>
<pre>get "<?=$url;?>vars/varsave.txt" {time:date()}
loadvars = $result.split('\r\n')
execute loadvars.pop()
if loadvars.length repeat
</pre>
<p>For support, questions and discussion about this application you can post here: <a href="http://forum.neatportal.com/viewtopic.php?f=9&t=1608" target="_blank">http://forum.neatportal.com/viewtopic.php?f=9&t=1608</a></p>
</div>
<?=ShowFooter()?>