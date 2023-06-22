<?php

declare(strict_types=1);

namespace App\Services;

use Nette;

class Config
{
	use Nette\SmartObject;

	/**
	 * Seznam povolenych IP adres, ze kterych je mo6n0 spustit cron tasky.
	 */
	public $cronAllowedIPs;

	/**
	 * Seznam linku v paticce stranky
	 */
	public $links;


	/**
	 * Jmeno aplikace
	 */
	public $appName;


	public $fontRegular;
	public $fontBold;
	public $dataRetentionDays;
	public $minYear;
	public $reg_enabled;

	private $masterPassword;
	private $device_id = "";

	public function __construct(
		$ips,
		$masterPassword,
		$links,
		$appName,
		$fontRegular,
		$fontBold,
		$dataRetentionDays,
		$minYear,
		$reg_enabled
	) {
		$this->cronAllowedIPs = $ips;
		$this->masterPassword = $masterPassword;
		$this->links = $links;
		$this->appName = $appName;
		$this->fontRegular = $fontRegular;
		$this->fontBold = $fontBold;
		$this->dataRetentionDays = $dataRetentionDays;
		$this->minYear = $minYear;
		$this->reg_enabled = $reg_enabled;
	}

	public function setDeviceId(string $device_id): void
	{
		$this->device_id = $device_id;
	}


	/*private*/
	public function getMasterKey(): string
	{
		return $this->masterPassword;
		//$fullPwd = $this->device_id . $this->masterPassword;
		//dump($fullPwd, strlen($fullPwd));
		//$aba = hash("sha256", $this->device_id . $this->masterPassword, true);
		//return $aba; //substr($aba, 0, 32);
	}

	public function encrypt(string $data, string $fieldName): string
	{
		$aesIV = substr(hash("sha256", $fieldName, true), 0, 16);
		$aesKey = $this->getMasterKey();
		$encrypted = openssl_encrypt($data, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA, $aesIV);
		/*if ($encrypted === FALSE) {
			Logger::log('webapp', Logger::ERROR, "nelze zasifrovat");
		}*/
		return bin2hex($encrypted);
	}

	public function decrypt(string $data, string $fieldName): string|false
	{
		//dump($data, $fieldName);
		$aesIV = substr(hash("sha256", $fieldName, true), 0, 16);
		$aesKey = $this->getMasterKey();
		dump($aesIV);
		//dump($aesKey);
		$bindata = hex2bin($data);
		//dump($bindata);
		$decrypted = openssl_decrypt($bindata, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $aesIV); //| OPENSSL_ZERO_PADDING
		/*if ($decrypted == FALSE) {
			//Logger::log('webapp', Logger::ERROR, "nelze desifrovat");
			return "";
		}*/
		return $decrypted;
	}
}
