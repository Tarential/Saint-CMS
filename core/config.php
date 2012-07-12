<?php
/**
 * Saint config file. Read the manual before changing any of these values.
 * @author Preston St. Pierre
 */

# Saint version XX.YYZZ
# X = Full release
# Y = Major revision
# Z = Minor revision
define('SAINT_CODE_VERSION','1.0301');

# Site location
define('SAINT_BASE_URL',"http://" . $_SERVER['SERVER_NAME']);
define('SAINT_SITE_ROOT',chop(getcwd(),'/'));
define('SAINT_CACHE_DIR',SAINT_SITE_ROOT."/cache");

# Set this to true if you want Saint core media files to appear in the CMS file manager
define('SAINT_USE_CORE_MEDIA',true);

# Basic settings
define('SAINT_DEF_LANG',"english");
define('SAINT_BLOG_LANG',"en-us");
define('SAINT_MAINT_MODE',false);
define('SAINT_LOG_LEVEL',3); // 0 disabled, 1 errors, 2 errors + warnings, 3 all
define('SAINT_LOG_DIR',SAINT_SITE_ROOT.'/logs/');
define('SAINT_ERR_FILE',SAINT_LOG_DIR.'error.log');
define('SAINT_WARN_FILE',SAINT_LOG_DIR.'warning.log');
define('SAINT_EVENT_FILE',SAINT_LOG_DIR.'events.log');

# Special note: Name patterns cannot include underscores for reasons elaborated upon in the manual.
define('SAINT_REG_NAME',"/^[a-zA-Z0-9\/\.\-]+$/");
define('SAINT_REG_ID',"/^\d+$/");
define('SAINT_REG_BOOL',"/^[01]$/");
define('SAINT_REG_EMAIL',"/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/");

# Password storage preferences
define('SAINT_SALT_LEN',32);
define('SAINT_NONCE_LEN',32);
define('SAINT_SEQ_LEN',32);

# Client authentication nonce length
define('SAINT_CLIENT_NONCE_LEN',64);

# For enabling/disabling sandbox
define('SAINT_PAYPAL_URL','www.sandbox.paypal.com');

# For future integration with PayPal API
define('SAINT_PAYPAL_USERNAME','');
define('SAINT_PAYPAL_PASSWORD','');
define('SAINT_PAYPAL_SIGNATURE','');

$saint_group_access = array(
	'administrator' => array(
		'edit-site',
		'admin-overlay',
		'add-page',
		'edit-page',
		'delete-page',
		'add-block',
		'edit-block',
		'delete-block',
		'add-label',
		'edit-label',
		'delete-label',
		'add-user',
		'edit-user',
		'delete-user',
		'edit-self',
		'make-moderator',
		'break-moderator',
		'make-administrator',
		'break-administrator',
		'maintenance-mode',
		'add-category',
		'delete-category',
		'edit-category',
		'manage-files',
		'upload-files',
		'delete-files',
		'view-transactions',
		'view-discounts',
	),
	'moderator' => array(
		'admin-overlay',
		'add-page',
		'edit-page',
		'delete-page',
		'add-block',
		'edit-block',
		'delete-block',
		'add-label',
		'edit-label',
		'delete-label',
		'add-user',
		'edit-user',
		'delete-user',
		'edit-self',
		'make-moderator',
		'break-moderator',
		'maintenance-mode',
		'add-category',
		'delete-category',
		'edit-category',
		'manage-files',
		'view-transactions',
		'view-discounts',
	),
	'user' => array(
		'edit-self',
	),
	'guest' => array(
	),
);


$saint_actions = array(

);

$saint_filetypes = array(
	'document' => array(
		'doc',
		'docx',
		'log',
		'msg',
		'pages',
		'rtf',
		'txt',
		'wpd',
		'wps',
	),

	'data' => array(
		'csv',
		'dat',
		'efx',
		'gbr',
		'key',
		'pps',
		'ppt',
		'pptx',
		'sdf',
		'tax2010',
		'vcf',
		'xml',
	),

	'audio' => array(
		'aif',
		'iff',
		'm3u',
		'm4a',
		'mid',
		'mp3',
		'mpa',
		'ra',
		'wav',
		'wma',
	),

	'video' => array(
		'3g2',
		'3gp',
		'asf',
		'asx',
		'avi',
		'flv',
		'mov',
		'mp4',
		'mpg',
		'rm',
		'swf',
		'vob',
		'wmv',
	),

	'image' => array(
		'bmp',
		'gif',
		'jpg',
		'png',
		'psd',
		'pspimage',
		'thm',
		'tif',
		'tiff',
		'yuv',
		'ai',
		'drw',
		'eps',
		'ps',
		'svg',
	),

	'spreadsheet' => array(
		'xlr',
		'xls',
		'xlsx',
	),

	'database' => array(
		'accdb',
		'db',
		'dbf',
		'mdb',
		'pdb',
		'sql',
	),

	'executable' => array(
		'app',
		'bat',
		'cgi',
		'com',
		'exe',
		'gadget',
		'jar',
		'pif',
		'vb',
		'wsf',
	),

	'game' => array(
		'gam',
		'nes',
		'rom',
		'sav',
	),

	'cad' => array(
		'dwg',
		'dxf',
	),

	'web' => array(
		'asp',
		'cer',
		'csr',
		'css',
		'htm',
		'html',
		'js',
		'jsp',
		'php',
		'rss',
		'xhtml',
	),

	'font' => array(
		'fnt',
		'fon',
		'otf',
		'ttf',
	),
	
	'system' => array(
		'cab',
		'cpl',
		'cur',
		'dll',
		'dmp',
		'drv',
		'lnk',
		'sys',
	),
	
	'settings' => array(
		'cfg',
		'ini',
		'keychain',
		'prf',
	),

	'encoded' => array(
		'bin',
		'hqx',
		'mim',
		'uue',
	),

	'compressed' => array(
		'7z',
		'deb',
		'gz',
		'pkg',
		'rar',
		'rpm',
		'sit',
		'sitx',
		'tar.gz',
		'zip',
		'zipx',
	),

	'disk' => array(
		'dmg',
		'iso',
		'toast',
		'vcd',
	),

	'source' => array(
		'c',
		'class',
		'cpp',
		'cs',
		'dtd',
		'fla',
		'java',
		'm',
		'pl',
		'py',
	),
);

