<?php

namespace Ecloud\Integrations\Model\Config\Source;


class Platforms implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var array
     */
    protected $_platformOptions;
    
    public function __construct(
        $platformOptions = []
    )
    {
        $this->_platformOptions = $platformOptions;
    }

    public function toOptionArray()
    {
        return $this->_platformOptions;
    }
}
