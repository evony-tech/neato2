<?php if(!class_exists('raintpl')){exit;}?><html>
<head>
	<title><?php echo $Title;?></title>
	<link href="tpl/css/neato.css" rel="stylesheet" type="text/css" />
	<link href="tpl/css/tables.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="tpl/js/jquery-latest.js"></script> 
	<script type="text/javascript" src="tpl/js/jquery.tablesorter.min.js"></script> 
	<script type="text/javascript" id="js">
		$(document).ready(function() 
		    { 
		        $("table#modulelist").tablesorter({widgets: ['zebra']}); 
		    } 
		); 
	</script>
</head>
<body>
	<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("include-header") . ( substr("include-header",-1,1) != "/" ? "/" : "" ) . basename("include-header") );?>

	<div id="main">
		<h2>Your personal webserver and toolkit</h2>
		<p>If you are seeing this web page, you have successfully installed <b>NEATO</b> onto your computer. <b>NEATO</b> is a collection of Open Source utilities to assist you with your 3vony playing. <b>NEATO</b> consists of <a href="https://code.google.com/p/mongoose/" target="_blank">Mongoose Web Server</a> with <b>PHP</b> as well as a <b>NEATO</b> little program for sending messages to your instant messenger. Mongoose is a very tiny webserver application, very lightweight on your computer. (Note: for proper operation with NEATOlert, you should NOT install it as a service.) The latest version of PHP has been bundled with Mongoose and configured to use most of the extensions for PHP that you should need. (See full info on currently loaded PHP version here: <a href="?phpinfo" target="_blank"><?php echo $NEATO_HTTPURL;?>?phpinfo</a>)</p>
		<p><b>NEATO</b> is preconfigured to interact with an SQLite database already, you can administer SQLite here: <a href="<?php echo $NEATO_HTTPURL;?>phpliteadmin.php" target="_blank"><?php echo $NEATO_HTTPURL;?>phpliteadmin.php</a> using the default login user: admin and password: NEATO</p>
		<p><b>NEATO</b> is also preconfigured to work with MySQL, but MySQL is NOT included with this installation. If you would prefer to use MySQL over SQLite, you can download the latest version of MySQL Server for Windows free here: <a href="http://dev.mysql.com/downloads/installer/" target="_blank">http://dev.mysql.com/downloads/installer/</a> and you may also want to download <b>phpMyAdmin</b> <a href="http://www.phpmyadmin.net/home_page/downloads.php" target="_blank">http://www.phpmyadmin.net/home_page/downloads.php</a> and unzip it into your webroot or www folder. Configuration will be required and you can Google for help if you need support.</p>
		<p><b>NEATO</b> includes some neato PHP applications developed to enhance your game by unlocking the potential of the new POST/GET functionality added to NEAT.<br/>
		Check out these apps:<br/></p>
		<table id="modulelist" border=1>
		<thead>
			<tr>
			  <th>Module Name</th>
			  <th>Module Address</th>
			  <th>Description</th>
			  <th>Status</th>
			  <th>Version</th>
			</tr>
		</thead>
		<tbody>
			<?php $counter1=-1; if( isset($modulelist) && is_array($modulelist) && sizeof($modulelist) ) foreach( $modulelist as $key1 => $value1 ){ $counter1++; ?>

			<tr>
				<td><?php echo $value1['title'];?></td>
				<td><a href="<?php echo $key1;?>" target="_blank"><?php echo $NEATO_HTTPURL;?><?php echo $key1;?></a></td>
				<td><?php echo $value1['description'];?></td>
				<td><?php echo $value1['status'];?></td>
				<td><?php echo $value1['version'];?></td>
			</tr>
			<?php } ?>

		</tbody>
		</table>
	</div>
	<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("include-footer") . ( substr("include-footer",-1,1) != "/" ? "/" : "" ) . basename("include-footer") );?>

</div>
</body>
</html>