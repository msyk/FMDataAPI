<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/04/24
 * Time: 17:29
 */
class FMDataAPI
{
    private $layoutTable = array();
    private $provider = NULL;

    public function __construct(
        $solution, $user, $password, $host = NULL, $port = NULL, $protocol = NULL)
    {
        $this->provider = new CommunicationProvider(
            $solution, $user, $password, $host, $port, $protocol);
    }

    public function __set($key, $value)
    {
        // Exception
    }

    public function __get($key)
    {
        return $this->layout($key);
    }

    public function layout($layout)
    {
        if (!isset($this->layoutTable[$layout])) {
            $this->layoutTable[$layout] = new FileMakerLayout($this->provider, $layout);
        }
        return $this->layoutTable[$layout];
    }

    public function setDebug($value)
    {
        $this->provider->isDebug = $value;
    }

    public function setCertValidating($value)
    {
        $this->provider->isCertVaridating = $value;
    }

    public function httpStatus()
    {
        return $this->provider->httpStatus;
    }

    public function errorCode()
    {
        return $this->provider->errorCode;
    }

    public function startCommunication($layout)
    {
        $this->provider->login($layout);
        $this->provider->keepAuth = true;
    }

    public function endCommunication()
    {
        $this->provider->keepAuth = false;
        $this->provider->logout();
    }
}

class FileMakerLayout
{
    private $restAPI = NULL;
    private $layout = NULL;

    public function __construct($restAPI, $layout)
    {
        $this->restAPI = $restAPI;
        $this->layout = $layout;
    }

    public function startCommunication()
    {
        $this->restAPI->login($this->layout);
        $this->restAPI->keepAuth = true;
    }

    public function endCommunication()
    {
        $this->restAPI->keepAuth = false;
        $this->restAPI->logout();
    }

    /*
     * $portal should be:
     * array('portal1', portal2') or
     * array('portal1' => array('offset'=>1,'range'=>5), 'portal2' => null)
     */
    private function buildPortalParameters($param)
    {
        $request = array();
        if (array_values($param) === $param) {
            $request["portal"] = $param;
        } else {
            $request["portal"] = array_keys($param);
            foreach ($param as $portalName => $options) {
                if (!is_null($options) && $options['range']) {
                    $request["range.{$portalName}"] = $options['range'];
                }
                if (!is_null($options) && $options['offset']) {
                    $request["offset.{$portalName}"] = $options['offset'];
                }
            }
        }
        return $request;
    }

    public function query($condition = NULL, $sort = NULL, $offset = -1, $range = -1, $portal = null)
    {
        $this->restAPI->login($this->layout);
        $request = array();
        if (!is_null($sort)) {
            $request["sort"] = $sort;
        }
        if ($offset > -1) {
            $request["offset"] = $offset;
        }
        if ($range > -1) {
            $request["range"] = $range;
        }
        if (!is_null($portal)) {
            $request = array_merge($request, $this->buildPortalParameters($portal));
        }
        $fmrel = null;
        if (!is_null($condition)) {
            $request["query"] = $condition;
            $this->restAPI->callRestAPI("find", $this->layout, true, "POST", $request);
        } else {
            $this->restAPI->callRestAPI("record", $this->layout, true);
        }
        $result = $this->restAPI->responseBody;
        $this->restAPI->httpStatus = $this->restAPI->getCurlInfo("http_code");
        $this->restAPI->errorCode = property_exists($result, 'errorCode') ? $result->errorCode : -1;
        $this->restAPI->errorMessage = property_exists($result, 'errorMessage') ? $result->errorMessage : null;
        if ($result &&
            property_exists($result, 'data') &&
            property_exists($result, 'result') &&
            property_exists($result, 'errorCode')
        ) {
            $fmrel = new FMRelation($result->data, $result->result, $result->errorCode);
        }
        $this->restAPI->logout();
        return $fmrel;
    }

