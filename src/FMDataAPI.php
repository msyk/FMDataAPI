<?php

namespace INTERMediator\FileMakerServer\RESTAPI;

use INTERMediator\FileMakerServer\RESTAPI\SessionCache\SessionCacheInterface;
use INTERMediator\FileMakerServer\RESTAPI\Supporting\FileMakerLayout;
use INTERMediator\FileMakerServer\RESTAPI\Supporting\FileMakerRelation;
use INTERMediator\FileMakerServer\RESTAPI\Supporting\CommunicationProvider;
use Exception;

/**
 * Class FMDataAPI is the wrapper of The REST API in Claris FileMaker Server and FileMaker Cloud for AWS.
 *
 * @package INTER-Mediator\FileMakerServer\RESTAPI
 * @link https://github.com/msyk/FMDataAPI GitHub Repository
 * @property-read FileMakerLayout $layout Returns the FileMakerLayout object from the layout named with the property.
 *    If the layout doesn't exist, no error arises here. Any errors might arise on methods of FileMakerLayout class.
 * @version 36
 * @author Masayuki Nii <nii@msyk.net>
 * @copyright 2017-2026 Masayuki Nii
 * (Claris FileMaker is a registered trademark of Claris International Inc. in the U.S. and other countries.)
 */
class FMDataAPI
{
    /* Document generating:
     * - Install PHP Documentor, and enter the command below.
     * php ../phpDocumentor.phar run -f ./src/FMDataAPI.php -f ./src/Supporting/CommunicationProvider.php -f ./src/Supporting/FileMakerLayout.php -f ./src/Supporting/FileMakerRelation.php  -t ../INTER-Mediator_Documents/FMDataAPI
     */

    /**
     * @var FileMakerLayout[] Keeping the FileMakerLayout object for each layout.
     * @ignore
     */
    private array $layoutTable = [];

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
     * @param string|null $password The password of the above user.
     * This can be null for testing purposes only. Null data is going to replace the string "password".
     * This prevents to being detected as a security issue.
     * On the real solutions, you have to set a valid password string.
     * If you’re going to call useOAuth method, you have to specify the data for X-FM-Data-OAuth-Identifier.
     * @param string|null $host FileMaker Server's host name or IP address. If omitted, 'localhost' is chosen.
     * The value "localserver" tries to connect directory 127.0.0.1, and you don't have to set $port and $protocol.
     * @param int|null $port FileMaker Server's port number. If omitted, 443 is chosen.
     * @param string|null $protocol FileMaker Server's protocol name. If omitted, 'https' is chosen.
     * @param array|null $fmDataSource Authentication information for external data sources.
     * Ex.  [{"database"=>"<databaseName>", "username"=>"<username>", "password"=>"<password>"}].
     * If you use OAuth, "oAuthRequestId" and "oAuthIdentifier" keys have to be specified.
     * @param boolean $isUnitTest If it's set to true, the communication provider just works locally.
     * @param SessionCacheInterface|null $sessionCache Cache backend for persistent sessions.
     * If omitted, the library logs in and out on every database operation, or once
     * per communication scope when using startCommunication() / endCommunication().
     * If specified, session tokens are persisted and reused across requests via
     * startCommunication() / endCommunication(), avoiding redundant logins against the FileMaker Server.
     * When a session cache is specified, {@see self::setRetryOnAccessTokenInvalidation()} is
     * automatically set to true, ensuring the library re-authenticates and retries the request if
     * the cached token has expired on the FileMaker Server.
     */
    public function __construct(string                     $solution,
                                string                     $user,
                                string|null                $password,
                                string|null                $host = null,
                                int|null                   $port = null,
                                string|null                $protocol = null,
                                array|null                 $fmDataSource = null,
                                bool                       $isUnitTest = false,
                                SessionCacheInterface|null $sessionCache = null)
    {
        if (is_null($password)) {
            $password = "password"; // For testing purpose.
        }

        if (!$isUnitTest) {
            $this->provider = new Supporting\CommunicationProvider($solution, $user, $password, $host, $port, $protocol, $fmDataSource, $sessionCache);
        } else {
            $this->provider = new Supporting\TestProvider($solution, $user, $password, $host, $port, $protocol, $fmDataSource, $sessionCache);
        }
    }

    /**
     * Can't set the value to the undefined name.
     * @param string $key The property name
     * @param mixed $value The value to set
     * @throws Exception
     * @ignore
     */
    public function __set(string $key,
                          mixed  $value): void
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
     * Refers to the FileMakerLayout object as the proxy of the layout.
     * If the layout doesn't exist, no error arises here. Any errors might arise on methods of the FileMakerLayout class.
     * @param string $layout_name Layout name.
     * @return FileMakerLayout object which is a proxy of FileMaker's layout.
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
     * In the authentication session, username and password are handled as OAuth parameters.
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
     * Set to verify the server certificate. The default is to handle as a self-signed certificate and doesn't verify.
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
     * This means a kind of compatible mode to FileMaker API for PHP.
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
     * @return string|null The session token.
     */
    public function getSessionToken(): string|null
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
     * @return string|null The error message of curl.
     */
    public function curlErrorMessage(): null|string
    {
        return $this->provider->curlError;
    }

