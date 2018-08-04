<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);
session_start();

define('CONFIG', 'config/');
define('DB_CONFIG', CONFIG.'config.ini');
define('PUBLIC_HTML', 'public/');
define('DS', '/');
define('PHP', '.php');
define('VIEW', 'template/');
define('COMPONENT_PATH', VIEW.'Component/');
define('COMPONENT', COMPONENT_PATH.'component'.PHP);
define('ADMIN_COMPONENTS', ['user']);
define('LAYOUT', VIEW.'Layout/');
define('HEADER', LAYOUT.'header');
define('SIDEBAR', LAYOUT.'sidebar');
define('FOOTER', LAYOUT.'footer');
define('ClASSES', 'classes/');
define('JS', 'js/');
define('CSS', PUBLIC_HTML.'css/');
define('IMG', PUBLIC_HTML.'img/');
define('UPLOAD_DIR', IMG.'uploads/');
define('DEBUG', true);
define('ERROR_LOG', true);
define('SITE_ROOT', __DIR__);
define('LOG_PATH', SITE_ROOT.'/logs/error.txt');
define('USER_TABLE', 'valami_vaasd_asda');
define('SECRET_KEY', 'xzqwe2sdadasdas');
define('GUEST_ENTRY', '?controller=user&action=login');
define('USER_ENTRY', '?controller=user&action=welcome');
define('ADMIN_ENTRY', '?controller=user');

include (CONFIG.'functions.php');
generateToken();
$Auth = getSession('Auth');
define('DEFAULT_LAYOUT', ($Auth && $Auth['rank'] > 1) ? 'admin' : 'default');
define('TOKEN', $_POST['token'] ?? "");
define('TOKEN_VALID', strlen(TOKEN) ? hash_equals($_SESSION['token'], TOKEN) : false);

$controller = ucfirst((!empty($_GET['controller']) && preg_match('/[^a-z_\-0-9]*$/i', $_GET['controller'])) ? strtolower($_GET['controller']) : "Home");
$action = (!empty($_GET['action']) && preg_match('/[^a-z_\-0-9]*$/i', $_GET['action'])) ? strtolower($_GET['action']) : "index";
$ajax = (!empty($_GET['ajax']) && $_GET['ajax'] == "ajax");

if (!file_exists(DB_CONFIG)) {
	$controller = "Home";
	if (!in_array($action, ['setmysql','setup'])) {
		$action = "setup";
	}
	$ajax = false;
} elseif ($controller === "Home" && $action === "setup"){
	$controller = "User";
	$action = "login";
	$ajax = false;
}

spl_autoload_register('loadClass');
register_shutdown_function("fatal_error");
$T = [];

try {
	$Model = new $controller($action, $ajax);
	$T = $Model::$action();
	if ($ajax) { die(); }
	generateToken(true);
	if (empty($T['view'])) {
		$view_path = VIEW.$controller.DS.$action.PHP;
	} else {
		$path = explode('.', $T['view']); //similiar like in laravel folder_name.view_name
		$view_path = VIEW.$path[0].DS.$path[1].PHP;
	}
	if (file_exists($view_path)) {
		loadView($T, $view_path);
	}
} catch(Exception $e) {
	die('error');
}

function loadView ($T, $view_path) {
	$Auth = getSession('Auth');
	if (!empty($T)) {
		extract($T, EXTR_PREFIX_SAME, "wddx");
	}
	if (empty($layout)) { $layout = DEFAULT_LAYOUT; }
	$HEADER = HEADER.'_'.$layout.PHP;
	$SIDEBAR = SIDEBAR.'_'.$layout.PHP;
	$FOOTER = FOOTER.'_'.$layout.PHP;
	include (LAYOUT.($layout ?? DEFAULT_LAYOUT).PHP);	
} 
?>