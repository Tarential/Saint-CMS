<?php
/**
 * Saint config file. See the manual for more information.
 */

# Theme
define('SAINT_THEME','');

# Database connection info
define('SAINT_DB_HOST','localhost');
define('SAINT_DB_NAME','jack');
define('SAINT_DB_USER','cms');
define('SAINT_DB_PASS','w4t3rm4rk');

define('SAINT_PAYPAL_EMAIL','david_1334271926_biz@aparadine.com');

# By default the shop uses the PayPal sandbox for setup. Comment the sandbox and uncomment the live version when ready.
#define('SAINT_PAYPAL_URL','www.paypal.com');
define('SAINT_PAYPAL_URL','www.sandbox.paypal.com');

# Site location
# These should autodetect properly except in cases where Saint's location is symlinked outside the document root.
# If you are having trouble, comment out the autodetect section and uncomment / fill in the regular area.
# Note: Leave no trailing / on the URL or the subdir.

# Regular:
#$subdir = '';
#define('SAINT_BASE_URL','http://example.com');
#define('SAINT_SUB_DIR','Saint');

# Autodetect:
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == true) $ssl = "s"; else $ssl = '';
define('SAINT_BASE_URL',"http".$ssl."://" . $_SERVER['SERVER_NAME']);
define('SAINT_SITE_ROOT',chop(getcwd(),'/'));
define('SAINT_CACHE_DIR',SAINT_SITE_ROOT."/cache");
define('SAINT_SUB_DIR',substr(SAINT_SITE_ROOT,strlen($_SERVER['DOCUMENT_ROOT'])));

# You shouldn't need to edit this, but if you do it should be set to the full URL of your Saint site's root.
#define('SAINT_URL','http://example.com/Saint');
define('SAINT_URL',chop(SAINT_BASE_URL . "/" . SAINT_SUB_DIR,'/'));

define('SAINT_THEME_DIR',SAINT_SITE_ROOT . "/themes/" . SAINT_THEME);

# Basic settings
define('SAINT_DEF_LANG',"english");
define('SAINT_BLOG_LANG',"en-us");
define('SAINT_MAINT_MODE',false);
define('SAINT_LOG_LEVEL',3); // 0 disabled, 1 errors, 2 errors + warnings, 3 all
define('SAINT_LOG_DIR',SAINT_SITE_ROOT.'/logs/');
define('SAINT_ERR_FILE',SAINT_LOG_DIR.'error.log');
define('SAINT_WARN_FILE',SAINT_LOG_DIR.'warning.log');
define('SAINT_EVENT_FILE',SAINT_LOG_DIR.'events.log');

# Don't enable caching until development is complete.
# You're safe to leave it disabled until performance
# becomes an issue.
define('SAINT_CACHING',false);
