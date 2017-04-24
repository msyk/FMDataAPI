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
$fmToken = $responseJSON->token;

if ($responseJSON->errorCode != 0) {
    echo "Authentication Error: {$responseJSON->errorCode}";
} else {
    // Create Record
    $result = callAPI(
        "https://{$host}/fmi/rest/api/record/TestDB/person_layout",
        array("FM-Data-token: {$fmToken}"),
        json_encode(array(
            "data" => array("name" => "西田東出", "mail" => "test@msyk.net"),
        )),
        "POST");
    $responseJSON = resultOutput($result);
    $recordId = $responseJSON->recordId;
    // Edit Record
    $result = callAPI(
        "https://{$host}/fmi/rest/api/record/TestDB/person_layout/{$recordId}",
        array("FM-Data-token: {$fmToken}"),
        json_encode(array(
            "data" => array("memo" => "メモです")
        )),
        "PUT");
    resultOutput($result);
    // Query
    $result = callAPI(
        "https://{$host}/fmi/rest/api/record/TestDB/person_layout/{$recordId}",
        array("FM-Data-token: {$fmToken}"));
    resultOutput($result);
}

