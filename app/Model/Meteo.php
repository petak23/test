<?php

namespace DbTable;

use Nette;

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

  public function getMeasures(int $limit = 40, bool $show_units = false)
  {
    $tmp = $this->findAll()->order("data_time DESC")->order("id_sensor ASC")->limit(40);
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
        ];
        $dt0 = $v->data_time;
      } else {
        $dat["sensors"][$v->sensor->id] = $v->s_value . ($show_units ? " " . $v->sensor->value_type->unit : "");
      }
    }
    //dumpe($out);
    return $out; //$this->findAll()->order("data_time DESC")->order("id_sensor ASC")->limit(40);
  }
}
