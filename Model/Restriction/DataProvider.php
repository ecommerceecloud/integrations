<?php

namespace Ecloud\Integrations\Model\Restriction;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Ecloud\Integrations\Model\ResourceModel\Restriction\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    const PERSISTED_DATA_INDEX = "ecloud_integrations_restriction";
    
    private $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var CollectionFactory
     */
    public $collection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $restrictionCollection = $this->collection->getItems();

        foreach ($restrictionCollection as $restriction) {
            $this->loadedData[$restriction->getId()] = $restriction->getData();
        }

        $presistedData = $this->dataPersistor->get(self::PERSISTED_DATA_INDEX);

        if (!empty($presistedData)) {
            $restriction = $this->collection->getNewEmptyItem();
            $restriction->setData($presistedData);
            $this->loadedData[$restriction->getId()] = $restriction->getData();
            $this->dataPersistor->clear(self::PERSISTED_DATA_INDEX);
        }

        return $this->loadedData;
    }

}
