<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/04/24
 * Time: 17:41
 */
include_once "FMDataAPI.php";

$fmdb = new FMDataAPI("TestDB", "web", "password", "192.168.56.1");
$fmdb->setDebug(true);
//$fm->setCertValidating(true);

$result = $fmdb->person_layout->query(/*array(array("id" => ">1"))*/);
echo "HTTP Status: {$fmdb->httpStatus()}<hr>";
foreach ($result as $record) {
    echo "id: {$record->id},";
    echo "name: {$record->name},";
    echo "mail: {$record->mail}<hr>";
    $contacts = $record->contact_to;
    foreach ($contacts as $item) {
        echo "[PORTAL(contact_to)] id: {$item->field("id", "contact_to")},";
        echo "summary: {$item->field("summary", "contact_to")}<hr>";
    }
    echo "<hr>";
}
$result->rewind();
for ($i = 0; $i < $result->count(); $i++) {
    echo "id: {$result->id},";
    echo "name: {$result->name},";
    echo "mail: {$result->mail}<hr>";
    $contacts = $result->contact_to;
    for ($j = 0; $j < $contacts->count(); $j++) {
        echo "[PORTAL(contact_to)] id: {$contacts->field("id", "contact_to")},";
        echo "summary: {$contacts->summary}<hr>";
        $contacts->next();
    }
    $result->next();
}
$recId = $fmdb->postalcode->create(array("f3" => "field 3 data", "f7" => "field 7 data"));
$result = $fmdb->postalcode->getRecord($recId);
foreach ($result as $record) {
    echo "f3: {$record->f3},";
    echo "f4: {$record->f4},";
    echo "f5: {$record->f5}<hr>";
    echo "<hr>";
}
$fmdb->postalcode->update($recId, array("f3" => "field 3 modifed", "f8" => "field 8 update"));
$result = $fmdb->postalcode->getRecord($recId);
foreach ($result as $record) {
    echo "f3: {$record->f3},";
    echo "f4: {$record->f4},";
    echo "f5: {$record->f5}<hr>";
    echo "<hr>";
}
//$fmdb->postalcode->delete($recId);

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

$portal = array("contact_to");
$result = $fmdb->person_layout->query(array(array("id" => "1")), null, 0, 1000000, null);
foreach ($result as $record) {
    $recordId = $record->getRecordId();
    $partialResult = $fmdb->person_layout->getRecord($recordId, $portal);
    var_export($partialResult);
    echo "<hr>";
}
