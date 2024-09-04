<?php

namespace INTERMediator\FileMakerServer\RESTAPI\Supporting;

/**
 * Class CommunicationProvider is for internal use to communicate with FileMaker Server.
 *
 * @package INTER-Mediator\FileMakerServer\RESTAPI
 * @link https://github.com/msyk/FMDataAPI GitHub Repository
 * @version 31
 * @author Masayuki Nii <nii@msyk.net>
 * @copyright 2017-2023 Masayuki Nii (Claris FileMaker is registered trademarks of Claris International Inc. in the U.S. and other countries.)
 */
class CommunicationProvider
{
    /**
     * @var integer
     * @ignore
     */
    public $vNum = -1;
    /**
     * @var null|string
     * @ignore
     */
    private $host = "127.0.0.1";
    /**
     * @var string
     * @ignore
     */
    private $user = "admin";
    /**
     * @var string
     * @ignore
     */
    private $password = "1234";
    /**
     * @var string
     * @ignore
     */
    private $solution;
    /**
     * @var null|string
     * @ignore
     */
    private $protocol = 'https';
    /**
     * @var int|null
     * @ignore
     */
    private $port = 443;

    /**
     * @var string
     * @ignore
     */
    public $accessToken = null;
    /**
     * @var string
     * @ignore
     */
    protected $method;
    /**
     * @var string
     * @ignore
     */
    public $url;
    /**
     * @var array
     * @ignore
     */
    protected $requestHeader;
    /**
     * @var string
     * @ignore
     */
    public $requestBody;
    /**
     * @var int
     * @ignore
     */
    public $curlErrorNumber;
    /**
     * @var string
     * @ignore
     */
    public $curlError;
    /**
     * @var array
     * @ignore
     */
    protected $curlInfo;
    /**
     * @var string
     * @ignore
     */
    private $responseHeader;
    /**
     * @var bool
     * @ignore
     */
    private $isLocalServer = false;
    /**
     * @var string
     * @ignore
     */
    public $targetTable = '';
    /**
     * @var int
     * @ignore
     */
    public $totalCount = 0;
    /**
     * @var int
     * @ignore
     */
    public $foundCount = 0;
    /**
     * @var int
     * @ignore
     */
    public $returnedCount = 0;
    /**
     * @var object
     * @ignore
     */
    public $responseBody;
    /**
     * @var int
     * @ignore
     */
    public $httpStatus;
    /**
     * @var int
     * @ignore
     */
    public $errorCode;
    /**
     * @var string
     * @ignore
     */
    public $errorMessage;
    /**
     * @var bool
     * @ignore
     */
    public $keepAuth = false;

    /**
     * @var bool
     * @ignore
     */
    public $isDebug;
    /**
     * @var bool
     * @ignore
     */
    public $isCertVaridating;
    /**
     * @var bool
     * @ignore
     */
    public $throwExceptionInError = true;
    /**
     * @var bool
     * @ignore
     */
    public $useOAuth = false;
    /**
     * @var array
     * @ignore
     */
    private $fmDataSource;
    /**
     * @var string
     * @ignore
     */
    public $scriptError;
    /**
     * @var string
     * @ignore
     */
    public $scriptResult;
    /**
     * @var string
     * @ignore
     */
    public $scriptErrorPrerequest;
    /**
     * @var string
     * @ignore
     */
    public $scriptResultPrerequest;
    /**
     * @var string
     * @ignore
     */
    public $scriptErrorPresort;
    /**
     * @var string
     * @ignore
     */
    public $scriptResultPresort;
    /**
     * @var int
     * @ignore
     */
    public $timeout;
    /**
     * @var bool
     * @ignore
     */
    public $fieldHTMLEncoding = false;

