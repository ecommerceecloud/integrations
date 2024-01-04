<?php

namespace Ecloud\Integrations\Api;

use \Magento\Sales\Api\Data\OrderInterface;
use \Magento\Customer\Api\Data\CustomerInterface;

interface ConnectionInterface
{
	/**
	 * Returns a connection model based on the active platform for the integration
	 * @param string integrationName name of the integration to get the connection
	 * @param string integrationType name of the integration type ("import" or "export")
	 */
	function getActivePlatformConnectionByIntegration($integrationName, $integrationType);

	/**
	 * Log in the integration file
	 * @param string message the message to log
	 * @param array|null an array to log as json
	 * 
	 */
	function log($message, $array = null);

	/**
	 * IMPORT STEP 1: Get entity IDs for import
	 * @param string importName the name of the integration to execute this step
	 * @param array|null entityTargetList entity IDs to import
	 * @return array
	 */
	function getImportIdList($importName, $entityTargetList = null);

	/**
	 * IMPORT STEP 2: Filter entity IDs for export
	 * @param string importName the name of the integration to execute this step
	 * @param array erpIdList entity IDs to filter
	 * @return array
	 */
	function filterImportIdList($importName, $erpIdList);

	/**
	 * IMPORT STEP 3: Get entities for import
	 * @param string importName the name of the integration to execute this step
	 * @param array erpIdList entity IDs for import
	 * @param array|null entityTargetList entities to import
	 * @return array
	 */
	function getImportList($importName, $erpIdList = null, $entityTargetList = null);

	/**
	 * IMPORT STEP 4: Filter entities for export
	 * @param string importName the name of the integration to execute this step
	 * @param array erpEntityList entities to filter
	 * @return array
	 */
	function filterImportList($importName, $erpEntityList);

	/**
	 * IMPORT STEP 5: Format entities for import
	 * @param string importName the name of the integration to execute this step
	 * @param array entities entities to format
	 * @return array
	 */
	function formatImportList($importName, $entities);

	/**
	 * Error during import
	 * @param string importName the name of the integration to execute this step
	 * @param int lastStep last step correctly executed for the integration
	 * @param array|null erpEntityList entities to format
	 * @param array|null erpFormattedEntityList formatted entities
	 * @return array
	 */
	function errorImport($importName, $lastStep, $erpEntityList = null, $erpFormattedEntityList = null);

	/**
	 * Executes custom logic to import entities
	 * @param string importName the name of the integration to execute this step
	 * @param array|null erpEntityList entities to format
	 * @param array|null erpFormattedEntityList formatted entities
	 */
	function customImportStrategy($importName, $erpEntityList = null, $erpFormattedEntityList = null);

	/**
	 * EXPORT STEP 1: Get entities for export
	 * @param string exportName the name of the integration to execute this step
	 * @param array entityTargetList entities to export
	 * @return array
	 */
	function getExportList($exportName, $entityTargetList = null);

	/**
	 * EXPORT STEP 2: Before starting export loop
	 * @param string exportName the name of the integration to execute this step
	 * @param mixed storeEntityList magento entity list to export
	 * @return mixed
	 */
	function getBeforeExportLoop($exportName, $storeEntityList);

	/**
	 * EXPORT STEP 3: Format entities for export
	 * @param string exportName the name of the integration to execute this step
	 * @param mixed storeEntity entity to format
	 * @return mixed
	 */
	function formatExportData($exportName, $storeEntity);

	/**
	 * EXPORT STEP 4: Before export
	 * @param string exportName the name of the integration to execute this step
	 * @param mixed storeEntity original magento entity that will be exported
	 * @param mixed erpEntity ERP entity to be created
	 */
	function beforeExport($exportName, $storeEntity, $erpEntity);

	/**
	 * EXPORT STEP 5: Export entities
	 * @param string exportName the name of the integration to execute this step
	 * @param mixed ERP erpEntity entity to create
	 * @return mixed
	 */
	function getExportCreate($exportName, $erpEntity);

	/**
	 * EXPORT STEP 6: After export
	 * @param string exportName the name of the integration to execute this step
	 * @param mixed storeEntity original magento entity that was exported
	 * @param mixed erpEntity ERP entity created
	 * @param mixed exportResult result of creating the entity in the ERP
	 */
	function afterExport($exportName, $storeEntity, $erpEntity, $exportResult);

	/**
	 * EXPORT STEP 7: After export loop
	 * @param string exportName the name of the integration to execute this step
	 * @param mixed storeEntityList original magento entity list that was exported
	 * @param mixed exportResult results of creating all entities in the ERP
	 */
	function getAfterExportLoop($exportName, $storeEntityList, $exportResults);

	/**
	 * Error export (only if an error occurred in previous steps)
	 * @param string exportName the name of the integration to execute this step
	 * @param int lastStep last step correctly executed for the integration
	 * @param \Throwable error error thrown by the las step attempted
	 * @param mixed|null storeEntity original entity that was exported
	 * @param mixed|null erpEntity ERP entity created
	 * @param mixed|null exportResult result of creating the entity in the ERP
	 * @throws \Throwable only if the export error prevents from continuing exporting other entites in the same export run
	 */
	function errorExport($exportName, $lastStep, $error, $storeEntity = null, $erpEntity = null, $exportResult = null);


