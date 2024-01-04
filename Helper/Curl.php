<?php

namespace Ecloud\Integrations\Helper;

use Ecloud\Integrations\Helper\Data;

class Curl
{
	const BASE_HEADERS = array();

	protected $baseUrl;
	protected $appKey;
	protected $appToken;
	protected $headers;

	public function init($baseUrl)
	{
		$this->baseUrl = $baseUrl;
		$this->headers = self::BASE_HEADERS;
		return $this;
	}

	public function removeHeader($headerName)
	{
		unset($this->headers[$headerName]);
	}

	public function clearHeaders()
	{
		$this->headers = self::BASE_HEADERS;
	}

	public function addHeader($headerName, $headerValue)
	{
		$this->headers[] = "$headerName: $headerValue";
		return $this;
	}

	/* REQUEST FUNCTIONS */

	/**
	 * @param array $request curl options array
	 * @param bool $customHandle if true, returns raw data, error and status, if false, returns parsed json response or throws exception handling
	 * @param bool $getHeaders if true, returns raw data, adding headers to the response
	 * @return array
	 * @throws \Exception
	 */
	public function execute($optionArray, $customHandle = false, $getHeaders = false)
	{
		return self::staticExecute($optionArray, $customHandle, $getHeaders);
	}

	/**
	 * @param array $request curl options array
	 * @param bool $customHandle if true, returns raw data, error and status, if false, returns parsed json response or throws exception handling
	 * @param bool $getHeaders if true, returns raw data, adding headers to the response
	 * @return array
	 * @throws \Exception
	 */
	public static function staticExecute($optionArray, $customHandle = false, $getHeaders = false)
	{
		$curl = curl_init();

		curl_setopt_array($curl, $optionArray);

		if ($getHeaders) {
			$rawResponse = curl_exec($curl);
			$curlError = curl_error($curl);
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$curlHeaders = substr($rawResponse, 0, $header_size);
			$curlResponse = substr($rawResponse, $header_size);
			$curlStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		} else {
			$curlResponse = curl_exec($curl);
			$curlError = curl_error($curl);
			$curlStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$curlHeaders = null;
		}

		curl_close($curl);

		if ($customHandle) {
			return array(
				$curlStatus,
				$curlResponse,
				$curlError,
				$curlHeaders,
			);
		}

		if ($curlError && $curlError != "") {
			throw new \Exception($curlError);
		}

		if ($curlStatus < 200 || $curlStatus > 299) {
			throw new \Exception("Status: " . $curlStatus);
		}

		try {
			$responseObj = json_decode($curlResponse, true);
			if ($getHeaders)
				return array(
					$responseObj,
					$curlHeaders
				);
			return $responseObj;
		} catch (\Exception $e) {
			throw new \Exception("Response is not json: " . $curlResponse);
		}
	}

	/**
	 * @param string $method HTTP method name (GET|POST|PUT|DELETE|PATCH)
	 * @param string $url full URL to request
	 * @param string|null the json parsed body of the request (default = null)
	 * @param bool if true, the request will return headers along with the response (default = false)
	 * @return array
	 */
	public function getRequest($method, $url, $body = null, $returnHeaders = false, $userPwd = null)
	{
		return $this->prepareRequest($method, $url, $body, $this->headers, $returnHeaders, $userPwd);
	}

	public static function prepareRequest($method, $url, $body = null, $headers, $returnHeaders = false, $userPwd = null)
	{
		return array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => strtoupper($method),
			CURLOPT_USERPWD => $userPwd,
			CURLOPT_POSTFIELDS => $body,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_HEADER => $returnHeaders
		);
	}

	/**
	 * @param string $headerName
	 * @param string $allHeaders
	 * @return string
	 */
	public static final function getHeader($headerName, $allHeaders)
	{
		$initialPos = strpos($allHeaders, $headerName) + strlen($headerName . ": ");
		$finalPos = strpos($allHeaders, "\n", $initialPos);
		if ($finalPos === false) $finalPos = null;
		$myHeader = substr($allHeaders, $initialPos, ($finalPos) - $initialPos);
		return $myHeader;
	}

	/* -REQUEST FUNCTIONS- */


	/* URL FUNCTIONS */

	public function getBaseUrl($uri, $params = array())
	{
		return $this->getUrl($this->baseUrl, $uri, $params);
	}

	public function getUrl($baseUrl, $uri, $params)
	{
		$url = $baseUrl . $uri;
		$char = "?";
		foreach ($params as $name => $value) {
			if (!$value) continue;
			$url .= $char . $name . "=" . $value;
			$char = "&";
		}
		return str_replace(" ", "%20", $url);
	}

	/* -URL FUNCTIONS- */

	public static final function strContains($needle, $haystack)
	{
		$pos = strpos($haystack, $needle);
		return !($pos === false);
	}

	/**
	 * Scroll thrpough a request
	 * @param string $method HTTP method name (GET|POST|PUT|DELETE|PATCH)
	 * @param string $uri the initial URI of the request
	 * @param array $body queryString parameters of the initial request
	 * @param array $params the body for each request
	 * @param array $validateMapping array to validate mapping of each response
	 * @param int $perPage expected number of items per page
	 * @param array $dataGetPath where to look for data in the response. Each array item indicates accessing a property of the origin
	 * @param array $scrollIdGetPath where to look for the scroll ID inside $scrollIdOrigin. Each array item indicates accessing a property of the origin
	 * @param array $scrollIdSetPath where to set the scroll ID inside $scrollIdDestination. Each array item indicates accessing a property of the destination
	 * @param string $scrollIdOrigin "header"|"body" indicates where to get the scroll ID in every request
	 * @param string $scrollIdDestination "header"|"body"|"uri" indicates where to set the scroll ID for each request
	 */
	public function scroll($method, $uri, $params, $body, $validateMapping, $perPage, $dataGetPath, $scrollIdGetPath, $scrollIdSetPath, $scrollIdOrigin = "header", $scrollIdDestination = "uri", $scrollId = null)
	{
		if ($scrollId) {
			switch ($scrollIdDestination) {
				case "header":
					$scrollIdGetTarget = &$this->headers;
					break;
				case "body":
					$scrollIdGetTarget = &$body;
					break;
				case "uri":
					$scrollIdGetTarget = &$params;
					break;
				default:
					throw new \Exception("Invalid destination for scroll ID: $scrollIdDestination.");
			}
			$scrollIdGetTarget = Data::addToArrayPath($scrollIdGetTarget, $scrollIdSetPath, $scrollId);
		}

		$url = $this->getBaseUrl($uri, $params);
		$request = $this->getRequest($method, $url, $body, true);


		try {
			list($response, $responseHeaders) = $this->execute($request, false, true);

			$values = Data::getArrayPath($response, $dataGetPath);

			if (!Data::validateCompleteMapping($validateMapping, $response)) {
				throw new \Exception("Invalid response");
			}
			
			$scrollIdSetTarget = $scrollIdOrigin == "header" ? $responseHeaders : $response;
			
			$scrollId = Data::getArrayPath($scrollIdSetTarget, $scrollIdGetPath);

			if (count($values) < $perPage) {
				return $values;
			}

			try {
				$nextValues = $this->scroll($method, $uri, $params, $body, $validateMapping, $perPage, $dataGetPath, $scrollIdGetPath, $scrollIdSetPath, $scrollIdOrigin, $scrollIdDestination, $scrollId);
			} catch (\Exception $e) {
				$nextValues = [];
			}
			return array_merge($values, $nextValues);
		} catch (\Exception $e) {
			throw $e;
		}
	}
}
