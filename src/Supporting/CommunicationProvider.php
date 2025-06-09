<?php

namespace INTERMediator\FileMakerServer\RESTAPI\Supporting;

use Exception;
use CurlHandle;

/**
 * Class CommunicationProvider is for internal use to communicate with FileMaker Server.
 *
 * @package INTER-Mediator\FileMakerServer\RESTAPI
 * @link https://github.com/msyk/FMDataAPI GitHub Repository
 * @version 33
 * @author Masayuki Nii <nii@msyk.net>
 * @copyright 2017-2024 Masayuki Nii (Claris FileMaker is registered trademarks of Claris International Inc. in the U.S. and other countries.)
 */
class CommunicationProvider
{
    /**
     * @var int
     * @ignore
     */
    public int $vNum = -1;
    /**
     * @var null|string
     * @ignore
     */
    private string|null $host = "127.0.0.1";
    /**
     * @var string
     * @ignore
     */
    private string $user;
    /**
     * @var string
     * @ignore
     */
    private string $password;
    /**
     * @var string|null
     * @ignore
     */
    private string|null $solution;
    /**
     * @var null|string
     * @ignore
     */
    private string|null $protocol = 'https';
    /**
     * @var int|null
     * @ignore
     */
    private int|null $port = 443;

    /**
     * @var string|null
     * @ignore
     */
    public string|null $accessToken = null;
    /**
     * @var string
     * @ignore
     */
    protected string $method;
    /**
     * @var string
     * @ignore
     */
    public string $url;
    /**
     * @var array
     * @ignore
     */
    protected array $requestHeader;
    /**
     * @var null|array|string
     * @ignore
     */
    public null|array|string $requestBody = "";
    /**
     * @var int
     * @ignore
     */
    public int $curlErrorNumber = 0;
    /**
     * @var string
     * @ignore
     */
    public string $curlError = "";
    /**
     * @var null|array
     * @ignore
     */
    protected null|array $curlInfo;
    /**
     * @var string
     * @ignore
     */
    private string $responseHeader;
    /**
     * @var bool
     * @ignore
     */
    private bool $isLocalServer = false;
    /**
     * @var null|string
     * @ignore
     */
    public null|string $targetTable = '';
    /**
     * @var null|int
     * @ignore
     */
    public null|int $totalCount = null;
    /**
     * @var null|int
     * @ignore
     */
    public null|int $foundCount = null;
    /**
     * @var null|int
     * @ignore
     */
    public null|int $returnedCount = null;
    /**
     * @var null|object
     * @ignore
     */
    public null|object $responseBody = null;
    /**
     * @var null|int
     * @ignore
     */
    public null|int $httpStatus = null;
    /**
     * @var int
     * @ignore
     */
    public int $errorCode;
    /**
     * @var null|string
     * @ignore
     */
    public null|string $errorMessage = "";
    /**
     * @var bool
     * @ignore
     */
    public bool $keepAuth = false;

    /**
     * @var bool
     * @ignore
     */
    public bool $isDebug = false;
    /**
     * @var bool
     * @ignore
     */
    public bool $isCertValidating = false;
    /**
     * @var bool
     * @ignore
     */
    public bool $throwExceptionInError = true;
    /**
     * @var bool
     * @ignore
     */
    public bool $useOAuth = false;
    /**
     * @var null|array
     * @ignore
     */
    private null|array $fmDataSource;
    /**
     * @var null|string
     * @ignore
     */
    public null|string $scriptError = "";
    /**
     * @var null|string
     * @ignore
     */
    public null|string $scriptResult = "";
    /**
     * @var null|string
     * @ignore
     */
    public null|string $scriptErrorPrerequest = "";
    /**
     * @var null|string
     * @ignore
     */
    public null|string $scriptResultPrerequest = "";
    /**
     * @var null|string
     * @ignore
     */
    public null|string $scriptErrorPresort = "";
    /**
     * @var null|string
     * @ignore
     */
    public null|string $scriptResultPresort = "";
    /**
     * @var null|int
     * @ignore
     */
    public null|int $timeout = null;
    /**
     * @var bool
     * @ignore
     */
    public bool $fieldHTMLEncoding = false;

