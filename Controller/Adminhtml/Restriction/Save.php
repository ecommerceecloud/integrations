<?php

namespace Ecloud\Integrations\Controller\Adminhtml\Restriction;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Ecloud\Integrations\Model\RestrictionFactory;
use Ecloud\Integrations\Model\ResourceModel\Restriction\CollectionFactory as RestrictionCollectionFactory;
use Ecloud\Integrations\Model\ResourceModel\Restriction as RestrictionResource;


class Save extends Action
{
	/**
	 * @var DataPersistorInterface
	 */
	protected $_dataPersistor;

	/**
	 * @var RestrictionResource
	 */
	protected $_restrictionResource;

	/**
	 * @var RestrictionFactory
	 */
	protected $_restrictionFactory;

	/**
	 * @var RestrictionCollectionFactory
	 */
	protected $_restrictionCollectionFactory;


	public function __construct(
		Context $context,
		RestrictionFactory $restrictionFactory,
		DataPersistorInterface $dataPersistor,
		RestrictionResource $restrictionResource,
		RestrictionCollectionFactory $restrictionCollectionFactory
	) {
		$this->_dataPersistor = $dataPersistor;
		$this->_restrictionFactory = $restrictionFactory;
		$this->_restrictionResource = $restrictionResource;
		$this->_restrictionCollectionFactory = $restrictionCollectionFactory;
		parent::__construct($context);
	}

	/**
	 * Save action
	 *
	 * @return ResultInterface
	 */
	public function execute()
	{
		/** @var Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();

		if ($postData = $this->getRequest()->getPostValue()) {
			$restrictionModel = $this->_restrictionFactory->create();
			try {

				// Edit restriction, first load data from DB
				if ($restrictionId = (int) $this->getRequest()->getParam('id')) {
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
				}

				// Add form data to restriction model
				$restrictionModel->addData($postData);

				$this->_restrictionResource->save($restrictionModel);
				$this->messageManager->addSuccessMessage(__('You saved the restriction.'));
				$this->_dataPersistor->clear('ecloud_integrations_restriction');

				if ($this->getRequest()->getParam('back')) {
					return $resultRedirect->setPath('*/*/view', ['id' => $restrictionModel->getId()]);
				}

				return $resultRedirect->setPath('*/*/');
			} catch (LocalizedException $e) {
				$this->messageManager->addErrorMessage($e->getMessage());
			} catch (\Exception $e) {
				$this->messageManager->addExceptionMessage(
					$e,
					__('Something went wrong while saving the Restriction.')
				);
			}

			$this->_dataPersistor->set('ecloud_integrations_restriction', $postData);
			return $resultRedirect->setPath(
				'*/*/edit',
				[
					'id' => $this->getRequest()->getParam('id')
				]
			);
		}
		return $resultRedirect->setPath('*/*/');
	}
}
