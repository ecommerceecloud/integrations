<?php

namespace Ecloud\Integrations\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class Restriction extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const RESOURCE_MODEL_NAME = "Ecloud\Integrations\Model\ResourceModel\Restriction";
	const CACHE_TAG = 'ecloud_integrations_restriction';

	const RESTRICTION_REASON_INACTIVE = "inactive_product";
	const RESTRICTION_REASON_WRONG_FORMAT = "wrong_format";
	const RESTRICTION_REASON_WRONG_RESPONSE = "wrong_response";
	const RESTRICTION_REASON_USER = "excluded_by_user";
	const RESTRICTION_REASON_BUSINESS_RULE = "busness_rule";

	/**
	 * Model cache tag for clear cache in after save and after delete
	 *
	 * @var string
	 */
	protected $_cacheTag = self::CACHE_TAG;

	/**
	 * Prefix of model events names
	 *
	 * @var string
	 */
	protected $_eventPrefix = 'restriction';

	/**
	 * @param Context $context
	 * @param Registry $registry
	 * @param AbstractResource $resource
	 * @param AbstractDb $resourceCollection
	 * @param array $data
	 */
	public function __construct(
		Context $context,
		Registry $registry,
		AbstractResource $resource = null,
		AbstractDb $resourceCollection = null,
		array $data = []
	) {
		parent::__construct($context, $registry, $resource, $resourceCollection, $data);
	}

	/**
	 * Initialize resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init(self::RESOURCE_MODEL_NAME);
	}

	/**
	 * Return a unique id for the model.
	 *
	 * @return array
	 */
	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}
}
