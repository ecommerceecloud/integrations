<?php

namespace Ecloud\Integrations\Model\Connection;

use Ecloud\Integrations\Api\ConnectionInterface;
use Ecloud\Integrations\Helper\Data;

class AbstractConnection implements ConnectionInterface
{
	const ACTIVE_PLATFORM_CONFIG_PATH = "{{integrationType}}/{{integrationName}}/platform";
	const ERP_PRODUCT_ID_ATTRIBUTE_CODE = "erp_product_id";
	const ERP_PARENT_ID_ATTRIBUTE_CODE = "erp_parent_product_id";
	const ERP_ORDER_SYNC_ATTRIBUTE_CODE = "erp_order_sync";
	const ERP_ORDER_SYNC_COUNT_ATTRIBUTE_CODE = "erp_order_sync_count";


	/**
	 * @var Data
	 */
	protected $_helper;

	/**
	 * @var array
	 */
	protected $_connectionsByPlatform;

	/**
	 * @var string
	 */
	protected $_logName;

	/**
	 * @var string
	 */
	protected $_connectionName;

	public function __construct(
		Data $helper,
		array $connections
	) {
		$this->_helper = $helper;
		$this->_connectionsByPlatform = [];
		foreach ($connections as $connectionLabel => $connection) {
			$this->_connectionsByPlatform[$connection->getConnectionName()] = $connection;
		}
	}

	/** @inheritdoc */
	public function getActivePlatformConnectionByIntegration($integrationName, $integrationType)
	{
		$integrationConnectionConfigPath = str_replace("{{integrationName}}", $integrationName, self::ACTIVE_PLATFORM_CONFIG_PATH);
		$integrationConnectionConfigPath = str_replace("{{integrationType}}", $integrationType, $integrationConnectionConfigPath);
		$activePlatform = $this->_helper->getGeneralConfigValue($integrationConnectionConfigPath);

		try {
			if (array_key_exists($activePlatform, $this->_connectionsByPlatform))
				return $this->_connectionsByPlatform[$activePlatform];
			throw new \Exception("No connection for platform $activePlatform for $integrationName integration");
		} catch (\Exception $e) {
			throw new \Exception("No connection for platform $activePlatform for $integrationName integration");
		}
	}

	public function getConnectionName()
	{
		return $this->_connectionName;
	}

	/** @inheritdoc */
	public function log($message, $array = null)
	{
		return $this->_helper->log($message, $this->_logName, $array);
	}

	public static function getIntegrationFunctionName($integrationName)
	{
		// To lower case
		$functionName = strtolower($integrationName);
		// Change _ for spaces
		$functionName = str_replace("_", " ", $functionName);
		// Each word to upper case
		$functionName = ucwords($functionName);
		// Remove spaces
		return str_replace(" ", "", $functionName);
	}

	/**
	 * IMPORT STEP 1: Get entity IDs for import
	 * @inheritdoc
	 */
	public function getImportIdList($importName, $entityTargetList = null)
	{
		$importFunctionName = self::getIntegrationFunctionName($importName);
		$importFunctionName = "getImport" . $importFunctionName . "Ids";
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid import function name $importFunctionName");

		return $this->$importFunctionName($entityTargetList);
	}

	/**
	 * IMPORT STEP 2: Filter entity IDs for export
	 * @inheritdoc
	 */
	public function filterImportIdList($importName, $erpIdList)
	{
		$importFunctionName = self::getIntegrationFunctionName($importName);
		$importFunctionName = "filterImport" . $importFunctionName . "Ids";
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid import function name $importFunctionName");

		return $this->$importFunctionName($erpIdList);
	}

	/**
	 * IMPORT STEP 3: Get entities for import
	 * @inheritdoc
	 */
	public function getImportList($importName, $erpIdList = null, $entityTargetList = null)
	{
		$importFunctionName = self::getIntegrationFunctionName($importName);
		$importFunctionName = "getImport" . $importFunctionName;
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid import function name $importFunctionName");

		return $this->$importFunctionName($erpIdList, $entityTargetList);
	}

