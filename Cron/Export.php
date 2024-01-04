<?php

namespace Ecloud\Integrations\Cron;

use Ecloud\Integrations\Model\Export as ExportModel;

class Export
{

	/**
	 * @var ExportModel
	 */
	protected $_exportModel;
	
	
	public function __construct(ExportModel $_exportModel)
	{
		$this->_exportModel = $_exportModel;
	}

	/**
	 * Export orders from cron
	 */
	public function exportOrders()
	{
		return $this->_exportModel->start(ExportModel::TYPE_ORDER, null);
	}

	/**
	 * Export customers from cron
	 */
	public function exportCustomers()
	{
		return $this->_exportModel->start(ExportModel::TYPE_CUSTOMER, null);
	}
}
