<?php

namespace Ecloud\Integrations\Controller\Import;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ecloud\Integrations\Model\Import;
use Ecloud\Integrations\Helper\Data;

class Index extends \Magento\Framework\App\Action\Action
{
	const LOG_NAME = "IMPORT_CONTROLLER";

	/**
	 * @var JsonFactory
	 */
	protected $_resultJsonFactory;

	/**
	 * @var Import
	 */
	protected $_importModel;

	/**
	 * @var Data
	 */
	protected $_helper;

	/**
	 * @param Context $context
	 * @param JsonFactory $resultJsonFactory
	 * @param Import $importModel
	 * @param Data $helper
	 */
	public function __construct(
		Context $context,
		JsonFactory $resultJsonFactory,
		Import $importModel,
		Data $helper
	) {
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_importModel = $importModel;
		$this->_helper = $helper;
		return parent::__construct($context);
	}

	public function execute()
	{
		try {
			$runType = $this->getRequest()->getParam('run_type');
			$importType = $this->getRequest()->getParam('import_type');
			
			$result = $this->_importModel->start($importType, $runType);
			
			$resultJson = $this->_resultJsonFactory->create();
			return $resultJson->setData(['success' => true]);
		} catch (\Exception $e) {
			$resultJson = $this->_resultJsonFactory->create();
			$this->_helper->log($e->getMessage(), self::LOG_NAME);
			return $resultJson->setData(['success' => false, "error" => $e->getMessage()]);
		}
	}
}
