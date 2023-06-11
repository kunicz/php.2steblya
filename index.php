<?
require_once __DIR__ . '/!autoload.php';

if (isset($_GET['script'])) {
	$className = $_GET['script'];
	$class = 'php2steblya\\scripts\\' . $className;
	if (class_exists($class)) {
		switch ($className) {
			case 'TildaOrderWebhook':
				$scriptInstance = new $class($_GET['site']);
				break;
			default:
				$scriptInstance = new $class();
		}
		$scriptInstance->init();
		die($scriptInstance->log->getJson());
	} else {
		die('script not found');
	}
} else {
	header('Location: https://2steblya.ru/php');
	die();
}
