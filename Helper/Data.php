<?php

namespace Ecloud\Integrations\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const BASE_CONFIG_PATH = "ecloud_integrations";
    const GENERAL_CONFIG_PATH = "general";

    protected $_context;

    protected $regionFactory;

    protected $countryFactory;

    protected $_integrationsLogger;
    protected $_apiLogger;

    protected $_loggerName;

    /**
     * @var WebsiteCollectionFactory
     */
    protected $_websiteCollectionFactory;


    public function __construct(
        Context $context,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        WebsiteCollectionFactory $websiteCollectionFactory,
        $loggers = []
    ) {
        parent::__construct($context);
        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        $this->_loggerName = $this->getLoggerName();
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_integrationsLogger = $loggers["system"];
        $this->_apiLogger = $loggers["api"];
    }

    /**
     * Get a config from all store configs
     */
    public function getConfigValue($config, $store = null)
    {
        return $this->scopeConfig->getValue(
            preg_replace("/\/+/", "/", $config),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get a config from integrations module configs
     */
    public function getModuleConfigValue($config, $store = null)
    {
        $configPath = self::BASE_CONFIG_PATH . "/" . $config;
        return $this->getConfigValue($configPath, $store);
    }

    /**
     * Get a config from integrations module general config
     */
    public function getGeneralConfigValue($config, $store = null)
    {
        $configPath = self::GENERAL_CONFIG_PATH . "/" . $config;
        return $this->getModuleConfigValue($configPath, $store);
    }

    protected function getLoggerName()
    {
        $this->getGeneralConfigValue("platforms");
    }

    public function getRegionCodeByRegionId($regionId)
    {
        return $this->regionFactory->create()->load($regionId)->getName();
    }

    public function getCountryNameByCode($countryCode)
    {
        return $this->countryFactory->create()->loadByCode($countryCode)->getName();
    }

    /**
     * Log custom message using logger instance
     *
     * @param                   $message
     * @param string|null       $name
     * @param array|null        $array
     */
    public function log($message, $name = null, $array = null)
    {
        //load admin configuration value, default is true
        $actionLog = $this->getConfigValue('ecloud_integrations/general/log');

        if (!$actionLog) {
            return;
        }

        $name = $name == null ? $this->_loggerName : $name;

        //if extra data is provided, it's encoded for better visualization
        if (!is_null($array)) {
            $message .= " - " . json_encode($array);
        }

        //set log
        $this->_integrationsLogger->setName($name);
        $this->_integrationsLogger->debug($message);
    }

    /**
     * Log custom message using api logger instance
     *
     * @param                   $message
     * @param string|null       $name
     * @param array|null        $array
     */
    public function apiLog($message, $name = null, $array = null)
    {
        //load admin configuration value, default is true
        $actionLog = $this->getConfigValue('ecloud_integrations/general/log');
        $apiLog = $this->getConfigValue('ecloud_integrations/general/log_api');

        if (!$actionLog || !$apiLog) {
            return;
        }

        $name = $name == null ? $this->_loggerName : $name;

        //if extra data is provided, it's encoded for better visualization
        if (!is_null($array)) {
            $message .= " - " . json_encode($array);
        }

        //set log
        $this->_apiLogger->setName($name);
        $this->_apiLogger->debug($message);
    }

    public static function validateMapping($mapping, $productData)
    {
        foreach ($mapping as $key => $subMap) {
            if (!array_key_exists($key, $productData)) {
                return false;
            }
            if ($subMap && is_array($subMap)) {
                if (!is_array($productData[$key]) || !self::validateMapping($subMap, $productData[$key])) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function validateCompleteMapping($mapping, $data)
    {
        $type = $mapping["_type"];
        $keys = $mapping["_keys"];
        $stringKeys = [];
        $subMappings = [];
        foreach ($keys as $key => $value) {
            if (is_numeric($key))
                $stringKeys[] = $value;
            else {
                $subMappings[$key] = $value;
                $stringKeys[] = $key;
            }
        }

        if ($type == "object") {
            // Validate all string keys
            if (!self::hasAllKeys($data, $stringKeys)) return false;
            // Validate all submappings
            foreach ($subMappings as $key => $subMap) {
                if (!self::validateCompleteMapping($subMap, $data[$key])) return false;
            }
        }
        if ($type == "array") {
            // Data must be an array
            if (!is_array($data)) return false;
            // Each element of array must validate given mapping as object
            $objMapping = $mapping;
            $objMapping["_type"] = "object";
            foreach ($data as $element) {
                if (!self::validateCompleteMapping($objMapping, $element)) return false;
            }
        }
        return true;
    }

    protected static function hasAllKeys($data, $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) return false;
        }
        return true;
    }

    /**
     * Log a request made to the ERP
     * @param string $actionName name of the action of the request
     * @param array $request CURL request
     * @param string $logName log name
     */
    public function logRequest(string $actionName, $request, $logName)
    {
        if (array_key_exists(CURLOPT_CUSTOMREQUEST, $request)) {
            $this->apiLog("$actionName METHOD: ", $logName);
            $this->apiLog($request[CURLOPT_CUSTOMREQUEST], $logName);
        }
        if (array_key_exists(CURLOPT_URL, $request)) {
            $this->apiLog("$actionName URL: ", $logName);
            $this->apiLog($request[CURLOPT_URL], $logName);
        }
        if (array_key_exists(CURLOPT_POSTFIELDS, $request)) {
            $this->apiLog("$actionName body: ", $logName);
            if (is_array($request[CURLOPT_POSTFIELDS]))
                $this->apiLog(print_r($request[CURLOPT_POSTFIELDS], true), $logName);
            else
                $this->apiLog($request[CURLOPT_POSTFIELDS], $logName);
        }
    }

    /**
     * Log a response obtained from the ERP
     * @param string $actionName name of the action of the request
     * @param array|string $response response obtained from the ERP
     * @param string $logName log name
     */
    public function logResponse(string $actionName, $response, $logName)
    {
        $this->apiLog("$actionName response: ", $logName);
        if (is_array($response))
            $this->apiLog(print_r($response, true), $logName);
        else
            $this->apiLog($response, $logName);
    }


    public static function getArrayPath($array, $path)
    {
        $currentTarget = $array;
        foreach ($path as $pathSection) {
            if (!isset($currentTarget[$pathSection])) {
                throw new \Exception("Unable to find path in array");
            }
            $currentTarget = $currentTarget[$pathSection];
        }
        return $currentTarget;
    }

    /**
     * Add the value to the array, given its path and value
     * @param array $array the array to add the value to
     * @param array $path the path where to place the value in the array. Each entry of the array means accessing a property of the array
     * @param string|int $value the value to add to the array
     */
    public static function addToArrayPath($array, $path, $value)
    {
        $currentTarget = &$array;
        $sectionCount = count($path);
        for ($i = 0; $i < $sectionCount; $i++) {
            $pathSection = $path[$i];
            if (!isset($currentTarget[$pathSection])) {
                $currentTarget[$pathSection] = [];
            }
            if ($i < $sectionCount - 1 && !is_array($currentTarget[$pathSection])) {
                throw new \Exception("Unable to set value in array");
            }
            $currentTarget = &$currentTarget[$pathSection];
        }
        $currentTarget = $value;
        return $array;
    }

    public function getAllWebsiteCodes()
    {
        $websiteCollection = $this->_websiteCollectionFactory->create();
        $allWebsiteIds = [];
        foreach ($websiteCollection as $websiteData) {
            $allWebsiteIds[] = $websiteData->getCode();
        }
        return $allWebsiteIds;
    }
}