    /**
     * CommunicationProvider constructor.
     * @param string $solution
     * @param string $user
     * @param string $password
     * @param null|string $host
     * @param null|int $port
     * @param null|string $protocol
     * @ignore
     */
    public function __construct($solution, $user, $password, $host = null, $port = null, $protocol = null, $fmDataSource = null)
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
     * @param null|array $request The query parameters as `"key" => "value"`.
     * @param string $methodLower The method in lowercase. Ex: `"get"`, `"delete"`, etc.
     * @param bool $isSystem If the query is for the system (sessions, databases, etc) or for a database.
     * @param false|string $directPath If we don't want to build the path with the other parameters, you can provide the direct path.
     * @return string
     * @ignore
     */
    public function getURL($params, $request, $methodLower, $isSystem = false, $directPath = false)
    {
        $vStr = $this->vNum < 1 ? 'Latest' : strval($this->vNum);
        $url = "{$this->protocol}://{$this->host}:{$this->port}";
        if ($directPath) {
            $url .= $directPath;
        } else {
            $url .= "/fmi/data/v{$vStr}" . ((!$isSystem) ? "/databases/{$this->solution}" : "");
        }
        foreach ($params as $key => $value) {
            $url .= "/{$key}" . (is_null($value) ? "" : "/{$value}");
        }
        if (!is_string($request) &&
            in_array($methodLower, array('get', 'delete')) &&
            !is_null($request) &&
            count($request) > 0
        ) {
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
                } else if ($key === 'limit' || $key === 'offset') {
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
     * @param $isAddToken
     * @param $addHeader
     * @return array
     * @ignore
     */
    public function getHeaders($isAddToken, $addHeader)
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
            $header[] = "Authorization: Bearer {$this->accessToken}";
        }
        if (!is_null($addHeader)) {
            foreach ($addHeader as $key => $value) {
                $header[] = "{$key}: {$value}";
            }
        }
        return $header;
    }