    public function getRecord($recordId, $portal = null)
    {
        $request = array();
        $this->restAPI->login($this->layout);
        if (!is_null($portal)) {
            $request = array_merge($request, $this->buildPortalParameters($portal));
        }
        $this->restAPI->callRestAPI("record", $this->layout, true, "GET", $request, $recordId);
        $result = $this->restAPI->responseBody;
        $this->restAPI->httpStatus = $this->restAPI->getCurlInfo("http_code");
        $fmrel = null;
        $this->restAPI->errorCode = property_exists($result, 'errorCode') ? $result->errorCode : -1;
        $this->restAPI->errorMessage = property_exists($result, 'errorMessage') ? $result->errorMessage : null;
        if ($result) {
            $fmrel = new FMRelation($result->data, "OK", $result->errorCode);
        }
        $this->restAPI->logout();
        return $fmrel;
    }

    public function create($data)
    {
        $this->restAPI->login($this->layout);
        $request = array("data" => $data);
        $this->restAPI->callRestAPI("record", $this->layout, true, "POST", $request);
        $result = $this->restAPI->responseBody;
        $this->restAPI->httpStatus = $this->restAPI->getCurlInfo("http_code");
        $this->restAPI->errorCode = property_exists($result, 'errorCode') ? $result->errorCode : -1;
        $this->restAPI->errorMessage = property_exists($result, 'errorMessage') ? $result->errorMessage : null;
        $this->restAPI->logout();
        return $result->recordId;
    }

    public function delete($recordId)
    {
        $this->restAPI->login($this->layout);
        $this->restAPI->callRestAPI("record", $this->layout, true, "DELETE", null, $recordId);
        $result = $this->restAPI->responseBody;
        $this->restAPI->httpStatus = $this->restAPI->getCurlInfo("http_code");
        $this->restAPI->errorCode = property_exists($result, 'errorCode') ? $result->errorCode : -1;
        $this->restAPI->errorMessage = property_exists($result, 'errorMessage') ? $result->errorMessage : null;
        $this->restAPI->logout();
    }

    public function update($recordId, $data, $modId = -1)
    {
        $this->restAPI->login($this->layout);
        $request = array("data" => $data);
        $this->restAPI->callRestAPI("record", $this->layout, true, "PUT", $request, $recordId);
        $result = $this->restAPI->responseBody;
        $this->restAPI->httpStatus = $this->restAPI->getCurlInfo("http_code");
        $this->restAPI->errorCode = property_exists($result, 'errorCode') ? $result->errorCode : -1;
        $this->restAPI->errorMessage = property_exists($result, 'errorMessage') ? $result->errorMessage : null;
        $this->restAPI->logout();
    }

    public function setGlobalField($fields)
    {
        $this->restAPI->login($this->layout);
        $request = array("globalFields" => $fields);
        $this->restAPI->callRestAPI("global", $this->layout, true, "PUT", $request);
        $result = $this->restAPI->responseBody;
        $this->restAPI->httpStatus = $this->restAPI->getCurlInfo("http_code");
        $this->restAPI->errorCode = property_exists($result, 'errorCode') ? $result->errorCode : -1;
        $this->restAPI->errorMessage = property_exists($result, 'errorMessage') ? $result->errorMessage : null;
        $this->restAPI->logout();
    }
}

class FMRelation implements Iterator
{
    private $data = null;
    private $result = null; // OK for output from API, RECORD, PORTAL, PORTALRECORD
    private $errorCode = null;
    private $pointer = 0;
    private $portalName = null;

    public function __construct($data, $result = "PORTAL", $errorCode = 0, $portalName = null)
    {
        $this->data = $data;
        $this->result = $result;
        $this->errorCode = $errorCode;
        $this->portalName = $portalName;
    }

    public function setPortalName($name)
    {
        $this->portalName = $name;
    }

    public function previos()
    {
        $this->pointer--;
    }

    public function next()
    {
        $this->pointer++;
    }

    public function last()
    {
        $this->pointer = count($this->data) - 1;
    }

    public function moveTo($position)
    {
        $this->pointer = $position - 1;
    }

    public function count()
    {
        return count($this->data);
    }

    public function __get($key)
    {
        return $this->field($key);
    }

