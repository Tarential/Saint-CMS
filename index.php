<?php
/*
END-USER LICENSE AGREEMENT FOR SAINT CONTENT MANAGEMENT SYSTEM. IMPORTANT:
PLEASE READ THE TERMS AND CONDITIONS OF THIS LICENSE AGREEMENT CAREFULLY BEFORE
CONTINUING WITH THIS PROGRAM INSTALL. Aparadine Software's End-User License
Agreement ("EULA") is a legal agreement between you (either an individual or a
single entity) and Aparadine Software for the Aparadine Software software
product(s) identified as the "Saint Content Management System" which may include
associated software components, media, printed materials, and "online" or
electronic documentation. By installing, copying, or otherwise using the Saint
Content Management System, you agree to be bound by the terms of this EULA. This
license agreement represents the entire agreement concerning the program between
you and Aparadine Software, (referred to as "licenser"), and it supersedes any
prior proposal, representation, or understanding between the parties. If you do
not agree to the terms of this EULA, do not install or use the Saint Content
Management System.

The Saint Content Management System is protected by copyright laws and
international copyright treaties, as well as other intellectual property laws
and treaties. The Saint Content Management System is licensed, not sold.

1. GRANT OF LICENSE.
The Saint Content Management System is licensed as follows:
(a) Installation and Use.
Aparadine Software grants you the right to install and use copies of the Saint
Content Management System on your computer or web server in order to host a
single website per license.
(b) Backup Copies.
You may also make copies of the Saint Content Management System as may be
necessary for backup and archival purposes.

2. DESCRIPTION OF OTHER RIGHTS AND LIMITATIONS.
(a) Maintenance of Copyright Notices.
You must not remove or alter any copyright notices on any and all copies of the
Saint Content Management System.
(b) Distribution.
You may not distribute registered copies of the Saint Content Management System
to third parties.
(c) Rental.
You may not rent, lease, or lend the Saint Content Management System.
(d) Support Services.
Aparadine Software may provide you with support services related to the Saint
Content Management System ("Support Services"). Any supplemental software code
provided to you as part of the Support Services shall be considered part of the
Saint Content Management System and subject to the terms and conditions of this
EULA.
(e) Compliance with Applicable Laws.
You must comply with all applicable laws regarding use of the Saint Content
Management System.

3. TERMINATION
Without prejudice to any other rights, Aparadine Software may terminate this
EULA if you fail to comply with the terms and conditions of this EULA. In such
event, you must destroy all copies of the Saint Content Management System in
your possession.

4. COPYRIGHT
All title, including but not limited to copyrights, in and to the Saint Content
Management System and any copies thereof are owned by Aparadine Software or its
suppliers. All title and intellectual property rights in and to the content
which may be accessed through use of the Saint Content Management System is the
property of the respective content owner and may be protected by applicable
copyright or other intellectual property laws and treaties. This EULA grants you
no rights to use such content. All rights not expressly granted are reserved by
Aparadine Software.

5. NO WARRANTIES
Aparadine Software expressly disclaims any warranty for the Saint Content
Management System. The Saint Content Management System is provided 'As Is'
without any express or implied warranty of any kind, including but not limited
to any warranties of merchantability, noninfringement, or fitness of a
particular purpose. Aparadine Software does not warrant or assume responsibility
for the accuracy or completeness of any information, text, graphics, links or
other items contained within the Saint Content Management System. Aparadine
Software makes no warranties respecting any harm that may be caused by the
transmission of a computer virus, worm, time bomb, logic bomb, or other such
computer program. Aparadine Software further expressly disclaims any warranty or
representation to Authorized Users or to any third party.

6. LIMITATION OF LIABILITY
In no event shall Aparadine Software be liable for any damages (including,
without limitation, lost profits, business interruption, or lost information)
rising out of 'Authorized Users' use of or inability to use the Saint Content
Management System, even if Aparadine Software has been advised of the
possibility of such damages. In no event will Aparadine Software be liable for
loss of data or for indirect, special, incidental, consequential (including lost
profit), or other damages based in contract, tort or otherwise. Aparadine
Software shall have no liability with respect to the content of the Saint
Content Management System or any part thereof, including but not limited to
errors or omissions contained therein, libel, infringements of rights of
publicity, privacy, trademark rights, business interruption, personal injury,
loss of privacy, moral rights or the disclosure of confidential information.
*/
/**
 * Index controller and router for the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
session_start();

define("SAINT_PROFILING",false);

if (SAINT_PROFILING) {
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$starttime = $mtime;
}

# Establish some basic runtime variables for use throughout the site

$included = true;
$installed = true;

require_once("core/config.php");

if (file_exists("config.php"))
	require_once("config.php");
else
	$installed = false;

if (preg_match('/^(\d+)\.(\d\d)(\d\d)$/',SAINT_CODE_VERSION,$matches)) {
	define('SAINT_FRIENDLY_VERSION',"v".$matches[1].".".ltrim($matches[2],'0'));
} else {
	define('SAINT_FRIENDLY_VERSION','');
}

$subdir = substr(SAINT_SITE_ROOT,strlen($_SERVER['DOCUMENT_ROOT']));
define('SAINT_URL',chop(SAINT_BASE_URL . "/" . $subdir,'/'));
if ($get_start = strpos($_SERVER['REQUEST_URI'],'?'))
	$uri_sans_get = trim(substr($_SERVER['REQUEST_URI'],0,$get_start),'/');
else
	$uri_sans_get = trim($_SERVER['REQUEST_URI'],'/');
$uri = trim(substr($uri_sans_get,strlen($subdir)),'/');

/**
 * Saint class autoload function.
 * @param string $class_name Name of class to load.
 * @return boolean True on success, false otherwise.
 */
