<?php

namespace Ecloud\Integrations\Model\Api;

use Ecloud\Integrations\Api\IntegrationInterface;
use Ecloud\Integrations\Model\Export as ExportModel;
use Ecloud\Integrations\Model\Import as ImportModel;
use Ecloud\Integrations\Api\IntegrationResponseInterfaceFactory;
use Magento\Framework\App\CacheInterface;


class Integration implements IntegrationInterface
{
	const IMPORT_API_LOCKED_STATUS_CACHE_TAG = "ecloud_integrations_import_api_locked_status";
	const EXPORT_API_LOCKED_STATUS_CACHE_TAG = "ecloud_integrations_export_api_locked_status";
	const DEFAULT_LOCK_EXPIRATION = 3600;
	const CACHE_TAG = 'config_scopes';

	/**
	 * @var ExportModel
	 */
	protected $_exportModel;

	/**
	 * @var ImportModel
	 */
	protected $_importModel;

	/**
	 * @var IntegrationResponseInterfaceFactory
	 */
	protected $_integrationResponseFactory;

	/**
	 * @var CacheInterface
	 */
	protected $_cache;

	/**
	 * @var string
	 */
	protected $executionCacheTag;
	/**
	 * @param ExportModel $exportModel
	 * @param IntegrationResponseInterfaceFactory $exportResponseFactory
	 */
	public function __construct(
		ExportModel $exportModel,
		ImportModel $importModel,
		IntegrationResponseInterfaceFactory $exportResponseFactory,
		CacheInterface $cache
	) {
		$this->_exportModel = $exportModel;
		$this->_importModel = $importModel;
		$this->_integrationResponseFactory = $exportResponseFactory;
		$this->_cache = $cache;
		$this->executionCacheTag = null;
	}

	/** @inheritdoc */
	public function import($integrationName = null, $runType = ImportModel::RUN_TYPE_FULL_IMPORT, $data = [])
	{
		$this->executionCacheTag = self::IMPORT_API_LOCKED_STATUS_CACHE_TAG;
		try {
			$this->validateLock();
		} catch (\Exception $e) {
			return $this->endError($e, false);
		}

		try {
			$result = $this->_importModel->start($integrationName, $runType, $data);
			return $this->endSuccess($result);
		} catch (\Exception $e) {
			return $this->endError($e);
		}
	}

	/** @inheritdoc */
	public function export($integrationName = null, $data = null)
	{
		$this->executionCacheTag = self::EXPORT_API_LOCKED_STATUS_CACHE_TAG;
		try {
			$this->validateLock();
		} catch (\Exception $e) {
			return $this->endError($e, false);
		}

		try {
			$result = $this->_exportModel->start($integrationName, $data);
			return $this->endSuccess($result);
		} catch (\Exception $e) {
			return $this->endError($e);
		}
	}

	protected function validateLock()
	{
		// if ($this->isLocked($this->executionCacheTag))
		// 	throw new \Exception("Concurrent API request are locked. Wait for previous request to end before starting a new one");
		// $this->lock();
	}

	protected function isLocked()
	{
		return $this->_cache->load($this->executionCacheTag) === "1";
	}

	protected function lock()
	{
		// $this->_cache->save(
		// 	"1",
		// 	$this->executionCacheTag,
		// 	[self::CACHE_TAG],
		// 	self::DEFAULT_LOCK_EXPIRATION
		// );
	}

	protected function unlock()
	{
		$this->_cache->save(
			"0",
			$this->executionCacheTag,
			[self::CACHE_TAG],
			self::DEFAULT_LOCK_EXPIRATION
		);
	}

	protected function endSuccess($result, $unlock = true)
	{
		$response = $this->_integrationResponseFactory->create();

		$response->setSuccess(true)
			->setResult($result)
			->setErrorTrace(null)
			->setErrorMessage(null);

		if ($unlock)
			$this->unlock();

		return $response;
	}

	protected function endError($error, $unlock = true)
	{
		$response = $this->_integrationResponseFactory->create();

		$response->setSuccess(false)
			->setResult(null)
			->setErrorTrace($error->getTraceAsString())
			->setErrorMessage($error->getMessage());

		if ($unlock)
			$this->unlock();

		return $response;
	}
}