    /**
     * CommunicationProvider constructor.
     * @param string $solution
     * @param string $user
     * @param string $password
     * @param string|null $host
     * @param string|null $port
     * @param string|null $protocol
     * @param array|null $fmDataSource
     * @ignore
     */
    public function __construct(string      $solution,
                                string      $user,
                                string      $password,
                                string|null $host = null,
                                string|null $port = null,
                                string|null $protocol = null,
                                array|null  $fmDataSource = null)
    {
        $this->solution = rawurlencode($solution);
        $this->user = $user;
        $this->password = $password;
        if (!is_null($host)) {
            if ($host == "localserver") {
                $this->host = "127.0.0.1";
                $this->port = "3000";
                $this->isLocalServer = true;
                $this->protocol = "http";
            } else {
                $this->host = $host;
                if (!is_null($port)) {
                    $this->port = $port;
                }
                if (!is_null($protocol)) {
                    $this->protocol = $protocol;
                }
            }
        }
        $this->fmDataSource = $fmDataSource;
        $this->errorCode = -1;
    }

    /**
     * @param array $params Array to build the API path. Ex: `["layouts" => null]` or `["sessions" => $this->accessToken]`.
     * @param string|array|null $request The query parameters as `"key" => "value"`.
     * @param string $methodLower The method in lowercase. Ex: `"get"`, `"delete"`, etc.
     * @param bool $isSystem If the query is for the system (sessions, databases, etc.) or for a database.
     * @param string|null|false $directPath If we don't want to build the path with the other parameters, you can provide the direct path.
     * @return string
     * @ignore
     */
    public function getURL(array             $params,
                           string|array|null $request,
                           string            $methodLower,
                           bool              $isSystem = false,
                           string|null|false $directPath = null): string
    {
        $vStr = $this->vNum < 1 ? 'Latest' : strval($this->vNum);
        $url = "$this->protocol://$this->host:$this->port";
        if (!empty($directPath)) {
            $url .= $directPath;
        } else {
            $url .= "/fmi/data/v$vStr" . ((!$isSystem) ? "/databases/$this->solution" : "");
        }
        foreach ($params as $key => $value) {
            $url .= "/$key" . (is_null($value) ? "" : "/$value");
        }
        if (!empty($request) && in_array($methodLower, array('get', 'delete'))) {
            $url .= '?';
            foreach ($request as $key => $value) {
                if (key($request) !== $key) {
                    $url .= '&';
                }
                if ($key === 'sort' && is_array($value)) {
                    $sortParam = $this->_buildSortParameters($value);
                    if ($sortParam !== '[]') {
                        $url .= '_' . $key . '=' . $sortParam;
                    }
                } elseif ($key === 'limit' || $key === 'offset') {
                    $url .= '_' . $key . '=' . (is_array($value) ? json_encode($value) : $value);
                } else {
                    // handling portal object name etc.
                    $url .= $key . '=' . (is_array($value) ? $this->_json_urlencode($value) : $value);
                }
            }
        }
        return $url;
    }