    /**
     * @ignore
     */
    public function justifyRequest($request)
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
            foreach ($result['sort'] as $sortKey => $sortCondition) {
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
     * @throws \Exception In case of any error, an exception arises.
     * @ignore
     */
    public function getProductInfo()
    {
        $returnValue = false;
        $params = ["productInfo" => null];
        $request = [];
        try {
            $this->callRestAPI($params, false, "GET", $request, null, true);
            $this->storeToProperties();
            if ($this->httpStatus == 200 && $this->errorCode == 0) {
                $returnValue = $this->responseBody->response->productInfo;
            }
        } catch (\Exception $e) {
            if ($this->httpStatus == 200 && $this->errorCode == 0) {
                $returnValue = array("version" => 17);
            } else {
                throw $e;
            }
        }
        return $returnValue;
    }

    /**
     * @throws \Exception In case of any error, an exception arises.
     * @ignore
     */
    public function getDatabaseNames()
    {
        $returnValue = false;
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
        try {
            $this->callRestAPI($params, false, "GET", $request, $headers, true);
            $this->storeToProperties();
            if ($this->httpStatus == 200 && $this->errorCode == 0) {
                $returnValue = $this->responseBody->response->databases;
            }
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->logout();
        }
        return $returnValue;
    }

    /**
     * @throws \Exception In case of any error, an exception arises.
     * @ignore
     */
    public function getLayoutNames()
    {
        $returnValue = false;
        if ($this->login()) {
            $params = ["layouts" => null];
            $request = [];
            $headers = [];
            try {
                $this->callRestAPI($params, true, "GET", $request, $headers);
                $this->storeToProperties();
                if ($this->httpStatus == 200 && $this->errorCode == 0) {
                    $returnValue = $this->responseBody->response->layouts;
                }
            } catch (\Exception $e) {
                throw $e;
            } finally {
                $this->logout();
            }
        }
        return $returnValue;
    }

    /**
     * @throws \Exception In case of any error, an exception arises.
     * @ignore
     */
    public function getScriptNames()
    {
        $returnValue = false;
        if ($this->login()) {
            $params = ["scripts" => null];
            $request = [];
            $headers = [];
            try {
                $this->callRestAPI($params, true, "GET", $request, $headers);
                $this->storeToProperties();
                if ($this->httpStatus == 200 && $this->errorCode == 0) {
                    $returnValue = $this->responseBody->response->scripts;
                }
            } catch (\Exception $e) {
                throw $e;
            } finally {
                $this->logout();
            }
        }
        return $returnValue;
    }

    /**
     * @throws \Exception In case of any error, an exception arises.
     * @ignore
     */
    public function login()
    {
        if ($this->keepAuth) {
            return true;
        }
        if (!is_null($this->accessToken)) {
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
            $headers = ["Content-Type" => "application/json", "Authorization" => $value];
        }
        $params = ["sessions" => null];
        $request = [];
        $request["fmDataSource"] = (!is_null($this->fmDataSource)) ? $this->fmDataSource : [];
        try {
            $this->callRestAPI($params, false, "POST", $request, $headers);
            $this->storeToProperties();
            if ($this->httpStatus == 200 && $this->errorCode == 0) {
                $this->accessToken = $this->responseBody->response->token;
                return true;
            }
        } catch (\Exception $e) {
            $this->accessToken = null;
            throw $e;
        }
        return false;
    }

    /**
     *
     * @throws \Exception In case of any error, an exception arises.
     * @ignore
     */
    public function logout()
    {
        if ($this->keepAuth) {
            return;
        }
        $params = ["sessions" => $this->accessToken];
        try {
            $this->callRestAPI($params, true, "DELETE");
            $this->accessToken = null;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getSupportingProviders()
    {
        try {
            $this->callRestAPI([], [], 'GET', [], [], false, "/fmws/oauthproviderinfo");
            $result = [];
            foreach ($this->responseBody as $key => $item) {

            }
            return $result;
        } catch (\Exception $ex) {
            return null;
        }
    }

    private function getOAuthIdentifier($provider)
    {
        try {
            $this->callRestAPI([], [
                "trackingID" => rand(10000000, 99999999),
                "provider" => $provider,
                "address" => "127.0.0.1",
                "X-FMS-OAuth-AuthType" => 2
            ], 'GET', [], [
                "X-FMS-Application-Type" => 9,
                "X-FMS-Application-Version" => 15,
                "X-FMS-Return-URL" => "http://127.0.0.1/",
            ], false, "/oauth/getoauthurl");
            $result = [];
            foreach ($this->responseBody as $key => $item) {

            }
            return $result;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * @param $params
     * @param $layout
     * @param boolean $isAddToken
     * @param string $method
     * @param array $request
     * @param array $addHeader
     * @param boolean $isSystem for Metadata
     * @throws \Exception In case of any error, an exception arises.
     * @ignore
     */
    public function callRestAPI($params, $isAddToken, $method = 'GET', $request = null, $addHeader = null, $isSystem = false, $directPath = false)
    {
        $methodLower = strtolower($method);
        $url = $this->getURL($params, $request, $methodLower, $isSystem, $directPath);
        $header = $this->getHeaders($isAddToken, $addHeader);
        $jsonEncoding = true;
        if (is_string($request)) {
            $jsonEncoding = false;
        } else if ($methodLower !== 'get' && !is_null($request)) {
            $request = $this->justifyRequest($request);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        if ($methodLower == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif (in_array($methodLower, ['put', 'patch', 'delete', 'get'], true)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($methodLower));
        }
        if ($this->isCertVaridating) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            // Use the OS native certificate authorities, if possible.
            // This fixes SSL validation errors if `php.ini` doesn't have
            // [curl] `curl.cainfo` set properly of if this PEM file isn't
            // up to date. Better rely on the OS certificate authorities, which
            // is maintained automatically.
            if (defined('CURLSSLOPT_NATIVE_CA')
                && version_compare(curl_version()['version'], '7.71', '>=')) {
                curl_setopt($ch, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA);
            }
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
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
        if (!is_null($this->timeout)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
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
                    throw new \Exception($description, $errorCode);
                }
            }
        }
    }

    /**
     * Return the base64 encoded data in container field.
     * Thanks to 'base64bits' as https://github.com/msyk/FMDataAPI/issues/18.
     * @param string $name The container field name.
     * The table occurrence name of the portal can be the portal name, and also the object name of the portal.
     * @param string $toName The table occurrence name of the portal as the prefix of the field name.
     * @return string The base64 encoded data in container field.
     * @ignore
     */
    public function accessToContainer($url)
    {
        $cookieFile = tempnam(sys_get_temp_dir(), "CURLCOOKIE"); //create a cookie file

        $ch = curl_init($url); //visit the container URL to set the cookie
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->isCertVaridating) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        if (!is_null($this->timeout)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        }
        curl_exec($ch);
        if (curl_errno($ch) !== 0) {
            $errMsg = curl_error($ch);
            throw new \Exception("Error in creating cookie file. {$errMsg}");
        }

        $ch = curl_init($url); //visit container URL again
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->isCertVaridating) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        if (!is_null($this->timeout)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        }
        $output = curl_exec($ch);
        if (curl_errno($ch) !== 0) {
            $errMsg = curl_error($ch);
            throw new \Exception("Error in downloading content of file. {$errMsg}");
        }

        return base64_encode($output); //process the image data as need it
    }

    /**
     * @ignore
     */
    public function storeToProperties()
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
    public function adjustSortDirection($direction)
    {
        if (strtoupper($direction) == 'ASC') {
            $direction = 'ascend';
        } else if (strtoupper($direction) == 'DESC') {
            $direction = 'descend';
        }

        return $direction;
    }

    /**
     * @param $key
     * @return mixed
     * @ignore
     */
    public function getCurlInfo($key)
    {
        return $this->curlInfo[$key];
    }

    /**
     * @param bool $isReturnValue
     * @return string
     * @ignore
     */
    public function debugOutput($isReturnValue = false)
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
    }

    /**
     * @param array $value
     * @return string
     * @ignore
     */
    private function _buildSortParameters($value)
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

        return $param;
    }

    /**
     * @param array $value
     * @return string
     * @ignore
     */
    private function _json_urlencode($value)
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
}
