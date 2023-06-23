<?

namespace php2steblya;

use php2steblya\File;
use php2steblya\OrderData_item_sku as Sku;

class OrderData_item
{
	public $name;
	private $site;
	public object $sku;
	public int $initialPrice; //цена продажи
	public int $purchasePrice; //цена закупки
	public int $transportPrice; //цена продажи измененная (в функции pushTransport)
	public int $quantity;
	public array $properties;

	public function __construct(string $site, array $productFromTilda = null)
	{
		$this->site = $site;
		if (!$productFromTilda) return;
		$this->name = $productFromTilda['name'];
		$this->setScu($productFromTilda['sku']);
		$this->initialPrice = (int) $productFromTilda['price'];
		$this->transportPrice = (int) $productFromTilda['price'];
		$this->purchasePrice = 0;
		$this->quantity = (int) $productFromTilda['quantity'];
		$this->properties = $productFromTilda['options'];
	}
	public function getCrm(): array
	{
		$item = [
			'offer' => $this->offer(),
			'productName' => $this->name,
			'quantity' => $this->quantity,
			'initialPrice' => $this->transportPrice,
			'purchasePrice' => $this->purchasePrice,
			'properties' => $this->properties()
		];
		return $item;
	}
	private function offer(): array
	{
		$offerData = [];
		switch ($this->name) {
			case 'Транспортировочное':
				$offerData['externalId'] = '214';
				break;
			case 'Упаковка':
				$offerData['externalId'] =  '223';
				break;
		}
		if (!empty($offerData)) return $offerData;
		$catalog = new File(dirname(dirname(__FILE__)) . '/TildaYmlCatalog_' . $this->site . '.txt');
		$catalog = json_decode($catalog->getContents(), true);
		foreach ($catalog['offers'] as $offer) {
			if ($offer['vendorCode'] == $this->sku->get()) $offerData['externalId'] = $offer['id'];
		}
		return $offerData;
	}
	private function properties(): array
	{
		$props = [];
		if (empty($this->properties)) return $props;
		foreach ($this->properties as $prop) {
			if (in_array($prop['option'], ['выебри карточку', 'выбери карточку']) && in_array($this->name, castrated_items())) continue; //не публикуем карточку для кастратов
			$props[] = [
				'name' => str_replace('?', '', $prop['option']), //удаляем ? в названиях опций (скока? какой цвет?)
				'value' => preg_replace('/\s*\([^)]+\)/', '', $prop['variant']) //удаляем все, что в скобках
			];
		}
		if ($this->sku->get()) {
			$props[] = [
				'name' => 'артикул',
				'value' => $this->sku->get()
			];
		}
		if ($this->sku->isVitrina()) { // если товар с витрины
			$props[] = [
				'name' => 'готов',
				'value' => 'на витрине'
			];
		}
		if ($this->initialPrice) {
			$props[] = [
				'name' => 'цена',
				'value' => $this->initialPrice
			];
		}
		return $props;
	}
	public function pushProperty(array $data)
	{
		if (!isset($data['option']) || !isset($data['value'])) return;
		$this->properties[] = $data;
	}
	public function setTransposrPrice($site)
	{
		switch ($site) {
			case $_ENV['site_2steblya_id']:
				$price = 1000; //транспортировочное(500) + доставка(500)
				break;
			case $_ENV['site_stf_id']:
				$price = 700; //упаковка х 2(200) + доставка(500)
				break;
		}
		$this->transportPrice = $this->initialPrice - round($price / $this->quantity);
	}
	public function setScu($data)
	{
		$this->sku = new Sku($data);
	}
	public function setQuantity($data)
	{
		$this->quantity = $data;
	}
	public function setName($data)
	{
		$this->name = $data;
	}
	public function setPrice($data)
	{
		$this->initialPrice = $data;
		$this->transportPrice = $data;
		$this->purchasePrice = $data;
	}
	public function setInitialPrice($data)
	{
		$this->initialPrice = $data;
	}
	public function setPurchasePrice($data)
	{
		$this->purchasePrice = $data;
	}
}