    /**
     * @param bool $isAddToken
     * @param array|null $addHeader
     * @return array
     * @ignore
     */
    public function getHeaders(bool $isAddToken, array|null $addHeader): array
    {
        $header = [];
        if ($this->isLocalServer) {
            $header[] = 'X-Forwarded-For: 127.0.0.1';
            $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL);
            if ($host === null || $host === false) {
                $host = 'localhost';
            }
            $header[] = 'X-Forwarded-Host: ' . $host;
        }
        if ($this->useOAuth) {
            $header[] = "X-FM-Data-Login-Type: oauth";
        }
        if ($isAddToken) {
            $header[] = "Authorization: Bearer $this->accessToken";
        }
        if (!is_null($addHeader)) {
            foreach ($addHeader as $key => $value) {
                $header[] = "$key: $value";
            }
        }
        return $header;
    }

    /**
     * @param array|null $request
     * @return array
     * @ignore
     */
    public function justifyRequest(array|null $request): array
    {
        $result = $request;
        // cast a number
        if (isset($result['fieldData'])) {
            foreach ($result['fieldData'] as $fieldName => $fieldValue) {
                $result['fieldData'][$fieldName] = (string)$fieldValue;
            }
        }
        if (isset($result['query'])) {
            foreach ($result['query'] as $key => $array) {
                foreach ($array as $fieldName => $fieldValue) {
                    if (!is_array($fieldValue)) {
                        $result['query'][$key][$fieldName] = (string)$fieldValue;
                    }
                }
            }
        }

        if (isset($result['sort'])) {
            $sort = [];
            foreach ($result['sort'] as $sortCondition) {
                if (isset($sortCondition[0])) {
                    $sortOrder = 'ascend';
                    if (isset($sortCondition[1])) {
                        $sortOrder = $this->adjustSortDirection($sortCondition[1]);
                    }
                    $sort[] = ['fieldName' => $sortCondition[0], 'sortOrder' => $sortOrder];
                }
            }
            $result['sort'] = $sort;
        }
        return $result;
    }

    /**
     * @return object|null
     * @throws Exception In case of any error, an exception arises.
     * @ignore
     */
    public function getProductInfo(): object|null
    {
        $returnValue = null;
        $params = ["productInfo" => null];
        $request = [];
        try {
            $this->callRestAPI($params, false, "GET", $request, null, true); // Throw Exception
            $this->storeToProperties();
            if ($this->httpStatus == 200 && $this->errorCode == 0) {
                $returnValue = $this->responseBody->response->productInfo;
            }
        } catch (Exception $e) {
            if ($this->httpStatus == 200 && $this->errorCode == 0) {
                $returnValue = array("version" => 17);
            } else {
                throw $e;
            }
        }
        return $returnValue;
    }

    /**
     * @return array|null
     * @throws Exception In case of any error, an exception arises.
     * @ignore
     */
    public function getDatabaseNames(): array|null
    {
        $returnValue = null;
        if ($this->useOAuth) {
            $headers = [
                "Content-Type" => "application/json",
                "X-FM-Data-OAuth-Request-Id" => "{$this->user}",
                "X-FM-Data-OAuth-Identifier" => "{$this->password}",
            ];
        } else {
            $value = "Basic " . base64_encode("{$this->user}:{$this->password}");
            $headers = ["Content-Type" => "application/json", "Authorization" => $value];
        }
        $params = ["databases" => null];
        $request = [];
        $this->callRestAPI($params, false, "GET", $request, $headers, true); // Throw Exception
        $this->storeToProperties();
        if ($this->httpStatus == 200 && $this->errorCode == 0) {
            $returnValue = $this->responseBody->response->databases;
        }
        $this->logout();
        return $returnValue;
    }

    /**
     * @return null|array
     * @throws Exception In case of any error, an exception arises.
     * @ignore
     */
    public function getLayoutNames(): null|array
    {
        $returnValue = null;
        if ($this->login()) {
            $params = ["layouts" => null];
            $request = [];
            $headers = [];
            $this->callRestAPI($params, true, "GET", $request, $headers); // Throw Exception
            $this->storeToProperties();
            if ($this->httpStatus == 200 && $this->errorCode == 0) {
                $returnValue = $this->responseBody->response->layouts;
            }
            $this->logout();
        }
        return $returnValue;
    }

    /**
     * @throws Exception In case of any error, an exception arises.
     * @ignore
     */
    public function getScriptNames(): null|array
    {
        $returnValue = null;
        if ($this->login()) {
            $params = ["scripts" => null];
            $request = [];
            $headers = [];
            $this->callRestAPI($params, true, "GET", $request, $headers); // Throw Exception
            $this->storeToProperties();
            if ($this->httpStatus == 200 && $this->errorCode == 0) {
                $returnValue = $this->responseBody->response->scripts;
            }
            $this->logout();
        }
        return $returnValue;
    }

    /**
     * @return bool
     * @throws Exception In case of any error, an exception arises.
     * @ignore
     */
    public function login(): bool
    {
        if ($this->keepAuth || !is_null($this->accessToken)) {
            return true;
        }

        if ($this->useOAuth) {
            $headers = [
                "Content-Type" => "application/json",
                "X-FM-Data-OAuth-Request-Id" => "{$this->user}",
                "X-FM-Data-OAuth-Identifier" => "{$this->password}",
            ];
        } else {
            $value = "Basic " . base64_encode("{$this->user}:{$this->password}");
            $headers = [
                "Content-Type" => "application/json",
                "Authorization" => $value
            ];
        }
        $params = ["sessions" => null];
        $request = [];
        $request["fmDataSource"] = (!is_null($this->fmDataSource)) ? $this->fmDataSource : [];
        try {
            $this->callRestAPI($params, false, "POST", $request, $headers); // Throw Exception
            $this->storeToProperties();
            if ($this->httpStatus == 200 && $this->errorCode == 0) {
                $this->accessToken = $this->responseBody->response->token;
                return true;
            }
        } catch (Exception $e) {
            $this->accessToken = null;
            throw $e;
        }
        return false;
    }

    /**
     *
     * @return void
     * @throws Exception In case of any error, an exception arises.
     * @ignore
     */
    public function logout(): void
    {
        if ($this->keepAuth) {
            return;
        }
        $params = ["sessions" => $this->accessToken];
        $this->callRestAPI($params, true, "DELETE"); // Throw Exception
        $this->accessToken = null;
    }

    /**
     * @return array|null
     * @ignore
     */
    private function getSupportingProviders(): null|array
    {
        try {
            $this->callRestAPI([], true, 'GET', [], [],
                false, "/fmws/oauthproviderinfo"); // Throw Exception
            $result = [];
//            foreach ($this->responseBody as $key => $item) {
//
//            }
            return $result;
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     * @param $provider
     * @return string|array|null
     * @ignore
     */
    private function getOAuthIdentifier($provider): string|array|null
    {
        try {
            $this->callRestAPI(
                [], false, 'GET',
                [
                    "trackingID" => rand(10000000, 99999999),
                    "provider" => $provider,
                    "address" => "127.0.0.1",
                    "X-FMS-OAuth-AuthType" => 2
                ],
                [
                    "X-FMS-Application-Type" => 9,
                    "X-FMS-Application-Version" => 15,
                    "X-FMS-Return-URL" => "http://127.0.0.1/",
                ],
                false, "/oauth/getoauthurl"
            ); // Throw Exception
            $result = [];
//            foreach ($this->responseBody as $key => $item) {
//
//            }
            return $result;
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     * @param array $params
     * @param bool $isAddToken
     * @param string $method
     * @param string|array|null $request
     * @param array|null $addHeader
     * @param bool $isSystem for Metadata
     * @param string|null|false $directPath
     * @return void
     * @throws Exception In case of any error, an exception arises.
     * @ignore
     */
    public function callRestAPI(array             $params,
                                bool              $isAddToken,
                                string            $method = 'GET',
                                string|array|null $request = null,
                                array|null        $addHeader = null,
                                bool              $isSystem = false,
                                string|null|false $directPath = null): void
    {
        $methodLower = strtolower($method);
        $url = $this->getURL($params, $request, $methodLower, $isSystem, $directPath);
        $header = $this->getHeaders($isAddToken, $addHeader);
        $jsonEncoding = true;
        if (is_string($request)) {
            $jsonEncoding = false;
        } elseif ($methodLower !== 'get' && !is_null($request)) {
            $request = $this->justifyRequest($request);
        }
        $ch = $this->_createCurlHandle($url);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        if ($methodLower == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif (in_array($methodLower, ['put', 'patch', 'delete', 'get'], true)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($methodLower));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if ($methodLower != 'get') {
            if ($jsonEncoding) {
                if ($methodLower === 'post' && isset($request['fieldData']) && $request['fieldData'] === []
                ) {
                    // create an empty record
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request, JSON_FORCE_OBJECT));
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
                }
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            }
        }
        $response = curl_exec($ch);
        $this->curlInfo = curl_getinfo($ch);
        $this->curlErrorNumber = curl_errno($ch);
        if ($this->curlErrorNumber) {
            $this->curlError = curl_error($ch);
        }
        curl_close($ch);

        $this->method = $method;
        $this->url = $url;
        $this->requestHeader = $header;
        $this->requestBody = ($methodLower != 'get') ? $request : null;
        $this->responseHeader = substr($response, 0, $this->curlInfo["header_size"]);
        $this->responseBody = json_decode(substr($response, $this->curlInfo["header_size"]), false, 512, JSON_BIGINT_AS_STRING);

        if ($this->isDebug) {
            $this->debugOutput();
        }
        if ($this->throwExceptionInError) {
            $httpStatus = $this->getCurlInfo("http_code");
            $errorCode = $this->responseBody && property_exists($this->responseBody->messages[0], 'code') ?
                intval($this->responseBody->messages[0]->code) : -1;
            $errorMessage = $this->responseBody && property_exists($this->responseBody->messages[0], 'message') ?
                $this->responseBody->messages[0]->message : 'ERROR';
            $description = '';
            if ($this->curlErrorNumber > 0) {
                $description .= "cURL in PHP / Error Code: {$this->curlErrorNumber}, Error Message: {$this->curlError}. ";
            } else {
                if ($httpStatus !== 200) {
                    $description .= "HTTP Status Code: {$httpStatus}. ";
                }
                if ($errorCode > 0) {
                    $description .= "FileMaker Data API / Error Code: {$errorCode}, Error Message: {$errorMessage}. ";
                }
            }
            if ($description !== '') {
                $description = date('Y-m-d H:i:s ') . "{$description}";
                $description .= "[URL({$this->method}): {$this->url}]";
                if ($errorCode !== 401) {
                    throw new Exception($description, $errorCode);
                }
            }
        }
    }

    /**
     * Return the base64 encoded data in container field.
     * Thanks to 'base64bits' as https://github.com/msyk/FMDataAPI/issues/18.
     * @param string $url
     * @return string The base64 encoded data in container field.
     * @throws Exception
     * @ignore
     */
    public function accessToContainer(string $url): string
    {
        $cookieFile = tempnam(sys_get_temp_dir(), "CURLCOOKIE"); // Create a cookie file.

        // Visit the container URL to set the cookie.
        $ch = $this->_createCurlHandle($url);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_exec($ch);
        if (curl_errno($ch) !== 0) {
            $errMsg = curl_error($ch);
            curl_close($ch);
            throw new Exception("Error in creating cookie file. {$errMsg}");
        }
        curl_close($ch);

        // Visit the container URL again.
        $ch = $this->_createCurlHandle($url);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        $output = curl_exec($ch);
        if (curl_errno($ch) !== 0) {
            $errMsg = curl_error($ch);
            curl_close($ch);
            throw new Exception("Error in downloading content of file. {$errMsg}");
        }
        curl_close($ch);

        return base64_encode($output); // Process the data as needed.
    }

    /**
     * @ignore
     */
    public function storeToProperties(): void
    {
        $this->httpStatus = 0;
        $this->errorCode = -1;
        $this->scriptError = null;
        $this->scriptResult = null;
        $this->scriptErrorPrerequest = null;
        $this->scriptResultPrerequest = null;
        $this->scriptErrorPresort = null;
        $this->scriptResultPresort = null;
        $this->targetTable = null;
        $this->totalCount = null;
        $this->foundCount = null;
        $this->returnedCount = null;
        $this->errorMessage = null;

        if (property_exists($this, 'responseBody')) {
            $rbody = $this->responseBody;
            if (is_object($rbody)) {
                if (property_exists($rbody, 'messages')) {
                    $result = $rbody->messages[0];
                    $this->httpStatus = $this->getCurlInfo("http_code");
                    $this->errorCode = property_exists($result, 'code') ? $result->code : -1;
                    $this->errorMessage = property_exists($result, 'message') && $result->code != 0 ? $result->message : null;
                }
                if (property_exists($rbody, 'response')) {
                    $result = $rbody->response;
                    $this->scriptError = property_exists($result, 'scriptError') ? $result->scriptError : null;
                    $this->scriptResult = property_exists($result, 'scriptResult') ? $result->scriptResult : null;
                    $this->scriptErrorPrerequest = property_exists($result, 'scriptError.prerequest') ?
                        $result->{'scriptError.prerequest'} : null;
                    $this->scriptResultPrerequest = property_exists($result, 'scriptResult.prerequest') ?
                        $result->{'scriptResult.prerequest'} : null;
                    $this->scriptErrorPresort = property_exists($result, "scriptError.presort") ?
                        $result->{"scriptError.presort"} : null;
                    $this->scriptResultPresort = property_exists($result, "scriptResult.presort") ?
                        $result->{"scriptResult.presort"} : null;
                    if (property_exists($result, 'dataInfo')) {
                        $dataInfo = $result->dataInfo;
                        $this->targetTable = property_exists($dataInfo, 'table') ?
                            $dataInfo->table : null;
                        $this->totalCount = property_exists($dataInfo, 'totalRecordCount') ?
                            $dataInfo->totalRecordCount : null;
                        $this->foundCount = property_exists($dataInfo, 'foundCount') ?
                            $dataInfo->foundCount : null;
                        $this->returnedCount = property_exists($dataInfo, 'returnedCount') ?
                            $dataInfo->returnedCount : null;
                    }
                }
            }
        }
    }

    /**
     * @param string $direction
     * @return string
     * @ignore
     */
    public function adjustSortDirection(string $direction): string
    {
        if (strtoupper($direction) == 'ASC') {
            $direction = 'ascend';
        } elseif (strtoupper($direction) == 'DESC') {
            $direction = 'descend';
        }

        return $direction;
    }

    /**
     * @param $key
     * @return mixed
     * @ignore
     */
    public function getCurlInfo($key): mixed
    {
        return $this->curlInfo[$key];
    }

    /**
     * @param bool $isReturnValue
     * @return string
     * @ignore
     */
    public function debugOutput(bool $isReturnValue = false): string
    {
        $str = "<div style='background-color: #DDDDDD'>URL: ";
        $str .= $this->method . ' ' . htmlspecialchars($this->url);
        $str .= "</div>Added Request Header:<br><pre>";
        $str .= htmlspecialchars(var_export($this->requestHeader, true));
        $str .= "</pre><hr>Request Body:<br><pre>";
        if (is_string($this->requestBody)) {
            $str .= htmlspecialchars(substr($this->requestBody, 0, 40));
        } else {
            $str .= htmlspecialchars(json_encode($this->requestBody, JSON_PRETTY_PRINT));
        }
        $str .= "</pre><hr>Response Header:<br><pre>";
        $str .= htmlspecialchars($this->responseHeader);
        $str .= "</pre><hr>Response Body:<br><pre>";
        $str .= htmlspecialchars(json_encode($this->responseBody, JSON_PRETTY_PRINT));
        //$str .= "</pre><hr>Info:<br><pre>";
        //$str .= var_export($this->curlInfo, true);
        $str .= "</pre><hr>CURL ErrorNumber: {$this->curlErrorNumber}";
        $str .= "</pre><hr>CURL Error: ";
        $str .= $this->curlError ? htmlspecialchars($this->curlError) : '';
        $str .= "<hr>";
        if ($isReturnValue) {
            return $str;
        } else {
            echo $str;
        }
        return "";
    }

    /**
     * @param array $value
     * @return string
     * @ignore
     */
    private function _buildSortParameters(array $value): string
    {
        $param = '[';
        foreach ($value as $sortCondition) {
            if (isset($sortCondition[0])) {
                if ($param !== '[') {
                    $param .= ',';
                }
                if (isset($sortCondition[1])) {
                    $sortOrder = $this->adjustSortDirection($sortCondition[1]);
                    $param .= '{"fieldName":' . json_encode($sortCondition[0]) .
                        ',"sortOrder":' . json_encode($sortOrder) . '}';
                } else {
                    $param .= '{"fieldName":' . json_encode($sortCondition[0]) . '}';
                }
            }
        }
        $param .= ']';

        return rawurlencode($param);
    }

    /**
     * @param array $value
     * @return string
     * @ignore
     */
    private function _json_urlencode(array $value): string
    {
        $str = '[';
        if (count($value) > 0) {
            foreach ($value as $el) {
                if ($str !== '[') {
                    $str .= ',';
                }
                $str .= '"' . urlencode($el) . '"';
            }
        }
        $str .= ']';

        return $str;
    }

    /**
     * To create and configure cURL at a single place, avoiding code redundancy.
     *
     * @param string $url The URL you want to access.
     * @return CurlHandle
     */
    private function _createCurlHandle(string $url): CurlHandle
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
        if ($this->isCertValidating) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            /* Use the OS native certificate authorities, if possible.
            This fixes SSL validation errors if `php.ini` doesn't have [curl] `curl.cainfo`,
            set properly of if this PEM file isn't up to date.
            Better rely on the OS certificate authorities, which is maintained automatically. */
            if (defined('CURLSSLOPT_NATIVE_CA')
                && version_compare(curl_version()['version'], '7.71', '>=')) {
                curl_setopt($ch, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA);
            }
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        if (!is_null($this->timeout)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        }
        return $ch;
    }
}
