<?php

namespace Ecloud\Integrations\Controller\Adminhtml\Restriction;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Ecloud\Integrations\Model\ResourceModel\Restriction\CollectionFactory as RestrictionCollectionFactory;

class MassDelete extends Action
{
	/**
	 * @var Filter
	 */
	protected $_filter;

	/**
	 * @var RestrictionCollectionFactory
	 */
	protected $_restrictionCollectionFactory;

	public function __construct(
		Filter $filter,
		RestrictionCollectionFactory $restrictionCollectionFactory,
		Context $context
	) {
		$this->_filter = $filter;
		$this->_restrictionCollectionFactory = $restrictionCollectionFactory;
		parent::__construct($context);
	}

	public function execute()
	{
		try {
			$restrictionCollection = $this->_filter->getCollection($this->_restrictionCollectionFactory->create());
			$deletedRestrictions = 0;

			foreach ($restrictionCollection as $restriction) {
				$restriction->delete();
				$deletedRestrictions++;
			}
			$this->messageManager->addSuccessMessage(__('A total of %1 restriction(s) were deleted.', $deletedRestrictions));
		} catch (\Exception $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
		}

		$resultRedirect = $this->resultRedirectFactory->create();
		return $resultRedirect->setPath('ecloud_integrations/restriction');
	}
}
