<?php

namespace Ecloud\Integrations\Block\System\Config;

class ExportActions extends \Magento\Config\Block\System\Config\Form\Field
{
    const GENERAL_CONFIG_PATH = "ecloud_integrations/general/export/";

    protected $_template = 'system/config/exportActions.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->element = $element;
        return parent::render($element);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getAjaxUrl()
    {
        return $this->getBaseUrl() . "ecloudintegrations/export/index/export_type/" . $this->getIntegrationName();
    }
    
    public function getIntegrationName()
    {
        return str_replace(self::GENERAL_CONFIG_PATH, "", $this->element->getOriginalData("path"));
    }

    public function getButtonLabel()
    {
        return $this->element->getOriginalData("label");
    }

    public function getButtonId() {
        return "export_{$this->getIntegrationName()}_{$this->getActionName()}_btn";
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => $this->getButtonId(),
                'label' => $this->getButtonLabel(),
            ]
        );

        return $button->toHtml();
    }
}
