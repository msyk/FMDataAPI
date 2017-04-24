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
    "https://{$host}/fmi/rest/api/auth/TestDB",
    null,
    json_encode(array(
        "user" => "web",
        "password" => "password",
        "layout" => "person_layout",
    )),
    "POST");
resultOutput($result);
