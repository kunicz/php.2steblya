<?

namespace php2steblya;

use php2steblya\Logger;

class Api
{
	protected $log;
	public $response;
	private $queryString;
	private $queryStringArgs;
	private $queryStringAdres;
	protected $adres;
	protected $token;

	public function get(string $method, array $args)
	{
		$this->log = new Logger('api GET /' . $method);
		$this->curl('get', $method, $args);
		return $this->response;
	}
	public function post(string $method, array $args)
	{
		$this->log = new Logger('api POST /' . $method);
		$this->curl('post', $method, $args);
		return $this->response;
	}
	private function curl(string $type, string $method, array $args)
	{
		$this->queryString($method, $args);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		switch ($type) {
			case 'get':
				curl_setopt($ch, CURLOPT_URL, $this->queryString);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				break;
			case 'post':
				curl_setopt($ch, CURLOPT_URL, $this->queryStringAdres);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->queryStringArgs . '&apiKey=' . $this->token);
				break;
		}
		try {
			$response = curl_exec($ch);
			if (!$response) throw new \Exception('api (' . $type . ' ' . $method . ') returned null');
			$this->response = json_decode($response);
			if (!$this->response) throw new \Exception('api (' . $type . ' ' . $method . ') returned with error -> ' . $response);
		} catch (\Exception $e) {
			$this->log->pushError($e->getMessage());
			$this->log->writeSummary();
			die($this->log->getJson());
		}
		curl_close($ch);
	}
	private function queryString(string $method, array $args)
	{
		$this->queryStringAdres = $this->adres . '/' . $method;
		$this->queryStringArgs = http_build_query($args);
		$this->queryString = $this->queryStringAdres . '?apiKey=' . $this->token . '&' . $this->queryStringArgs;
	}
	public function getQueryStringArgs(): string
	{
		return $this->queryStringArgs;
	}
	public function getError()
	{
	}
}