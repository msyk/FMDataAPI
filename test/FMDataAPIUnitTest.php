<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2018/07/29
 * Time: 2:02
 */

namespace INTERMediator\FileMakerServer\RESTAPI;

use \PHPUnit\Framework\TestCase;

class FMDataAPIUnitTest extends TestCase
{
    private $fmdataapi;

    public function setUp(): void
    {
        $this->fmdataapi = new FMDataAPI("TestDB", "web", "password",
            "localhost", "443", "https", null, true);
    }

    public function test_initializeObjects()
    {
        $this->assertNotNull($this->fmdataapi, 'FMDataAPI class must be instanticate.');
        $this->assertEquals($this->fmdataapi->errorCode(), -1, 'It must be no error before calling.');
        $this->assertEquals($this->fmdataapi->errorMessage(), "", 'It must be no error before calling.');
        $this->assertEquals($this->fmdataapi->httpStatus(), 0, 'It must be no status before calling.');
    }

    public function test_Query()
    {
        $result = $this->fmdataapi->person_layout->query();
        $this->assertNotNull($result, 'Returned something.');
        $this->assertEquals($result->count(), 3, 'Checking the record number.');
        $this->assertEquals($result->getTargetTable(), 'person_to', 'Checking the table occurrence name.');
        $this->assertEquals($result->getTotalCount(), 3, 'Checking the total record number.');
        $this->assertEquals($result->getFoundCount(), 3, 'Checking the found record number.');
        $this->assertEquals($result->getReturnedCount(), 3, 'Checking the returned record number.');
        $this->assertEquals($result->getPortalNames(), ['Contact', 'History'], 'The query result returns portal names.');

        $counter = 0;
        foreach ($result as $record) {
            $contacts = $record->Contact;
            if ($counter === 0) {
                $this->assertEquals($record->id, 1, 'Field value has to match with defined value.');
                $this->assertEquals($record->name, 'Masayuki Nii', 'Field value has to match with defined value.');
                $this->assertEquals($record->mail, 'msyk@msyk.net', 'Field value has to match with defined value.');

                $this->assertEquals($record->getModId(), 6, 'It has ModID.');
                $this->assertEquals($record->getRecordId(), 1, 'It has RecordID.');
                $pcounter = 0;
                $this->assertEquals($contacts->count(), 3, 'Checking the record number.');
                $this->assertEquals($contacts->getTargetTable(), 'contact_to', 'Checking the table occurrence name.');
                $this->assertNull($contacts->getTotalCount(), 'Checking NULL as the total record number.');
                $this->assertEquals($contacts->getFoundCount(), 3, 'Checking the found record number.');
                $this->assertEquals($contacts->getReturnedCount(), 3, 'Checking the returned record number.');

                foreach ($contacts as $item) {
                    $item->setPortalName("contact_to");
                    if ($pcounter === 0) {
                        $this->assertEquals($item->field("datetime"), '12/01/2009 15:23:00', 'Portal field value has to match with defined value.');
                    } else if ($pcounter === 1) {
                        $this->assertEquals($item->field("datetime"), '12/02/2009 15:23:00', 'Portal field value has to match with defined value.');
                    } else if ($pcounter === 2) {
                        $this->assertEquals($item->field("datetime"), '12/03/2009 15:23:00', 'Portal field value has to match with defined value.');
                    }
                    $pcounter += 1;
                }
                $this->assertEquals($pcounter, 3, 'Cheking the record number in portal.');
            } else if ($counter === 1) {
                $this->assertEquals($record->id, 2, '');
                $this->assertEquals($record->name, 'Someone', 'Field value has to match with defined value.');
                $this->assertEquals($record->mail, 'msyk@msyk.net', 'Field value has to match with defined value.');
                $pcounter = 0;
                $this->assertEquals($contacts->count(), 2, 'Checking the record number.');
                foreach ($contacts as $item) {
                    if ($pcounter === 0) {
                        $this->assertEquals($item->field("datetime", "contact_to"), '12/04/2009 15:23:00', 'Portal field value has to match with defined value.');
                    } else if ($pcounter === 1) {
                        $this->assertEquals($item->field("datetime", "contact_to"), '12/01/2009 15:23:00', 'Portal field value has to match with defined value.');
                    }
                    $pcounter += 1;
                }
                $this->assertEquals($pcounter, 2, 'Cheking the record number in portal.');
            } else if ($counter === 2) {
                $this->assertEquals($record->id, 3, 'Field value has to match with defined value.');
                $this->assertEquals($record->name, 'Anyone', 'Field value has to match with defined value.');
                $this->assertEquals($record->mail, 'msyk@msyk.net', 'Field value has to match with defined value.');
                $pcounter = 0;
                $this->assertEquals($contacts->count(), 2, 'Checking the record number.');
                foreach ($contacts as $item) {
                    if ($pcounter === 0) {
                        $this->assertEquals($item->field("datetime", "contact_to"), '12/02/2009 15:23:00', 'Portal field value has to match with defined value.');
                    } else if ($pcounter === 1) {
                        $this->assertEquals($item->field("datetime", "contact_to"), '12/03/2009 15:23:00', 'Portal field value has to match with defined value.');
                    }
                    $pcounter += 1;
                }
                $this->assertEquals($pcounter, 2, 'Cheking the record number in portal.');

                $recId = $record->getRecordId();
                $this->assertEquals($recId, 333, 'The record id of last record must be 333.');
            }
            $counter += 1;
        }
    }

