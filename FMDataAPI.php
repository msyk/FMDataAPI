<?php

/**
 * Object-oriented class for the REST API in FileMaker Server 17/Cloud.
 *
 * @version 12.0
 * @author Masayuki Nii <nii@msyk.net>
 * @copyright 2017-2018 Masayuki Nii (FileMaker is registered trademarks of FileMaker, Inc. in the U.S. and other countries.)
 */

namespace INTERMediator\FileMakerServer\RESTAPI;
/**
 * Class FMDataAPI is the wrapper of The REST API in FileMaker Server 17/Cloud.
 *
 * @package INTER-Mediator\FileMakerServer\RESTAPI
 * @link https://github.com/msyk/FMDataAPI GitHub Repository
 * @property-read FileMakerLayout $<<layout_name>> FileMakerLayout object named as the property name.
 *    If the layout doesn't exist, no error arises here. Any errors might arise on methods of FileMakerLayout class.
 * @version 12
 * @author Masayuki Nii <nii@msyk.net>
 * @copyright 2017-2018 Masayuki Nii (FileMaker is registered trademarks of FileMaker, Inc. in the U.S. and other countries.)
 */
class FMDataAPI
{
    /* Document generating:
     * - Install PHP Documentor, and enter command 'php ../phpDocumentor.phar -f ./FMDataAPI.php -t ../INTER-Mediator_Documents/FMDataAPI'.
     */

    /**
     * Keeping the FileMakerLayout object for each layout
     * @ignore
     */
    private $layoutTable = [];
    /**
     * Keeping the CommunicationProvider object
     * @ignore
     */
    private $provider = NULL;

    /**
     * FMDataAPI constructor. If you want to activate OAuth authentication, $user and $pasword are set as
     * oAuthRequestId and oAuthIdentifier. Moreover call useOAuth method before accessing layouts.
     * @param String $solution The database file name which is just hosting.
     * @param String $user The fmrest privilege accessible user to the database.
     * If you are going to call useOAuth method, you have to specify the data for X-FM-Data-OAuth-Request-Id.
     * @param String $password The password of above user.
     * If you are going to call useOAuth method, you have to specify the data for X-FM-Data-OAuth-Identifier.
     * @param String $host FileMaker Server's host name or IP address. If omitted, 'localhost' is chosen.
     * The value "localserver" tries to connect directory 127.0.0.1, and you don't have to set $port and $protocol.
     * @param int $port FileMaker Server's port number. If omitted, 443 is chosen.
     * @param String $protocol FileMaker Server's protocol name. If omitted, 'https' is chosen.
     * @param array $fmDataSource Authentication information for external data sources.
     * Ex.  [{"database"=>"<databaseName>", "username"=>"<username>", "password"=>"<password>"].
     * If you use OAuth, "oAuthRequestId" and "oAuthIdentifier" keys have to be spedified.
     * @param boolean $isUnitTest It it's set to true, the communication provider just works locally.
     */
    public function __construct(
        $solution, $user, $password, $host = NULL, $port = NULL, $protocol = NULL, $fmDataSource = null, $isUnitTest = false)
    {
        if (! $isUnitTest) {
            $this->provider = new Supporting\CommunicationProvider($solution, $user, $password, $host, $port, $protocol, $fmDataSource);
        } else {
            $this->provider = new Supporting\TestProvider($solution, $user, $password, $host, $port, $protocol, $fmDataSource);
        }
    }

    /**
     * Can't set the value to the undefined name.
     * @ignore
     * @param String $key The property name
     * @return FileMakerLayout FileMakerLayout object
     */
    public function __set($key, $value)
    {
        throw new \Exception("The {$key} property is read-only, and can't set any value.");
    }

    /**
     * Handle the undefined name as the layout name.
     * @ignore
     * @param String $key The property name
     * @return FileMakerLayout FileMakerLayout object
     */
    public function __get($key)
    {
        return $this->layout($key);
    }

    /**
     * Refers the FileMakerLayout object as the proxy of the layout.
     * If the layout doesn't exist, no error arises here. Any errors might arise on methods of FileMakerLayout class.
     * @param String $layout_name Layout name.
     * @return FileMakerLayout object which is proxy of FileMaker's layout.
     */
    public function layout($layout_name)
    {
        if (!isset($this->layoutTable[$layout_name])) {
            $this->layoutTable[$layout_name] = new Supporting\FileMakerLayout($this->provider, $layout_name);
        }
        return $this->layoutTable[$layout_name];
    }

    /**
     * Set the debug mode or not. The debug mode isn't in default.
     * @param bool $value set the debug mode if the value is true.
     */
    public function setDebug($value)
    {
        $this->provider->isDebug = $value;
    }

    /**
     * On the authentication session, username and password are handled as OAuth parameters.
     */
    public function useOAuth()
    {
        $this->provider->useOAuth = true;
    }

