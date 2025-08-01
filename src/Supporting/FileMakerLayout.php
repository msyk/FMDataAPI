<?php

namespace INTERMediator\FileMakerServer\RESTAPI\Supporting;

use Exception;

/**
 * Class FileMakerLayout is the proxy of layout in FileMaker database.
 * The object of this class is going to be generated by the FMDataAPI class,
 * and you shouldn't call the constructor of this class.
 *
 * @package INTER-Mediator\FileMakerServer\RESTAPI
 * @link https://github.com/msyk/FMDataAPI GitHub Repository
 * @version 34
 * @author Masayuki Nii <nii@msyk.net>
 * @copyright 2017-2024 Masayuki Nii (Claris FileMaker is registered trademarks of Claris International Inc. in the U.S. and other countries.)
 */
class FileMakerLayout
{
    /**
     * @var CommunicationProvider|null The instance of the communication class.
     * @ignore
     */
    private CommunicationProvider|null $restAPI;
    /**
     * @var null|string
     * @ignore
     */
    private string|null $layout;

    /**
     * FileMakerLayout constructor.
     * @param CommunicationProvider|null $restAPI
     * @param string $layout
     * @ignore
     */
    public function __construct(CommunicationProvider|null $restAPI,
                                string                     $layout)
    {
        $this->restAPI = $restAPI;
        $this->layout = $layout;
    }

    /**
     * Start a transaction which is a serial calling of any database operations,
     * and login with the target layout.
     * @throws Exception
     */
    public function startCommunication(): void
    {
        if ($this->restAPI->login()) {
            $this->restAPI->keepAuth = true;
        }
    }

    /**
     * Finish a transaction which is a serial calling of any database operations, and logout.
     * @throws Exception
     */
    public function endCommunication(): void
    {
        $this->restAPI->keepAuth = false;
        $this->restAPI->logout();
    }

    /**
     * @param array|null $param
     * @param bool|string $shortKey
     * @param string $method
     * @return array
     * @ignore
     */
    private function buildPortalParameters(array|null  $param,
                                           bool|string $shortKey = false,
                                           string      $method = "GET"): array
    {
        $key = $shortKey ? "portal" : "portalData";
        $prefix = $method === "GET" ? "" : "_";
        $request = [];
        if (array_values($param) === $param) {
            $request[$key] = $param;
        } else {
            $request[$key] = array_keys($param);
            foreach ($param as $portalName => $options) {
                if (!is_null($options) && $options['limit']) {
                    $request["{$prefix}limit.{$portalName}"] = $options['limit'];
                }
                if (!is_null($options) && $options['offset']) {
                    $request["{$prefix}offset.{$portalName}"] = $options['offset'];
                }
            }
        }
        return $request;
    }

