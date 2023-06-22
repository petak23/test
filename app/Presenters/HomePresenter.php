<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Exceptions;
use App\Services;
use DbTable;
use Exception;
use Nette;
use Tracy\Debugger;
use Nette\Utils\Json;
use Nette\Utils\Strings;

final class HomePresenter extends Nette\Application\UI\Presenter
{
	/** DbTable\Meteo */
	public $meteo;

	/** DbTable\Main_menu */
	public $main_menu;

	/** @var Services\Config */
	private $config;

	public function __construct(
		DbTable\Meteo $meteo,
		DbTable\Main_menu $main_menu,
		Services\Config $config
	) {
		$this->meteo = $meteo;
		$this->main_menu = $main_menu;
		$this->config = $config;
	}

	public function renderDefault(int $count = 10)
	{
		$count_sensors = $this->meteo->getSensors()->count();
		$this->template->meteo = $this->meteo->getMeasures($count * $count_sensors);
	}

	public function renderDevices()
	{
		$this->template->devices = $this->meteo->getDevices();
	}

	function beforeRender()
	{
		$this->template->main_menu = $this->main_menu->getMenu();
	}

	public function renderTest()
	{
		try {
			$cont = "{\"HU\":\"62.0\",\"TE\":\"24.90\",\"data_time\":\"22.06.2023 12:19:28\",\"out_time\":\"Poslené meranie: 22.06.2023 12:19:28\",\"device_id\":\"dht_11_doma\",\"enc_message\":\"497656656b746f723200000000000000:cc0c88f9f01c7475825c0e4c7a049c5083389fd69868a7910e119de3c2365cc7\"}\u0000";

			$c = explode("}", (string)$cont)[0] . "}";
			$items = json_decode($c, true);
			if (!isset($items['device_id']))
				throw new Exceptions\DeviceNotFoundException("Not correct JSON: '{$cont}'!", 1);

			$device = $this->meteo->getDeviceByDeviceName("dht_11_doma");
			$device_sensors = $this->meteo->getDeviceSensors($device->id);
			/*foreach ($device['sensors'] as $sensor) {
				dump($sensor);
				if (isset($items[$sensor->value_type->shortcut])) {
					$this->meteo->pridaj([
						"id_sensor"	=>	$sensor->id_value_type,
						"s_value"	=> $items[$sensor->value_type->shortcut]
					]);
				}
			}*/
			dump($items);
			dump($device);
			dumpe($device_sensors);
		} catch (Exceptions\DeviceNotFoundException $th) {
			dumpe($th->getMessage());
		}
	}

	public function actionMeasurePost(String $message = null)
	{
		$httpRequest = $this->getHttpRequest();

		$remoteIp = $httpRequest->getRemoteAddress();

		$cont = $httpRequest->getRawBody();
		try {
			$out = $this->meteo->recordMeasuresJson($cont);
			$m = [
				"status"  => 200,
				"out" => $out,
				//"rawBody" => (string)$cont,
			];
		} catch (Nette\Utils\JsonException $e) {
			$m = ["status"  => 500, 'err_msg' => "Chyba dekódovania json", "rawBody" => $cont];
		} catch (Exceptions\DeviceNotFoundException $th) {
			$m = ["status"  => 500, 'err_msg' => $th->getMessage(), "rawBody" => $cont];
		}

		$this->sendJson($m);
	}

	const DEVICE_ID = "134679";											 	// Dočasné nastavenie, kým sa nevyrieši prenos kľúčov...
	const PASS_PHRASE = "Heslo_pre456zakodovanie+++"; // Dočasné Hlavné heslo pre šifrovanie komunikácie

	public function actionMeasureEnc(String $message)
	{

		$this->config->setDeviceId(self::DEVICE_ID);

		/*$payload = explode(":", $message, 10);
		if (count($payload) < 2) {
			throw new \Exception("Bad request (2).");
		}
		dump($payload[0]);
		dump($payload[1]);
		
		$mes = "TE:25.00;HU:52.00";
		dump($mes);
		$encr = $this->config->encrypt($mes, Strings::trim($payload[0]));
		dump($encr);

		$decrypted = $this->config->decrypt($encr, Strings::trim($payload[0]));
		dump($decrypted);

		$decrypted = $this->config->decrypt(Strings::trim($payload[1]), Strings::trim($payload[0]));
		dumpe($decrypted);*/

		$decrypted = $this->decryptDataBlock($message, $this->config->getMasterKey());

		if ($decrypted == FALSE) {
			//$logger->write(Logger::ERROR,  "nelze rozbalit");
			throw new \Exception("Bad crypto block (1).");
		}

		dump(bin2hex($decrypted));
		dump(bin2hex($decrypted[4]), bin2hex($decrypted[5]));
		dump(ord($decrypted[4]) << 8, ord($decrypted[5]));
		$dataLen = (ord($decrypted[4]) << 8) | ord($decrypted[5]);
		//D $logger->write( Logger::INFO,  "data len {$dataLen}" );
		dump($dataLen);

		$crcReceived = bin2hex(substr($decrypted, 0, 4));
		$msgTotal = substr($decrypted, 6, $dataLen);
		$hash = hash("crc32b", $msgTotal, FALSE);
		dump($hash);
		dump($crcReceived);
		dump(bin2hex($msgTotal));

		if (strcmp($hash, $crcReceived) != 0) {
			//$logger->write(Logger::ERROR,  "Nesouhlasi CRC. Prijato: {$crcReceived} Spocteno: {$hash}");
			throw new \Exception("Bad CRC.");
		}


		dumpe($msgTotal);
		$httpResponse = $this->getHttpResponse();
		$httpResponse->setCode(Nette\Http\IResponse::S200_OK);
		$httpResponse->setContentType('text/plain', 'UTF-8');
		$response = new \Nette\Application\Responses\TextResponse("OK: ");
		$this->sendResponse($response);
		$this->terminate();
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
		//$aesIV /*Hex*/ = substr(hash("sha256", Strings::trim($payload[0]), true), 0, 16);
		$aesIV = hex2bin($aesIvHex);
		dump($aesIV);

		$aesDataHex = Strings::trim($payload[1]);
		//D $logger->write( Logger::INFO, "data: {$aesDataHex}");
		$aesData = hex2bin($aesDataHex);
		dump($aesData);
		//$key = hex2bin($sessionKey);
		$key = $sessionKey;
		dump($key);
		$decrypted = openssl_decrypt(
			$aesData,
			'AES-256-CBC',
			$key,
			OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
			$aesIV
		);

		if ($decrypted == FALSE) {
			//$logger->write(Logger::ERROR,  "nelze rozbalit");
			throw new \Exception("Bad crypto block (1).");
		}


		$dataLen = (ord($decrypted[4]) << 8) | ord($decrypted[5]);
		//D $logger->write( Logger::INFO,  "data len {$dataLen}" );

		$crcReceived = bin2hex(substr($decrypted, 0, 4));
		$msgTotal = substr($decrypted, 6, $dataLen);
		$hash = hash("crc32b", $msgTotal, FALSE);
		dumpe($msgTotal);
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
