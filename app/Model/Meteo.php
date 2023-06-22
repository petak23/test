<?php

namespace DbTable;

use Nette;
use App\Exceptions;

/**
 * Model, ktory sa stara o tabulky
 * 
 * Posledna zmena 05.01.2023
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.6
 */
class Meteo extends Table
{
	/** @var string */
	protected $tableName = 'measures';

	/** Nette\Database\Table\Selection */
	public $devices;
	/** Nette\Database\Table\Selection */
	public $sensors;
	/** Nette\Database\Table\Selection */
	public $value_types;

	public function __construct(Nette\Database\Explorer $db)
	{
		parent::__construct($db);
		$this->devices = $this->connection->table("devices");
		$this->sensors = $this->connection->table("sensors");
		$this->value_types = $this->connection->table("value_types");
	}

	public function getSensors(): Nette\Database\Table\Selection
	{
		return $this->sensors;
	}

	public function getDevices(): array
	{
		$out = [];

		foreach ($this->devices as $d) {
			$senn = clone $this->sensors;
			$se = $senn->where("id_devices", $d->id)->order("id ASC");
			//dump($se->count());
			$out[] = [
				'info' => $d,
				'sensors' => $se,
			];
		}
		//dumpe($out);
		return $out;
	}

	public function getDevice(int $id_device): array
	{
		if ($id_device < 1)
			throw new Exceptions\DeviceNotFoundException("Incorect id_device: '{$id_device}'!", 5);

		$device = $this->devices->get($id_device);
		if ($device == null)
			throw new Exceptions\DeviceNotFoundException("Device with id '{$id_device}' not found!!!", 1);

		$out = [
			'info' => $device,
			'sensors' => $this->getDeviceSensors($id_device),
		];
		return $out;
	}

	public function getDeviceByDeviceName(string $device_name): ?Nette\Database\Table\ActiveRow
	{
		$device = $this->devices->where("device_name", $device_name)->limit(1)->fetch();
		if ($device == null)
			throw new Exceptions\DeviceNotFoundException("Device with name '{$device_name}' not found!!!", 1);
		if ($device_name != $device->device_name)
			throw new Exceptions\DeviceNotFoundException("Device name '{$device_name}' & device_name '{$device_name}' not same!!!", 2);

		return $device;
	}

	function getDeviceSensors(int $id_device): array
	{
		if ($id_device < 1)
			throw new Exceptions\DeviceNotFoundException("Incorect id_device: '{$id_device}'!", 5);

		$senn = clone $this->sensors;
		$sensors = $senn->where("id_devices", $id_device)->order("id ASC")->fetchAll();

		if (count($sensors) == 0)
			throw new Exceptions\DeviceNotFoundException("The device with id_device: '{$id_device}' has no sensors!", 7);

		return $sensors;
	}

	function recordMeasuresJson(String $cont): array
	{

		$c = explode("}", (string)$cont)[0] . "}";
		$items = json_decode($c, true);
		if (!isset($items['device_id']))
			throw new Exceptions\DeviceNotFoundException("Not correct JSON: '{$cont}'!", 1);

		$device = $this->getDeviceByDeviceName($items['device_id']);

		$device_sensors = $this->getDeviceSensors($device->id);

		$o = [];
		foreach ($device_sensors as $id => $sensor) {
			if (isset($items[$sensor->value_type->shortcut])) {
				$tmp = [
					"id_sensor"	=>	$id,
					"s_value"	=> $items[$sensor->value_type->shortcut]
				];

				$this->pridaj($tmp);
				$sensor->update([
					"last_data_time"	=> date('Y-m-d H:i:s'),
					"last_out_value"	=> $items[$sensor->value_type->shortcut],
				]);
				$o[] = $tmp;
			}
		}
		$tmp_device_update = ["last_login" => date('Y-m-d H:i:s')];
		if ($device->first_login == null) $tmp_device_update['first_login'] = date('Y-m-d H:i:s');
		$device->update($tmp_device_update);
		return $o;
	}

	public function getMeasures(int $limit = 80, bool $show_units = false): array
	{
		$tmp = $this->findAll()->order("data_time DESC")->order("id_sensor ASC")->limit($limit);
		$out = [];
		$dt0 = "";
		$dat = [];
		foreach ($tmp as $v) {
			if ($dt0 != $v->data_time) {
				if ($dt0 != "") {
					$out[] = $dat;
				}
				$dat = [
					"time" => $v->data_time,
					"sensors" => [
						$v->sensor->id => $v->s_value . ($show_units ? " " . $v->sensor->value_type->unit : ""),
					],
					"device" => $v->sensor->devices->name,
				];
				$dt0 = $v->data_time;
			} else {
				$dat["sensors"][$v->sensor->id] = $v->s_value . ($show_units ? " " . $v->sensor->value_type->unit : "");
			}
		}
		return $out;
	}
}
