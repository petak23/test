<?php

declare(strict_types=1);

namespace App\Presenters;

use DbTable;

/**
 * Presenter pre zariadenia
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
final class DevicePresenter extends BasePresenter
{
	/** @var DbTable\Meteo @inject */
	public $meteo;

	public function actionDefault()
	{
		$this->setView("devices");
	}

	public function renderDevices()
	{
		$this->template->devices = $this->meteo->getDevices();
	}

	public function renderDevice(int $id)
	{
		$this->template->device = $this->meteo->getDevice($id);
	}
}
