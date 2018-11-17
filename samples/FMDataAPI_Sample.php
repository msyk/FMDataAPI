<?php
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
    // Instanticate the class FMDataAPI with database name, user name, password and host.
    // Although the port number and protocol can be set in parameters of constractor,
    // these parameters can be omitted with default values.
    $fmdb = new FMDataAPI("TestDB", "web", "password", "localserver");

    //==============================
    //$fmdb = new FMDataAPI("TestDB", "web", "password", "localserver");
    // "localserver" is added on Ver.2 and it's a magic term for FMDataAPI. It happendes direct connect to
    // FileMaker Server in the same host. I've refered Atsushi Matsuo's script below and I got his way
    // to be able to connect port number 3000.
    // https://gist.github.com/matsuo/ef5cb7c98bb494d507731886883bcbc1
    //==============================

    // If you want to try this program just right now, it's convinient to download the FileMaker database file:
    // https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/TestDB.fmp12?raw=true

    // You can turn off to throw an exception in case of error. You have to handle errors with checking result error.
    $fmdb->setThrowException(false);

    // If you call with true, the debug mode is activated. Debug mode echos the contents of communication
    // such as request and response.
    $fmdb->setDebug(true);

    // If you call with true, the certificate from the server is going to verify.
    // In case of self-signed one (usually default situation), you don't have to call this method.
    //$fmdb->setCertValidating(true);

    // The metadata api call is undocumented thing.
    //$result = $fmdb->person_layout->getMetadata();

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

    // The FileMakerRelation class implements the Iterator interface and it can repeat with 'foreach.'
    // The $record also refers a FileMakerRelation object but it is for single record.
    // This layout has fields as like 'id', 'name', 'mail' and so on, and the field name can be handle
    // as a property name of the the record referring with $record.
    if (!is_null($result)) {
        foreach ($result as $record) {
            echo "id: {$record->id},";
            echo "name: {$record->name},";
            echo "mail: {$record->mail}<hr>";
            // If you named field name as not variable friendly, you can use field('field_name') method or
            // set the name to any variable such as $fname = 'field_name'; echo $record->$fname;.

            // A portal name property returns records of portal as FileMakerRelation object.
            $contacts = $record->Contact;

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
            $contacts = $result->Contact;
            for ($j = 0; $j < $contacts->count(); $j++) {
                echo "[PORTAL(contact_to)] id: {$contacts->field("id", "contact_to")},";
                echo "summary: {$contacts->field("summary", "contact_to")}<hr>";
                $contacts->next();
            }
            $result->next();
        }
    }
    // The 'create()' method creates a record with values in parameter.
    // The associated array of the parameter has to be a series of field name key and its value.
    $recId = $fmdb->postalcode->create(array("f3" => "field 3 data", "f7" => "field 7 data"));

    // The 'getRecord()' method query the record with the recordId of the parameter.
    // It returns the FileMakerRelation object and you can handle it with the return value from 'query()' method.
    $result = $fmdb->postalcode->getRecord($recId);
    if (!is_null($result)) {
        foreach ($result as $record) {
            echo "f3: {$record->f3},";
            echo "f7: {$record->f7},";
            echo "f8: {$record->f8}<hr>";
            echo "<hr>";
        }
    }

    // The 'update()' method modifies fields in a record. You have to set parameters as the recordId of target
    // record and associated array to specify the modified data.
    $fmdb->postalcode->update($recId, array("f3" => "field 3 modifed", "f8" => "field 8 update"));
    $result = $fmdb->postalcode->getRecord($recId);
    if (!is_null($result)) {
        foreach ($result as $record) {
            echo "f3: {$record->f3},";
            echo "f7: {$record->f7},";
            echo "f8: {$record->f8}<hr>";
            echo "<hr>";
        }
    }
    // The 'delete()' method deletes the record specified by the parameter.
    $fmdb->postalcode->delete($recId);

    // Call script
    $result = $fmdb->person_layout->query(null, null, -1, 1, null, ["script" => "TestScript", "script.param" => "ok"]);
    if (!is_null($result)) {
        echo "Script Error: {$fmdb->person_layout->getScriptError()}<hr>";
        echo "Script Result: {$fmdb->person_layout->getScriptResult()}<hr>";
    }
    $result = $fmdb->person_layout->query(null, null, -1, 1, null, ["script.prerequest" => "TestScript", "script.prerequest.param" => "ok"]);
    if (!is_null($result)) {
        echo "Script Error: {$fmdb->person_layout->getScriptErrorPrerequest()}<hr>";
        echo "Script Result: {$fmdb->person_layout->getScriptResultPrerequest()}<hr>";
    }
    $result = $fmdb->person_layout->query(null, null, -1, 1, null, ["script" => "TestScript", "script.param" => "not"]);
    if (!is_null($result)) {
        echo "Script Error: {$fmdb->person_layout->getScriptError()}<hr>";
        echo "Script Result: {$fmdb->person_layout->getScriptResult()}<hr>";
    }
    $result = $fmdb->person_layout->query(null, null, -1, 1);
    if (!is_null($result)) {
        echo "Script Error: {$fmdb->person_layout->getScriptError()}<hr>";
        echo "Script Result: {$fmdb->person_layout->getScriptResult()}<hr>";
    }
    // A new record is created in "testtable" table.
    $recId = $fmdb->testtable->create();
    // The "testtable" table has a container filed "vc1". One image file is going to be uploaded to it.
    // The file path, record id and field name are required.
    $fmdb->testtable->uploadFile("cat.jpg", $recId, "vc1");
    // What kind of data does the container field which inserted an image return?
    // For example, the returned value was like this:
    // https://localhost/Streaming_SSL/MainDB/6A4A253F7CE33465DCDFBFF0704B34C0993D54AD85702396920E85249BD0271A.jpg?RCType=EmbeddedRCFileProcessor
    // This url can get the content of the container field, and it means you can download with file_put_content() function and so on.
    $result = $fmdb->testtable->getRecord($recId);
    if(!is_null($result)) {
        foreach ($result as $record) {
            echo "vc1: {$record->vc1}<hr>";
            echo "<p><img src='data:image/jpeg;base64," . $record->getContainerData('vc1') . "'></p>";
        }
    }
    // If you call the 'startCommunication()' method, you can describe a series of database operation
    // calls. This means the authentication is going to be done at the 'startCommunication()' method,
    // and the token is going to be shared with following statements. The 'endCommunication()' calls
    // logout REST API call and invalidate the shared token.
    $recIds = array();
    $fmdb->postalcode->startCommunication();
    $recIds[] = $fmdb->postalcode->create(array("f3" => "field 3 data 1", "f7" => "field 7 data"));
    $recIds[] = $fmdb->postalcode->create(array("f3" => "field 3 data 2", "f7" => "field 7 data"));
    $recIds[] = $fmdb->postalcode->create(array("f3" => "field 3 data 3", "f7" => "field 7 data"));
    $recIds[] = $fmdb->postalcode->create(array("f3" => "field 3 data 4", "f7" => "field 7 data"));
    $recIds[] = $fmdb->postalcode->create(array("f3" => "field 3 data 5", "f7" => "field 7 data"));
    $recIds[] = $fmdb->postalcode->create(array("f3" => "field 3 data 6", "f7" => "field 7 data"));
    $recIds[] = $fmdb->postalcode->create(array("f3" => "field 3 data 7", "f7" => "field 7 data"));
    $fmdb->postalcode->endCommunication();
    var_export($recIds);
    echo "<hr>";

    // The 'query()' method can have several parameters. The portal specification has to be an array
    // with the object name of the portal not the table occurrence name.
    $portal = array("Contact");
    $result = $fmdb->person_layout->query(array(array("id" => "1")), null, 1, -1, $portal);
    if(!is_null($result)) {
        foreach ($result as $record) {
            $recordId = $record->getRecordId();
            $partialResult = $fmdb->person_layout->getRecord($recordId, $portal);
            var_export($partialResult);
            echo "<hr>";
        }
    }
    // The 'query()' method can have several parameters. The second parameter is for sorting.
    $portal = array("Contact");
    $result = $fmdb->person_layout->query(array(array("id" => "1...")), array(array("id", "descend")), 1, -1, $portal);
    if(!is_null($result)) {
        foreach ($result as $record) {
            $recordId = $record->getRecordId();
            $partialResult = $fmdb->person_layout->getRecord($recordId, $portal);
            var_export($partialResult);
            echo "<hr>";
        }
    }
    // The 'query()' method can have several parameters. The second parameter is for sorting.
    $result = $fmdb->person_layout->query(null, null, 2, 2);
    if(!is_null($result)) {
        foreach ($result as $record) {
            $recordId = $record->getRecordId();
            $partialResult = $fmdb->person_layout->getRecord($recordId, $portal);
            var_export($partialResult);
            echo "<hr>";
        }
    }
} catch (Exception $e) {
    echo '<div><h3>例外発生</h3>', $e->getMessage(), "<div>";
}
