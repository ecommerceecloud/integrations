<?php

namespace Ecloud\Integrations\Model;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Ecloud\Integrations\Model\Connection\AbstractConnection;
use Ecloud\Integrations\Helper\Data;
use Ecloud\Integrations\Model\Config\Source\ExecutionStrategies;
use Ecloud\Integrations\Model\Job\Import as ModelImport;
use Firebear\ImportExport\Api\Import\RunByIdsInterface as ConsoleImport;

class Import
{

    const LOG_NAME = "IMPORT_MODEL";

    const TYPE_STOCK = "stock";
    const TYPE_PRICE = "price";
    const TYPE_CATALOG = "catalog";

    const RUN_TYPE_GENERATE_FILE = "generate_file";
    const RUN_TYPE_FULL_IMPORT = "import_now";

    const IMPORT_DESTINATION_PATH = "import";

    const STEP_ID_1_IDS = 1;
    const STEP_ID_2_FILTERED_IDS = 2;
    const STEP_ID_3_ENTITIES = 3;
    const STEP_ID_4_FILTERED_ENTITIES = 4;
    const STEP_ID_5_FORMATTED_ENTITIES = 5;
    const STEP_ID_6_CSV = 6;

    const STEP_NAME_BY_ID = [
        self::STEP_ID_1_IDS => "get IDs",
        self::STEP_ID_2_FILTERED_IDS => "filter IDs",
        self::STEP_ID_3_ENTITIES => "get entities",
        self::STEP_ID_4_FILTERED_ENTITIES => "filter entities",
        self::STEP_ID_5_FORMATTED_ENTITIES => "format entities",
        self::STEP_ID_6_CSV => "create CSV file",
    ];

    /**
     * @var AbstractConnection
     */
    protected $_abstractConnection;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var Import
     */
    protected $_import;

    /**
     * @var AbstractConnection
     */
    protected $_connection;

    /**
     * @var ModelImport
     */
    protected $_modelImport;

    /**
     * @var ConsoleImport
     */
    protected $_consoleImport;

    /**
     * @var int
     */
    protected $_lastStep = 0;

    /**
     * @var array
     */
    protected $_additionalImportTypes;

    /**
     * @var array
     */
    protected $_importTypes;

    /**
     * Check if $this->_connection is defined correctly
     * @param AbstractConnection $abstractConnection
     */
    public function __construct(
        AbstractConnection $abstractConnection,
        Data $helper,
        Filesystem $filesystem,
        ModelImport $modelImport,
        ConsoleImport $consoleImport,
        array $additionalImportTypes = []
    ) {
        $this->_helper = $helper;
        $this->_filesystem = $filesystem;
        $this->_modelImport = $modelImport;
        $this->_consoleImport = $consoleImport;
        $this->_additionalImportTypes = $additionalImportTypes;
        $this->_importTypes = [
            self::TYPE_STOCK,
            self::TYPE_PRICE,
            self::TYPE_CATALOG
        ];

        foreach ($this->_additionalImportTypes as $importType) {
            $this->_importTypes[] =  $importType;
        }

        if (!$this->_helper->getGeneralConfigValue("active")) {
            return;
        }
        $this->_abstractConnection = $abstractConnection;
    }

    /**
     * Set the model connection based on the integration it's running
     * @param String $integrationName the name of the integration to get used connection
     */
    protected function initConnection($integrationName)
    {
        // If already set, return connection
        if ($this->_connection) return $this->_connection;
        try {
            // Get connection based on integration name
            $this->_connection = $this->_abstractConnection->getActivePlatformConnectionByIntegration($integrationName, "import");
            if (!$this->_connection) {
                throw new Exception("Connection must be defined for $integrationName integration");
            }
        } catch (\Throwable $th) {
            throw new Exception("Connection must be defined for $integrationName integration");
        }
    }


    public function start($integrationName, $runType = self::RUN_TYPE_FULL_IMPORT, $data = [])
    {
        // End if integrations module is not enabled
        if (!$this->_helper->getGeneralConfigValue("active")) {
            throw new \Exception("Integration not active");
        }

        // Validate existing integration name
        if (!in_array($integrationName, $this->_importTypes))
            throw new \Exception("Invalid import type $integrationName");

        // End if the integration to run is not active
        if (!$this->_helper->getGeneralConfigValue("import/$integrationName/active")) {
            throw new \Exception("$integrationName integration not active");
        }

        // Get connection based on import name
        $this->initConnection($integrationName);

        // Execute full import or only generate file
        switch ($runType) {
            case self::RUN_TYPE_FULL_IMPORT:
                $this->_helper->log("Running full $integrationName import integration", self::LOG_NAME);
                return $this->importNow($integrationName, $data);
                break;
            case self::RUN_TYPE_GENERATE_FILE:
                $this->_helper->log("Running $integrationName file generation integration", self::LOG_NAME);
                return $this->generateFile($integrationName, $data)[1];
                break;

            default:
                throw new \Exception("Invalid run type $runType");
                break;
        }
    }