    /**
     * FileMaker Data API's version is going to be set. If you don't call, the "vLatest" is specified.
     * As far as FileMaker 17 supports just "v1", no one has to call this method.
     * @param integer $vNum FileMaker Data API's version number.
     */
    public function setAPIVersion($vNum)
    {
        $this->provider->vNum = intval($vNum);
    }

    /**
     * Set to verify the server certificate. The default is to handle as self-signed certificate and doesn't verify.
     * @param bool $value Turn on to verify the certificate if the value is true.
     */
    public function setCertValidating($value)
    {
        $this->provider->isCertVaridating = $value;
    }

    /**
     * Set session token
     * @param string $value The session token.
     */
    public function setSessionToken($value)
    {
        $this->provider->accessToken = $value;
    }

    /**
     * The session token earned after authentication.
     * @return string The session token.
     */
    public function getSessionToken()
    {
        return $this->provider->accessToken;
    }

    /**
     * The HTTP status code of the latest response from the REST API.
     * @return int The HTTP status code.
     */
    public function httpStatus()
    {
        return $this->provider->httpStatus;
    }

    /**
     * The error code of the latest response from the REST API.
     * The code 0 means no error, and -1 means error information wasn't return.
     * This error code is associated with FileMaker's error code.
     * @return int The error code.
     */
    public function errorCode()
    {
        return $this->provider->errorCode;
    }

    /**
     * The error message of the latest response from the REST API.
     * This error message is associated with FileMaker's error code.
     * @return string The error messege.
     */
    public function errorMessage()
    {
        return $this->provider->errorMessage;
    }

    /**
     * Set to prevent to throw an exception in case of error.
     * The default is true and an exception is going to throw in error.
     * @param bool $value Turn off to throw an exception in case of error if the value is false.
     */
    public function setThrowException($value)
    {
        $this->provider->throwExceptionInError = $value;
    }

    /**
     * Start a transaction which is a serial calling of any database operations,
     * and login with the layout in parameter.
     */
    public function startCommunication()
    {
        $this->provider->login();
        $this->provider->keepAuth = true;
    }

    /**
     * Finish a transaction which is a serial calling of any database operations, and logout.
     */
    public function endCommunication()
    {
        $this->provider->keepAuth = false;
        $this->provider->logout();
    }

