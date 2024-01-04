<?php

namespace Ecloud\Integrations\Cron;

use Ecloud\Integrations\Model\Import as ImportModel;

class Import
{

	/**
	 * @var ImportModel
	 */
	protected $_importModel;
	
	
	public function __construct(ImportModel $importModel)
	{
		$this->_importModel = $importModel;
	}

	/**
	 * Import catalog from cron
	 */
	public function importCatalog()
	{
		return $this->_importModel->start(ImportModel::TYPE_CATALOG, ImportModel::RUN_TYPE_FULL_IMPORT);
	}

	/**
	 * Generate catalog file from cron
	 */
	public function generateCatalogFile()
	{
		return $this->_importModel->start(ImportModel::TYPE_CATALOG, ImportModel::RUN_TYPE_GENERATE_FILE);
	}

	/**
	 * Import stock from cron
	 */
	public function importStock()
	{
		return $this->_importModel->start(ImportModel::TYPE_STOCK, ImportModel::RUN_TYPE_FULL_IMPORT);
	}

	/**
	 * Generate stock file from cron
	 */
	public function generateStockFile()
	{
		return $this->_importModel->start(ImportModel::TYPE_STOCK, ImportModel::RUN_TYPE_GENERATE_FILE);
	}

	/**
	 * Import price from cron
	 */
	public function importPrice()
	{
		return $this->_importModel->start(ImportModel::TYPE_PRICE, ImportModel::RUN_TYPE_FULL_IMPORT);
	}

	/**
	 * Generate price file from cron
	 */
	public function generatePriceFile()
	{
		return $this->_importModel->start(ImportModel::TYPE_PRICE, ImportModel::RUN_TYPE_GENERATE_FILE);
	}
}
