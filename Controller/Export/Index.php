<?php

namespace Ecloud\Integrations\Controller\Export;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ecloud\Integrations\Model;
use Ecloud\Integrations\Helper\Data;

class Index extends \Magento\Framework\App\Action\Action
{
	const LOG_NAME = "EXPORT_CONTROLLER";

	/**
	 * @var JsonFactory
	 */
	protected $_resultJsonFactory;

	/**
	 * @var Model\Export
	 */
	protected $_exportModel;

	/**
	 * @var Data
	 */
	protected $_helper;

	/**
	 * @param Context $context
	 * @param Model\Export $exportModel
	 */
	public function __construct(
		Context $context,
		JsonFactory $resultJsonFactory,
		Model\Export $exportModel,
		Data $helper
	) {
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_exportModel = $exportModel;
		$this->_helper = $helper;
		return parent::__construct($context);
	}

	public function execute()
	{
		try {
			$entityId = $this->getRequest()->getParam("entity_id");
			if (!$entityId) {
				$entityIds = [];
			} else {
				$entityIds = [$entityId];
			}

			$exportType = $this->getRequest()->getParam('export_type');
			if (!$exportType) {
				throw new \Exception("Export type must be specified");
			}

			$this->_exportModel->start($exportType, $entityIds);

			$resultJson = $this->_resultJsonFactory->create();
			return $resultJson->setData(['success' => true]);
		} catch (\Exception $e) {
			$resultJson = $this->_resultJsonFactory->create();
			$this->_helper->log($e->getMessage(), self::LOG_NAME);
			return $resultJson->setData(['success' => false, "error" => $e->getMessage()]);
		}
	}
}