    /**
     * The HTTP status code of the latest response from the REST API.
     * @return int|null The HTTP status code.
     */
    public function httpStatus(): int|null
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
     * @return string|null The error message.
     */
    public function errorMessage(): string|null
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
     * Start a communication scope with a shared authenticated session.
     *
     * Usually most methods login and logout before and after each database operation.
     * By calling startCommunication() and endCommunication(), methods between them don't
     * log in and out every time, and it can expect faster operations.
     *
     * Without a session cache, one authenticated session is kept for the duration of
     * the current communication scope and discarded when endCommunication() is called.
     *
     * With a session cache, the session token is persisted beyond the current communication
     * scope and reused across requests. If no cached token is available, a new session is
     * created and stored for future reuse.
     *
     * @throws Exception
     */
    public function startCommunication(): void
    {
        $this->provider->startCommunication();
    }

    /**
     * Finish a communication scope.
     *
     * Without a session cache, the authenticated session for the current communication
     * scope is ended and the server session is logged out.
     *
     * With a session cache, the cached token's TTL is renewed if it still matches the
     * token held by this instance. If another process has replaced the cached token in
     * the meantime, only this instance's now-stale token is logged out, leaving the
     * newer cached token intact.
     *
     * @throws Exception
     */
    public function endCommunication(): void
    {
        $this->provider->endCommunication();
    }

    /**
     * Set the value to the global field.
     * @param array $fields Associated array contains the global field names (Field names must be Fully Qualified) and its values.
     * Keys are global field names and values are these values.
     * @throws Exception In case of any error, an exception arises.
     */
    public function setGlobalField(array $fields): void
    {
        if ($this->provider->login()) {
            $headers = ["Content-Type" => "application/json"];
            $params = ["globals" => null];
            $request = ["globalFields" => $fields];
            $this->provider->callRestAPI($params, true, "PATCH", $request, $headers); // Throw Exception
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
     * Get the information about a hosting database. It includes the target database and others in FileMaker Server.
     * This is required to authenticate.
     * @return null|array The information of hosting databases. Every element is an object and just has 'name'
     * property.Ex.: [{"name": "TestDB"},{"name": "sample_db" }]
     * @throws Exception In case of any error, an exception arises.
     */
    public function getDatabaseNames(): null|array
    {
        return $this->provider->getDatabaseNames();
    }

    /**
     * Get the list of layout name in a database.
     * @return null|array The information of layouts in the target database. Every element is an object and just has 'name'
     * property.
     * Ex.: [{"name": "person_layout"},{"name": "contact_to"},{"name": "history_to"}...]
     * @throws Exception In case of any error, an exception arises.
     */
    public function getLayoutNames(): null|array
    {
        return $this->provider->getLayoutNames();
    }

    /**
     * Get the list of script name in a database.
     * @return null|array The information of scripts in the target database. Every element is an object and has a 'name' property.
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
     * Return the base64 encoded data in the container field with streaming url.
     * @param string $url The container data URL.
     * @return string The base64 encoded data in the container field.
     * @throws Exception The exception from the accessToContainer method.
     */
    public function getContainerData(string $url): string
    {
        return $this->provider->accessToContainer($url);
    }

    /**
     * Set the property of the communication provider for excluding the timestamp from the exception message.
     * The default value of this property is false.
     * @param bool $value Excluding the timestamp from the exception message if true.
     */
    public function setExcludeTimeStampInException(bool $value = true): void
    {
        $this->provider->excludeTimeStampInException = $value;
    }

    /**
     * Controls whether failed Data API calls are automatically retried after session invalidation.
     *
     * When enabled and a call fails with error 952 (invalid token) or 112 (window missing), the
     * current session is discarded, a new session is established, and the call is retried once.
     *
     * When a session cache is provided to the constructor, retry on token invalidation is always
     * active regardless of this setting. This flag only has an effect when no session cache is
     * configured.
     *
     * Warning: The retry runs in a fresh session. Any session-scoped state from the original session
     * is lost — for example, global fields set before the retry will not carry over.
     * @param bool $value
     */
    public function setRetryOnAccessTokenInvalidation(bool $value = true): void
    {
        $this->provider->retryOnAccessTokenInvalidation = $value;
    }

    /**
     * Overrides the time-to-live (TTL) of the cached FileMaker Data API session token.
     *
     * WARNING: Setting a TTL that exceeds the FileMaker Data API session timeout (15 minutes)
     * will cause the library to use expired tokens, resulting in authentication failures.
     * Do not use this method unless you fully understand the implications.
     *
     * The default TTL is 840 seconds (14 minutes), intentionally set one minute below the
     * FileMaker Data API session timeout of 15 minutes to ensure the cached token is
     * invalidated before it expires on the FileMaker Server.
     * @param int $ttl Time-to-live in seconds. Defaults to 840 seconds (14 minutes).
     * @throws Exception If a session cache is not set, an exception is thrown.
     */
    public function setSessionCacheTtl(int $ttl = 840): void
    {
        if ($this->provider->sessionCache === null) {
            throw new Exception("setSessionCacheTtl() requires a session cache to be configured via the constructor.");
        }
        $this->provider->sessionCache->setTtl($ttl);
    }
}