    public function field($name, $toName = null)
    {
        $toName = is_null($toName) ? "" : "{$toName}::";
        $fieldName = "{$toName}$name";
        $value = null;
        if (isset($this->data)) {
            switch ($this->result) {
                case "OK":
                    if (isset($this->data[$this->pointer])) {
                        if (isset($this->data[$this->pointer]->fieldData) &&
                            isset($this->data[$this->pointer]->fieldData->$name)
                        ) {
                            $value = $this->data[$this->pointer]->fieldData->$name;
                        } else if (isset($this->data[$this->pointer]->portalData) &&
                            isset($this->data[$this->pointer]->portalData->$name)
                        ) {
                            $value = new FMRelation($this->data[$this->pointer]->portalData->$name, "PORTAL");
                        }
                    }
                    break;
                case "PORTAL":
                    if (isset($this->data[$this->pointer]) &&
                        isset($this->data[$this->pointer]->$fieldName)
                    ) {
                        $value = $this->data[$this->pointer]->$fieldName;
                    }
                    break;
                case "RECORD":
                    if (isset($this->data->fieldData) && isset($this->data->fieldData->$name)) {
                        $value = $this->data->fieldData->$name;
                    } else if (isset($this->data->portalData) && isset($this->data->portalData->$name)) {
                        $value = new FMRelation($this->data->portalData->$name, "PORTAL", 0, $name);
                    }
                    break;
                case "PORTALRECORD":
                    $convinedName = "{$this->portalName}::{$fieldName}";
                    if (isset($this->data->$fieldName)) {
                        $value = $this->data->$fieldName;
                    } else if (isset($this->data->$convinedName)) {
                        $value = $this->data->$convinedName;
                    }
                    break;
                default:
            }
        }
        return $value;
    }

    public function getRecordId()
    {
        $value = null;
        switch ($this->result) {
            case "OK":
                if (isset($this->data[$this->pointer])) {
                    if (isset($this->data[$this->pointer]->recordId)
                    ) {
                        $value = $this->data[$this->pointer]->recordId;
                    }
                }
                break;
            case "PORTAL":
                if (isset($this->data[$this->pointer]) &&
                    isset($this->data[$this->pointer]->recordId)
                ) {
                    $value = $this->data[$this->pointer]->recordId;
                }
                break;
            case "RECORD":
            case "PORTALRECORD":
                if (isset($this->data) && isset($this->data->recordId)) {
                    $value = $this->data->recordId;
                }
                break;
        }
        return $value;
    }

