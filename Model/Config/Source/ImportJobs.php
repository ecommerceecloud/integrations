<?php

namespace Ecloud\Integrations\Model\Config\Source;

use Firebear\ImportExport\Api\JobRepositoryInterface;

class ImportJobs implements \Magento\Framework\Data\OptionSourceInterface
{

  /**
   * @var Firebear\ImportExport\Api\JobRepositoryInterface
   */
  protected $_importJobInterface;

  public function __construct(
    JobRepositoryInterface $importJobInterface
  ) {
    $this->_importJobInterface = $importJobInterface;
  }

  public function toOptionArray()
  {

    $jobs = $this->_importJobInterface->getList();
    return array_reduce($jobs->getItems(), function ($optionsArray, $job) {
      $optionsArray[] = array(
        "value" => $job->getId(),
        "label" => $job->getTitle()
      );
      return $optionsArray;
    }, array(array("value" => null, "label" => "Seleccione un valor")));
  }
}
