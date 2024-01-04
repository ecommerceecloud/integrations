<?php

namespace Ecloud\Integrations\Model\Config\Source;

use Ecloud\Integrations\Model\Restriction;


class RestrictionReason implements \Magento\Framework\Data\OptionSourceInterface
{
	public function toOptionArray()
	{
		$result = [];
		foreach ($this->getOptions() as $value => $label) {
			$result[] = [
				'value' => $value,
				'label' => $label,
			];
		}

		return $result;
	}

	public function getOptions()
	{
		return [
			Restriction::RESTRICTION_REASON_INACTIVE => __('Inactive entity'),
			Restriction::RESTRICTION_REASON_USER => __('Restricted by user'),
			Restriction::RESTRICTION_REASON_WRONG_FORMAT => __('Wrong ERP format'),
			Restriction::RESTRICTION_REASON_WRONG_RESPONSE => __('Wrong ERP response'),
			Restriction::RESTRICTION_REASON_BUSINESS_RULE => __('Business rule'),
		];
	}
}
