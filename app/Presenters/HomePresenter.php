<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Exceptions;
use App\Services;
use DbTable;
use Nette;
use Tracy\Debugger;
use Nette\Utils\Strings;

/**
 * Presenter pre homepage
 * 
 * Posledna zmena(last change): 23.06.2023
 *
 * Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */
final class HomePresenter extends BasePresenter
{
	/** @var DbTable\Meteo @inject */
	public $meteo;


	function renderDefault(): void
	{
		$this->template->devices = $this->meteo->getDevices();
	}

	public function renderMeasures(int $count = 10): void
	{
		$count_sensors = $this->meteo->getSensors()->count();
		$this->template->meteo = $this->meteo->getMeasures($count * $count_sensors);
	}
}
