<?php

$version = "3.1";
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header('Content-Type: text/plain'); // make it plain text rather than html so linebreaks show correctly.

?>// MODULE_NAME: TECH's NEATO AUTOPILOT
// MODULE_DESC: shared startup script for all bots
// MODULE_STATUS: Released
// MODULE_VERSION: 3.1
// AutoPilot.php - to be used as -runscript http://localhost:92/autopilot.php
// TECH November 1, 2016 
// 

city.script.debug = 0    // turn debugging on (1) or off (0)

// map colors - passed in via cmdparms -colors 1,2,3,4,etc
c = (Config.colors) && Config.colors.split(",") || 0
// if map colors are passed in and this is the main city, set map colors
if !city.timeSlot if c MapColors.splice(0,15,c[0],c[1],c[2],c[3],c[4],c[5],c[6],c[7],c[8],c[9],c[10],c[11],c[12],c[13],c[14])

// set log colors.
Settings.logColors = {"4096":5921370,"1":24319,"2":1144576,"4":0,"256":0,"512":6776679,"8192":16711883,"1024":0,"8":9539985,"32":3703808,"128":0,"16":16711680,"16384":624384,"2048":10658466,"32768":65672,"64":0}

// accounts to whisper warnings to -whisper jack,jill 
w = (Config.whisper) && Config.whisper.split(",") || 0

// how loud to announce warnings 0 = off, 0.5 = half volume, 1 = max volume
// requires that ttsconfig is setup correctly. 
volume = (Config.volume) && Config.volume || 0

FormatNum = CreateFunction("n","(p=n>=1t && 5 || n>=1b && 4 || n>=1m && 3 || n>=1k && 2 || 1) && FormatNumber(n/pow(10,p*3-3),p>1 && 2 || 0)+(p>1 && 'kmbt'.substr(p-2,1) || '')")

TwoDigits = CreateFunction("x",'x>9?x:"0{x}"')

NEATOURL = "http://<?php echo $_SERVER['HTTP_HOST']; ?>/"

MIN_FOOD = 5

// if -silent 1 is passed in, do not output to messages to AC 
ALLIANCE_ALERT = (Config.silent) && 0 || 1

HR_TEXT="\n-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-\n"

// if city does not have goals or problems with goals- this could trigger automatic basic goals......
if city.CityHasGoalErrors echo "City {city.name} has goal errors."

if city.timeSlot goto allCityScript // if this is not the main city, goto allCityScripts

//////////////////////
// MAIN CITY SCRIPT //
//////////////////////
echo HR_TEXT+"\t\tAutopilot v.3.0\nlast modified <?php 
	echo date("Y/m/d H:i:s", filemtime('autopilot.php')); // make this equal to last modified data of file 
?>\n\t\tnow starting!"+HR_TEXT

// switch to city log
//Screen.selectLog(1) 

"apwbs".split("").forEach(CreateFunction("x,i,a",'Screen[x + "ChatText"].setStyle("fontFamily","Verdana") || Screen[x + "ChatText"].setStyle("backgroundColor","#fefefe") || Screen[x + "ChatText"].setStyle("fontSize","24")')) // set style variables for chat windows

// SIGNAL LOGIN TO NEATO:
get NEATOURL+"pokeNEATO.php" {action:"login", server:Config.server, email:player.playerInfo.accountName}
echo $result

linked = Screen.mainLog.buffer.match(/Linked the bot to Forum profile successfully/) && 1 || 0

// output account age
accountAge = FormatTime(abs(TimeDiff(player.playerInfo.createrTime)/1000))
echo "This account is {accountAge} old."