function st_autoload($class_name) {
	if (preg_match('/^\w+$/',$class_name)) {
		$class_name = preg_replace('/Saint_/','',$class_name);
		$class_name = preg_replace('/_/','/',$class_name);
		$saint_class_name = SAINT_SITE_ROOT . '/core/code/' . $class_name . ".php";
		/* $user_class_name = Saint::getThemeDir() . '/code/' . $class_name . ".php";
		if (file_exists($user_class_name)) {
			include_once($user_class_name);
			return 1;
		} else */
		if (file_exists($saint_class_name)) {
			include_once($saint_class_name);
			return 1;
		}
	}
	return 0;
}
spl_autoload_register('st_autoload');

# Connect to the database
if (!($st_link = mysql_connect(SAINT_DB_HOST,SAINT_DB_USER,SAINT_DB_PASS))) {
	$installed = false;
	echo "Error connecting to database. Check config file and error log for details.";
	Saint::logError(mysql_error()); }
if (!($st_linkdb = mysql_select_db(SAINT_DB_NAME))) {
	$installed = false;
	echo "Error selecting database. Check config file and error log for details.";
	Saint::logError(mysql_error()); }

try {
	$installed = Saint::getOne("SELECT `version` FROM `st_config`");
} catch (Exception $e) {
	$installed = false;
	Saint::logError("Could not verify installation: ".$e->getMessage());
}

define('SAINT_DB_VERSION',$installed);

$args = array();
foreach ($_GET as $key=>$val) {
	if (trim($key,'/') != $uri) {
		$args[$key] = $val;
	}
}

if (preg_match('/^([^\/]+)\/(.*)$/',$uri,$matches)) {
	$pid = $matches[1];
	$args['subids'] = explode('/',$matches[2]);
} else {
	$pid = $uri;
	$args['subids'] = array();
}
if ($pid == '')
	$pid = "home";

if (Saint::getCurrentUsername() == "guest" && isset($_COOKIE['saintcookie'])) {
	Saint_Model_User::loginViaCookie($_COOKIE['saintcookie']);
}

if (SAINT_DB_VERSION) {
	if (SAINT_CODE_VERSION > SAINT_DB_VERSION) {
		include_once(SAINT_SITE_ROOT."/core/installer/db-upgrade.php");
	}
	Saint::callPage($pid,$args);
} else {
	Saint::runInstall();
}

if (SAINT_PROFILING) {
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime)*1000;
	Saint::logEvent("Page ".Saint::getCurrentPage()->getName()." was created in ".$totaltime." ms using " . memory_get_peak_usage() . " bytes.");
}
