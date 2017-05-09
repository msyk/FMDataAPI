<?php

function targetHost()
{
    //return "127.0.0.1";
    return "192.168.56.1";
}

function callAPI($url, $headerAdding, $request = NULL, $method = 'GET')
{
    $header = array("Content-Type: application/json",);
    if (is_array($headerAdding)) {
        foreach ($headerAdding as $element) {
            $header[] = $element;
        }
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
    if (strtolower($method) == 'post') {
        curl_setopt($ch, CURLOPT_POST, 1);
    } else if (strtolower($method) == 'put') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    } else if (strtolower($method) == 'delete') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    } else  {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $errornumber = curl_errno($ch);
    if ($errornumber) {
        $error = curl_error($ch);
    }
    curl_close($ch);

    return array(
        $method, $url, $header, $request, $errornumber, $error, $info,
        substr($response, 0, $info["header_size"]),
        substr($response, $info["header_size"]),
    );
}

function resultOutput($result)
{
    list($method, $url, $requestHeader, $request, $errornumber,
        $error, $info, $responseHeader, $body) = $result;

    echo "<hr style='height:2px; border:0; background-color: black;'><a href='index.html'>Return to Menu</a>";
    echo "<hr>URL:<br>";
    echo $method . ' ' . htmlspecialchars($url);
    echo "<hr>Request Header:<br><pre>";
    echo htmlspecialchars(var_export($requestHeader, true));
    echo "<hr>Request Body:<br><pre>";
    echo htmlspecialchars($request);
    echo "</pre><hr>Response Header:<br><pre>";
    echo htmlspecialchars($responseHeader);
    echo "</pre><hr>Response Body:<br><pre>";
    $responseJSON = json_decode($body);
    if (is_null($responseJSON)) {
        echo htmlspecialchars($body);
    } else {
        echo htmlspecialchars(json_encode($responseJSON, JSON_PRETTY_PRINT));
    }
    echo "</pre><hr>Info:<br><pre>";
    var_export($info);
    echo "</pre><hr>ErrorNumber:<br><pre>";
    var_export($errornumber);
    echo "</pre><hr>Error:<br>";
    echo htmlspecialchars($error);

    return json_decode($body);
}