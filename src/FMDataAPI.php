<?php

namespace INTERMediator\FileMakerServer\RESTAPI;

use INTERMediator\FileMakerServer\RESTAPI\Supporting\FileMakerLayout;
use INTERMediator\FileMakerServer\RESTAPI\Supporting\FileMakerRelation;
use INTERMediator\FileMakerServer\RESTAPI\Supporting\CommunicationProvider;
use Exception;

/**
 * Class FMDataAPI is the wrapper of The REST API in Claris FileMaker Server and FileMaker Cloud for AWS.
 *
 * @package INTER-Mediator\FileMakerServer\RESTAPI
 * @link https://github.com/msyk/FMDataAPI GitHub Repository
 * @property-read FileMakerLayout <<layout_name>> Returns the FileMakerLayout object from the layout named with the property.
 *    If the layout doesn't exist, no error arises here. Any errors might arise on methods of FileMakerLayout class.
 * @version 31
 * @author Masayuki Nii <nii@msyk.net>
 * @copyright 2017-2024 Masayuki Nii
 * (Claris FileMaker is registered trademarks of Claris International Inc. in the U.S. and other countries.)
 */
class FMDataAPI
{
    /* Document generating:
     * - Install PHP Documentor, and enter command below.
     * php ../phpDocumentor.phar run -f ./src/FMDataAPI.php -f ./src/Supporting/CommunicationProvider.php -f ./src/Supporting/FileMakerLayout.php -f ./src/Supporting/FileMakerRelation.php  -t ../INTER-Mediator_Documents/FMDataAPI
     */

    /**
     * @var FileMakerLayout[] Keeping the FileMakerLayout object for each layout.
     * @ignore
     */
    private $layoutTable = [];

    /**
     * @var null|CommunicationProvider Keeping the CommunicationProvider object.
     * @ignore
     */
    private CommunicationProvider|null $provider;

    /**
     * FMDataAPI constructor. If you want to activate OAuth authentication, $user and $password are set as
     * oAuthRequestId and oAuthIdentifier. Moreover, call useOAuth method before accessing layouts.
     * @param string $solution The database file name which is just hosting.
     * Every database must have the accessing privilege 'fmrest' including external data sources.
     * @param string $user The fmrest privilege accessible user to the database.
     * If you’re going to call useOAuth method, you have to specify the data for X-FM-Data-OAuth-Request-Id.
     * @param string $password The password of the above user.
     * If you’re going to call useOAuth method, you have to specify the data for X-FM-Data-OAuth-Identifier.
     * @param ?string $host FileMaker Server's host name or IP address. If omitted, 'localhost' is chosen.
     * The value "localserver" tries to connect directory 127.0.0.1, and you don't have to set $port and $protocol.
     * @param int $port FileMaker Server's port number. If omitted, 443 is chosen.
     * @param string $protocol FileMaker Server's protocol name. If omitted, 'https' is chosen.
     * @param array $fmDataSource Authentication information for external data sources.
     * Ex.  [{"database"=>"<databaseName>", "username"=>"<username>", "password"=>"<password>"].
     * If you use OAuth, "oAuthRequestId" and "oAuthIdentifier" keys have to be spedified.
     * @param boolean $isUnitTest If it's set to true, the communication provider just works locally.
     */
    public function __construct(
        string  $solution, string $user, string $password,
        ?string $host = null, ?int $port = null, ?string $protocol = null,
        ?array  $fmDataSource = null, bool $isUnitTest = false)
    {
        if (!$isUnitTest) {
            $this->provider = new Supporting\CommunicationProvider($solution, $user, $password, $host, $port, $protocol, $fmDataSource);
        } else {
            $this->provider = new Supporting\TestProvider($solution, $user, $password, $host, $port, $protocol, $fmDataSource);
        }
    }

    /**
     * Can't set the value to the undefined name.
     * @param string $key The property name
     * @param mixed $value The value to set
     * @throws Exception
     * @ignore
     */
    public function __set(string $key, mixed $value): void
    {
        throw new Exception("The $key property is read-only, and can't set any value.");
    }

    /**
     * Handle the undefined name as the layout name.
     * @param string $key The property name
     * @return FileMakerLayout FileMakerLayout object
     * @ignore
     */
    public function __get(string $key): FileMakerLayout
    {
        return $this->layout($key);
    }