    public function test_ErrorQuery()
    {
        $fm = new FMDataAPI("TestDB", "web", "password", "localserver123",
            "443", "https", null, true);
        $result = $fm->person_layout->query();    // Host name is DNS unaware.
        $this->assertNull($result, 'No results returns.');
        $this->assertEquals($fm->httpStatus(), 0, 'Returns 0 for http status.');
        $this->assertEquals($fm->errorCode(), -1, 'The error code has to be -1.');
//        $this->assertEquals($fm->curlErrorCode(), 6, 'The error code has to be 6.');
    }

    public function test_SingleRecord()
    {
        $result = $this->fmdataapi->person_layout->query();
        $record = $result->getFirstRecord();
        $this->assertEquals($record->id, 1, 'Field value has to match with defined value.');
        $this->assertEquals($record->name, 'Masayuki Nii', 'Field value has to match with defined value.');
        $this->assertEquals($record->mail, 'msyk@msyk.net', 'Field value has to match with defined value.');
        $pcounter = 0;
        $contacts = $record->Contact;
        $this->assertEquals($contacts->count(), 3, 'Checking the record number.');
        $this->assertEquals($contacts->getTargetTable(), 'contact_to', 'Checking the table occurrence name.');
        $this->assertNull($contacts->getTotalCount(), 'Checking NULL as the total record number.');
        $this->assertEquals($contacts->getFoundCount(), 3, 'Checking the found record number.');
        $this->assertEquals($contacts->getReturnedCount(), 3, 'Checking the returned record number.');

        foreach ($contacts as $item) {
            if ($pcounter === 0) {
                $this->assertEquals($item->field("datetime", "contact_to"), '12/01/2009 15:23:00', 'Portal field value has to match with defined value.');
            } else if ($pcounter === 1) {
                $this->assertEquals($item->field("datetime", "contact_to"), '12/02/2009 15:23:00', 'Portal field value has to match with defined value.');
            } else if ($pcounter === 2) {
                $this->assertEquals($item->field("datetime", "contact_to"), '12/03/2009 15:23:00', 'Portal field value has to match with defined value.');
            }
            $pcounter += 1;
        }
        $this->assertEquals($pcounter, 3, 'Cheking the record number in portal.');

        $this->assertEquals($record->count(), 1, 'The single record is just one record.');
        $currentRecord = $record->current();
        $this->assertEquals($currentRecord->id, 1, 'The single record can call current and return a Relation.');
        $this->assertEquals($record->getPortalNames(), ['Contact', 'History'], 'The single record returns portal names.');
        $this->assertEquals($record->getModId(), 6, 'The single record returns ModID.');
        $this->assertEquals($record->getRecordId(), 1, 'The single record returns RecordID.');
        $this->assertEquals($record->getTargetTable(),  'person_to', 'The single record returns the target table.');

        $pcount = 0;
        foreach ($record as $item) {
            $this->assertEquals($item->id, 1, 'The single record can iterate.');
            $pcount++;
        }
        $this->assertEquals($pcount, 1, 'The single record has to repeat just once.');
        $this->assertEquals($record->getTotalCount(), 3,'Checking the total record number for queried data.');
        $this->assertEquals($record->getFoundCount(), 3, 'Checking the found record number for queried data.');
        $this->assertEquals($record->getReturnedCount(), 1, 'Checking the returned record number.');

    }
}
