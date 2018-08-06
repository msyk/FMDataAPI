<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2018/08/06
 * Time: 13:25
 */

echo sha1(json_encode(
    [   // Login
        'url' => "https://localhost:443/fmi/data/vLatest/databases/TestDB/sessions",
        'method' => 'post',
        'header' => [0 => 'Content-Type: application/json', 1 => 'Authorization: Basic d2ViOnBhc3N3b3Jk'],
        'request' => ['fmDataSource' => true]
    ]
)), "\n";

echo sha1(json_encode(
    [   //Logout
        'url' => "https://localhost:443/fmi/data/vLatest/databases/TestDB/sessions/1f3c9bd128ef29e97b2d7fd941df4a88198bd8b5eb9aa69c4",
        'method' => 'get',
        'header' => ['Authorization: Bearer 1f3c9bd128ef29e97b2d7fd941df4a88198bd8b5eb9aa69c4'],
        'request' => null
    ]
)), "\n";

echo sha1(json_encode(
    [   // Simple Query
        'url' => "https://localhost:443/fmi/data/vLatest/databases/TestDB/layouts/person_layout/records",
        'method' => 'get',
        'header' => ['Authorization: Bearer 1f3c9bd128ef29e97b2d7fd941df4a88198bd8b5eb9aa69c4', 'Content-Type: application/json'],
        'request' => []
    ]
)), "\n";

echo sha1(json_encode(
    [   // Error Simulation
        'url' => "https://localhost:443/fmi/data/vLatest/databases/TestDB/layouts/person_layout2/records",
        'method' => 'get',
        'header' => ['Authorization: Bearer 1f3c9bd128ef29e97b2d7fd941df4a88198bd8b5eb9aa69c4', 'Content-Type: application/json'],
        'request' => []
    ]
)), "\n";

echo sha1(json_encode(
    [   // Error Simulation - illegal host name
        'url' => "https://localserver123:443/fmi/data/vLatest/databases/TestDB/sessions",
        'method' => 'get',
        'header' => [0 => 'Content-Type: application/json', 1 => 'Authorization: Basic d2ViOnBhc3N3b3Jk'],
        'request' => []
    ]
)), "\n";

echo sha1(json_encode(
    [   // Error Simulation - Old version server
        'url' => "https://10.0.1.21:443/fmi/data/vLatest/databases/TestDB/sessions",
        'method' => 'get',
        'header' => [0 => 'Content-Type: application/json', 1 => 'Authorization: Basic d2ViOnBhc3N3b3Jk'],
        'request' => []
    ]
)), "\n";