    /**
     * Refers the FileMakerLayout object as the proxy of the layout.
     * If the layout doesn't exist, no error arises here. Any errors might arise on methods of FileMakerLayout class.
     * @param string $layout_name Layout name.
     * @return FileMakerLayout object which is proxy of FileMaker's layout.
     */
    public function layout(string $layout_name): FileMakerLayout
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
    public function setDebug(bool $value): void
    {
        $this->provider->isDebug = $value;
    }

    /**
     * Set the cURL communication timeout in seconds
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void
    {
        $this->provider->timeout = $timeout;
    }

    /**
     * On the authentication session, username and password are handled as OAuth parameters.
     */
    public function useOAuth(): void
    {
        $this->provider->useOAuth = true;
    }

    /**
     * FileMaker Data API's version is going to be set. If you don't call, the "vLatest" is specified.
     * As far as FileMaker 18 supports just "v1", no one has to call this method.
     * @param int $vNum FileMaker Data API's version number.
     */
    public function setAPIVersion(int $vNum): void
    {
        $this->provider->vNum = $vNum;
    }

    /**
     * Set to verify the server certificate. The default is to handle as self-signed certificate and doesn't verify.
     * @param bool $value Turn on to verify the certificate if the value is true.
     */
    public function setCertValidating(bool $value): void
    {
        $this->provider->isCertValidating = $value;
    }

    /**
     * Set to true if the return value of the field() method uses the htmlspecialchars function.
     * The default value is FALSE.
     * The nostalgic FileMaker API for PHP was returning the htmlspecialchars value of the field.
     * If we want to get the row field data, we had to call the getFieldUnencoded method.
     * If this property is set to true,
     * FileMakerRelation class's field method (including describing field name directly) returns the value processed
     * with the htmlspecialchars.
     * This means kind of compatible mode of FileMaker API for PHP.
     * This feature works whole the FMDataAPI library.
     * @param bool $value Turn on to verify the certificate if the value is true.
     */
    public function setFieldHTMLEncoding(bool $value): void
    {
        $this->provider->fieldHTMLEncoding = $value;
    }

    /**
     * Detect the return value of the field() method uses htmlspecialchars function or not.
     * @return bool The result.
     */
    public function getFieldHTMLEncoding(): bool
    {
        return $this->provider->fieldHTMLEncoding;
    }

    /**
     * Set session token
     * @param string $value The session token.
     */
    public function setSessionToken(string $value): void
    {
        $this->provider->accessToken = $value;
    }

    /**
     * The session token earned after authentication.
     * @return string The session token.
     */
    public function getSessionToken(): string
    {
        return $this->provider->accessToken;
    }

    /**
     * The error number of curl, i.e., kind of communication error code.
     * @return int The error number of curl.
     */
    public function curlErrorCode(): int
    {
        return $this->provider->curlErrorNumber;
    }

    /**
     * The error message of curl, text representation of code.
     * @return string The error message of curl.
     */
    public function curlErrorMessage(): null|string
    {
        return $this->provider->curlError;
    }

    /**
     * The HTTP status code of the latest response from the REST API.
     * @return int The HTTP status code.
     */
    public function httpStatus(): null|int
    {
        return $this->provider->httpStatus;
    }

    /**
     * The error code of the latest response from the REST API.
     * Code 0 means no error, and -1 means error information wasn't returned.
     * This error code is associated with FileMaker's error code.
     * @return int The error code.
     */
    public function errorCode(): int
    {
        return $this->provider->errorCode;
    }

    /**
     * The error message of the latest response from the REST API.
     * This error message is associated with FileMaker's error code.
     * @return string The error message.
     */
    public function errorMessage(): null|string
    {
        return $this->provider->errorMessage;
    }

    /**
     * Set to prevent throwing an exception in case of error.
     * The default is true, so an exception is going to throw in error.
     * @param bool $value Turn off to throw an exception in case of error if the value is false.
     */
    public function setThrowException(bool $value): void
    {
        $this->provider->throwExceptionInError = $value;
    }

    /**
     * Start a transaction which is a serial calling of multiple database operations before the single authentication.
     * Usually most methods login and logout before/after the database operation, and so a little bit of time is going to
     * take.
     * The startCommunication() login and endCommunication() logout, and methods between them don't log in/out, and
     * it can expect faster operations.
     * @throws Exception
     */
    public function startCommunication(): void
    {
        try {
            if ($this->provider->login()) {
                $this->provider->keepAuth = true;
            }
        } catch (Exception $e) {
            $this->provider->keepAuth = false;
            throw $e;
        }
    }