    /**
     * @param array|null $param
     * @return array
     * @ignore
     */
    private function buildScriptParameters(array|null $param): array
    {
        $request = [];
        $scriptKeys = [
            "script", "script.param", "script.prerequest", "script.prerequest.param",
            "script.presort", "script.presort.param", "layout.response"
        ];
        foreach ($scriptKeys as $key) {
            if (isset($param[$key])) {
                $request[$key] = $param[$key];
            }
        }
        if (!empty($request) && !is_null($param)) {
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
     * @param array|null $condition The array of associated array which has a field name and "omit" keys as like:
     * array(array("FamilyName"=>"Nii*", "Country"=>"Japan")).
     * In this example of apply the AND operation for two fields,
     * and "FamilyName" and "Country" are field name. The value can contain the operator:
     * =, ==, !, <, ≤ or <=, >, ≥ or >=, ..., //, ?, @, #, *, \, "", ~.
     * If you want to apply the OR operation, describe array of array as like:
     * array(array("FamilyName"=>"Nii*"), array("Country"=>"Japan")).
     * If you want to omit record match with condition set the "omit" element as like:
     * array("FamilyName"=>"Nii*", "omit"=>"true").
     * If you want to query all records in the layout, set the first parameter to null.
     * @param array|null $sort The array of array which has two elements as a field name and order key:
     * array(array("FamilyName", "ascend"), array("GivenName", "descend")).
     * The value of order key can be 'ascend', 'descend' or value list name. The default value is 'ascend'.
     * @param int $offset The start number of the record set, and the first record is 1, but the number 0
     * queries from the first record. The default value is 0.
     * @param int $range The number of records contains in the result record set. The default value is 100.
     * @param array|null $portal The array of the portal's object names. The query result is going to contain portals
     * specified in this parameter. If you want to include all portals, set it null or omit it.
     * Simple case is array('portal1', portal2'), and just includes two portals named 'portal1' and 'portal2'
     * in the query result. If you set the range of records to a portal, you have to build an associated array as like:
     * array('portal1' => array('offset'=>1,'limit'=>5), 'portal2' => null). The record 1 to 5 of portal1 include
     * the query result, and also all records in portal2 do.
     * @param array|null $script scripts that should execute the right timings.
     * The most understandable description is an associated array with API's keywords "script", "script.param",
     * "script.prerequest", "script.prerequest.param", "script.presort", "script.presort.param", "layout.response."
     * These keywords have to be a key, and the value is script name or script parameter,
     * ex. {"script"=>"StartingOver", "script.param"=>"344|21|abcd"}.
     * If $script is array with one element, it's handled as the value of "script."
     * If $script is array with two elements, these are handled as values of "script" and "layout.response."
     * If it's three elements, these are "script", "script.param" and "layout.response."
     * If it's four elements, these are "script.prerequest", "script.presort", "script" and "layout.response."
     * @param int|null $dateformats Use this option to specify date formats for date, time, and timestamp fields. The relevant values are: 0 for US, 1 for file locale, or 2 for ISO8601
     * @return FileMakerRelation|null Query result.
     * @throws Exception In case of any error, an exception arises.
     */
    public function query(array|null $condition = null,
                          array|null $sort = null,
                          int        $offset = 0,
                          int        $range = 0,
                          array|null $portal = null,
                          array|null $script = null,
                          int|null   $dateformats = null): FileMakerRelation|null
    {
        if ($this->restAPI->login()) {
            $headers = ["Content-Type" => "application/json"];
            $request = [];
            $method = is_null($condition) ? "GET" : "POST";
            if (!is_null($sort)) {
                $request["sort"] = $sort;
            }
            if ($offset > 0) {
                $request["offset"] = (string)$offset;
            }
            if ($range > 0) {
                $request["limit"] = (string)$range;
            }
            if (!is_null($portal)) {
                $request = array_merge($request, $this->buildPortalParameters($portal, true, $method));
            }
            if (!is_null($script)) {
                $request = array_merge($request, $this->buildScriptParameters($script));
            }
            if (!is_null($condition)) {
                $request["query"] = $condition;
                $params = ["layouts" => $this->layout, "_find" => null];
            } else {
                $params = ["layouts" => $this->layout, "records" => null];
            }
            if (!is_null($dateformats)) {
                $request["dateformats"] = $dateformats;
            }
            $this->restAPI->callRestAPI($params, true, $method, $request, $headers); // Throw Exception
            $this->restAPI->storeToProperties();
            $result = $this->restAPI->responseBody;
            $fmrel = null;
            if ($result && $result->response &&
                property_exists($result->response, 'data') &&
                property_exists($result, 'messages')
            ) {
                $fmrel = new FileMakerRelation($result->response->data,
                    property_exists($result->response, 'dataInfo') ? $result->response->dataInfo : null,
                    "OK", $result->messages[0]->code, null, $this->restAPI);
            }
            $this->restAPI->logout();
            return $fmrel;
        } else {
            return null;
        }
    }

    /**
     * Query to the FileMaker Database with recordId special field and returns the result as FileMakerRelation object.
     * @param int|null $recordId The recordId.
     * @param array|null $portal See the query() method's same parameter.
     * @param array|null $script scripts that should execute the right timings. See FileMakerRelation::query().
     * @param int|null $dateformats Use this option to specify date formats for date, time, and timestamp fields. The relevant values are: 0 for US, 1 for file locale, or 2 for ISO8601
     * @return FileMakerRelation|null Query result.
     * @throws Exception In case of any error, an exception arises.
     */
    public function getRecord(int|null   $recordId,
                              array|null $portal = null,
                              array|null $script = null,
                              int|null   $dateformats = null): FileMakerRelation|null
    {
        if (is_null($recordId)) {
            return null;
        }
        if ($this->restAPI->login()) {
            $request = [];
            if (!is_null($portal)) {
                $request = array_merge($request, $this->buildPortalParameters($portal, true));
            }
            if (!is_null($script)) {
                $request = array_merge($request, $this->buildScriptParameters($script));
            }
            if (!is_null($dateformats)) {
                $request["dateformats"] = $dateformats;
            }
            $headers = ["Content-Type" => "application/json"];
            $params = ["layouts" => $this->layout, "records" => $recordId];
            $this->restAPI->callRestAPI($params, true, "GET", $request, $headers); // Throw Exception
            $this->restAPI->storeToProperties();
            $result = $this->restAPI->responseBody;
            $fmrel = null;
            if ($result) {
                $dataInfo = null;
                if (property_exists($result->response, 'dataInfo') && is_object($result->response->dataInfo)) {
                    $dataInfo = clone $result->response->dataInfo;
                    $dataInfo->returnedCount = 1;
                }
                $fmrel = new FileMakerRelation($result->response->data, $dataInfo,
                    "OK", $result->messages[0]->code, null, $this->restAPI);
            }
            $this->restAPI->logout();
            return $fmrel;
        } else {
            return null;
        }
    }

    /**
     * Create a record on the target layout of the FileMaker database.
     * @param array|null $data Associated array contains the initial values.
     * Keys are field names and values is these initial values.
     * @param array|null $portal Associated array contains the modifying values in the portal.
     * Ex.: {"<PortalName>"=>{"<FieldName>"=>"<Value>"...}}. FieldName has to "<TOCName>::<FieldName>".
     * @param array|null $script scripts that should execute the right timings. See FileMakerRelation::query().
     * @param int|null $dateformats Use this option to specify date formats for date, time, and timestamp fields. The relevant values are: 0 for US, 1 for file locale, or 2 for ISO8601
     * @return int|null The recordId of created record.
     * If the returned value is an integer larger than 0, it shows one record was created.
     * @throws Exception In case of any error, an exception arises.
     */
    public function create(array|null $data = null,
                           array|null $portal = null,
                           array|null $script = null,
                           int|null   $dateformats = null): int|null
    {
        if ($this->restAPI->login()) {
            $headers = ["Content-Type" => "application/json"];
            $params = ["layouts" => $this->layout, "records" => null];
            $request = ["fieldData" => is_null($data) ? [] : $data];
            if (!is_null($portal)) {
                $request = array_merge($request, ["portalData" => $portal]);
            }
            if (!is_null($script)) {
                $request = array_merge($request, $this->buildScriptParameters($script));
            }
            if (!is_null($dateformats)) {
                $request["dateformats"] = $dateformats;
            }
            $this->restAPI->callRestAPI($params, true, "POST", $request, $headers); // Throw Exception
            $result = $this->restAPI->responseBody;
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
            return $result->response->recordId;
        } else {
            return null;
        }
    }

    /**
     * Duplicate the record.
     * @param int|null $recordId The valid recordId value to duplicate.
     * @param array|null $script scripts that should execute the right timings. See FileMakerRelation::query().
     * @throws Exception In case of any error, an exception arises.
     */
    public function duplicate(int|null   $recordId,
                              array|null $script = null): void
    {
        if (is_null($recordId)) {
            return;
        }
        if ($this->restAPI->login()) {
            $request = "{}"; //FileMaker expects an empty object, so we have to set "{}" here
            $headers = ["Content-Type" => "application/json"];
            $params = ['layouts' => $this->layout, 'records' => $recordId];
            if (!is_null($script)) {
                $request = $this->buildScriptParameters($script);
            }
            $this->restAPI->callRestAPI($params, true, 'POST', $request, $headers); // Throw Exception
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
        }
    }

    /**
     * Delete the record.
     * @param int|null $recordId The valid recordId value to delete.
     * @param array|null $script scripts that should execute the right timings. See FileMakerRelation::query().
     * @throws Exception In case of any error, an exception arises.
     */
    public function delete(int|null   $recordId,
                           array|null $script = null): void
    {
        if (is_null($recordId)) {
            return;
        }
        if ($this->restAPI->login()) {
            $request = [];
            $headers = null;
            $params = ['layouts' => $this->layout, 'records' => $recordId];
            if (!is_null($script)) {
                $request = $this->buildScriptParameters($script);
            }
            $this->restAPI->callRestAPI($params, true, 'DELETE', $request, $headers); // Throw Exception
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
        }
    }

    /**
     * Update fields in one record.
     * @param int|null $recordId The valid recordId value to update.
     * @param array|null $data Associated array contains the modifying values.
     * Keys are field names and values are these initial values.
     * If it's null or [], no operation is going.
     * @param int $modId The modId to allow updating. This parameter is for detect to modifying other users.
     * If you omit this parameter, update operation does not care the value of modId special field.
     * @param array|object|null $portal Associated array contains the modifying values in the portal.
     * Ex.: {"<PortalName>"=>{"<FieldName>"=>"<Value>", "recordId"=>"12"}}. FieldName has to "<TOCName>::<FieldName>".
     * The recordId key specifies the record to edit in the portal.
     * @param array|null $script scripts that should execute the right timings. See FileMakerRelation::query().
     * @param int|null $dateformats Use this option to specify date formats for date, time, and timestamp fields. The relevant values are: 0 for US, 1 for file locale, or 2 for ISO8601
     * @throws Exception In case of any error, an exception arises.
     */
    public function update(int|null          $recordId,
                           array|null        $data,
                           int               $modId = -1,
                           array|object|null $portal = null,
                           array|null        $script = null,
                           int|null          $dateformats = null): void
    {
        if (is_null($recordId)
            || ((is_null($data) || count($data) === 0)
                && (is_null($portal) || count($portal) === 0))) {
            return;
        }
        if ($this->restAPI->login()) {
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
            if (!is_null($dateformats)) {
                $request["dateformats"] = $dateformats;
            }
            if ($modId > -1) {
                $request = array_merge($request, ["modId" => (string)$modId]);
            }
            $this->restAPI->callRestAPI($params, true, "PATCH", $request, $headers); // Throw exception
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
        }
    }

    /**
     * Set the value to the global field.
     * @param array $fields The Associated array contains the global field names and its values.
     * Keys are global field names and values is these values.
     * @throws Exception In case of any error, an exception arises.
     */
    public function setGlobalField(array $fields): void
    {
        if ($this->restAPI->login()) {
            foreach ($fields as $name => $value) {
                if ((function_exists('mb_strpos') && mb_strpos($name, '::') === false) || !str_contains($name, '::')) {
                    unset($fields[$name]);
                    $fields[$this->layout . '::' . $name] = $value;
                }
            }
            $headers = ["Content-Type" => "application/json"];
            $params = ["globals" => null];
            $request = ["globalFields" => $fields];
            $this->restAPI->callRestAPI($params, true, "PATCH", $request, $headers); // Throw exception
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
        }
    }

    /**
     * Upload the file into container filed.
     * @param string $filePath The file path to upload.
     * @param int|null $recordId The Record ID of the record.
     * @param string $containerFieldName The field name of container field.
     * @param int|null $containerFieldRepetition In the case of repetiton field, this has to be the number from 1.
     * If omitted this, the number "1" is going to be specified.
     * @param string|null $fileName Another file name for an uploading file. If omitted, the original file name is chosen.
     * @throws Exception In case of any error, an exception arises.
     */
    public function uploadFile(string      $filePath,
                               int|null    $recordId,
                               string      $containerFieldName,
                               int|null    $containerFieldRepetition = null,
                               string|null $fileName = null): void
    {
        if (!file_exists($filePath)) {
            throw new Exception("File doesn't exist: {$filePath}.");
        }
        if (is_null($recordId)) {
            return;
        }
        if ($this->restAPI->login()) {
            $CRLF = chr(13) . chr(10);
            $DQ = '"';
            $boundary = "FMDataAPI_UploadFile-" . uniqid();
            $fileName = is_null($fileName) ? basename($filePath) : $fileName;
            $headers = ["Content-Type" => "multipart/form-data; boundary={$boundary}"];
            $repNum = is_null($containerFieldRepetition) ? 1 : $containerFieldRepetition;
            $params = [
                "layouts" => $this->layout,
                "records" => $recordId,
                "containers" => "{$containerFieldName}/{$repNum}",
            ];
            $request = "--{$boundary}{$CRLF}";
            $request .= "Content-Disposition: form-data; name={$DQ}upload{$DQ}; filename={$DQ}{$fileName}{$DQ}{$CRLF}";
            $request .= $CRLF;
            $request .= file_get_contents($filePath);
            $request .= "{$CRLF}{$CRLF}--{$boundary}--{$CRLF}";
            $this->restAPI->callRestAPI($params, true, "POST", $request, $headers); // Throw Exception
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
        }
    }

    /**
     * Get the metadata information of the layout. Until ver.16 this method was 'getMetadata'.
     * @return object|null The metadata information of the layout.
     * It has just 1 property 'metaData' the array of the field information is set under the 'metaData' property.
     * There is no information about portals. Ex.:
     * {"metaData": [{"name": "id","type": "normal","result": "number","global": "false","repetitions": 1,"id": "1"},
     *{"name": "name","type": "normal","result": "text","global": "false","repetitions": 1,"id": "2"},....,]}
     * @throws Exception In case of any error, an exception arises.
     */
    public function getMetadataOld(): object|null
    {
        $returnValue = null;
        if ($this->restAPI->login()) {
            $request = [];
            $headers = ["Content-Type" => "application/json"];
            $params = ['layouts' => $this->layout, 'metadata' => null];
            $this->restAPI->callRestAPI($params, true, 'GET', $request, $headers); // Throw Exception
            $result = $this->restAPI->responseBody;
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
            $returnValue = $result->response;
        }
        return $returnValue;
    }

    /**
     * Get metadata information of the layout.
     * @return object|null The metadata information of the layout.
     * It has 3 properties 'fieldMetaData', 'portalMetaData' and 'valueLists'.
     * The later one has properties having portal object name of TO name.
     * The array of the field information is set under 'fieldMetaData' and the portal named properties.
     * Ex.: {"fieldMetaData": [{"name": "id","type": "normal","displayType": "editText","result": "number","global": false,
     * "autoEnter": true,"fourDigitYear": false,"maxRepeat": 1,"maxCharacters": 0,"notEmpty": false,"numeric": false,
     * "timeOfDay": false,"repetitionStart": 1,"repetitionEnd": 1},....,],"portalMetaData": {"Contact": [{
     * "name": "contact_to::id","type": "normal",...},...], "history_to": [{"name": "history_to::id","type": "normal",
     * ...}...]}
     * @throws Exception In case of any error, an exception arises.
     */
    public function getMetadata(): object|null
    {
        $returnValue = null;
        if ($this->restAPI->login()) {
            $request = [];
            $headers = ["Content-Type" => "application/json"];
            $params = ['layouts' => $this->layout];
            $this->restAPI->callRestAPI($params, true, 'GET', $request, $headers); // Throw Exception
            $result = $this->restAPI->responseBody;
            $this->restAPI->storeToProperties();
            $this->restAPI->logout();
            $returnValue = $result->response;
        }
        return $returnValue;
    }

    /**
     * Get debug information includes internal request URL and request body.
     * @return string
     */
    public function getDebugInfo(): string
    {
        return $this->restAPI->url . " " . json_encode($this->restAPI->requestBody);
    }

    /**
     * Get the script error code.
     * @return int|null The value of the error code.
     * If any script wasn't called, returns null.
     */
    public function getScriptError(): int|null
    {
        return $this->restAPI->scriptError;
    }

    /**
     * Get the return value from the script.
     * @return string|null  The return value from the script.
     * If any script wasn't called, returns null.
     */
    public function getScriptResult(): string|null
    {
        return $this->restAPI->scriptResult;
    }

    /**
     * Get the prerequest script error code.
     * @return int|null The value of the error code.
     * If any script wasn't called, returns null.
     */
    public function getScriptErrorPrerequest(): int|null
    {
        return $this->restAPI->scriptErrorPrerequest;
    }

    /**
     * Get the return value from the prerequest script.
     * @return string|null  The return value from the prerequest script.
     * If any script wasn't called, returns null.
     */
    public function getScriptResultPrerequest(): string|null
    {
        return $this->restAPI->scriptResultPrerequest;
    }

    /**
     * Get the presort script error code.
     * @return int|null The value of the error code.
     * If any script wasn't called, returns null.
     */
    public function getScriptErrorPresort(): int|null
    {
        return $this->restAPI->scriptErrorPresort;
    }

    /**
     * Get the return value from the presorted script.
     * @return string|null  The return value from the presorted script.
     * If any script wasn't called, returns null.
     */
    public function getScriptResultPresort(): string|null
    {
        return $this->restAPI->scriptResultPresort;
    }

}
