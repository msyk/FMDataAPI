<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2018/07/29
 * Time: 1:51
 */

namespace INTERMediator\FileMakerServer\RESTAPI\Supporting;

class TestProvider extends CommunicationProvider
{

    public function __construct($solution, $user, $password, $host = NULL, $port = NULL, $protocol = NULL, $fmDataSource = NULL)
    {
        parent::__construct($solution, $user, $password, $host, $port, $protocol, $fmDataSource);
        $this->buildResponses();
    }

    /**
     * Override communication method.
     * @param $params
     * @param $isAddToken
     * @param string $method
     * @param null $request
     * @param null $addHeader
     */
    public function callRestAPI($params, $isAddToken, $method = 'GET', $request = NULL, $addHeader = null, $isSystem = false)
    {
        $methodLower = strtolower($method);
        $url = $this->getURL($params, $request, $methodLower);
        $header = $this->getHeaders($isAddToken, $addHeader);
        if ($methodLower !== 'get' && !is_null($request) && !is_string($request)) {
            $request = $this->justifyRequest($request);
        }
        $inputs = ['url' => $url, 'method' => $methodLower, 'header' => $header, 'request' => $request];
        $response = $this->validResponse($inputs);

        $this->curlInfo = $response['curlinfo'];
        $this->curlErrorNumber = $response['curlerror'];
        $this->curlError = $response['curlerrormessage'];

        $this->method = $method;
        $this->url = $url;
        $this->requestHeader = $header;
        $this->requestBody = ($methodLower != 'get') ? $request : null;
        $this->responseBody = json_decode($response['response'], false, 512, JSON_BIGINT_AS_STRING);
    }

    /**
     * Override communication method.
     * @param $url
     */
    public function accessToContainer($url)
    {

    }

    private function validResponse($input)
    {
        $hash = sha1(json_encode($input));
        foreach ($this->responses as $key => $value) {
            if ($hash === $key) {
                return $value;
            }
        }
    }

    private $responses;

    private function buildResponses()
    {
        $this->responses = [
            '1f07ed437a339095a5634da38074a2d1173442e6' => [    //Login
                'response' => '{"response":{"token":"1f3c9bd128ef29e97b2d7fd941df4a88198bd8b5eb9aa69c4"},"messages":[{"code":"0","message":"OK"}]}',
                'curlerror' => '0',
                'curlerrormessage' => '',
                'curlinfo' => ['http_code' => 200]
            ],
            'e6de3c79ed664d3f4fa49c9013bf566eba78da1a' => [     //LogOut
                'response' => '{"response": {},"messages": [{"code": "0", "message": "OK"}]}',
                'curlerror' => '0',
                'curlerrormessage' => '',
                'curlinfo' => ['http_code' => 200]
            ],
            'd973a3dff40c30a307a9872714c88f9ee6873dd5' => [     //Simple Query
                'response' => '{"response":{"data":[{"fieldData":{"id":1,"name":"Masayuki Nii","address":"Saitama, Japan","mail":"msyk@msyk.net","category":102,"checking":1,"location":201,"memo":""},"portalData":{"Contact":[{"recordId":"1","contact_to::id":1,"contact_to::person_id":1,"contact_to::summary":"Telephone","contact_to::datetime":"12/01/2009 15:23:00","contact_to::description":"a\rb","contact_to::important":"","contact_to::way":4,"contact_to::kind":4,"modId":"1"},{"recordId":"2","contact_to::id":2,"contact_to::person_id":1,"contact_to::summary":"Meetings","contact_to::datetime":"12/02/2009 15:23:00","contact_to::description":"aq","contact_to::important":1,"contact_to::way":4,"contact_to::kind":7,"modId":"3"},{"recordId":"3","contact_to::id":3,"contact_to::person_id":1,"contact_to::summary":"Mail","contact_to::datetime":"12/03/2009 15:23:00","contact_to::description":"","contact_to::important":"","contact_to::way":5,"contact_to::kind":8,"modId":"0"}],"History":[{"recordId":"1","history_to::id":1,"history_to::person_id":1,"history_to::description":"Hight School","history_to::startdate":"04/01/2001","history_to::enddate":"03/31/2003","history_to::username":"","modId":"0"},{"recordId":"2","history_to::id":2,"history_to::person_id":1,"history_to::description":"University","history_to::startdate":"04/01/2003","history_to::enddate":"03/31/2007","history_to::username":"","modId":"0"}]},"recordId":"1","modId":"6"},{"fieldData":{"id":2,"name":"Someone","address":"Tokyo, Japan","mail":"msyk@msyk.net","category":"","checking":"","location":"","memo":""},"portalData":{"Contact":[{"recordId":"4","contact_to::id":4,"contact_to::person_id":2,"contact_to::summary":"Calling","contact_to::datetime":"12/04/2009 15:23:00","contact_to::description":"","contact_to::important":"","contact_to::way":6,"contact_to::kind":12,"modId":"0"},{"recordId":"5","contact_to::id":5,"contact_to::person_id":2,"contact_to::summary":"Telephone","contact_to::datetime":"12/01/2009 15:23:00","contact_to::description":"","contact_to::important":"","contact_to::way":4,"contact_to::kind":4,"modId":"0"}],"History":[]},"recordId":"2","modId":"0"},{"fieldData":{"id":3,"name":"Anyone","address":"Osaka, Japan","mail":"msyk@msyk.net","category":101,"checking":1,"location":202,"memo":""},"portalData":{"Contact":[{"recordId":"6","contact_to::id":6,"contact_to::person_id":3,"contact_to::summary":"Meeting","contact_to::datetime":"12/02/2009 15:23:00","contact_to::description":"","contact_to::important":1,"contact_to::way":4,"contact_to::kind":7,"modId":"0"},{"recordId":"7","contact_to::id":7,"contact_to::person_id":3,"contact_to::summary":"Mail etcsss","contact_to::datetime":"12/03/2009 15:23:00","contact_to::description":"aaaqq","contact_to::important":"","contact_to::way":5,"contact_to::kind":8,"modId":"4"}],"History":[]},"recordId":"3","modId":"6"}]},"messages":[{"code":"0","message":"OK"}]}',
                'curlerror' => '0',
                'curlerrormessage' => '',
                'curlinfo' => ['http_code' => 200]
            ],
            '2fd2ddb55e5f93862602b27185fe83d2f5e03e5c' => [ // Error Simulation
                'response' => '{"messages":[{"code":"105","message":"Layout is missing"}],"response":{}}',
                'curlerror' => '0',
                'curlerrormessage' => '',
                'curlinfo' => ['http_code' => 500]
            ],
            '9f4afb05c4cddcd7de774119976e9ab868633af1' => [// Error Simulation - illegal host name
                'response' => null,
                'curlerror' => 6,
                'curlerrormessage' => 'Could not resolve host: localserver123',
                'curlinfo' => ['http_code' => 0]
            ],
            '977673d8758a3ec9253cc7429259766bb60b8114' => [// Error Simulation - old version server
                'response' => null,
                'curlerror' => 0,
                'curlerrormessage' => '',
                'curlinfo' => ['http_code' => 404]
            ],
            'hash' => [
                'response' => '',
                'curlerror' => '0',
                'curlerrormessage' => '',
                'curlinfo' => ['http_code' => 200]
            ],
        ];
    }
}