// output number of medals needed for promotion if account is not prinz
promo = player.playerInfo.titleId < 9 && (CreateFunction("","x = [ [ 'hero.loyalty.1', 'hero.loyalty.2', 'cross', 'rose', 'knight',10,5 ],[ 'hero.loyalty.2', 'hero.loyalty.3', 'rose', 'lion', 'Baronet',10,5 ],[ 'hero.loyalty.3', 'hero.loyalty.4', 'lion', 'honor', 'Baron',10,5 ],[ 'hero.loyalty.4', 'hero.loyalty.5', 'honor', 'courage', 'Viscount',10,5 ],[ 'hero.loyalty.5', 'hero.loyalty.6', 'courage', 'wisdom', 'Earl',10,5 ],[ 'hero.loyalty.6', 'hero.loyalty.7', 'wisdom', 'freedom', 'Marquis',10,5 ],[ 'hero.loyalty.7', 'hero.loyalty.8', 'freedom', 'justice', 'Duke',10,5 ],[ 'hero.loyalty.8', 'hero.loyalty.9', 'justice', 'nation', 'Furstin',10,5 ],[ 'hero.loyalty.8', 'hero.loyalty.9', 'justice', 'nation', 'prinzessin',30,15 ],[ 'hero.loyalty.8','hero.loyalty.9','','','I am the best.',0,0 ] ]")() && "I need "+(max(0,x[player.playerInfo.titleId][5] - ItemCount(x[player.playerInfo.titleId][0])))+" "+x[player.playerInfo.titleId][2]+" and "+(max(0,x[player.playerInfo.titleId][6]-ItemCount(x[player.playerInfo.titleId][1])))+" "+x[player.playerInfo.titleId][3]+" medals for "+x[player.playerInfo.titleId][4]) || ""
echo promo

label update
// UPDATE ACCOUNT INFORMATION:
(info = {lordName:player.playerInfo.userName,email:Config.username,password:Config.password,alliance:player.playerInfo.alliance, title:player.playerInfo.titleId,linked:0,prestige:player.playerInfo.prestige,linked:linked,accountAge:accountAge,promo:promo,items:{coins:player.playerInfo.medal,amulets:ItemCount("player.box.gambling.3"),nations:ItemCount("hero.loyalty.9"),onWarLvls:round(1*ItemCount("player.experience.1.c")+0.3*ItemCount("player.experience.1.b")+0.08*ItemCount("player.experience.1.a"),2),excal:ItemCount("hero.power.1"),wealth:ItemCount("hero.management.1"),artOfWar:ItemCount("hero.intelligence.1"),ardeeHero:ItemCount("player.box.hero.f"),crystalHero:ItemCount("player.box.hero.e"),marsHero:ItemCount("player.box.hero.d"),romanKit:ItemCount("player.box.romanbuildingkit"),advPort:ItemCount("player.more.castle.1.a"),randomPort:ItemCount("consume.move.1"),warPort:ItemCount("player.more.castle.1.c"),brokenGates:ItemCount("player.box.present.money.77"),fleetFeet:ItemCount("player.box.present.money.70"),amplifier:m_context.ItemCount("player.box.present.money.72"),endurance:ItemCount("player.box.present.money.71"),poison:ItemCount("player.box.present.money.76"),lost:ItemCount("player.box.present.money.75"),stones:ItemCount("player.item.stoneoffinding"),keys:ItemCount("player.key.silver"),horde:ItemCount("player.item.stygandrsbannerofthehorde")},cities:[],heroes:[]})+(total={resources:GetResources("f:0"),troops:GetTroops("a:0"),upkeep:0}) + cities.forEach(CreateFunction("c,i,a",'info.cities.push({ fieldId:c.cityManager.fieldId, timeSlot:c.cityManager.timeSlot,name:c.cityManager.name,coords:c.cityManager.coords,state:GetZoneName(c.cityManager.fieldId),iron:floor(c.cityManager.estResource.iron),wood:floor(c.cityManager.estResource.wood),stone:floor(c.cityManager.estResource.stone),food:floor(c.cityManager.estResource.food),gold:floor(c.cityManager.estResource.gold),abs:c.cityManager.fortification.abatis, tra:c.cityManager.fortification.trap, at:c.cityManager.fortification.arrowTower, rl:c.cityManager.fortification.rollingLogs, tre:c.cityManager.fortification.rockfall, wo:c.cityManager.getAvailableTroop().peasants, w:c.cityManager.getAvailableTroop().militia, s:c.cityManager.getAvailableTroop().scouter, p:c.cityManager.getAvailableTroop().pikemen, sw:c.cityManager.getAvailableTroop().swordsmen, a:c.cityManager.getAvailableTroop().archer, c:c.cityManager.getAvailableTroop().lightCavalry, cata:c.cityManager.getAvailableTroop().heavyCavalry, t:c.cityManager.getAvailableTroop().carriage, b:c.cityManager.getAvailableTroop().ballista, r:c.cityManager.getAvailableTroop().batteringRam, cp:c.cityManager.getAvailableTroop().catapult, burn:c.cityManager.getAvailableTroop().foodConsumeRate})')) + cities.forEach(CreateFunction("c,i,a","total.troops.add(c.cityManager.getAvailableTroop())||c.cityManager.estResource.addTo(total.resources)||total.upkeep+=c.cityManager.resource.troopCostFood"))+ cities.forEach(CreateFunction("c,i,a",'c.cityManager.heroes.toArray().forEach(CreateFunction("h,in,ar",(CastleName=c.cityManager.name)&&(CastleFID=c.cityManager.fieldId)&&\'info.heroes.push({id:h.id,cityName:CastleName,cityFieldId:CastleFID,name:h.name,lvl:h.level,pol:h.management,att:h.power,int:h.stratagem,ulvl:h.expLevels,won:h.buffs.HeroManagementBuff?round(TimeDiff(date(h.buffs.HeroManagementBuff.endTime))/(24*HOUR),1):0,aow:h.buffs.HeroStratagemBuff?round(TimeDiff(date(h.buffs.HeroStratagemBuff.endTime))/(24*HOUR),1):0,exc:h.buffs.HeroPowerBuff?round(TimeDiff(date(h.buffs.HeroPowerBuff.endTime))/(24*HOUR),1):0})\'))'))+(info.totalRes = round((total.resources.gold+total.resources.food+total.resources.wood+total.resources.stone+total.resources.iron)/1b,1))+ (info.totalBurn = round(total.upkeep/1000000,2)) 
//echo "info = "+ json_encode(info)

