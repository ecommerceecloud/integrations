<?php

namespace Ecloud\Integrations\Model\Api;

use Ecloud\Integrations\Api\IntegrationResponseInterface;
use Magento\Framework\DataObject;

class IntegrationResponse extends DataObject implements IntegrationResponseInterface
{
	/** @inheritdoc */
	public function getSuccess()
	{
		return $this->getData(self::KEY_SUCCESS);
	}

	/** @inheritdoc */
	public function getResult()
	{
		return $this->getData(self::KEY_RESULT);
	}

	/** @inheritdoc */
	public function getErrorTrace()
	{
		return $this->getData(self::KEY_ERROR_TRACE);
	}

	/** @inheritdoc */
	public function getErrorMessage()
	{
		return $this->getData(self::KEY_ERROR_MESSAGE);
	}

	/** @inheritdoc */
	public function setSuccess($success)
	{
		$this->setData(self::KEY_SUCCESS, $success);
		return $this;
	}

	/** @inheritdoc */
	public function setResult($result)
	{
		$this->setData(self::KEY_RESULT, $result);
		return $this;
	}

	/** @inheritdoc */
	public function setErrorTrace($errorTrace)
	{
		$this->setData(self::KEY_ERROR_TRACE, $errorTrace);
		return $this;
	}

	/** @inheritdoc */
	public function setErrorMessage($errorMessage)
	{
		$this->setData(self::KEY_ERROR_MESSAGE, $errorMessage);
		return $this;
	}
}
