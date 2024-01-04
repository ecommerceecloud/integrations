<?php

namespace Ecloud\Integrations\Api;

interface IntegrationResponseInterface
{
    const KEY_SUCCESS = "success";
    const KEY_RESULT = "result";
    const KEY_ERROR_TRACE = "errorTrace";
    const KEY_ERROR_MESSAGE = "errorMessage";

    /**
     * @return bool $success
     */
    public function getSuccess();

    /**
     * @return mixed $result
     */
    public function getResult();

    /**
     * @return string $errorTrace
     */
    public function getErrorTrace();

    /**
     * @return string $errorMessage
     */
    public function getErrorMessage();

    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success);

    /**
     * @param mixed $result
     * @return $this
     */
    public function setResult($result);

    /**
     * @param string $errorTrace
     * @return $this
     */
    public function setErrorTrace($errorTrace);

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function setErrorMessage($errorMessage);
}
