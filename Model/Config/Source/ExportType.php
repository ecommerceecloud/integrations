<?php

namespace Ecloud\Integrations\Model\Config\Source;

use Ecloud\Integrations\Model\Export;

class ExportType implements \Magento\Framework\Data\OptionSourceInterface
{

  protected $_additionalExportTypes = [];

  public function __construct(array $additionalExportTypes = [])
  {
    $this->_additionalExportTypes = $additionalExportTypes;
  }

  public function toOptionArray()
  {
    $options =  [
      ['value' => Export::TYPE_ORDER, 'label' => __('Order')],
      ['value' => Export::TYPE_CUSTOMER, 'label' => __('Customer')]
    ];

    foreach ($this->_additionalExportTypes as $exportTypeValue => $exportTypeLabel) {
      $options[] =  ['value' => $exportTypeValue, 'label' => __($exportTypeLabel)];
    }

    return $options;
  }
}
