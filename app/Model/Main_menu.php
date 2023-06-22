<?php

namespace DbTable;

use Nette;

/**
 * Model, ktory sa stara o tabulku main_menu
 * 
 * Posledna zmena 16.06.2023
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class Main_menu extends Table
{
	/** @var string */
	protected $tableName = 'main_menu';

	public function getMenu(): Nette\Database\Table\Selection
	{
		return $this->findAll();
	}
}
