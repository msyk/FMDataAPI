<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2018/07/29
 * Time: 1:51
 */

namespace INTERMediator\FileMakerServer\RESTAPI\Supporting;

class TestProvider extends CommunicationProvider {

    /**
     * Override communication method.
     * @param $params
     * @param $isAddToken
     * @param string $method
     * @param null $request
     * @param null $addHeader
     */
    public function callRestAPI($params, $isAddToken, $method = 'GET', $request = NULL, $addHeader = null)
    {

    }

    /**
     * Override communication method.
     * @param $url
     */
    public function accessToContainer($url)
    {

    }
}