    /**
     * Set the value to the global field.
     * @param array $fields Associated array contains the global field names (Field names must be Fully Qualified) and its values.
     * Keys are global field names and values is these values.
     * @throws Exception In case of any error, an exception arises.
     */
    public function setGlobalField($fields)
    {
        try {
            $this->provider->login();
            $headers = ["Content-Type" => "application/json"];
            $params = ["globals" => null];
            $request = ["globalFields" => $fields];
            try {
                $this->provider->callRestAPI($params, true, "PATCH", $request, $headers);
            } catch (\Exception $e) {
                throw $e;
            }
            $this->provider->storeToProperties();
            $this->provider->logout();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

namespace INTERMediator\FileMakerServer\RESTAPI\Supporting;
/**
 * Class FileMakerLayout is the proxy of layout in FileMaker database.
 * The object of this class is going to be generated by the FMDataAPI class,
 * and you shouldn't call the constructor of this class.
 *
 * @package INTER-Mediator\FileMakerServer\RESTAPI
 * @link https://github.com/msyk/FMDataAPI GitHub Repository
 * @version 12
 * @author Masayuki Nii <nii@msyk.net>
 * @copyright 2017-2018 Masayuki Nii (FileMaker is registered trademarks of FileMaker, Inc. in the U.S. and other countries.)
 */
class FileMakerLayout
{
    /**
     * @var CommunicationProvider The instance of the communication class.
     * @ignore
     */
    private $restAPI = NULL;
    /**
     * @var null
     * @ignore
     */
    private $layout = NULL;

    /**
     * FileMakerLayout constructor.
     * @param $restAPI
     * @param $layout
     * @ignore
     */
    public function __construct($restAPI, $layout)
    {
        $this->restAPI = $restAPI;
        $this->layout = $layout;
    }

    /**
     * Start a transaction which is a serial calling of any database operations,
     * and login with the target layout.
     */
    public function startCommunication()
    {
        $this->restAPI->login();
        $this->restAPI->keepAuth = true;
    }

    /**
     * Finish a transaction which is a serial calling of any database operations, and logout.
     */
    public function endCommunication()
    {
        $this->restAPI->keepAuth = false;
        $this->restAPI->logout();
    }

    /**
     * @param $param
     * @return array
     * @ignore
     */
    private function buildPortalParameters($param, $shortKey = false)
    {
        $key = $shortKey ? "portal" : "portalData";
        $request = [];
        if (array_values($param) === $param) {
            $request[$key] = $param;
        } else {
            $request[$key] = array_keys($param);
            foreach ($param as $portalName => $options) {
                if (!is_null($options) && $options['limit']) {
                    $request["_limit.{$portalName}"] = $options['limit'];
                }
                if (!is_null($options) && $options['offset']) {
                    $request["_offset.{$portalName}"] = $options['offset'];
                }
            }
        }
        return $request;
    }

    /**
     * @param $param
     * @return array
     * @ignore
     */
    private function buildScriptParameters($param)
    {
        $request = [];
        $scriptKeys = ["script", "script.param", "script.prerequest", "script.prerequest.param",
            "script.presort", "script.presort.param", "layout.response"];
        foreach ($scriptKeys as $key) {
            if (isset($param[$key])) {
                $request[$key] = $param[$key];
            }
        }
        if (count($request) === 0) {
            switch (count($request)) {
                case 1:
                    $request["script"] = $param[0];
                    break;
                case 2:
                    $request["script"] = $param[0];
                    $request["layout.response"] = $param[1];
                    break;
                case 3:
                    $request["script"] = $param[0];
                    $request["script.param"] = $param[1];
                    $request["layout.response"] = $param[2];
                    break;
                case 4:
                    $request["script.prerequest"] = $param[0];
                    $request["script.presort"] = $param[1];
                    $request["script"] = $param[2];
                    $request["layout.response"] = $param[3];
                    break;
            }
        }
        return $request;
    }

    /**
     * Query to the FileMaker Database and returns the result as FileMakerRelation object.
     * @param array $condition The array of associated array which has a field name and "omit" keys as like:
     * array(array("FamilyName"=>"Nii*", "Country"=>"Japan")).
     * In this example of apply the AND operation for two fields,
     * and "FamilyName" and "Country" are field name. The value can contain the operator:
     * =, ==, !, <, ≤ or <=, >, ≥ or >=, ..., //, ?, @, #, *, \, "", ~.
     * If you want to apply the OR operation, describe array of array as like:
     * array(array("FamilyName"=>"Nii*"), array("Country"=>"Japan")).
     * If you want to omit records match with condition set the "omit" element as like:
     * array("FamilyName"=>"Nii*", "omit"=>"true").
     * If you want to query all records in the layout, set the first parameter to null.
     * @param array $sort The array of associated array which has "fieldName" and "sortOrder" keys as like:
     * array(array("fieldName"=>"FamilyName", "sortOrder"=>"ascend"), array("fieldName"=>"GivenName", "sortOrder"=>"ascend")).
     * The value of sortOrder key can be 'ascend', 'descend' or value list name.
     * @param int $offset The start number of the record set, and the first record is 0.
     * @param int $range The number of records contains in the result record set.
     * @param array $portal The array of the portal's object names. The query result is going to contain portals
     * specified in this parameter. If you want to include all portals, set it null or omit it.
     * Simple case is array('portal1', portal2'), and just includes two portals named 'portal1' and 'portal2'
     * in the query result. If you set the range of records to a portal, you have to build associated array as like:
     * array('portal1' => array('offset'=>1,'range'=>5), 'portal2' => null). The record 1 to 5 of portal1 include
     * the query result, and also all records in portal2 do.
     * @param array $script scripts that should execute right timings.
     * The most understandable description is an associated array with API's keywords "script", "script.param",
     * "script.prerequest", "script.prerequest.param", "script.presort", "script.presort.param", "layout.response."
     * These keywords have to be a key, and the value is script name or script parameter,
     * ex. {"script"=>"StartingOver", "script.param"=>"344|21|abcd"}.
     * If $script is array with one element, it's handled as the value of "script."
     * If $script is array with two elements, these are handled as values of "script" and "layout.response."
     * If it it's three elements, these are  "script", "script.param" and "layout.response."
     * If it it's four elements, these are  "script.prerequest", "script.presort", "script" and "layout.response."
     * @return FileMakerRelation|null Query result.
     * @throws Exception In case of any error, an exception arises.
     */
    public function query($condition = NULL, $sort = NULL, $offset = -1, $range = -1, $portal = null, $script = null)
    {
        try {
            $this->restAPI->login();
            $headers = ["Content-Type" => "application/json"];
            $request = [];
            if (!is_null($sort)) {
                $request["sort"] = $sort;
            }
            if ($offset > -1) {
                $request["offset"] = (string)$offset;
            }
            if ($range > -1) {
                $request["limit"] = (string)$range;
            }
            if (!is_null($portal)) {
                $request = array_merge($request, $this->buildPortalParameters($portal, true));
            }
            if (!is_null($script)) {
                $request = array_merge($request, $this->buildScriptParameters($script));
            }
            if (!is_null($condition)) {
                $request["query"] = $condition;
                $params = ["layouts" => $this->layout, "_find" => null];
                $this->restAPI->callRestAPI($params, true, "POST", $request, $headers);
            } else {
                $params = ["layouts" => $this->layout, "records" => null];
                $this->restAPI->callRestAPI($params, true, "GET", $request, $headers);
            }
            $this->restAPI->storeToProperties();
            $result = $this->restAPI->responseBody;
            $fmrel = null;
            if ($result && $result->response &&
                property_exists($result->response, 'data') &&
                property_exists($result, 'messages')
            ) {
                $fmrel = new FileMakerRelation($result->response->data, "OK",
                    $result->messages[0]->code, null, $this->restAPI);
            }
            $this->restAPI->logout();
            return $fmrel;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Query to the FileMaker Database with recordId special field and returns the result as FileMakerRelation object.
     * @param int $recordId The recordId.
     * @param array $portal See the query() method's same parameter.
     * @param array $script scripts that should execute right timings. See FileMakerRelation::query().
     * @return FileMakerRelation|null Query result.
     * @throws Exception In case of any error, an exception arises.
     */
    public function getRecord($recordId, $portal = null, $script = null)
    {
        try {
            $request = [];
            $this->restAPI->login();
            if (!is_null($portal)) {
                $request = array_merge($request, $this->buildPortalParameters($portal, true));
            }
            if (!is_null($script)) {
                $request = array_merge($request, $this->buildScriptParameters($script));
            }
            $headers = ["Content-Type" => "application/json"];
            $params = ["layouts" => $this->layout, "records" => $recordId];
            $this->restAPI->callRestAPI($params, true, "GET", $request, $headers);
            $this->restAPI->storeToProperties();
            $result = $this->restAPI->responseBody;
            $fmrel = null;
            if ($result) {
                $fmrel = new FileMakerRelation($result->response->data, "OK",
                    $result->messages[0]->code, null, $this->restAPI);
            }
            $this->restAPI->logout();
            return $fmrel;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create a record on the target layout of the FileMaker database.
     * @param array $data Associated array contains the initial values.
     * Keys are field names and values is these initial values.
     * @param array $portal Associated array contains the modifying values in portal.
     * Ex.: {"<PortalName>"=>{"<FieldName>"=>"<Value>"...}}. FieldName has to "<TOCName>::<FieldName>".
     * @param array $script scripts that should execute right timings. See FileMakerRelation::query().
     * @return integer The recordId of created record. If the returned value is an integer larger than 0,
     * it shows one record was created.
     * @throws Exception In case of any error, an exception arises.
     */
    public function create($data = null, $portal = null, $script = null)
    {
        try {
            $this->restAPI->login();
            $headers = ["Content-Type" => "application/json"];
            $params = ["layouts" => $this->layout, "records" => null];
            $request = ["fieldData" => is_null($data) ? [] : $data];
            if (!is_null($portal)) {
                $request = array_merge($request, ["portalData" => $portal]);
            }
            if (!is_null($script)) {
                $request = array_merge($request, $this->buildScriptParameters($script));
            }
            $this->restAPI->callRestAPI($params, true, "POST", $request, $headers);
            $result = $this->restAPI->responseBody;
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
            return $result->response->recordId;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete on record.
     * @param int $recordId The valid recordId value to delete.
     * @param array $script scripts that should execute right timings. See FileMakerRelation::query().
     * @throws Exception In case of any error, an exception arises.
     */
    public function delete($recordId, $script = null)
    {
        try {
            $this->restAPI->login();
            $request = [];
            $headers = NULL;
            $params = ['layouts' => $this->layout, 'records' => $recordId];
            if (!is_null($script)) {
                $request = $this->buildScriptParameters($script);
            }
            $this->restAPI->callRestAPI($params, true, 'DELETE', $request, $headers);
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update fields in one record.
     * @param int $recordId The valid recordId value to update.
     * @param array $data Associated array contains the modifying values.
     * Keys are field names and values is these initial values.
     * @param int $modId The modId to allow to update. This parameter is for detect to modifying other users.
     * If you omit this parameter, update operation does not care the value of modId special field.
     * @param array $portal Associated array contains the modifying values in portal.
     * Ex.: {"<PortalName>"=>{"<FieldName>"=>"<Value>", "recordId"=>"12"}}. FieldName has to "<TOCName>::<FieldName>".
     * The recordId key specifiy the record to edit in portal.
     * @param array $script scripts that should execute right timings. See FileMakerRelation::query().
     * @throws Exception In case of any error, an exception arises.
     */
    public function update($recordId, $data, $modId = -1, $portal = null, $script = null)
    {
        try {
            $this->restAPI->login();
            $headers = ["Content-Type" => "application/json"];
            $params = ["layouts" => $this->layout, "records" => $recordId];
            $request = [];
            if (!is_null($data)) {
                $request = array_merge($request, ["fieldData" => $data]);
            }
            if (!is_null($portal)) {
                $request = array_merge($request, ["portalData" => $portal]);
            }
            if (!is_null($script)) {
                $request = array_merge($request, $this->buildScriptParameters($script));
            }
            if ($modId > -1) {
                $request = array_merge($request, ["modId" => (string)$modId]);
            }
            try {
                $this->restAPI->callRestAPI($params, true, "PATCH", $request, $headers);
            } catch (\Exception $e) {
                throw $e;
            }
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Set the value to the global field.
     * @param array $fields Associated array contains the global field names and its values.
     * Keys are global field names and values is these values.
     * @throws Exception In case of any error, an exception arises.
     */
    public function setGlobalField($fields)
    {
        foreach ($fields as $name => $value) {
            if ((function_exists('mb_strpos') && mb_strpos($name, '::') === FALSE) || strpos($name, '::') === FALSE) {
                unset($fields[$name]);
                $fields[$this->layout . '::' . $name] = $value;
            }
        }
        try {
            $this->restAPI->login();
            $headers = ["Content-Type" => "application/json"];
            $params = ["globals" => null];
            $request = ["globalFields" => $fields];
            try {
                $this->restAPI->callRestAPI($params, true, "PATCH", $request, $headers);
            } catch (\Exception $e) {
                throw $e;
            }
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Upload the file into container filed.
     * @param string $filePath The file path to upload.
     * @param integer $recordId The Record Id of the record.
     * @param string $containerFieldName The field name of container field.
     * @param integer $containerFieldRepetition In case of repetiton field, this has to be the number from 1.
     * If omitted this, the number "1" is going to be specified.
     * @param string $fileName Another file name for uploading file. If omitted, origina file name is choosen.
     * @throws Exception In case of any error, an exception arises.
     */
    public function uploadFile($filePath, $recordId, $containerFieldName, $containerFieldRepetition = null, $fileName = null)
    {
        try {
            if (!file_exists($filePath)) {
                throw new \Exception("File doesn't exsist: {$filePath}.");
            }
            $CRLF = chr(13) . chr(10);
            $DQ = '"';
            $boundary = "FMDataAPI_UploadFile-" . uniqid();
            $fileName = is_null($fileName) ? basename($filePath) : $fileName;
            $this->restAPI->login();
            $headers = ["Content-Type" => "multipart/form-data; boundary={$boundary}"];
            $repNum = is_null($containerFieldRepetition) ? 1 : intval($containerFieldRepetition);
            $params = [
                "layouts" => $this->layout,
                "records" => $recordId,
                "containers" => "{$containerFieldName}/{$repNum}",
            ];
            $request = "--{$boundary}{$CRLF}";
            $request .= "Content-Disposition: name={$DQ}upload{$DQ} filename={$DQ}{$fileName}{$DQ}{$CRLF}";
            $request .= $CRLF;
            $request .= file_get_contents($filePath);
            $request .= "{$CRLF}{$CRLF}--{$boundary}--{$CRLF}";
            try {
                $this->restAPI->callRestAPI($params, true, "POST", $request, $headers);
            } catch (\Exception $e) {
                throw $e;
            }
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the script error code.
     * @return integer The value of the error code.
     * If any script wasn't called, returns null.
     */
    public function getScriptError()
    {
        return $this->restAPI->scriptError;
    }

    /**
     * Get the return value from the script.
     * @return string  The return value from the script.
     * If any script wasn't called, returns null.
     */
    public function getScriptResult()
    {
        return $this->restAPI->scriptResult;
    }

    /**
     * Get the prerequest script error code.
     * @return integer The value of the error code.
     * If any script wasn't called, returns null.
     */
    public function getScriptErrorPrerequest()
    {
        return $this->restAPI->scriptErrorPrerequest;
    }

    /**
     * Get the return value from the prerequest script.
     * @return string  The return value from the prerequest script.
     * If any script wasn't called, returns null.
     */
    public function getScriptResultPrerequest()
    {
        return $this->restAPI->scriptResultPrerequest;
    }

    /**
     * Get the presort script error code.
     * @return integer The value of the error code.
     * If any script wasn't called, returns null.
     */
    public function getScriptErrorPresort()
    {
        return $this->restAPI->scriptErrorPresort;
    }

    /**
     * Get the return value from the presort script.
     * @return string  The return value from the presort script.
     * If any script wasn't called, returns null.
     */
    public function getScriptResultPresort()
    {
        return $this->restAPI->scriptResultPresort;
    }
}

/**
 * Class FileMakerRelation is the record set of queried data.
 * The object of this class is going to be generated by the FileMakerLayout class,
 * and you shouldn't call the constructor of this class.
 *
 * @package INTER-Mediator\FileMakerServer\RESTAPI
 * @link https://github.com/msyk/FMDataAPI GitHub Repository
 * @property string $<<field_name>> The field value named as the property name.
 * @property FileMakerRelation $<<portal_name>> FileMakerRelation object associated with the property name.
 *    The table occurrence name of the portal can be the 'portal_name,' and also the object name of the portal.
 * @version 12
 * @author Masayuki Nii <nii@msyk.net>
 * @copyright 2017-2018 Masayuki Nii (FileMaker is registered trademarks of FileMaker, Inc. in the U.S. and other countries.)
 */
class FileMakerRelation implements \Iterator
{
    /**
     * @var null
     * @ignore
     */
    private $data = null;
    /**
     * @var null|string
     * @ignore
     */
    private $result = null; // OK for output from API, RECORD, PORTAL, PORTALRECORD
    /**
     * @var int|null
     * @ignore
     */
    private $errorCode = null;
    /**
     * @var int
     * @ignore
     */
    private $pointer = 0;
    /**
     * @var null
     * @ignore
     */
    private $portalName = null;
    /**
     * @var CommunicationProvider The instance of the communication class.
     * @ignore
     */
    private $restAPI = NULL;

    /**
     * FileMakerRelation constructor.
     * @param $data
     * @param string $result
     * @param int $errorCode
     * @param null $portalName
     * @ignore
     */
    public function __construct($data, $result = "PORTAL", $errorCode = 0, $portalName = null, $provider = null)
    {
        $this->data = $data;
        $this->result = $result;
        $this->errorCode = $errorCode;
        $this->portalName = $portalName;
        $this->restAPI = $provider;
    }

    /**
     * If the portal name is different with the name used as the portal referencing name, this method can set it.
     * @param string $name The portal name.
     */
    public function setPortalName($name)
    {
        $this->portalName = $name;
    }

    /**
     * The record pointer goes back to previous record. This does not care the range of pointer value.
     */
    public function previos()
    {
        $this->pointer--;
    }

    /**
     * The record pointer goes forward to previous record. This does not care the range of pointer value.
     */
    public function next()
    {
        $this->pointer++;
    }

    /**
     * The record pointer goes to first record.
     */
    public function last()
    {
        $this->pointer = count($this->data) - 1;
    }

    /**
     * The record pointer goes to the specified record.
     * @param int $position The position of the record. The first record is 0.
     */
    public function moveTo($position)
    {
        $this->pointer = $position - 1;
    }

    /**
     * Count the number of records.
     * @return int The number of records.
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @param $key
     * @return FileMakerRelation|null
     * @ignore
     */
    public function __get($key)
    {
        return $this->field($key);
    }

    /**
     * Return the array of field names.
     * @return array List of field names
     */
    public function getFieldNames()
    {
        $list = [];
        if (isset($this->data)) {
            switch ($this->result) {
                case 'OK':
                    if (isset($this->data[$this->pointer])
                        && isset($this->data[$this->pointer]->fieldData)
                    ) {
                        foreach ($this->data[$this->pointer]->fieldData as $key => $val) {
                            array_push($list, $key);
                        }
                    }
                    break;
                case 'PORTAL':
                    if (isset($this->data[$this->pointer])) {
                        foreach ($this->data[$this->pointer] as $key => $val) {
                            array_push($list, $key);
                        }
                    }
                    break;
                default:
            }
        }
        return $list;
    }

    /**
     * Return the array of portal names.
     * @return array List of portal names
     */
    public function getPortalNames()
    {
        $list = [];
        if (isset($this->data)
            && isset($this->data[$this->pointer])
            && isset($this->data[$this->pointer]->portalData)
        ) {
            foreach ($this->data[$this->pointer]->portalData as $key => $val) {
                array_push($list, $key);
            }
        }
        return $list;
    }

    /**
     * The field value of the first parameter. Or the FileMakerRelation object associated with the the first paramenter.
     * @param string $name The field or portal name.
     * The table occurrence name of the portal can be the portal name, and also the object name of the portal.
     * @param string $toName The table occurrence name of the portal as the prefix of the field name.
     * @return string|FileMakerRelation The field value as string, or the FileMakerRelation object of the portal.
     * @throws Exception The field specified in parameters doesn't exist.
     */
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
                            $value = new FileMakerRelation($this->data[$this->pointer]->portalData->$name,
                                "PORTAL", 0, null, $this->restAPI);
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
                        $value = new FileMakerRelation($this->data->portalData->$name,
                            "PORTAL", 0, $name, $this->restAPI);
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
        if (is_null($value)) {
            throw new \Exception("Field {$fieldName} doesn't exist.");
        }
        return $value;
    }

    /**
     * Return the value of special field recordId in the current pointing record.
     * @return int The value of special field recordId.
     */
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

    /**
     * Return the value of special field modId in the current pointing record.
     * @return int The value of special field modId.
     */
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
     * Return the base64 encoded data in container field with streaming interface. The access with
     * streaming url depends on the setCertValidating(_) call, and it can work on self-signed certificate as a default.
     * Thanks to 'base64bits' as https://github.com/msyk/FMDataAPI/issues/18.
     * @param string $name The container field name.
     * The table occurrence name of the portal can be the portal name, and also the object name of the portal.
     * @param string $toName The table occurrence name of the portal as the prefix of the field name.
     * @return string The base64 encoded data in container field.
     */
    public function getContainerData($name, $toName = null)
    {
        $fieldValue = $this->field($name, $toName);
        if (strpos($fieldValue, "https://") !== 0) {
            throw new \Exception("The field '{$name}' is not field name or container field.");
        }
        try {
            return $this->restAPI->accessToContainer($fieldValue);
        } catch (\Exception $e) {
            throw $e;
        }
        return null;
    }

    /**
     * Return the current element. This method is implemented for Iterator interface.
     * @return FileMakerRelation|null The record set of the current pointing record.
     */
    public function current()
    {
        $value = null;
        if (isset($this->data) &&
            isset($this->data[$this->pointer])
        ) {
            $value = new FileMakerRelation(
                $this->data[$this->pointer],
                ($this->result == "PORTAL") ? "PORTALRECORD" : "RECORD",
                $this->errorCode, $this->portalName, $this->restAPI);
        }
        return $value;
    }

    /**
     * Return the key of the current element. This method is implemented for Iterator interface.
     * @return integer The current number as the record pointer.
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     * Checks if current position is valid. This method is implemented for Iterator interface.
     * @return bool Returns true on existing the record or false on not existing.
     */
    public function valid()
    {
        if (isset($this->data) &&
            isset($this->data[$this->pointer])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Rewind the Iterator to the first element. This method is implemented for Iterator interface.
     */
    public function rewind()
    {
        $this->pointer = 0;
    }
}

/**
 * Class CommunicationProvider is for internal use to communicate with FileMaker Server.
 *
 * @package INTER-Mediator\FileMakerServer\RESTAPI
 * @link https://github.com/msyk/FMDataAPI GitHub Repository
 * @version 12
 * @author Masayuki Nii <nii@msyk.net>
 * @copyright 2017-2018 Masayuki Nii (FileMaker is registered trademarks of FileMaker, Inc. in the U.S. and other countries.)
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
     * @var
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
    public $accessToken = NULL;
    /**
     * @var
     * @ignore
     */
    private $method;
    /**
     * @var
     * @ignore
     */
    private $url;
    /**
     * @var
     * @ignore
     */
    private $requestHeader;
    /**
     * @var
     * @ignore
     */
    private $requestBody;
    /**
     * @var
     * @ignore
     */
    private $curlErrorNumber;
    /**
     * @var
     * @ignore
     */
    private $curlError;
    /**
     * @var
     * @ignore
     */
    private $curlInfo;
    /**
     * @var
     * @ignore
     */
    private $responseHeader;
    /**
     * @var
     * @ignore
     */
    private $isLocalServer = false;
    /**
     * @var
     * @ignore
     */
    public $responseBody;
    /**
     * @var
     * @ignore
     */
    public $httpStatus;
    /**
     * @var
     * @ignore
     */
    public $errorCode;
    /**
     * @var
     * @ignore
     */
    public $errorMessage;
    /**
     * @var bool
     * @ignore
     */
    public $keepAuth = false;

    /**
     * @var
     * @ignore
     */
    public $isDebug;
    /**
     * @var
     * @ignore
     */
    public $isCertVaridating;
    /**
     * @var
     * @ignore
     */
    public $throwExceptionInError = true;
    /**
     * @var
     * @ignore
     */
    public $useOAuth = false;
    /**
     * @var
     * @ignore
     */
    private $fmDataSource;
    /**
     * @var
     * @ignore
     */
    public $scriptError;
    /**
     * @var
     * @ignore
     */
    public $scriptResult;
    /**
     * @var
     * @ignore
     */
    public $scriptErrorPrerequest;
    /**
     * @var
     * @ignore
     */
    public $scriptResultPrerequest;
    /**
     * @var
     * @ignore
     */
    public $scriptErrorPresort;
    /**
     * @var
     * @ignore
     */
    public $scriptResultPresort;


    /**
     * CommunicationProvider constructor.
     * @param $solution
     * @param $user
     * @param $password
     * @param null $host
     * @param null $port
     * @param null $protocol
     * @ignore
     */
    public function __construct($solution, $user, $password, $host = NULL, $port = NULL, $protocol = NULL, $fmDataSource = NULL)
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
    }

    /**
     * @param $action
     * @param $layout
     * @param null $recordId
     * @return string
     * @ignore
     */
    public function getURL($params)
    {
        $vStr = $this->vNum < 1 ? 'Latest' : strval($this->vNum);
        $url = "{$this->protocol}://{$this->host}:{$this->port}/fmi/data/v{$vStr}/databases/{$this->solution}";
        foreach ($params as $key => $value) {
            $url .= "/{$key}" . (is_null($value) ? "" : "/{$value}");
        }
        return $url;
    }

    /**
     * @throws Exception In case of any error, an exception arises.
     * @ignore
     */
    public function login()
    {
        if ($this->keepAuth) {
            return;
        }
        if ($this->useOAuth) {
            $headers = [
                "Content-Type" => "application/json",
                "X-FM-Data-OAuth-Request-Id" => "{$this->user}",
                "X-FM-Data-OAuth-Identifier" => "{$this->password}",
            ];
        } else {
            $value = "Basic " . base64_encode("{$this->user}:{$this->password}");
            $headers = ["Content-Type" => "application/json", "Authorization" => $value,];
        }
        $params = ["sessions" => null];
        $request = [];
        if (!is_null($this->fmDataSource)) {
            $request["fmDataSource"] = $this->fmDataSource;
        }
        if (is_null($this->accessToken)) {
            try {
                $this->callRestAPI($params, false, "POST", $request, $headers);
            } catch (\Exception $e) {
                $this->accessToken = NULL;
                throw $e;
            }
            if (intval($this->responseBody->messages[0]->code) != 0) {
                $this->accessToken = NULL;
            } else {
                $this->accessToken = $this->responseBody->response->token;
            }
        }
    }

    /**
     *
     * @throws Exception In case of any error, an exception arises.
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
        } catch (\Exception $e) {
            throw $e;
        }
        $this->accessToken = NULL;
    }

    /**
     * @param $params
     * @param $layout
     * @param $isAddToken
     * @param string $method
     * @param null $request
     * @param null $recordId
     * @throws Exception In case of any error, an exception arises.
     * @ignore
     */
    public function callRestAPI($params, $isAddToken, $method = 'GET', $request = NULL, $addHeader = null)
    {
        $methodLower = strtolower($method);
        $url = $this->getURL($params);
        $header = [];
        if ($this->isLocalServer) {
            $header[] = 'X-Forwarded-For: 127.0.0.1';
            $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL);
            if ($host === NULL || $host === FALSE) {
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

        $jsonEncoding = true;
        if (is_string($request)) {
            $jsonEncoding = false;
        } else if (in_array($methodLower, array('get', 'delete')) && !is_null($request)) {
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
                    $url .= $key . '=' . (is_array($value) ? json_encode($value) : $value);
                }
            }
        } else if ($methodLower !== 'get' && !is_null($request)) {
            // cast a number
            if (isset($request['fieldData'])) {
                foreach ($request['fieldData'] as $fieldName => $fieldValue) {
                    if (is_numeric($fieldValue)) {
                        $request['fieldData'][$fieldName] = (string)$fieldValue;
                    }
                }
            }
            if (isset($request['query'])) {
                foreach ($request['query'] as $key => $array) {
                    foreach ($array as $fieldName => $fieldValue) {
                        if (!is_array($fieldValue)) {
                            $request['query'][$key][$fieldName] = (string)$fieldValue;
                        }
                    }
                }
            }

            if (isset($request['sort'])) {
                $sort = [];
                foreach ($request['sort'] as $sortKey => $sortCondition) {
                    if (isset($sortCondition[0])) {
                        $sortOrder = 'ascend';
                        if (isset($sortCondition[1])) {
                            $sortOrder = $this->adjustSortDirection($sortCondition[1]);
                        }
                        $sort[] = ['fieldName' => $sortCondition[0], 'sortOrder' => $sortOrder];
                    }
                }
                $request['sort'] = $sort;
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
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            } else if ($methodLower == 'patch') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            } else if ($methodLower == 'delete') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
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
            $errorCode = property_exists($this->responseBody->messages[0], 'code') ?
                intval($this->responseBody->messages[0]->code) : -1;
            $errorMessage = property_exists($this->responseBody->messages[0], 'message') ?
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
                    throw new \Exception($description);
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
        $cookieFile = tempnam(sys_get_temp_dir(), "CURLCOOKIE");//create a cookie file

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
        $result = $this->responseBody->messages[0];
        $this->httpStatus = $this->getCurlInfo("http_code");
        $this->errorCode = property_exists($result, 'code') ? $result->code : -1;
        $result = $this->responseBody->response;
        $this->scriptError = property_exists($result, 'scriptError') ?
            $result->scriptError : null;
        $this->scriptResult = property_exists($result, 'scriptResult') ?
            $result->scriptResult : null;
        $this->scriptErrorPrerequest = property_exists($result, 'scriptError.prerequest') ?
            $result->{'scriptError.prerequest'} : null;
        $this->scriptResultPrerequest = property_exists($result, 'scriptResult.prerequest') ?
            $result->{'scriptResult.prerequest'} : null;
        $this->scriptErrorPresort = property_exists($result, "scriptError.presort") ?
            $result->{"scriptError.presort"} : null;
        $this->scriptResultPresort = property_exists($result, "scriptResult.presort") ?
            $result->{"scriptResult.presort"} : null;
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
        $str .= htmlspecialchars($this->curlError);
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
}
