<?php
/*
This software is distributed under the GNU General Public License version 3.0.
A copy should be provided with the software or can be located online at:
http://www.gnu.org/copyleft/gpl.html
*/
/**
 * Index controller and router for the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
session_start();

define("SAINT_PROFILING",false);

if (SAINT_PROFILING) {
	global $profiling_events;
	global $script_start;
	$profiling_events = array();
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$script_start = $mtime;
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

if ($get_start = strpos($_SERVER['REQUEST_URI'],'?'))
	$uri_sans_get = trim(substr($_SERVER['REQUEST_URI'],0,$get_start),'/');
else
	$uri_sans_get = trim($_SERVER['REQUEST_URI'],'/');
$uri = trim(substr($uri_sans_get,strlen(SAINT_SUB_DIR)),'/');

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
		$args[$key] = $val;;
	}
}

if (preg_match('/^([^\/]+)\/(.*)$/',$uri,$matches)) {
	$pid = $matches[1];
	$args['subids'] = explode('/',$matches[2]);
} else {
	$pid = $uri;
	$args['subids'] = array();
}
if ($pid == '') {
	$pid = "home";
}

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
	global $profiling_events;
	global $script_start;
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $script_start)*1000;
	$profiling_events[] = "Page ".Saint::getCurrentPage()->getName()." was created in $totaltime ms using " . memory_get_peak_usage() . " bytes.";

	$events = '';
	foreach ($profiling_events as $event) {
		$events .= $event . "\n\n";
	}
	Saint::logEvent($events);
}