    /**
     * Finish a transaction which is a serial calling of any database operations, and logout.
     * @throws Exception
     */
    public function endCommunication(): void
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
    public function setGlobalField(array $fields): void
    {
        if ($this->provider->login()) {
            $headers = ["Content-Type" => "application/json"];
            $params = ["globals" => null];
            $request = ["globalFields" => $fields];
            $this->provider->callRestAPI($params, true, "PATCH", $request, $headers);
            $this->provider->storeToProperties();
            $this->provider->logout();
        }
    }

    /**
     * Get the product information, such as the version, etc. This isn't required to authenticate.
     * @return null|object The information of this FileMaker product. Ex.:
     * {'name' => 'FileMaker Data API Engine', 'buildDate' => '03/27/2019', 'version' => '18.0.1.109',
     * 'dateFormat' => 'MM/dd/yyyy', 'timeFormat' => 'HH:mm:ss', 'timeStampFormat' => 'MM/dd/yyyy HH:mm:ss'}.
     * @throws Exception In case of any error, an exception arises.
     */
    public function getProductInfo(): null|object
    {
        return $this->provider->getProductInfo();
    }

    /**
     * Get the information about hosting database. It includes the target database and others in FileMaker Server.
     * This is required to authenticate.
     * @return null|array The information of hosting databases. Every element is an object and just having 'name'
     * property.Ex.: [{"name": "TestDB"},{"name": "sample_db"}]
     * @throws Exception In case of any error, an exception arises.
     */
    public function getDatabaseNames(): null|array
    {
        return $this->provider->getDatabaseNames();
    }

    /**
     * Get the list of layout name in a database.
     * @return null|array The information of layouts in the target database. Every element is an object and just having 'name'
     * property.
     * Ex.: [{"name": "person_layout"},{"name": "contact_to"},{"name": "history_to"}...]
     * @throws Exception In case of any error, an exception arises.
     */
    public function getLayoutNames(): null|array
    {
        return $this->provider->getLayoutNames();
    }

    /**
     * Get the list of script name in database.
     * @return null|array The information of scripts in the target database. Every element is an object and having 'name' property.
     * The 'isFolder' property is true if it's a folder item, and it has the 'folderScriptNames' property and includes
     * an object with the same structure.
     * Ex.: [{"name": "TestScript1","isFolder": false},{"name": "TestScript2","isFolder": false},{"name": "Maintenance",
     * "isFolder": true, "folderScriptNames": [{"name": "DataImport","isFolder": false}]}]
     * @throws Exception In case of any error, an exception arises.
     */
    public function getScriptNames(): null|array
    {
        return $this->provider->getScriptNames();
    }

    /**
     * Get the table occurrence name of just a previous query.
     * Usually this method returns the information of the FileMakerRelation class.
     * @return null|string  The table name.
     * @see FileMakerRelation::getTargetTable()
     */
    public function getTargetTable(): null|string
    {
        return $this->provider->targetTable;
    }

    /**
     * Get the total record count of just a previous query.
     * Usually this method returns the information of the FileMakerRelation class.
     * @return null|int  The total record count.
     * @see FileMakerRelation::getTotalCount()
     */
    public function getTotalCount(): null|int
    {
        return $this->provider->totalCount;
    }

    /**
     * Get the founded record count of just a previous query.
     * Usually this method returns the information of the FileMakerRelation class.
     * @return null|int  The founded record count.
     * @see FileMakerRelation::getFoundCount(): null|int
     */
    public function getFoundCount(): null|int
    {
        return $this->provider->foundCount;
    }

    /**
     * Get the returned record count of just a previous query.
     * Usually this method returns the information of the FileMakerRelation class.
     * @return null|int  The returned record count.
     * @see FileMakerRelation::getReturnedCount()
     */
    public function getReturnedCount(): null|int
    {
        return $this->provider->returnedCount;
    }

    /**
     * Return the base64 encoded data in container field with streaming url.
     * @param string $url The container data URL.
     * @return string The base64 encoded data in container field.
     * @throws Exception The exception from the accessToContainer method.
     */
    public function getContainerData(string $url): string
    {
        return $this->provider->accessToContainer($url);
    }

}
