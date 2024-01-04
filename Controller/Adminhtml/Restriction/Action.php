<?php

namespace Ecloud\Integrations\Controller\Adminhtml\Restriction;

use Magento\Backend\App\Action as MagentoAction;

abstract class Action extends MagentoAction
{
	/**
	 * Authorization level of a basic admin session
	 *
	 * @see _isAllowed()
	 */
	const ADMIN_RESOURCE = 'Ecloud_Integrations::restriction';
}
