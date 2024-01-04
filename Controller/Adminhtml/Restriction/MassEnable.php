<?php

namespace Ecloud\Integrations\Controller\Adminhtml\Restriction;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Ecloud\Integrations\Model\ResourceModel\Restriction\CollectionFactory as RestrictionCollectionFactory;
use Ecloud\Integrations\Model\ResourceModel\Restriction as RestrictionResource;

class MassEnable extends Action
{
	/**
	 * @var Filter
	 */
	protected $_filter;

	/**
	 * @var RestrictionCollectionFactory
	 */
	protected $_restrictionCollectionFactory;

	/**
	 * @var RestrictionResource
	 */
	protected $_restrictionResource;


	public function __construct(
		Filter $filter,
		RestrictionCollectionFactory $restrictionCollectionFactory,
		RestrictionResource $restrictionResource,
		Context $context
	) {
		$this->_filter = $filter;
		$this->_restrictionCollectionFactory = $restrictionCollectionFactory;
		$this->_restrictionResource = $restrictionResource;
		parent::__construct($context);
	}

	public function execute()
	{
		try {
			$restrictionCollection = $this->_filter->getCollection($this->_restrictionCollectionFactory->create());
			$enabledRestrictions = 0;

			foreach ($restrictionCollection as $restriction) {
				$restriction->setActive(1);
				$this->_restrictionResource->save($restriction);
				$enabledRestrictions++;
			}
			$this->messageManager->addSuccessMessage(__('A total of %1 restriction(s) were enabled.', $enabledRestrictions));
		} catch (\Exception $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
		}
		$resultRedirect = $this->resultRedirectFactory->create();
		return $resultRedirect->setPath('ecloud_integrations/restriction');
	}
}
