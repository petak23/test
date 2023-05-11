<?php

declare(strict_types=1);

namespace App\Presenters;

use DbTable;
use Nette;
use Tracy\Debugger;
use Nette\Utils\Strings;


final class HomePresenter extends Nette\Application\UI\Presenter
{
	/** DbTable\Meteo */
	public $meteo;

	public function __construct(DbTable\Meteo $meteo)
	{
		$this->meteo = $meteo;
	}

	public function renderDefault()
	{
		$this->template->meteo = $this->meteo->getMeasures();
	}

	public function actionMeasure(String $message)
	{
		$tmp = explode(";", $message, 6);
		/*String message = "TE:" + String(temperature) + ";HU:" + String(humidity);
  	message += ";AP:" + String(abs_pressure) + ";RP:" + String(rel_pressure);
  	message += ";AL:" + String(altitude);*/
		//dumpe($tmp);
		foreach ($tmp as $v) {
			$m = explode(":", $v);
			if (count($m) == 2) {
				if ($m[0] == "TE") {
					$this->meteo->pridaj([
						"id_sensor"	=>	1,
						"s_value"	=> $m[1]
					]);
				} elseif ($m[0] == "HU") {
					$this->meteo->pridaj([
						"id_sensor"	=>	2,
						"s_value"	=> $m[1]
					]);
				} elseif ($m[0] == "RP") {
					$this->meteo->pridaj([
						"id_sensor"	=>	3,
						"s_value"	=> $m[1]
					]);
				} elseif ($m[0] == "LX") {
					$this->meteo->pridaj([
						"id_sensor"	=>	4,
						"s_value"	=> $m[1]
					]);
				}
			}
		}
		//$this->redirect("Home:default");
	}

	const DEVICE_ID = "134679";											 	// Dočasné nastavenie, kým sa nevyrieši prenos kľúčov...
	const PASS_PHRASE = "Heslo_pre456zakodovanie+++"; // Dočasné Hlavné heslo pre šifrovanie komunikácie

	public function actionMeasureEnc(String $message)
	{
	}

	/**
	 * Dekrypce datoveho bloku.
	 * 
	 * Datovy blok ma format:
	 *   <AES IV>:<AES_encrypted_data>
	 * 
	 * Obsahem sifrovanych dat je:
	 *   CRC32 of data (4 byte)
	 *   length of data (2 byte, high endian)
	 *   payload_data
	 *
	 */
	private function decryptDataBlock($data, $sessionKey/*, $logger*/)
	{
		// payload rozdelit na IV a cryptext
		$payload = explode(":", $data, 10);
		if (count($payload) < 2) {
			throw new \Exception("Bad request (2).");
		}
		$aesIvHex = Strings::trim($payload[0]);
		//D $logger->write( Logger::INFO, "iv: {$aesIvHex}");
		$aesIV = hex2bin($aesIvHex);

		$aesDataHex = Strings::trim($payload[1]);
		//D $logger->write( Logger::INFO, "data: {$aesDataHex}");
		$aesData = hex2bin($aesDataHex);

		$decrypted = openssl_decrypt($aesData, 'AES-256-CBC', hex2bin($sessionKey), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $aesIV);
		if ($decrypted == FALSE) {
			//$logger->write(Logger::ERROR,  "nelze rozbalit");
			throw new \Exception("Bad crypto block (1).");
		}

		$dataLen = (ord($decrypted[4]) << 8) | ord($decrypted[5]);
		//D $logger->write( Logger::INFO,  "data len {$dataLen}" );

		$crcReceived = bin2hex(substr($decrypted, 0, 4));
		$msgTotal = substr($decrypted, 6, $dataLen);
		$hash = hash("crc32b", $msgTotal, FALSE);

		if (strcmp($hash, $crcReceived) != 0) {
			//$logger->write(Logger::ERROR,  "Nesouhlasi CRC. Prijato: {$crcReceived} Spocteno: {$hash}");
			throw new \Exception("Bad CRC.");
		}

		return $msgTotal;
	}

	/**
	 * Format data zpravy:
	 *      <session_id>\n
	 *      <data_payload_block>\n
	 *      
	 * Format data_paylod_block:
	 *      <AES IV>:<AES_encrypted_data>
	 * oboji zapsane jako retezec hexitu
	 * 
	 * Obsah AES_encrypted_data:
	 *      <CRC32 z desifrovaneho payloady><delka dat 2 byte><data><random padding>
	 *      
	 * Result:
	 *      200 - OK
	 *      403 - re-login, session invalid
	 *      400 - other error
	 */
	public function actionData()
	{
		Debugger::enable(Debugger::PRODUCTION);
		//$logger = new Logger(self::NAME);

		try {

			$httpRequest = $this->getHttpRequest();

			$remoteIp = $httpRequest->getRemoteAddress();
			//$logger->setContext("D");

			$postSize = strlen($httpRequest->getRawBody());
			//$logger->write(Logger::INFO, "data+ {$postSize}b {$remoteIp}");
			//D $logger->write( Logger::INFO, "[" . $httpRequest->getRawBody() ."]" );

			$radky = explode("\n", $httpRequest->getRawBody(), 3);
			if (count($radky) < 2) {
				throw new \Exception("Bad request (1).");
			}
			$session = Strings::trim($radky[0]);
			$data = Strings::trim($radky[1]);

			if (Strings::length($session) == 0) {
				throw new \Exception("Empty session ID.");
			}

			$sessionData = explode(":", $session, 3);
			if (count($sessionData) < 2) {
				throw new \Exception("Bad request (3).");
			}
			//$logger->write(Logger::INFO, "S:{$sessionData[0]}");
			$sessionDevice = $this->datasource->checkSession($sessionData[0], $sessionData[1]);
			//$logger->setContext("D;D:{$sessionDevice->deviceId}");

			//D $logger->write( Logger::INFO,  $sessionDevice );

			$msgTotal = $this->decryptDataBlock($data, $sessionDevice->sessionKey/*, $logger*/);

			//D/ $logger->write( Logger::INFO,  '  celek: ' . bin2hex($msgTotal) );
			//$this->msgProcessor->process($sessionDevice, $msgTotal, $remoteIp, $logger);

			//$logger->write(Logger::INFO, "OK");

			$this->template->result = "OK";
		} catch (\App\Exceptions\NoSessionException $e) {

			//$logger->write(Logger::ERROR,  "ERR: " . get_class($e) . ": " . $e->getMessage());

			$httpResponse = $this->getHttpResponse();
			$httpResponse->setCode(Nette\Http\IResponse::S403_Forbidden);
			$httpResponse->setContentType('text/plain', 'UTF-8');
			$response = new \Nette\Application\Responses\TextResponse("ERR {$e->getMessage()}");
			$this->sendResponse($response);
			$this->terminate();
		} catch (\Exception $e) {

			//TODO: zapsat chybu do tabulky chyb

			//$logger->write(Logger::ERROR,  "ERR: " . get_class($e) . ": " . $e->getMessage());

			$httpResponse = $this->getHttpResponse();
			$httpResponse->setCode(Nette\Http\IResponse::S400_BadRequest);
			$httpResponse->setContentType('text/plain', 'UTF-8');
			$response = new \Nette\Application\Responses\TextResponse("ERR {$e->getMessage()}");
			$this->sendResponse($response);
			$this->terminate();
		}
	}
}