// UPDATE NEATO - NEWEST VERSION
post "{NEATOURL}neato.php" {server:Config.server.toLowerCase(),lordName:player.playerInfo.userName,alliance:player.playerInfo.alliance, info:json_encode(info),time:date().time}
echo $result

// CHECK FOR HOLIDAY MODE: (if player is in holiday, stop scripts here)
if player.buffs.FurloughBuff != null goto holiday

// CHECK FOR TRUCE STATUS:
(message = false) + (neatostatus = false)

if player.buffs.PlayerPeaceBuff message = "Truce Agreement activated for {FormatTime(abs(TimeDiff(player.buffs.PlayerPeaceBuff.endTime)/1k))}. "
if player.buffs.PlayerPeaceBuff neatostatus = "TRUCE for {FormatTime(abs(TimeDiff(player.buffs.PlayerPeaceBuff.endTime)/1k))} "

if player.buffs.PlayerPeaceCoolDownBuff message ="Truce Agreement in cooldown for {FormatTime(abs(TimeDiff(player.buffs.PlayerPeaceCoolDownBuff.endTime)/1k))}. "
if player.buffs.PlayerPeaceCoolDownBuff neatostatus = "TRUCE COOLDOWN for {FormatTime(abs(TimeDiff(player.buffs.PlayerPeaceCoolDownBuff.endTime)/1k))} "

if message gosub sendMessage

// Set automatic completion of quests to 3 if prestige > 0.
Settings.completeQuests = (player.playerInfo.prestige) && 3 || 0


// CHECK FOR NEW ACCOUNT: (if player is new account, call NewAcctScript.txt and then stop scripts here)
if player.playerInfo.prestige < 10000 call NEATOURL+"Scripts/noobScript.txt" {timestamp:date().time}
if player.playerInfo.prestige < 10000 stop

Settings.farmingMode = 2 // Set farming mode to smart farming 2

/////////////////////////////
//USEITEMS ON ALL ACCOUNTS //
/////////////////////////////

Settings.autoUseItems([ "player.box.present.money.60" ,"player.box.present.money.99" , "player.box.present.money.69", "player.box.present.money.73", "player.box.present.money.210", "player.box.present.money.141", "player.box.present.money.142", "player.box.present.money.143", "player.box.present.money.275", "player.box.present.money.276", "player.box.present.money.298", "player.box.present.money.299", "player.box.present.money.330" ,"player.item.warpackage2016", "player.box.present.money.174","player.box.present.money.349" ])

// Below is just for your info on what is what from above list ^^^

