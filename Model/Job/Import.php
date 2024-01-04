<?php

namespace Ecloud\Integrations\Model\Job;

use Ecloud\Integrations\Helper\Data;
use Firebear\ImportExport\Api\Import\BeforeRunInterface;
use Firebear\ImportExport\Api\Import\RunInterface;
use Firebear\ImportExport\Api\Import\ProcessInterface;
use Firebear\ImportExport\Api\Import\ConsoleInterface;
use Firebear\ImportExport\Model\Job\Handler\CompressHandler;

/**
 * Runs import job using firebear webapi
 */
class Import
{
	const LOG_NAME = "ECLOUD_IMPORT_JOB";

	/**
	 * @var Data
	 */
	protected $_helper;

	/**
	 * @var BeforeRunInterface
	 */
	protected $_beforeRun;

	/**
	 * @var RunInterface
	 */
	protected $_run;

	/**
	 * @var ProcessInterface
	 */
	protected $_process;

	/**
	 * @var ConsoleInterface
	 */
	protected $_console;

	/**
	 * @var CompressHandler
	 */
	protected $_compressHandler;

	public function __construct(
		Data $helper,
		BeforeRunInterface $beforeRun,
		RunInterface $run,
		ProcessInterface $process,
		ConsoleInterface $console,
		CompressHandler $compressHandler
	) {
		$this->_helper = $helper;
		$this->_beforeRun = $beforeRun;
		$this->_run = $run;
		$this->_process = $process;
		$this->_console = $console;
		$this->_compressHandler = $compressHandler;
	}

	/**
	 * Executes the full import job given by its ID
	 * @param string $jobId Firebear import job ID to execute
	 */
	public function execute($jobId)
	{
		try {
			$fileId = $this->beforeRun($jobId);
			$this->run($jobId, $fileId);
			$this->process($jobId, $fileId, 0, "");
			$messages = $this->console($fileId);
			return $messages;
		} catch (\Exception $e) {
			$messages = $this->console($fileId);
			throw new \Exception("Couldn't run import job: " . $e->getMessage() . ": " . implode(".\n", $messages));
		}
	}

	/**
	 * STEP 1: before run
	 * @param string $jobId
	 * @return string file ID to import
	 */
	protected function beforeRun($jobId)
	{
		try {
			$fileId = $this->_beforeRun->execute($jobId);

			if (!$fileId || $fileId == "")
				throw new \Exception("Invalid file ID");
			return $fileId;
		} catch (\Exception $e) {
			throw new \Exception("Error on step 1 (before run). {$e->getMessage()}");
		}
	}

	/**
	 * STEP 2: run
	 * @param string $jobId
	 * @param string $fileId file ID to import
	 * @throws \Exception when the result is failiure
	 */
	protected function run($jobId, $fileId)
	{
		try {
			$success = $this->_run->execute($jobId, $fileId);

			if (!$success)
				throw new \Exception("Failiure running");
			return $success;
		} catch (\Exception $e) {
			throw new \Exception("Error on step 2 (run). {$e->getMessage()}");
		}
	}

	/**
	 * STEP 3: process
	 * @param string $jobId
	 * @param string $fileId file ID to import
	 * @param int $offset bunch number in which to start the process
	 * @param string $error
	 * @throws \Exception when the result is failiure
	 */
	protected function process($jobId, $fileId, $offset = 0, $error = "")
	{
		try {
			/** @var \Firebear\ImportExport\Api\Import\ProcessResponseInterface $response  */
			$response = $this->_process->execute($jobId, $fileId, $offset, $error);
			$processor = $this->_process->getProcessor();
			$this->_compressHandler->execute($processor->getJob(), $fileId, (int)$response->getResult());

			if (!$response || !$response->getResult())
				throw new \Exception("Invalid response");
			return $response;
		} catch (\Exception $e) {
			throw new \Exception("Error on step 3 (process). {$e->getMessage()}");
		}
	}

	/** 
	 * Get logs of a job
	 * @param string $fileId file ID to import
	 * @param int $counter debug line in which to start the response. Default (0) is get all lines
	 * @param array array of debug messages when importing
	 */
	protected function console($fileId, $counter = 0)
	{
		try {
			$response = $this->_console->execute($fileId, $counter);

			if (!$response || $response == "")
				throw new \Exception("Invalid response");
			return $this->formatMessages($response);
		} catch (\Exception $e) {
			return [];
			// throw new \Exception("Error on step 4 (console). {$e->getMessage()}");
		}
	}

	/**
	 * Separates HTML debug messages into an array with only text for each message
	 * @param string $messagesString HTML string with debug messages
	 * @return array an array of separated debug, info and warning messages
	 */
	protected function formatMessages($messagesString)
	{
		// Get only messages array (remove HTML)
		$spanArray = explode("<span text=\"item\"></span><br/>", $messagesString);

		$info = $debug = $warning = [];
		$prefix = "";
		foreach ($spanArray as $html) {
			if (!str_ends_with(trim($html), "</span>")) {
				$prefix .= trim($html);
				continue;
			} else {
				$html = $prefix . trim($html);
				$prefix = "";
			}
			$message = trim(preg_replace("/<span class=\"console-(info)?(debug)?(warning)?\">/", "$1$2$3: ", $html), "\r");
			$message = trim(preg_replace("/\<\/span\>/", "", $message), "\r");

			// Filter null lines
			if (!$message) continue;

			// Separate info, debug and warning
			$messageData = explode(":", $message, 2);
			$type = trim(str_replace("/", "", $messageData[0]));
			$messageWithDate = trim($messageData[1]);
			$message = trim(explode(" : ", $messageWithDate, 2)[1]);
			if ($type == "info") $info[] = $message;
			else if ($type == "debug") $debug[] = $message;
			else $warning[] = $message;
		}

		return array_merge($debug, $warning);
	}
}
