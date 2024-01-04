<?php

namespace Ecloud\Integrations\Controller\Adminhtml\Restriction;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Backend\App\Action
{
	protected $_resultPageFactory = false;

	public function __construct(
		Context $context,
		PageFactory $resultPageFactory
	) {
		parent::__construct($context);
		$this->_resultPageFactory = $resultPageFactory;
	}

	public function execute()
	{
		$resultPage = $this->_resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->prepend((__('Restriction')));

		return $resultPage;
	}
}
