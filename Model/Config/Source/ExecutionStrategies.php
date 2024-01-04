<?php

namespace Ecloud\Integrations\Model\Config\Source;


class ExecutionStrategies implements \Magento\Framework\Data\OptionSourceInterface
{
  const STRATEGY_CONSOLE = "console";
  const STRATEGY_MODEL = "model";
  const STRATEGY_CUSTOM = "custom";

  public function toOptionArray()
  {
    return [
      ['value' => self::STRATEGY_CONSOLE, 'label' => __('Console')],
      ['value' => self::STRATEGY_MODEL, 'label' => __('Model')],
      ['value' => self::STRATEGY_CUSTOM, 'label' => __('Custom')]
    ];
  }
}