	/**
	 * IMPORT STEP 4: Filter entities for export
	 * @inheritdoc
	 */
	public function filterImportList($importName, $erpEntityList)
	{
		$importFunctionName = self::getIntegrationFunctionName($importName);
		$importFunctionName = "filterImport" . $importFunctionName . "Data";
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid import function name $importFunctionName");

		return $this->$importFunctionName($erpEntityList);
	}

	/**
	 * IMPORT STEP 5: Format entities for import
	 * @inheritdoc
	 */
	public function formatImportList($importName, $erpEntityList)
	{
		$importFunctionName = self::getIntegrationFunctionName($importName);
		$importFunctionName = "formatImport" . $importFunctionName . "Data";
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid import function name $importFunctionName");

		return $this->$importFunctionName($erpEntityList);
	}

	/**
	 * Error during import
	 * @inheritdoc
	 */
	public function errorImport($importName, $lastStep, $erpEntityList = null, $erpFormattedEntityList = null)
	{
		$importFunctionName = self::getIntegrationFunctionName($importName);
		$importFunctionName = "errorImport" . $importFunctionName;
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid import function name $importFunctionName");

		return $this->$importFunctionName($lastStep, $erpEntityList, $erpFormattedEntityList);
	}

	/**
	 * Custom import logic
	 * @inheritdoc
	 */
	public function customImportStrategy($importName, $erpEntityList = null, $erpFormattedEntityList = null)
	{
		$importFunctionName = self::getIntegrationFunctionName($importName);
		$importFunctionName = "customImport" . $importFunctionName;
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid import function name $importFunctionName");

		return $this->$importFunctionName($erpEntityList, $erpFormattedEntityList);
	}

	/**
	 * EXPORT STEP 1: Get entities for export
	 * @inheritdoc
	 */
	public function getExportList($exportName, $entityTargetList = null)
	{
		$importFunctionName = self::getIntegrationFunctionName($exportName);
		$importFunctionName = "getExport" . $importFunctionName . "List";
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid export function name $importFunctionName");

		return $this->$importFunctionName($entityTargetList);
	}

	/**
	 * EXPORT STEP 2: Before starting export loop
	 * @inheritdoc
	 */
	public function getBeforeExportLoop($exportName, $storeEntityList)
	{
		$importFunctionName = self::getIntegrationFunctionName($exportName);
		$importFunctionName = "beforeExport" . $importFunctionName . "Loop";
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid export function name $importFunctionName");

		return $this->$importFunctionName($storeEntityList);
	}

	/**
	 * EXPORT STEP 3: Format entities for export
	 * @inheritdoc
	 */
	public function formatExportData($exportName, $storeEntity)
	{
		$importFunctionName = self::getIntegrationFunctionName($exportName);
		$importFunctionName = "formatExport" . $importFunctionName . "Data";
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid export function name $importFunctionName");

		return $this->$importFunctionName($storeEntity);
	}

	/**
	 * EXPORT STEP 4: Before export
	 * @inheritdoc
	 */
	public function beforeExport($exportName, $storeEntity, $erpEntity)
	{
		$importFunctionName = self::getIntegrationFunctionName($exportName);
		$importFunctionName = "beforeExport" . $importFunctionName;
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid export function name $importFunctionName");

		return $this->$importFunctionName($storeEntity, $erpEntity);
	}

	/**
	 * EXPORT STEP 5: Export entities
	 * @inheritdoc
	 */
	public function getExportCreate($exportName, $erpEntity)
	{
		$importFunctionName = self::getIntegrationFunctionName($exportName);
		$importFunctionName = "export" . $importFunctionName;
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid export function name $importFunctionName");

		return $this->$importFunctionName($erpEntity);
	}

	/**
	 * EXPORT STEP 6: After export
	 * @inheritdoc
	 */
	public function afterExport($exportName, $storeEntity, $erpEntity, $exportResult)
	{
		$importFunctionName = self::getIntegrationFunctionName($exportName);
		$importFunctionName = "afterExport" . $importFunctionName;
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid export function name $importFunctionName");

		return $this->$importFunctionName($storeEntity, $erpEntity, $exportResult);
	}

