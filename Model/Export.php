<?php

namespace Ecloud\Integrations\Model;

use Ecloud\Integrations\Helper\Data;
use Ecloud\Integrations\Model\Connection\AbstractConnection;

class Export
{

	const LOG_NAME = "EXPORT_MODEL";

	const TYPE_ORDER = "order";
	const TYPE_CUSTOMER = "customer";

	const STEP_ID_1_EXPORT_LIST = 1;
	const STEP_ID_2_BEFORE_EXPORT_LOOP = 2;
	const STEP_ID_3_FORMAT_ENTITY = 3;
	const STEP_ID_4_BEFORE_EXPORT_ENTITY = 4;
	const STEP_ID_5_EXPORT_ENTITY = 5;
	const STEP_ID_6_AFTER_EXPORT_ENTITY = 6;
	const STEP_ID_7_AFTER_EXPORT_LOOP = 7;

	const STEP_NAME_BY_ID = [
		self::STEP_ID_1_EXPORT_LIST => "get export list",
		self::STEP_ID_2_BEFORE_EXPORT_LOOP => "before export list",
		self::STEP_ID_3_FORMAT_ENTITY => "get format entity for export",
		self::STEP_ID_4_BEFORE_EXPORT_ENTITY => "before exporting entity",
		self::STEP_ID_5_EXPORT_ENTITY => "export entity",
		self::STEP_ID_6_AFTER_EXPORT_ENTITY => "after exporting entity",
		self::STEP_ID_7_AFTER_EXPORT_LOOP => "after exporting list",
	];

	/**
	 * @var AbstractConnection
	 */
	protected $_connection;

	/**
	 * @var Data
	 */
	protected $_helper;

	/**
	 * @var AbstractConnection
	 */
	protected $_abstractConnection;

	/**
	 * @var int
	 */
	protected $_lastStep = 0;

	/**
	 * @var array
	 */
	protected $_additionalExportTypes;

	/**
	 * @var array
	 */
	protected $_exportTypes;

	/**
	 * @param AbstractConnection $abstractConnection
	 */
	public function __construct(
		AbstractConnection $abstractConnection,
		Data $helper,
		array $additionalExportTypes = []
	) {
		$this->_helper = $helper;
		$this->_additionalExportTypes = $additionalExportTypes;
		$this->_exportTypes = [
			self::TYPE_ORDER,
			self::TYPE_CUSTOMER,
		];

		foreach ($this->_additionalExportTypes as $exportType) {
			$this->_exportTypes[] =  $exportType;
		}

		if (!$this->_helper->getGeneralConfigValue("active")) {
			return;
		}
		$this->_abstractConnection = $abstractConnection;
	}

	/**
	 * Set the model connection based on the integration it's running
	 * @param String $integrationName the name of the integration to get used connection
	 */
	protected function initConnection($integrationName)
	{
		// If already set, return connection
		if ($this->_connection) return $this->_connection;
		try {
			// Get connection based on integration name
			$this->_connection = $this->_abstractConnection->getActivePlatformConnectionByIntegration($integrationName, "export");
			if (!$this->_connection) {
				throw new \Exception("Connection must be defined for $integrationName integration");
			}
		} catch (\Throwable $th) {
			throw new \Exception("Connection must be defined for $integrationName integration");
		}
	}

	public function start($integrationName, $entityTargetList)
	{
		// End if integrations module is not enabled
		if (!$this->_helper->getGeneralConfigValue("active")) {
			throw new \Exception("Integration not active");
		}

		// Validate existing integration name
		// Validate existing integration name
		if (!in_array($integrationName, $this->_exportTypes))
			throw new \Exception("Invalid export type $integrationName");


		// End if the integration to run is not active
		if (!$this->_helper->getGeneralConfigValue("export/$integrationName/active")) {
			throw new \Exception("$integrationName integration not active");
		}

		// Get connection based on integration name
		$this->initConnection($integrationName);

		// Execute export
		return $this->exportNow($integrationName, $entityTargetList);
	}

	public function exportNow($integrationName, $entityTargetList)
	{
		try {
			// STEP 1: Get entities for export
			$storeEntityList = $this->_connection->getExportList($integrationName, $entityTargetList);
			$this->_lastStep = self::STEP_ID_2_BEFORE_EXPORT_LOOP;
			
			// End if there are no entities for export
			if (!$storeEntityList || count($storeEntityList) <= 0) {
				throw new \Exception("No entities for export");
			}
			
			$storeEntityList = $this->_connection->getBeforeExportLoop($integrationName, $storeEntityList);
			$this->_lastStep = self::STEP_ID_2_BEFORE_EXPORT_LOOP;
		} catch (\Exception $e) {
			throw new \Exception("Error running $integrationName export. " . $e->getMessage());
		}
		
		$storeEntityList = $this->limitExportData($integrationName, $storeEntityList);
		
		$exportResults = [];
		
		// Format and export each entity
		foreach ($storeEntityList as $storeEntity) {
			$erpEntity = null;
			$exportResult = null;
			$this->_lastStep = self::STEP_ID_2_BEFORE_EXPORT_LOOP;
			try {
				// STEP 2: Format entity
				$erpEntity = $this->_connection->formatExportData($integrationName, $storeEntity);
				$this->_lastStep = self::STEP_ID_3_FORMAT_ENTITY;
				
				// STEP 3: Before export
				$this->_connection->beforeExport($integrationName, $storeEntity, $erpEntity);
				$this->_lastStep = self::STEP_ID_4_BEFORE_EXPORT_ENTITY;

				// STEP 4: Export entity
				$exportResult = $this->_connection->getExportCreate($integrationName, $erpEntity);
				$this->_lastStep = self::STEP_ID_5_EXPORT_ENTITY;

				// STEP 5: After export
				$afterExportResult = $this->_connection->afterExport($integrationName, $storeEntity, $erpEntity, $exportResult);
				$this->_lastStep = self::STEP_ID_6_AFTER_EXPORT_ENTITY;

				$exportResults[] = $afterExportResult;
			} catch (\Exception $e) {
				try {
					$this->_connection->errorExport($integrationName, $this->_lastStep, $e, $storeEntity, $erpEntity, $exportResult);
				} catch (\Exception $e) {
					throw new \Exception("Error running $integrationName export. " . $e->getMessage());
				}
			}

			try {
				$storeEntityList = $this->_connection->getAfterExportLoop($integrationName, $storeEntityList, $exportResults);
				$this->_lastStep = self::STEP_ID_7_AFTER_EXPORT_LOOP;
			} catch (\Exception $e) {
				throw new \Exception("Error running $integrationName export. " . $e->getMessage());
			}
		}
	}

	protected function limitExportData($integrationName, $stepDataList)
	{
		$limitActive = $this->_helper->getGeneralConfigValue("export/$integrationName/limit_active");
		$limitSize = $this->_helper->getGeneralConfigValue("export/$integrationName/limit_size");
		if ($limitActive) {
			if (!$limitSize || !is_numeric($limitSize)) {
				throw new \Exception("Invalid limit size $limitSize");
			}
			if (count($stepDataList) > $limitSize) {
				$this->_helper->log("Limiting export to $limitSize entities", self::LOG_NAME);
				return array_slice($stepDataList, 0, $limitSize);
			}
			return $stepDataList;
		}
		return $stepDataList;
	}
}
