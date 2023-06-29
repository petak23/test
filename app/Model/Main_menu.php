<?php

namespace DbTable;

use Nette;

/**
 * Model, ktory sa stara o tabulku main_menu
 * 
 * Posledna zmena 29.06.2023
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class Main_menu extends Table
{
	/** @var string */
	protected $tableName = 'main_menu';

	public function __construct(Nette\Database\Explorer $db, Nette\Security\User $user)
	{
		parent::__construct($db);
		$this->user = $user;
	}

	/** Vráti položky menu ako pole, ale len tie na ktoré mám oprávnenie */
	public function getMenu(): array
	{
		$_t =	$this->findAll();
		$out = [];
		foreach ($_t as $v) {
			$to_acl = explode(":", $v->link);
			if ($this->user->isAllowed($to_acl[0], $to_acl[1] == '' ? 'default' : $to_acl[1])) $out[$v->id] = $v;
		}
		return $out;
	}
}