    /**
     * Generate file and import using import job
     */
    public function importNow($integrationName, $entityTargetList)
    {
        $erpEntityList = null;
        $erpFormattedEntityList = null;
        try {
            // STEPS 1, 2, 3, 4, 5, 6: Generate file for import
            list($erpEntityList, $erpFormattedEntityList) = $this->generateFile($integrationName, $entityTargetList);
            $this->_helper->log("Finished $integrationName file generation", self::LOG_NAME);

            return $this->executeImport($erpEntityList, $erpFormattedEntityList, $integrationName);
        } catch (\Throwable $e) {
            try {
                $this->_connection->errorImport($integrationName, $this->_lastStep, $erpEntityList, $erpFormattedEntityList);
            } catch (\Throwable $e) {
                throw new \Exception("Error running $integrationName import: " . $e->getMessage());
            }
            throw new \Exception("Error running $integrationName import: " . $e->getMessage());
        }
    }

    /**
     * Generate import file
     */
    public function generateFile($integrationName, $entityTargetList)
    {
        $this->_helper->log("Starting $integrationName file generation", self::LOG_NAME);
        $erpEntityList = null;
        $erpFormattedEntityList = null;
        try {
            // STEP 1: Get entity IDs from connection
            $erpIdList = $this->_connection->getImportIdList($integrationName, $entityTargetList);
            $this->_helper->log("Obtained " . count($erpIdList) . " entity IDs", self::LOG_NAME);
            $erpIdList = $this->afterStep($integrationName, self::STEP_ID_1_IDS, $erpIdList);

            // STEP 2: Filter entity IDs
            $erpIdList = $this->_connection->filterImportIdList($integrationName, $erpIdList);
            $this->_helper->log(count($erpIdList) . " entity IDs remaining after filtering", self::LOG_NAME);
            $erpIdList = $this->afterStep($integrationName, self::STEP_ID_2_FILTERED_IDS, $erpIdList);

            // STEP 3: Get entity data from connection
            $erpEntityList = $this->_connection->getImportList($integrationName, $erpIdList, $entityTargetList);
            $this->_helper->log("Obtained " . count($erpEntityList) . " entities", self::LOG_NAME);
            $erpEntityList = $this->afterStep($integrationName, self::STEP_ID_3_ENTITIES, $erpEntityList);

            // STEP 4: Filter entities
            $erpEntityList = $this->_connection->filterImportList($integrationName, $erpEntityList);
            $this->_helper->log(count($erpEntityList) . " entities remaining after filtering", self::LOG_NAME);
            $erpEntityList = $this->afterStep($integrationName, self::STEP_ID_4_FILTERED_ENTITIES, $erpEntityList);

            // STEP 5: Format filtered entity data
            $erpFormattedEntityList = $this->_connection->formatImportList($integrationName, $erpEntityList);
            $this->_helper->log("Formatted " . count($erpFormattedEntityList) . " entities", self::LOG_NAME);
            $erpFormattedEntityList = $this->afterStep($integrationName, self::STEP_ID_5_FORMATTED_ENTITIES, $erpFormattedEntityList);

            // STEP 6: Create CSV with data
            $this->createCsv($integrationName, $erpFormattedEntityList);
            $this->_lastStep++;
            return [$erpEntityList, $erpFormattedEntityList];
        } catch (\Exception $e) {
            $this->_connection->errorImport($integrationName, $this->_lastStep, $erpEntityList, $erpFormattedEntityList);
            throw new \Exception("Error generating $integrationName import file: " . $e->getMessage());
        }
    }

