<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/04/10
 * Time: 18:42
 */
include_once "lib.php";

$host = targetHost();
$result = callAPI(
    "http://{$host}/fmi/rest/api/auth/TestDB",
    null,
    json_encode(array(
        "user" => "web",
        "password" => "password",
        "layout" => "postalcode",
    )),
    "POST");
resultOutput($result);
