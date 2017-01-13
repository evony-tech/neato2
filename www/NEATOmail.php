<?
// MODULE_NAME: NEATOmail
// MODULE_DESC: Send email using your gmail account
// MODULE_STATUS: In development
// MODULE_VERSION: 1.0

// NEATO includes (define)
require_once "StandardIncludes.php";

// define $gmailEmail and $gmailPass here:
require_once "../gmail.php";

// Swift Mailer Library
require_once '../lib/swift_required.php';

$url='http://'.$_SERVER['HTTP_HOST'].'/NEATOmail.php';
$errors = "";

if (!isset($gmailEmail)) $errors .="You must edit gmail.php and put in your gmail email.";
if (!isset($gmailPass)) $errors .="You must edit gmail.php and put in your gmail password.";

if (isset($_POST['send2mail'])) {
	$send2mail = $_POST['send2mail'];
	} else {
	$errors .= "You must define send2mail. ";
	}
if (isset($_POST['send2name'])) {
	$send2name = $_POST['email'];
	} else {
	$errors .= "You must define send2name. ";
	}
if (isset($_POST['subject'])) {
	$subject = $_POST['subject'];
	} else {
	$errors .= "You must define subject. ";
	}
if (isset($_POST['body'])) {
	$body = $_POST['body'];	
	} else {
	$errors .= "You must define body. ";
	}
if (count($_POST)) {
	if ($errors == "") {

		// Mail Transport
		$transport = Swift_SmtpTransport::newInstance('ssl://smtp.gmail.com', 465)
			->setUsername($gmailEmail) // Your Gmail Username
			->setPassword($gmailPass); // Your Gmail Password
		// Mailer
		$mailer = Swift_Mailer::newInstance($transport);
		// Create a message
		$message = Swift_Message::newInstance($subject)
			->setFrom(array($gmailEmail => 'NEATOmail')) // can be $_POST['email'] etc...
			->setTo(array($send2mail => $send2name)) // your email / multiple supported.
			->setBody($body, 'text/html');
		// Send the message
		if ($mailer->send($message)) {
			echo 'Mail sent successfully.';
		} else {
			echo 'I am sure, your configuration are not correct. :(';
		}
	} else {
		echo $errors;
	}
exit();
}
?><html>
<head>
<title>NEATOmail</title>
<link href="./css/neato.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div><h1>NEATOmail</h1>
<p>To utilize NEATOmail you need to have a Google email account, and edit the file "gmail.php" in the NEATO folder.</p>
<p>To send mail, you can use the following script from NEAT:</p>
<pre>URL = "<?=$url;?>"
send2mail = "john.q.public@hotmail.com"
send2name = "John Q. Public"
subject = "Under Attack"
body = "Dear "+send2name+",\n\tPlease help! City BlahBlah at 123,456 is under attack by SirBlah\n\n\tRegards,\n\tSoAndSo"
data = {neatokey:Config.neatokey,send2mail:send2mail,send2name:send2name,subject:subject,body:body}
post URL data
echo $result // (optional)</pre>
</div>
</body>
</html>
