<?php

namespace DbTable;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\Strings;


/**
 * Reprezentuje repozitar pre databázovu tabulku
 * 
 * Posledna zmena(last change): 05.01.2023
 * 
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.2.0
 */
abstract class Table
{

  use Nette\SmartObject;

  protected $tableName;

  /** @var Nette\Database\Explorer */
  protected $connection;

  /** @var Nette\Security\User */
  protected $user;

  /**
   * @throws Nette\InvalidStateException */
  public function __construct(Nette\Database\Explorer $db)
  {
    $this->connection = $db;
    if ($this->connection === NULL) {
      $class = get_class($this);
      throw new Nette\InvalidStateException("Nemám pripojenie na DB!");
    }
    if ($this->tableName === NULL) {
      $class = get_class($this);
      throw new Nette\InvalidStateException("Názov tabuľky musí byť definovaný v $class::\$tableName.");
    }
  }

  /** 
   * Vracia celu tabulku z DB */
  protected function getTable(): Selection
  {
    if ($this->connection === NULL) {
      throw new Nette\InvalidStateException("Nemám pripojeniena DB!");
    }
    return $this->connection->table($this->tableName);
  }

  /** 
   * V poli vrati info o jednotlivych stlpcoch tabulky
   * @ return array */
  /*public function getTableColsInfo(): array {
    $pom = $this->connection->getConnection()->getSupplementalDriver()->getColumns($this->tableName);
    $out = [];
    foreach ($pom as $key => $value) {
      $out[$key] = $value["vendor"];
    }
    return $out;
  }*/

  /** 
   * Funkcia v poli vrati zakladne info. o pripojeni. */
  public function getDBInfo(): array
  {
    $pom = explode(";", $this->connection->getConnection()->getDsn()); // Rozlozi text na host a dbname napr. "mysql:host=localhost:8111;dbname=d264787_echomsz"
    $out = [];
    foreach ($pom as $p) {
      $t = explode("=", $p);
      $x = explode(":", $t[0]);
      if (is_array($x) && count($x) == 2) {
        $out[$x[1]] = $t[1];
      } else {
        $out[$t[0]] = $t[1];
      }
    }
    return $out;
  }

  /** 
   * Vracia vsetky zaznamy z DB */
  public function findAll(): Selection
  {
    return $this->getTable();
  }

  /** 
   * Vracia vyfiltrovane zaznamy na zaklade vstupneho pola */
  public function findBy(string|array $by): Selection
  {
    return $this->getTable()->where($by);
  }

  /** 
   * Rovnak ako findBy ale vracia len jeden zaznam */
  public function findOneBy(string|array $by): ?ActiveRow
  {
    return $this->findBy($by)->limit(1)->fetch();
  }

  /** 
   * Vracia zaznam s danym primarnym klucom */
  public function find(int $id): ?ActiveRow
  {
    return $this->getTable()->get($id);
  }

  /** 
   * Hlada jednu polozku podla specifickeho nazvu a min. urovne registracie uzivatela
   * @param string $spec_nazov Specificky nazov
   * @param int $id_reg Min. uroven registracie */
  public function hladaj_spec(string $spec_nazov, int $id_reg = 5): ?ActiveRow
  {
    return $this->findOneBy(["spec_nazov" => $spec_nazov, "id_user_roles <= " . $id_reg]);
  }

  /** 
   * Hlada jednu polozku podla id a min. urovne registracie uzivatela
   * @param int $id Id polozky
   * @param int $id_reg Min. uroven registracie */
  public function hladaj_id(int $id = 0, int $id_reg = 5): ?ActiveRow
  {
    return $this->findOneBy(["id" => $id, "id_user_roles <= " . $id_reg]);
  }

  /** 
   * Zmeni spec nazov na '-' ak min. uroven registracie uzivatela suhlasi
   * @param string $spec_nazov Specificky nazov
   * @param int $id_reg Min. uroven registracie */
  public function delSpecNazov(string $spec_nazov, int $id_reg): void
  {
    $this->hladaj_spec($spec_nazov, $id_reg)->update(['spec_nazov' => '-']);
  }

  /** 
   * Funkcia skontroluje a priradi specificky nazov pre polozku
   * @param string $nazov nazov clanku */
  public function najdiSpecNazov(string $nazov): string
  {
    //Prevedie na tvar pre URL s tym, ze _ akceptuje
    $spec_nazov = Strings::webalize($nazov, '_');
    $pom = 0;
    if ($this->hladaj_spec($spec_nazov)) {
      do {
        $pom++;
      } while ($this->hladaj_spec($spec_nazov . $pom));
    }
    return $spec_nazov . ($pom == 0 ? '' : $pom);
  }

  /** 
   * Prida zaznam(y) do tabulky
   * @param  array|\Traversable|Selection array($column => $value)|\Traversable|Selection for INSERT ... SELECT
   * Returns IRow or number of affected rows for Selection or table without primary key */
  public function pridaj($data): ActiveRow|int|bool
  {
    return $this->getTable()->insert($data);
  }

  /** 
   * Opravy v tabulke zaznam s danym id
   * @param mixed $id primary key
   * @param iterable (column => value) */
  public function repair(mixed $id, iterable $data): ?ActiveRow
  {
    $this->find($id)->update($data); //Ak nieco opravil tak true inak(nema co opravit) false
    return $this->find($id);
  }

  /**
   * @deprecated use repair */
  public function oprav(mixed $id, iterable $data): ?ActiveRow
  {
    return $this->repair($id, $data);
  }

  /** 
   * Funkcia pridava alebo aktualizuje v DB podla toho, ci je zadané ID */
  public function uloz(iterable $data, mixed $id = 0): ActiveRow|int|bool
  {
    return $id ? $this->repair($id, $data) : $this->pridaj($data);
  }

  /**
   * Zmaze v tabulke zaznam s danym id
   * Return number of affected rows */
  public function zmaz(mixed $id): int
  {
    return isset($id) ? $this->find($id)->delete() : 0;
  }

  /** 
   * Funkcia vymaze subor ak exzistuje
   * @param string $subor Nazov suboru aj srelativnou cestou
   * Ak zmaze alebo neexistuje(nie je co mazat) tak vráti true inak false */
  public function deleteFile(string $file): bool
  {
    return (is_file($file)) ? unlink($file) : true;
  }
}
