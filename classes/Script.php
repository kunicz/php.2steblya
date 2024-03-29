<?

namespace php2steblya;

class Script
{
	protected $scriptData;

	public function __construct($scriptData = [])
	{
		$this->scriptData = $scriptData;
	}

	/**
	 * инициализируем класс скрипта
	 */
	public static function initClass($scriptData = [])
	{
		$className = $scriptData['script'];
		$class = 'php2steblya\\scripts\\' . $className;
		if (class_exists($class)) {
			$scriptInstance = new $class($scriptData);
			$scriptInstance->init();
			die();
		} else {
			die('script not found');
		}
	}
}
