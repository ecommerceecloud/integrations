<?php

namespace Ecloud\Integrations\Controller\Adminhtml\Restriction;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Ecloud\Integrations\Model\ResourceModel\Restriction as RestrictionResource;
use Ecloud\Integrations\Model\ResourceModel\Restriction\CollectionFactory as RestrictionCollectionFactory;

class Delete extends Action
{
	/**
	 * @var RestrictionResource
	 */
	protected $_restrictionResource;

	/**
	 * @var RestrictionCollectionFactory
	 */
	protected $_restrictionCollectionFactory;


	public function __construct(
		Context $context,
		RestrictionResource $restrictionResource,
		RestrictionCollectionFactory $restrictionCollectionFactory
	) {
		$this->_restrictionResource = $restrictionResource;
		$this->_restrictionCollectionFactory = $restrictionCollectionFactory;
		parent::__construct($context);
	}

	/**
	 * Delete action
	 *
	 * @return ResultInterface
	 */
	public function execute()
	{
		/** @var Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		$restrictionId = $this->getRequest()->getParam('id');
		if ($restrictionId) {
			try {
				$restrictionCollection = $this->_restrictionCollectionFactory->create()->addFieldToFilter("id", ["eq" => $restrictionId]);

				if ($restrictionCollection->getSize() <= 0) {
					$this->messageManager->addErrorMessage(__('This restriction no longer exists.'));
					return $resultRedirect->setPath('*/*/');
				}

				$restrictionModel = $restrictionCollection->getFirstItem();

				if ($restrictionId != $restrictionModel->getId()) {
					$this->messageManager->addErrorMessage(__('This restriction no longer exists.'));
					return $resultRedirect->setPath('*/*/');
				}

				$this->_restrictionResource->delete($restrictionModel);
                $this->messageManager->addSuccessMessage(__('You deleted the restriction.'));
				return $resultRedirect->setPath('*/*/');

			} catch (\Exception $e) {
				$this->messageManager->addErrorMessage($e->getMessage());
				return $resultRedirect->setPath('*/*/edit', ['id' => $restrictionId]);
			}
		}
		$this->messageManager->addErrorMessage(__('Couldn\'t find a restriction to delete.'));
		return $resultRedirect->setPath('*/*/');
	}
}
