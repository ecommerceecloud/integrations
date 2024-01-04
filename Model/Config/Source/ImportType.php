<?php

namespace Ecloud\Integrations\Model\Config\Source;

use Ecloud\Integrations\Model\Import;

class ImportType implements \Magento\Framework\Data\OptionSourceInterface
{

  protected $_additionalImportTypes = [];

  public function __construct(array $additionalImportTypes = [])
  {
    $this->_additionalImportTypes = $additionalImportTypes;
  }

  public function toOptionArray()
  {
    $options =  [
      ['value' => Import::TYPE_STOCK, 'label' => __('Stock')],
      ['value' => Import::TYPE_PRICE, 'label' => __('Price')],
      ['value' => Import::TYPE_CATALOG, 'label' => __('Catalog')]
    ];

    foreach ($this->_additionalImportTypes as $importTypeValue => $importTypeLabel) {
      $options[] =  ['value' => $importTypeValue, 'label' => __($importTypeLabel)];
    }

    return $options;
  }
}