    /**
     * Execute the import process based on selected strategy
     * @param array erpEntityList raw ERP entities data
     * @param array erpFormattedEntityList formated ERP entities data
     * @param string $integrationName the name of the import to run
     */
    protected function executeImport($erpEntityList, $erpFormattedEntityList, $integrationName)
    {
        // Get execution strategy from config
        $executionStrategy = $this->_helper->getGeneralConfigValue("import/$integrationName/strategy");

        switch ($executionStrategy) {
            case ExecutionStrategies::STRATEGY_MODEL:
            case ExecutionStrategies::STRATEGY_CONSOLE:
                $importResults = $this->executeImportJob($executionStrategy, $integrationName);
                $this->_helper->log("Finished $integrationName file import", self::LOG_NAME);
                return $importResults;
            case ExecutionStrategies::STRATEGY_CUSTOM:
                $importResults = $this->_connection->customImportStrategy($integrationName, $erpEntityList, $erpFormattedEntityList);
                $this->_helper->log("Finished $integrationName custom import", self::LOG_NAME);
                return $importResults;
            default:
                throw new \Exception("Invalid execution strategy " . $executionStrategy);
                break;
        }
    }

    /**
     * Execute firebear import job based on selected strategy and import job
     * @param string $executionStrategy selected execution strategy
     * @param string $integrationName the name of the import to run
     */
    protected function executeImportJob($executionStrategy, $integrationName)
    {
        $this->_helper->log("Starting $integrationName file import with $executionStrategy strategy", self::LOG_NAME);

        // Get job ID to execute the import
        $jobId = $this->_helper->getGeneralConfigValue("import/$integrationName/import_job_id");
        if (!$jobId || $jobId == "") 
            throw new \Exception("The import file has been created, but there's no import job configured");

        switch ($executionStrategy) {
            case (ExecutionStrategies::STRATEGY_MODEL):
                // Use models to execute the import job
                $results = $this->_modelImport->execute($jobId);
                if (is_array($results) && count($results)) {
                    foreach ($results as $message) {
                        $this->_helper->log($message, self::LOG_NAME);
                    }
                }
                return $results;
            case (ExecutionStrategies::STRATEGY_CONSOLE):
                // Run the import using a console command
                return $this->_consoleImport->execute([$jobId], "console");

            default:
                throw new \Exception("Invalid execution strategy " . $executionStrategy);
        }
    }

    /**
     * Create the CSV given the data in it
     */
    protected function createCsv($integrationName, $erpFormattedEntityList)
    {
        $this->_helper->log("Creating CSV file with $integrationName formatted data", self::LOG_NAME);
        try {
            $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $directory->create(self::IMPORT_DESTINATION_PATH);
            $relativePath = self::IMPORT_DESTINATION_PATH . "/" . $integrationName . ".csv";
            $stream = $directory->openFile($relativePath, 'w+');
            $stream->lock();
            $firstLine = reset($erpFormattedEntityList);
            $headers = array_keys($firstLine);
            $stream->writeCsv($headers);

            $orderedKeysEntityList = array_map(function ($erpFormattedEntityData) use ($headers) {
                $orderedKeysEntity = [];
                foreach ($headers as $headerName) {
                    if (!array_key_exists($headerName, $erpFormattedEntityData))
                        $orderedKeysEntity[] = null;
                    else
                        $orderedKeysEntity[] = $erpFormattedEntityData[$headerName];
                }
                return $orderedKeysEntity;
            }, $erpFormattedEntityList);

            foreach ($orderedKeysEntityList as $entity) {
                $stream->writeCsv(array_values($entity));
            }
            $stream->unlock();
            return true;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    protected function limitStepData($integrationName, $stepDataList, $currentStepId)
    {
        $limitActive = $this->_helper->getGeneralConfigValue("import/$integrationName/limit_active");
        $limitTarget = $this->_helper->getGeneralConfigValue("import/$integrationName/limit_target");
        $limitSize = $this->_helper->getGeneralConfigValue("import/$integrationName/limit_size");
        if ($limitActive && $limitTarget == $currentStepId) {
            if (!$limitSize || !is_numeric($limitSize)) {
                throw new \Exception("Invalid limit size $limitSize");
            }
            $currentStepName = self::STEP_NAME_BY_ID[$currentStepId];
            $this->_helper->log("Limiting on '$currentStepName' step to $limitSize entities", self::LOG_NAME);
            return array_slice($stepDataList, 0, $limitSize);
        }
        return $stepDataList;
    }

    protected function afterStep($integrationName, $finishedStepId, $returnedData)
    {
        $stepName = self::STEP_NAME_BY_ID[$finishedStepId];

        // Complete current step
        $this->_lastStep++;

        // Limit array if needed
        return $this->limitStepData($integrationName, $returnedData, $finishedStepId);
    }
}
