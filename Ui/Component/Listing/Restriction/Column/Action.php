<?php

namespace Ecloud\Integrations\Ui\Component\Listing\Restriction\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Action extends Column
{
	const ROW_EDIT_URL = 'ecloud_integrations/restriction/view';

	/** 
	 * @var UrlInterface
	 */
	protected $_urlBuilder;

	/**
	 * @var string
	 */
	private $_editUrl;

	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		UrlInterface $urlBuilder,
		array $components = [],
		array $data = [],
		$editUrl = self::ROW_EDIT_URL
	) {
		$this->_urlBuilder = $urlBuilder;
		$this->_editUrl = $editUrl;
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}

	/**
	 * Prepare Data Source.
	 * @param array $dataSource
	 * @return array
	 */
	public function prepareDataSource(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as &$item) {
				$name = $this->getData('name');
				if (isset($item['id'])) {
					$item[$name]['edit'] = [
						'href' => $this->_urlBuilder->getUrl(
							$this->_editUrl,
							['id' => $item['id']]
						),
						'label' => __('Edit'),
					];
				}
			}
		}

		return $dataSource;
	}
}
