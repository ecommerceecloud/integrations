<?php

namespace Ecloud\Integrations\Model\Config\Source;

use Ecloud\Integrations\Model\Import;

class ImportStepTarget implements \Magento\Framework\Data\OptionSourceInterface
{
	public function toOptionArray()
	{
		return [
			['value' => Import::STEP_ID_1_IDS, 'label' => __('IDs')],
			['value' => Import::STEP_ID_2_FILTERED_IDS, 'label' => __('IDs filtradas')],
			['value' => Import::STEP_ID_3_ENTITIES, 'label' => __('Datos de entidad')],
			['value' => Import::STEP_ID_4_FILTERED_ENTITIES, 'label' => __('Datos de entidad filtrados')],
			['value' => Import::STEP_ID_5_FORMATTED_ENTITIES, 'label' => __('Datos de entidad formateados')],
		];
	}
}
