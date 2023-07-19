<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\User\SignInFormFactory;
use DbTable;
use Nette\Application\UI;

/**
 * Zakladny presenter pre vsetky presentery
 * 
 * Posledna zmena(last change): 19.07.2023
 *
 * Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.1
 */
abstract class BasePresenter extends UI\Presenter
{
	/** @var DbTable\Main_menu @inject */
	public $main_menu;

	/** @var SignInFormFactory @inject*/
	public $signInForm;

	/** @persistent */
	public $backlink = '';

	function beforeRender(): void
	{
		// Kontrola ACL
		if (!$this->user->isAllowed($this->name, $this->action)) {
			$this->flashRedirect('Home:', "Akcia {$this->action} nie je dovolená. Prihláste sa, prosím!", 'danger');
			$this->template->allowed = false;
		} else {
			$this->template->allowed = true;
		}

		$this->template->db_info_link = $this->main_menu->getDBInfo();

		$this->template->main_menu = $this->main_menu->getMenu();
	}

	/** 
	 * Akcia pre odhlasenie - spolocna pre vsetky presentery */
	public function actionSignOut(): void
	{
		$this->getUser()->logout(true); // Odhlásenie spolu so smazaním identity
		$this->flashRedirect('Home:', "Bol si odhlásený...", 'success');
	}

	/** 
	 * Formular pre prihlasenie uzivatela */
	protected function createComponentSignInForm(): UI\Form
	{
		$form = $this->signInForm->create(/*$this->language*/);
		$form['login']->onClick[] = function () {
			//$useri = $this->user->getIdentity();
			//$this->myMailer->sendAdminMail("Prihlásenie", "Prihlásenie užívateľa:" . $useri->meno . " " . $useri->priezvisko);
			$this->flashMessage('Prihlásenie prebehlo v poriadku', 'success');
			$this->restoreRequest($this->backlink);
			$this->redirect('Home:');
		};
		/*$form['forgottenPassword']->onClick[] = function ($button) {
			$this->redirect('User:forgottenPassword', [$button->getForm()->getHttpData()["email"]]);
		};*/
		return $form; //$this->_vzhladForm($form);
	}

	/** 
	 * Funkcia pre zjednodusenie vypisu flash spravy a presmerovania
	 * @param array|string $redirect Adresa presmerovania
	 * @param string $text Text pre vypis hlasenia
	 * @param string $druh - druh hlasenia */
	public function flashRedirect(array|string $redirect, string $text = "", string $druh = "info"): void
	{
		$this->flashMessage($text, $druh);
		if (is_array($redirect)) {
			if (count($redirect) > 1) {
				if (!$this->isAjax()) {
					$this->redirect($redirect[0], $redirect[1]);
				} else {
					$this->redrawControl();
				}
			} elseif (count($redirect) == 1) {
				$this->redirect($redirect[0]);
			}
		} else {
			if (!$this->isAjax()) {
				$this->redirect($redirect);
			} else {
				$this->redrawControl();
			}
		}
	}

	/**
	 * Funkcia pre zjednodusenie vypisu flash spravy a presmerovania aj pre chybovy stav
	 * @param boolean $ok Podmienka
	 * @param array|string $redirect Adresa presmerovania
	 * @param string $textOk Text pre vypis hlasenia ak je podmienka splnena
	 * @param string $textEr Text pre vypis hlasenia ak NIE je podmienka splnena  */
	public function flashOut($ok, $redirect, $textOk = "", $textEr = ""): void
	{
		if ($ok) {
			$this->flashRedirect($redirect, $textOk, "success");
		} else {
			$this->flashMessage($textEr, 'danger');
		}
	}
}
