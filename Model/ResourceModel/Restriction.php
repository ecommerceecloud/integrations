<?php
namespace Ecloud\Integrations\Model\ResourceModel;

class Restriction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	const TABLE_NAME = "ecloud_integrations_restriction";
	const ID_FIELD = "id";
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init(self::TABLE_NAME, self::ID_FIELD);
	}
}
