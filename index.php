<?php
/**
 * Index controller and router for the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
session_start();

$profiling = false;

if ($profiling) {
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$starttime = $mtime;
}

/**
 * @todo Block all xml file and restricted folder downloads in .htaccess
 */

# Establish some basic runtime variables for use throughout the site
$uri = rtrim(trim($_SERVER['REQUEST_URI'],'/'),'/');
$rawargs = explode('/',$uri);
$args = array();
$included = true;
$installed = true;

require_once("core/config.php");

if (file_exists("config.php"))
	require_once("config.php");
else
	$installed = false;

/**
 * Saint class autoload function.
 * @param string $class_name Name of class to load.
 * @return boolean True on success, false otherwise.
 */
function st_autoload($class_name) {
	if (preg_match('/^\w+$/',$class_name)) {
		$class_name = preg_replace('/Saint_/','',$class_name);
		$class_name = preg_replace('/_/','/',$class_name);
		$user_class_name = SAINT_SITE_ROOT . '/code/' . $class_name . ".php";
		$saint_class_name = SAINT_SITE_ROOT . '/core/code/' . $class_name . ".php";
		if (file_exists($user_class_name)) {
			include_once($user_class_name);
			return 1;
		} elseif (file_exists($saint_class_name)) {
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
	$result = Saint::getOne("SELECT `installed` FROM `st_config`");
	$installed = $result;
} catch (Exception $e) {
	$installed = false;
	Saint::logError("Could not verify installation: ".$e->getMessage());
}

$argument_pattern = '/[\/]*([^\/\.]+\.[^\/\.]+)\/*/';

if (preg_match_all($argument_pattern,$uri,$matches)) {
	foreach ($matches[1] as $match) {
		$mix = explode('.',$match);
		$args[$mix[0]] = $mix[1];
	}
}

$pid = preg_replace($argument_pattern,'',$uri);
if ($pid == '')
	$pid = "home";

if (Saint::getCurrentUsername() == "guest" && isset($_COOKIE['saintcookie'])) {
	Saint_Model_User::loginViaCookie($_COOKIE['saintcookie']);
}

//unset($_SESSION['saint_scid']);
//$transaction = new Saint_Model_Transaction();
//$transaction->create(1, 891, '10', rand(0,10000), 'jones@gmail.com', "this:that;not:yet;");

if ($installed)
	Saint::callPage($pid,$args);
else
	Saint::runInstall();

if ($profiling) {
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime);
	echo "This page was created in ".$totaltime." seconds using " . memory_get_peak_usage() . " bytes.";
}