	/* --------------------- */
	/* ------ GET IDS ------ */
	/* --------------------- */

	/**
	 * Get stock IDs from ERP
	 * @param array|null $entityTargetList initial ERP IDs to import
	 * @return array
	 */
	public function getImportStockIds($entityTargetList = null);

	/**
	 * Get price IDs from ERP
	 * @param array|null $entityTargetList initial ERP IDs to import
	 * @return array
	 */
	public function getImportPriceIds($entityTargetList = null);

	/**
	 * Get catalog IDs from ERP
	 * @param array|null $entityTargetList initial ERP IDs to import
	 * @return array
	 */
	public function getImportCatalogIds($entityTargetList = null);


	/* ------------------------ */
	/* ------ FILTER IDS ------ */
	/* ------------------------ */

	/**
	 * Filter stocks IDs to import
	 * @param array erpStockIdList stocks to filter
	 * @return array
	 */
	public function filterImportStockIds($erpStockIdList);

	/**
	 * Filter prices IDs to import
	 * @param array erpPriceIdList prices to filter
	 * @return array
	 */
	public function filterImportPriceIds($erpPriceIdList);

	/**
	 * Filter catalog IDs to import
	 * @param array erpCatalogIdList catalog to filter
	 * @return array
	 */
	public function filterImportCatalogIds($erpCatalogIdList);


	/* ---------------------- */
	/* ------ GET DATA ------ */
	/* ---------------------- */

	/**
	 * Get stock from ERP
	 * @param array|null $erpIdList list of IDs to get entity data
	 * @param array|null $entityTargetList initial ERP entities to import
	 * @return array
	 */
	function getImportStock($erpIdList = null, $entityTargetList = null);

	/**
	 * Get price from ERP
	 * @param array|null $erpIdList list of IDs to get entity data
	 * @param array|null $entityTargetList initial ERP entities to import
	 * @return array
	 */
	function getImportPrice($erpIdList = null, $entityTargetList = null);

	/**
	 * Get catalog from ERP
	 * @param array|null $erpIdList list of IDs to get entity data
	 * @param array|null $entityTargetList initial ERP entities to import
	 * @return array
	 */
	function getImportCatalog($erpIdList = null, $entityTargetList = null);

	/**
	 * Get orders to export from the store
	 * @return array
	 */
	function getExportOrderList();

	/**
	 * Get customers to export from the store
	 * @return array
	 */
	function getExportCustomerList();


	/* ------------------------- */
	/* ------ FILTER DATA ------ */
	/* ------------------------- */

	/**
	 * Filter stocks to import
	 * @param array erpStockList stocks to filter
	 * @return array
	 */
	function filterImportStockData($erpStockList);

	/**
	 * Filter prices to import
	 * @param array erpPriceList prices to filter
	 * @return array
	 */
	function filterImportPriceData($erpPriceList);

	/**
	 * Filter catalog to import
	 * @param array erpCatalogList catalog to filter
	 * @return array
	 */
	function filterImportCatalogData($erpCatalogList);


	/* ------------------------- */
	/* ------ FORMAT DATA ------ */
	/* ------------------------- */

	/**
	 * Format stock obtained from ERP into CSV data
	 * @param array erpStockData stock data obtained from ERP
	 * @return mixed
	 */
	function formatImportStockData($erpStockData);

	/**
	 * Format price obtained from ERP into CSV data
	 * @param array erpPriceData price data obtained from ERP
	 * @return mixed
	 */
	function formatImportPriceData($erpPriceData);

	/**
	 * Format catalog obtained from ERP into CSV data
	 * @param array erpCatalogData catalog data obtained from ERP
	 * @return mixed
	 */
	function formatImportCatalogData($erpCatalogData);

	/**
	 * Format magento order data into ERP accepted format
	 * @param OrderInterface storeOrderData the order to format
	 * @return mixed
	 */
	function formatExportOrderData($storeOrderData);

	/**
	 * Format magento customer data into ERP accepted format
	 * @param CustomerInterface storeCustomerData the customer to format
	 * @return mixed
	 */
	function formatExportCustomerData($storeCustomerData);


	/* -------------------------------- */
	/* ------ BEFORE EXPORT LIST ------ */
	/* -------------------------------- */

	/**
	 * Execute before creating an order in the ERP
	 * @param mixed storeOrderList original magento order list that will be exported
	 */
	function beforeExportOrderLoop($storeOrderList);

	/**
	 * Execute before creating a customer in the ERP
	 * @param mixed storeCustomerList original magento customer list that will be exported
	 */
	function beforeExportCustomerLoop($storeCustomerList);


	/* -------------------------------- */
	/* ------ BEFORE EXPORT DATA ------ */
	/* -------------------------------- */

	/**
	 * Execute before creating an order in the ERP
	 * @param mixed storeOrderData original magento order that will be exported
	 * @param mixed erpOrderData ERP order to be created
	 */
	function beforeExportOrder($storeOrderData, $erpOrderData);

