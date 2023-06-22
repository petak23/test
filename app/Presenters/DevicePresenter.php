<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Services;
use DbTable;
use Nette;

final class DevicePresenter extends Nette\Application\UI\Presenter
{
	/** DbTable\Meteo */
	public $meteo;

	/** DbTable\Main_menu */
	public $main_menu;

	/** @var Services\Config */
	//private $config;

	public function __construct(
		DbTable\Meteo $meteo,
		DbTable\Main_menu $main_menu,
		//Services\Config $config
	) {
		$this->meteo = $meteo;
		$this->main_menu = $main_menu;
		//$this->config = $config;
	}

	/*public function renderDefault(int $count = 10)
	{
		$count_sensors = $this->meteo->getSensors()->count();
		$this->template->meteo = $this->meteo->getMeasures($count * $count_sensors);
	}*/

	public function renderDevices()
	{
		$this->template->devices = $this->meteo->getDevices();
	}

	public function renderDevice(int $id)
	{
		$this->template->device = $this->meteo->getDevice($id);
	}

	function beforeRender()
	{
		$this->template->main_menu = $this->main_menu->getMenu();
	}
}
