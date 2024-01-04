<?php

namespace Ecloud\Integrations\Model\ResourceModel\Restriction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
	const MODEL_NAME = "Ecloud\Integrations\Model\Restriction";
	const RESOURCE_MODEL_NAME = "Ecloud\Integrations\Model\ResourceModel\Restriction";

	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'ecloud_integrations_restriction_collection';
	protected $_eventObject = 'restriction_collection';

	/**
	 * Define the resource model & the model.
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init(self::MODEL_NAME, self::RESOURCE_MODEL_NAME);
	}
}