// "player.box.present.money.60" = 2x random medal
// "player.box.present.money.99" = angel/devil token99
// "player.box.present.money.69" = angel's boon
// "player.box.present.money.73" = devil's pact
// "player.box.present.money.210" = anniversary gift 2014
// "player.box.present.money.141" = angel/devil token
// "player.box.present.money.142" = devil token
// "player.box.present.money.143" = angel token
// "player.box.present.money.275" = anniversary gift 2015
// "player.box.present.money.298" = 2015 xmas gift
// "player.box.present.money.276" = ????
// "player.box.present.money.299" = ????
// "player.box.present.money.330" =  
// "player.box.present.money.349" = sun's gift  
// "player.box.present.money.174" = gambler's charm (amulets)

//if ItemCount("player.box.gambling.3") useitem aries amulet
//if ItemCount("player.box.gambling.3") repeat

//foodBuff = "player.item.afeastforthepeople2016"

//if ItemCount(foodBuff) execute "useitem "+ foodBuff
//if !$error if ItemCount(foodBuff) repeat


// End of oncePerAccount

/////////////////////////
// ONE CITY ALT SCRIPT //
/////////////////////////

// run ONE CITY ALT SCRIPT if:
// 1) this is a one city account
// 2) the account has no less than needed to farm
// 3) -keepalive or -keepon is NOT defined

if (Config.keepalive) || (Config.keepon) || (player.playerInfo.castleCount > 1) || (city.troop.ballista > 1000) goto allCityScript 

echo HR_TEXT+"\t\tOne City Alt Script Beginning"+HR_TEXT

//if city.resourceFieldType = 1 goal "build s:9:37,q:9:1,i:9:1,f:9:1\nbuild s:0:37,q:0:1,i:0:1,f:0:1"
//if city.resourceFieldType = 3 goal "build i:9:37,q:9:1,s:9:1,f:9:1\nbuild i:0:37,q:0:1,s:0:1,f:0:1"
//goal "build i:9:37,q:9:1,s:9:1,f:9:1,c:9:11,b:9:9\nbuild i:0:37,q:0:1,s:0:1,f:0:1,b:0:9"

//if player.playerInfo.medal >= 50 buyitem player.key.silver
//if player.playerInfo.medal >= 50 repeat

//if city.getBuildingLevel(31) > 3 call NEATOURL+"Scripts/StealValleys.txt" {timestamp:date().time}

sleep 10
label OneCityLoop

if info.totalRes > 0.5 echo "too much res"
if info.totalRes > 0.5 stop

// call to 1cityMarket script TODO LIST

echo "Sleeping for 15minutes. If grievance is 0 and tax is 10 or more then bot will exit. (stop script to abort)"
sleep 5:00
echo "Sleeping for 10minutes. If grievance is 0 and tax is 10 or more then bot will exit. (stop script to abort)"
sleep 5:00
echo "Sleeping for 5minutes. If grievance is 0 and tax is 10 or more then bot will exit. (stop script to abort)"
sleep 5:00
if city.resource.complaint goto OneCityLoop
if city.resource.texRate < 10 goto OneCityLoop
if city.enemyArmies.length goto OneCityLoop
exit


///////////////////////////////////////
/// ALL CITIES RUN STUFF BELOW HERE ///
///////////////////////////////////////
label allCityScript

// Check for holiday....
if player.buffs.FurloughBuff != null goto holiday

prestigeMin = FormatNum(player.playerInfo.prestige/3)

//if city.resourceFieldType = 1 goal "build s:9:37,q:9:1,i:9:1,f:9:1\nbuild s:0:37,q:0:1,i:0:1,f:0:1"
//if city.resourceFieldType = 3 goal "build i:9:37,q:9:1,s:9:1,f:9:1\nbuild i:0:37,q:0:1,s:0:1,f:0:1"

// Check for incoming
message = false
if city.hasEnemyArmiesWithin(3600) message = "INCOMING to {city.name} ({city.coords}), setting wartown:1"
if message if NeutralAlliances.indexOf(city.enemyArmies[0].alliance) >= 0 message = false
if message goal "config wartown:1"
if message neatostatus = "INCOMING to {city.name} ({city.coords})"
if message gosub sendMessage