	/**
	 * Execute before creating a customer in the ERP
	 * @param mixed storeCustomerData original magento customer that will be exported
	 * @param mixed erpCustomerData ERP customer to be created
	 */
	function beforeExportCustomer($storeCustomerData, $erpCustomerData);


	/* ------------------------- */
	/* ------ EXPORT DATA ------ */
	/* ------------------------- */

	/**
	 * Create an order in the ERP
	 * @param mixed erpOrderData ERP order data in ERP accepted format
	 * @return mixed
	 */
	function exportOrder($erpOrderData);

	/**
	 * Create a customer in the ERP
	 * @param mixed erpCustomerData ERP customer data in ERP accepted format
	 * @return mixed
	 */
	function exportCustomer($erpCustomerData);


	/* ------------------------------- */
	/* ------ AFTER EXPORT DATA ------ */
	/* ------------------------------- */

	/**
	 * Execute after creating an order in the ERP
	 * @param mixed storeOrderData original magento order that was exported
	 * @param mixed erpOrderData ERP order created
	 * @param mixed orderExportResult result of creating the order in the ERP
	 */
	function afterExportOrder($storeOrderData, $erpOrderData, $orderExportResult);

	/**
	 * Execute after creating a customer in the ERP
	 * @param mixed storeCustomerData original magento customer that was exported
	 * @param mixed erpCustomerData ERP customer created
	 * @param mixed customerExportResult result of creating the customer in the ERP
	 */
	function afterExportCustomer($storeCustomerData, $erpCustomerData, $customerExportResult);


	/* ------------------------------- */
	/* ------ AFTER EXPORT LIST ------ */
	/* ------------------------------- */

	/**
	 * Execute after creating an order in the ERP
	 * @param mixed storeOrderList original magento customer list that was exported
	 * @param mixed orderExportResults results of creating all orders in the ERP
	 */
	function afterExportOrderLoop($storeOrderList, $orderExportResult);

	/**
	 * Execute after creating a customer in the ERP
	 * @param mixed storeCustomerList original magento customer list that was exported
	 * @param mixed customerExportResults results of creating all customers in the ERP
	 */
	function afterExportCustomerLoop($storeCustomerList, $customerExportResults);


	/* --------------------------- */
	/* ------ ERROR HANDLER ------ */
	/* --------------------------- */

	/**
	 * Handle error during price import
	 * @param int lastStep last step correctly executed for the integration
	 * @param array|null erpPriceList entities for import
	 * @param array|null erpFormattedPriceList formatted entities for import
	 */
	function errorImportStock($lastStep, $erpPriceList = null, $erpFormattedPriceList = null);

	/**
	 * Handle error during price import
	 * @param int lastStep last step correctly executed for the integration
	 * @param array|null erpStockList entities for import
	 * @param array|null erpFormattedStockList formatted entities for import
	 */
	function errorImportPrice($lastStep, $erpStockList = null, $erpFormattedStockList = null);

	/**
	 * Handle error during catalog import
	 * @param int lastStep last step correctly executed for the integration
	 * @param array|null erpCatalogList entities for import
	 * @param array|null erpFormattedCatalogList formatted entities for import
	 */
	function errorImportCatalog($lastStep, $erpCatalogList = null, $erpFormattedCatalogList = null);

	/**
	 * Handle error during order export
	 * @param mixed orderData original magento order that was exported
	 * @param int lastStep last step correctly executed for the integration
	 * @param mixed erpOrderData ERP order created
	 * @param mixed orderExportResult result of creating the order in the ERP
	 * @throws \Throwable only if the export error prevents from continuing exporting other orders in the same export run
	 */
	function errorExportOrder($storeOrderData, $erpOrderData, $orderExportResult);

	/**
	 * Handle error during customer export
	 * @param mixed storeCustomerData original magento customer that was exported
	 * @param int lastStep last step correctly executed for the integration
	 * @param mixed erpCustomerData ERP customer created
	 * @param mixed customerExportResult result of creating the customer in the ERP
	 * @throws \Throwable only if the export error prevents from continuing exporting other customers in the same export run
	 */
	function errorExportCustomer($storeCustomerData, $erpCustomerData, $customerExportResult);


	/* -------------------------------------- */
	/* ------ CUSTOM IMPORT STRATEGIES ------ */
	/* -------------------------------------- */

	/**
	 * Custom import logic for stock integration
	 * @param array|null erpEntityList entities for import
	 * @param array|null erpFormattedEntityList formatted entities for import
	 */
	function customImportStock($erpEntityList, $erpFormattedEntityList);

	/**
	 * Custom import logic for price integration
	 * @param array|null erpEntityList entities for import
	 * @param array|null erpFormattedEntityList formatted entities for import
	 */
	function customImportPrice($erpEntityList, $erpFormattedEntityList);

	/**
	 * Custom import logic for catalog integration
	 * @param array|null erpEntityList entities for import
	 * @param array|null erpFormattedEntityList formatted entities for import
	 */
	function customImportCatalog($erpEntityList, $erpFormattedEntityList);
}
