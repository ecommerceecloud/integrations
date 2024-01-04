<?php

namespace Ecloud\Integrations\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File as FileConfigModel;
use Magento\Framework\File\Csv;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Ecloud\Integrations\Model\ResourceModel\Restriction\CollectionFactory as RestrictionCollectionFactory;
use Ecloud\Integrations\Model\RestrictionFactory;
use Ecloud\Integrations\Model\Restriction;
use Ecloud\Integrations\Model\ResourceModel\Restriction as RestrictionResource;

class ProductRestrictionFile extends FileConfigModel
{

	const ALLOWED_EXTENSION = ["csv"];

	/**
	 * @var Csv
	 */
	protected $_csv;

	/**
	 * @var ProductCollectionFactory
	 */
	protected $_productCollectionFactory;

	/**
	 * @var RestrictionCollectionFactory
	 */
	protected $_restrictionCollectionFactory;

	/**
	 * @var RestrictionFactory
	 */
	protected $_restrictionFactory;

	/**
	 * @var RestrictionResource
	 */
	protected $_restrictionResource;

	public function __construct(
		Csv $csv,
		ProductCollectionFactory $productCollectionFactory,
		RestrictionCollectionFactory $restrictionCollectionFactory,
		RestrictionFactory $restrictionFactory,
		RestrictionResource $restrictionResource,
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\App\Config\ScopeConfigInterface $config,
		\Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
		\Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
		\Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
		\Magento\Framework\Filesystem	 $filesystem,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
	) {
		$this->_csv = $csv;
		$this->_restrictionCollectionFactory = $restrictionCollectionFactory;
		$this->_restrictionFactory = $restrictionFactory;
		$this->_restrictionResource = $restrictionResource;
		$this->_productCollectionFactory = $productCollectionFactory;

		return parent::__construct($context, $registry, $config, $cacheTypeList, $uploaderFactory, $requestData, $filesystem, $resource, $resourceCollection, $data);
	}

	/**
	 * @return string[]
	 */
	public function _getAllowedExtensions()
	{
		return self::ALLOWED_EXTENSION;
	}

	/**
	 * Delete previous loaded restrictions and save new ones
	 */
	public function afterSave()
	{
		if (!$this->isValueChanged()) {
			return parent::afterSave();
		}

		try {
			// Get file name and path
			$scope = $this->getScope();
			$uploadDir = $this->_getUploadDir();
			$integrationName = $this->getGroupId();
			$fileName = str_replace($scope, "", $this->getValue());
			$filePath = "$uploadDir$fileName";

			// Get file data
			$csvData = $this->_csv->getData($filePath);

			// Get only SKUs from file
			$newRestrictionSkus = [];
			foreach ($csvData as $row => $data) {
				if ($row > 0) {
					$sku = preg_replace('/[^A-Za-z0-9]/', '', $data[0]);
					if (trim($sku) != "")
						$newRestrictionSkus[] = $sku;
				}
			}
		} catch (\Exception $e) {
			throw new \Exception("Error getting uploaded file data: {$e->getMessage()}");
		}

		try {
			// Get product IDs of given SKUs
			$productIds = $this->_productCollectionFactory
				->create()
				->addFieldToSelect("id")
				->addFieldToSelect("sku")
				->addFieldToFilter("sku", array("in" => $newRestrictionSkus));
		} catch (\Exception $e) {
			throw new \Exception("Error getting products to exclude: {$e->getMessage()}");
		}


		try {
			// Delete all user created restrictions for this integration
			$userCreatedRestrictions = $this->_restrictionCollectionFactory
				->create()
				->addFieldToFilter("integration_name", $integrationName)
				->addFieldToFilter("reason", Restriction::RESTRICTION_REASON_USER);

			$userCreatedRestrictions->walk("delete");
		} catch (\Exception $e) {
			throw new \Exception("Error deleting existent user-created restrictions: {$e->getMessage()}");
		}

		$existentErpProductIds = [];

		try {
			// Create new restrictions for existent products
			foreach ($productIds as $product) {
				$productId = $product->getId();
				$productSku = $product->getSku();
				$existentErpProductIds[] = $productSku;

				try {
					// Add new restriction
					$newRestriction = $this->_restrictionFactory
						->create()
						->setIntegrationName($integrationName)
						->setEntityId($productId)
						->setErpEntityId($productSku)
						->setActive(1)
						->setReason(Restriction::RESTRICTION_REASON_USER)
						->setComment("Disabled using file import")
						->setRestrictionDate(null);

					$this->_restrictionResource->save($newRestriction);
				} catch (\Exception $e) {
					throw new \Exception("Error creating restriction for existent product with ERP ID $productSku and Magento entity ID $productId: {$e->getMessage()}");
				}
			}
		} catch (\Exception $e) {
			throw new \Exception("Error creating new restrictions for existent products: {$e->getMessage()}");
		}

		// Create new restrictions for non-existent products
		foreach ($newRestrictionSkus as $restrictionErpProductId) {
			if (in_array($restrictionErpProductId, $existentErpProductIds)) {
				// The restriction has already been added
				continue;
			}

			try {
				// Add new restriction
				$newRestriction = $this->_restrictionFactory
					->create()
					->setIntegrationName($integrationName)
					->setEntityId(null)
					->setErpEntityId($restrictionErpProductId)
					->setActive(1)
					->setReason(Restriction::RESTRICTION_REASON_USER)
					->setComment("Disabled using file import")
					->setRestrictionDate(null);

				$this->_restrictionResource->save($newRestriction);
			} catch (\Exception $e) {
				throw new \Exception("Error creating restriction for ERP product with ID $restrictionErpProductId: {$e->getMessage()}");
			}
		}

		return parent::afterSave();
	}
}
