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
for ($i = 0 ; $i < $result->count(); $i++)    {
    echo "id: {$result->id},";
    echo "name: {$result->name},";
    echo "mail: {$result->mail}<hr>";
    $contacts = $result->contact_to;
    for ($j = 0 ; $j < $contacts->count(); $j++)    {
        echo "[PORTAL(contact_to)] id: {$contacts->field("id", "contact_to")},";
        echo "summary: {$contacts->field("summary", "contact_to")}<hr>";
        $contacts->next();
    }
    $result->next();
}
//$fm->postalcode->getRecord($recordId);
//$fm->postalcode->create($data);
//$fm->postalcode->delete($condition);
//$fm->postalcode->update($condition, $data);