    public function getModId()
    {
        $value = null;
        switch ($this->result) {
            case "OK":
                if (isset($this->data[$this->pointer])) {
                    if (isset($this->data[$this->pointer]->modId)
                    ) {
                        $value = $this->data[$this->pointer]->modId;
                    }
                }
                break;
            case "PORTAL":
                if (isset($this->data[$this->pointer]) &&
                    isset($this->data[$this->pointer]->modId)
                ) {
                    $value = $this->data[$this->pointer]->modId;
                }
                break;
            case "RECORD":
            case "PORTALRECORD":
                if (isset($this->data) && isset($this->data->modId)) {
                    $value = $this->data->recordId;
                }
                break;
        }
        return $value;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public
    function current()
    {
        $value = null;
        if (isset($this->data) &&
            isset($this->data[$this->pointer])
        ) {
            $value = new FMRelation(
                $this->data[$this->pointer],
                ($this->result == "PORTAL") ? "PORTALRECORD" : "RECORD",
                $this->errorCode,
                $this->portalName);
        }
        return $value;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public
    function key()
    {
        return $this->pointer;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public
    function valid()
    {
        if (isset($this->data) &&
            isset($this->data[$this->pointer])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public
    function rewind()
    {
        $this->pointer = 0;
    }
}

class CommunicationProvider
{
    private $host = "127.0.0.1";
    private $user = "admin";
    private $password = "1234";
    private $solution;
    private $protocol = 'https';
    private $port = 443;

    private $accessToken = '';
    private $method;
    private $url;
    private $requestHeader;
    private $requestBody;
    private $curlErrorNumber;
    private $curlError;
    private $curlInfo;
    private $responseHeader;
    public $responseBody;
    public $httpStatus;
    public $errorCode;
    public $errorMessage;
    public $keepAuth = false;

    public $isDebug;
    public $isCertVaridating;

    public function __construct($solution, $user, $password, $host = NULL, $port = NULL, $protocol = NULL)
    {
        $this->solution = $solution;
        $this->user = $user;
        $this->password = $password;
        if (!is_null($host)) {
            $this->host = $host;
        }
        if (!is_null($port)) {
            $this->port = $port;
        }
        if (!is_null($protocol)) {
            $this->protocol = $protocol;
        }
    }

    public function getURL($action, $layout, $recordId = null)
    {
        return "{$this->protocol}://{$this->host}:{$this->port}" .
            "/fmi/rest/api/{$action}/{$this->solution}/{$layout}" .
            ($recordId ? "/{$recordId}" : "");
    }

    public function login($layout)
    {
        if ($this->keepAuth) {
            return;
        }
        $request = array(
            "user" => $this->user,
            "password" => $this->password,
            "layout" => $layout,
        );
        $this->callRestAPI("auth", "", false, "POST", $request);
        if ($this->responseBody->errorCode != 0) {
            echo "Authentication Error: {$this->responseBody->errorCode}";  // Exception
            $this->accessToken = NULL;
        } else {
            $this->accessToken = $this->responseBody->token;
        }
    }

    public function logout()
    {
        if ($this->keepAuth) {
            return;
        }
        $this->callRestAPI("auth", "", true, "DELETE");
        $this->accessToken = NULL;
    }

    public function callRestAPI($action, $layout, $isAddToken, $method = 'GET', $request = NULL, $recordId = null)
    {
        $methodLower = strtolower($method);
        $url = $this->getURL($action, $layout, $recordId);
        $header = array();
        if (!is_null($request) && $methodLower != 'get') {
            $header[] = "Content-Type: application/json";
        }
        if ($isAddToken) {
            $header[] = "FM-Data-token: {$this->accessToken}";
        }
        if ($methodLower == 'get' && !is_null($request)) {
            $url .= '?';
            foreach ($request as $key => $value) {
                $url .= $key . '=' . (is_array($value) ? json_encode($value) : $value);
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
        if ($methodLower == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
        } else
            if ($methodLower == 'put') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            } else if ($methodLower == 'delete') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            }
        if ($this->isCertVaridating) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if ($methodLower != 'get') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
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
        $this->responseBody = json_decode(substr($response, $this->curlInfo["header_size"]));

        if ($this->isDebug) {
            $this->debugOutput();
        }
    }

    public
    function getCurlInfo($key)
    {
        return $this->curlInfo[$key];
    }

    public
    function debugOutput($isReturnValue = false)
    {
        $str = "<div style='background-color: #DDDDDD'>URL: ";
        $str .= $this->method . ' ' . htmlspecialchars($this->url);
        $str .= "</div>Added Request Header:<br><pre>";
        $str .= htmlspecialchars(var_export($this->requestHeader, true));
        $str .= "<hr>Request Body:<br><pre>";
        $str .= htmlspecialchars(json_encode($this->requestBody, JSON_PRETTY_PRINT));
        $str .= "</pre><hr>Response Header:<br><pre>";
        $str .= htmlspecialchars($this->responseHeader);
        $str .= "</pre><hr>Response Body:<br><pre>";
        $str .= htmlspecialchars(json_encode($this->responseBody, JSON_PRETTY_PRINT));
        $str .= "</pre><hr>Info:<br><pre>";
        $str .= var_export($this->curlInfo, true);
        $str .= "</pre><hr>ErrorNumber: {$this->curlErrorNumber}";
        $str .= "</pre><hr>Error:<br>";
        $str .= htmlspecialchars($this->curlError);
        $str .= "<hr>";
        if ($isReturnValue) {
            return $str;
        } else {
            echo $str;
        }
    }
}