	/**
	 * EXPORT STEP 7: After export loop
	 * @inheritdoc
	 */
	public function getAfterExportLoop($exportName, $storeEntityList, $exportResults)
	{
		$importFunctionName = self::getIntegrationFunctionName($exportName);
		$importFunctionName = "afterExport" . $importFunctionName . "Loop";
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid export function name $importFunctionName");

		return $this->$importFunctionName($storeEntityList, $exportResults);
	}

	/**
	 * Error during export
	 * @inheritdoc
	 */
	public function errorExport($exportName, $lastStep, $error, $storeEntity = null, $erpEntity = null, $exportResult = null)
	{
		$importFunctionName = self::getIntegrationFunctionName($exportName);
		$importFunctionName = "errorExport" . $importFunctionName;
		if (!method_exists($this, $importFunctionName))
			throw new \Exception("Invalid export function name $importFunctionName");

		return $this->$importFunctionName($lastStep, $error, $storeEntity, $erpEntity, $exportResult);
	}


	/* --------------------- */
	/* ------ GET IDS ------ */
	/* --------------------- */

	/** @inheritdoc */
	public function getImportStockIds($entityTargetList = [])
	{
		return $entityTargetList;
	}

	/** @inheritdoc */
	public function getImportPriceIds($entityTargetList = [])
	{
		return $entityTargetList;
	}

	/** @inheritdoc */
	public function getImportCatalogIds($entityTargetList = [])
	{
		return $entityTargetList;
	}


	/* ------------------------ */
	/* ------ FILTER IDS ------ */
	/* ------------------------ */

	/** @inheritdoc */
	public function filterImportStockIds($erpEntityList)
	{
		return $erpEntityList;
	}

	/** @inheritdoc */
	public function filterImportPriceIds($erpEntityList)
	{
		return $erpEntityList;
	}

	/** @inheritdoc */
	public function filterImportCatalogIds($erpEntityList)
	{
		return $erpEntityList;
	}


	/* ---------------------- */
	/* ------ GET DATA ------ */
	/* ---------------------- */

	/** @inheritdoc */
	public function getImportStock($erpIdList = null, $entityTargetList = null)
	{
		throw new \Exception("Method 'getImportStock' not implemented for $this->_connectionName");
	}

	/** @inheritdoc */
	public function getImportPrice($erpIdList = null, $entityTargetList = null)
	{
		throw new \Exception("Method 'getImportPrice' not implemented for $this->_connectionName");
	}

	/** @inheritdoc */
	public function getImportCatalog($erpIdList = null, $entityTargetList = null)
	{
		throw new \Exception("Method 'getImportCatalog' not implemented for $this->_connectionName");
	}

	/** @inheritdoc */
	public function getExportOrderList($entityTargetList = null)
	{
		throw new \Exception("Method 'getExportOrderList' not implemented for $this->_connectionName");
	}

	public function getExportCustomerList($entityTargetList = null)
	{
		throw new \Exception("Method 'getExportCustomerList' not implemented for $this->_connectionName");
	}


	/* ------------------------- */
	/* ------ FILTER DATA ------ */
	/* ------------------------- */

	/** @inheritdoc */
	public function filterImportStockData($erpEntityList)
	{
		return $erpEntityList;
	}

	/** @inheritdoc */
	public function filterImportPriceData($erpEntityList)
	{
		return $erpEntityList;
	}

	/** @inheritdoc */
	public function filterImportCatalogData($erpEntityList)
	{
		return $erpEntityList;
	}


	/* ------------------------- */
	/* ------ FORMAT DATA ------ */
	/* ------------------------- */

	/** @inheritdoc */
	public function formatImportStockData($erpStockList)
	{
		throw new \Exception("Method 'formatImportStockData' not implemented for $this->_connectionName");
	}

	/** @inheritdoc */
	public function formatImportPriceData($erpPriceList)
	{
		throw new \Exception("Method 'formatImportPriceData' not implemented for $this->_connectionName");
	}

	/** @inheritdoc */
	public function formatImportCatalogData($erpCatalogList)
	{
		throw new \Exception("Method 'formatImportCatalogData' not implemented for $this->_connectionName");
	}

	/** @inheritdoc */
	public function formatExportOrderData($storeOrderData)
	{
		throw new \Exception("Method 'formatExportOrderData' not implemented for $this->_connectionName");
	}

