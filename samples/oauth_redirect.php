<?php
session_start();

var_export($_SESSION['oAuthRequestId']);
echo '<hr>';
var_export($_SESSION['privider']);
echo '<hr>';
var_export($_GET);
/*
 * array ( 'code' => '4/vQFaV_q6DesqQhdq99letzldYsbNAmEvVsygdgiLFOiEvP6v5vXiKdY2CrRGquCsPBVnOlzTCwhWNirgKUAEUrM',
 *  'scope' => 'email profile https://www.googleapis.com/auth/userinfo.profile openid https://www.googleapis.com/auth/userinfo.email',
 *  'authuser' => '0', 'session_state' => 'b751c57eee2b8f929df4f6780f19ef86a7984d13..9dab', 'prompt' => 'none', )
 */
echo '<hr>';
//var_export($_POST);


/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/04/24
 * Time: 17:41
 */
// First of all, the FMDataAPI.php file has to be included. All classes are defined in it.
include_once "../FMDataAPI.php";

// For your convenience, the main class name FMDataAPI is defined at the current namespace.
use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI as FMDataAPI;

// FMDataAPI class handles an error as an exception by default.
try {
    // Instantiate the class FMDataAPI with database name, user name, password and host.
    // Although the port number and protocol can be set in parameters of constructor,
    // these parameters can be omitted with default values.
    $fmdb = new FMDataAPI("TestDB", "msyk.nii83@gmail", "password", "homeserver.msyk.net");

    // You can turn off to throw an exception in case of error. You have to handle errors with checking result error.
    $fmdb->setThrowException(false);

    // If you call with true, the debug mode is activated. Debug mode echos the contents of communication
    // such as request and response.
    $fmdb->setDebug(true);

    // If you call with true, the certificate from the server is going to verify.
    // In case of self-signed one (usually default situation), you don't have to call this method.
    //$fmdb->setCertValidating(true);

    $fmdb->retrieveOAuth($_GET['code']);

    // The FMDataAPI has the property as the same name of layout. This sample database has the 'person_layout' layout,
    // so '$fmdb->person_layout' refers FMLayout object fo the proxy of the layout. FMLayout class has the 'query' method
    // and returns FileMakerRelation class's object. The condition spefied in parameter is same as FileMaker's Find Record API.
    $result = $fmdb->person_layout->query(/*array(array("id" => ">1"))*/);

    // The 'httpStatus()' method returns the HTTP status code in the latest response.
    echo "HTTP Status: {$fmdb->httpStatus()}<hr>";

    // The following two methods return the error code and message of the latest API call which is submitted in query() method.
    // You can check API calling succeed or fail if error code is or isn't 0 every after API calling methods.
    echo "Error Code: {$fmdb->errorCode()}<hr>";
    echo "Error Message: {$fmdb->errorMessage()}<hr>";

    // If the query is succeed, the following information can be detected.
    echo "Target Table: {$fmdb->getTargetTable()}<hr>";
    echo "Total Count: {$fmdb->getTotalCount()}<hr>";
    echo "Found Count: {$fmdb->getFoundCount()}<hr>";
    echo "Returned Count: {$fmdb->getReturnedCount()}<hr>";

    // The FileMakerRelation class implements the Iterator interface and it can repeat with 'foreach.'
    // The $record also refers a FileMakerRelation object but it is for single record.
    // This layout has fields as like 'id', 'name', 'mail' and so on, and the field name can be handle
    // as a property name of the the record referring with $record.
    if (!is_null($result)) {
        // If the query is succeed, the following information can be detected.
        echo "Target Table: {$result->getTargetTable()}<hr>";
        echo "Total Count: {$result->getTotalCount()}<hr>";
        echo "Found Count: {$result->getFoundCount()}<hr>";
        echo "Returned Count: {$result->getReturnedCount()}<hr>";
        foreach ($result as $record) {
            echo "id: {$record->id},";
            echo "name: {$record->name},";
            echo "mail: {$record->mail}<hr>";
            // If you named field name as not variable friendly, you can use field('field_name') method or
            // set the name to any variable such as $fname = 'field_name'; echo $record->$fname;.

            // A portal name property returns records of portal as FileMakerRelation object.
            $contacts = $record->contact_to;

            // If the query is succeed, the following information can be detected.
            var_dump($contacts);
            echo "Target Table: {$contacts->getTargetTable()}<hr>";
            echo "Total Count: {$contacts->getTotalCount()}<hr>";
            echo "Found Count: {$contacts->getFoundCount()}<hr>";
            echo "Returned Count: {$contacts->getReturnedCount()}<hr>";

            // You can repeat with foreach for the portal records.
            foreach ($contacts as $item) {
                // Technically portal field has to be refered as "contact_to::id" but it can be an indentifier in PHP.
                // In this case you can call field method as like 'field("summary", "contact_to").'
                // If the field belongs to the table occurrence for the portal, you can refer the field as like '$item->id.'
                // If the field belongs to another table occurrence, you have to call the 'field()' method.
                echo "[PORTAL(contact_to)] id: {$item->field("id", "contact_to")},";
                echo "summary: {$item->field("summary", "contact_to")}<hr>";
                // If the object name of the portal is blank, it can be referred as the table occurrence name.
                // If the object name is specified, you have to access with the object name and it means you have to
                // call 'field()' method to get the value.
            }
            echo "<hr>";
        }
        exit;
        // Move to pointer to the first record.
        $result->rewind();

        // The FileMakerRelation object from 'query()' method can be accessed as like the 'cursor' style repeating.
        // The 'count()' method returns the number of records in response. The variable $result referes current
        // record and you can get the field value with the propaty having the same field name.
        // The portal can be done with same way. The 'next()' method steps forward the pointer of current record.
        for ($i = 0; $i < $result->count(); $i++) {
            echo "id: {$result->id},";
            echo "name: {$result->name},";
            echo "mail: {$result->mail}<hr>";
            $contacts = $result->contact_to;

            for ($j = 0; $j < $contacts->count(); $j++) {
                echo "[PORTAL(contact_to)] id: {$contacts->field("id", "contact_to")},";
                echo "summary: {$contacts->field("summary", "contact_to")}<hr>";
                $contacts->next();
            }
            $result->next();
        }
    }
} catch (Exception $e) {
    echo '<div><h3>例外発生</h3>', $e->getMessage(), "<div>";
}
