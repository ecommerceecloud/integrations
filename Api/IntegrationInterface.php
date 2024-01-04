<?php

namespace Ecloud\Integrations\Api;

interface IntegrationInterface
{
    /**
     * Starts an export given its type.
     *
     * @param string exportType
     * @param mixed data
     * @return \Ecloud\Integrations\Api\IntegrationResponseInterface
     */
    public function export($exportType, $data);

    /**
     * Starts an import given its type and run type.
     *
     * @param string importType
     * @param string runType
     * @param mixed data
     * @return \Ecloud\Integrations\Api\IntegrationResponseInterface
     */
    public function import($importType, $runType, $data);
}