	/** @inheritdoc */
	public function formatExportCustomerData($storeCustomerData)
	{
		throw new \Exception("Method 'formatExportCustomerData' not implemented for $this->_connectionName");
	}


	/* -------------------------------- */
	/* ------ BEFORE EXPORT LIST ------ */
	/* -------------------------------- */

	/** @inheritdoc */
	public function beforeExportOrderLoop($storeOrderList)
	{
		return $storeOrderList;
	}

	/** @inheritdoc */
	public function beforeExportCustomerLoop($storeCustomerList)
	{
		return $storeCustomerList;
	}


	/* -------------------------------- */
	/* ------ BEFORE EXPORT DATA ------ */
	/* -------------------------------- */

	/** @inheritdoc */
	public function beforeExportOrder($storeOrderData, $erpOrderData)
	{
		return null;
	}

	/** @inheritdoc */
	public function beforeExportCustomer($storeCustomerData, $erpCustomerData)
	{
		return null;
	}


	/* ------------------------- */
	/* ------ EXPORT DATA ------ */
	/* ------------------------- */

	/** @inheritdoc */
	public function exportOrder($erpOrderData)
	{
		throw new \Exception("Method 'exportOrder' not implemented for $this->_connectionName");
	}

	/** @inheritdoc */
	public function exportCustomer($erpCustomerData)
	{
		throw new \Exception("Method 'exportCustomer' not implemented for $this->_connectionName");
	}


	/* ------------------------------- */
	/* ------ AFTER EXPORT DATA ------ */
	/* ------------------------------- */

	/** @inheritdoc */
	public function afterExportOrder($storeOrderData, $erpOrderData, $orderExportResult)
	{
		return null;
	}

	/** @inheritdoc */
	public function afterExportCustomer($storeCustomerData, $erpCustomerData, $customerExportResult)
	{
		return null;
	}


	/* ------------------------------- */
	/* ------ AFTER EXPORT LIST ------ */
	/* ------------------------------- */

	/** @inheritdoc */
	public function afterExportOrderLoop($storeOrderList, $orderExportResults)
	{
		return $orderExportResults;
	}

	/** @inheritdoc */
	public function afterExportCustomerLoop($storeCustomerList, $customerExportResults)
	{
		return $customerExportResults;
	}


	/* --------------------------- */
	/* ------ ERROR HANDLER ------ */
	/* --------------------------- */

	/** @inheritdoc */
	public function errorImportStock($lastStep, $erpStockList = null, $erpFormattedStockList = null)
	{
		return null;
	}

	/** @inheritdoc */
	public function errorImportPrice($lastStep, $erpPriceList = null, $erpFormattedPriceList = null)
	{
		return null;
	}

	/** @inheritdoc */
	public function errorImportCatalog($lastStep, $erpCatalogList = null, $erpFormattedCatalogList = null)
	{
		return null;
	}

	/** @inheritdoc */
	public function errorExportOrder($lastStep, $error, $storeOrderData = null, $erpOrderData = null, $orderExportResult = null)
	{
		throw $error;
	}

	/** @inheritdoc */
	public function errorExportCustomer($lastStep, $error, $storeCustomerData = null, $erpCustomerData = null, $customerExportResult = null)
	{
		throw $error;
	}


	/* -------------------------------------- */
	/* ------ CUSTOM IMPORT STRATEGIES ------ */
	/* -------------------------------------- */

	/**
	 * @inheritdoc
	 */
	public function customImportStock($erpEntityList, $erpFormattedEntityList)
	{
		throw new \Exception("Method 'customImportStock' not implemented for $this->_connectionName");
	}

	/**
	 * @inheritdoc
	 */
	public function customImportPrice($erpEntityList, $erpFormattedEntityList)
	{
		throw new \Exception("Method 'customImportPrice' not implemented for $this->_connectionName");
	}

	/**
	 * @inheritdoc
	 */
	public function customImportCatalog($erpEntityList, $erpFormattedEntityList)
	{
		throw new \Exception("Method 'customImportCatalog' not implemented for $this->_connectionName");
	}
}
