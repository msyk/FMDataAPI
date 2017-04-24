<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/04/10
 * Time: 18:42
 */
include_once "lib.php";

$host = targetHost();

// Authentication
$result = callAPI(
    "https://{$host}/fmi/rest/api/auth/TestDB",
    null,
    json_encode(array(
            "user" => "web",
            "password" => "password",
            "layout" => "person_layout",)
    ),
    "POST");
$responseJSON = resultOutput($result);

// Query
if ($responseJSON->errorCode != 0) {
    echo "Authentication Error: {$responseJSON->errorCode}";
} else {
    $result = callAPI(
        "https://{$host}/fmi/rest/api/find/TestDB/person_layout",
        array("FM-Data-token: {$responseJSON->token}"),
        json_encode(array(
            "query" => array(
                array("id" => "=2"),
                array("id" => "=3"),
            ),
        )),
        "POST");
    resultOutput($result);
}