// Check if wartown is set, announce it to AC (if in alliance)
if ALLIANCE_ALERT if player.playerInfo.alliance if city.getConfig("wartown") execute "alliancechat WARTOWN:{city.getConfig("wartown")} set on {city.name}. I came here to drink beer kick ass. Oh, look, I've finished my beer."
if city.getConfig("wartown") echo "WARTOWN:{city.getConfig("wartown")} set on {city.name}."

// Check for bad goals
if city.getConfig("npc") && city.getConfig("npc")<5 goal "config npc:5"
if city.getConfig("buildnpc") && city.getConfig("buildnpc")=20 goal "config buildnpc:15"
if ALLIANCE_ALERT if player.playerInfo.alliance if city.getConfig("npclimit") execute "alliancechat NPCLIMIT:{city.getConfig("npclimit")} set on {city.name}. Why? because I'm a total n00b."

// Check if gates are broken
message = false
if city.buff("ForceopenclosegateBuff") message = "gates are broken on {city.name} ({city.coords}) for {FormatTime(abs(TimeDiff(city.buff("ForceopenclosegateBuff").endTime)/1k))}"
if message neatostatus = "BROKENGATES on {city.name} for {FormatTime(abs(TimeDiff(city.buff("ForceopenclosegateBuff").endTime)/1k))}"
if message gosub sendMessage

// Check if gates are in cooldown
message = false
if city.buff("ForceopenclosegateCooldownBuff") message = "gates are in GB cooldown on {city.name} ({city.coords}) for {FormatTime(abs(TimeDiff(city.buff("ForceopenclosegateCooldownBuff").endTime)/1k))}"
if message neatostatus = "BG COOLDOWN on {city.name} for {FormatTime(abs(TimeDiff(city.buff("ForceopenclosegateCooldownBuff").endTime)/1k))}"
if message gosub sendMessage

// Check for low food
message = false
hrsOfFood = (city.resource.food.amount+city.incomingResources().food) / city.resource.troopCostFood
hrsOfFood = floor(hrsOfFood)
echo "{city.name} is at {hrsOfFood} hours of food and is burning {FormatNum(city.resource.troopCostFood)}/hour."
if hrsOfFood < MIN_FOOD message = "{city.name} @ {city.coords} is at {hrsOfFood} hours of food and is burning {FormatNum(city.resource.troopCostFood)}/hour. Please help if your prest is more than {prestigeMin}"
if hrsOfFood < MIN_FOOD neatostatus = "LOW FOOD: {city.name} is at {hrsOfFood} hrs."
if message gosub sendMessage
// could put a call in to trade script here.

// finished with custom all city scripts - goto the end of script


// if you pass in custom script as -acs (all cities scripts) it will run it now:
if (Config.acs) echo HR_TEXT+"Autopilot complete, now launching {Config.acs}\n"+HR_TEXT
if (Config.acs) call Config.acs {time:date().time}

goto EOS


////////////////////////////////////////////////////////// SUBROUTINES

label holiday
// if player is in holiday, signal to pokeNEATO, resetgoals and stop script 
if !city.timeSlot post "{NEATOURL}pokeNEATO.php" {action:"update",server:Config.server,email:player.playerInfo.accountName,neatonote:"HOLIDAY for {FormatTime(abs(TimeDiff(player.buffs.FurloughBuff.endTime)/1k))}"}
resetgoals
stop
goto EOS


label sendMessage
echo message

if !Config.silent if player.playerInfo.alliance if ALLIANCE_ALERT execute "alliancechat " + message

if volume execute "say /volume={volume} '{player.playerInfo.userName} {neatostatus}'"

if neatostatus call NEATOURL+"pokeNEATO.php" {action:"update",server:Config.server,email:player.playerInfo.accountName,neatonote:neatostatus}
echo $result // (optional for debugging)

// Let us see if they want to whisper. This is last because if not, we bail out.

if !w return
w2=w.slice()
execute "whisper '{w2.pop()}' {message}"
if w2.length repeat
return

label EOS
/// End operation of -runscript NEATO AUTOPILOT script... this will now continue on to run anything at label autorun
if !city.timeSlot echo HR_TEXT+"Autopilot Ending.\nEnd operation of -runscript NEATO AUTOPILOT script...\nScript operation will now goto label autorun"+HR_TEXT
