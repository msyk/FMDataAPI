<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2018/07/29
 * Time: 2:02
 */

namespace INTERMediator\FileMakerServer\RESTAPI;

use PHPUnit\Framework\TestCase;

class FMDataAPIUnitTest extends TestCase
{
    private $fmdataapi;

    public function setUp()
    {
        $this->fmdataapi = new FMDataAPI("TestDB", "web", "password", "localhost", "443", "https", true, true);
    }

    public function test_initializeObjects()
    {
        $this->assertNotNull($this->fmdataapi, 'FMDataAPI class must be instanticate.');
        $this->assertEquals($this->fmdataapi->errorCode(), 0, 'It must be no error before calling.');
        $this->assertEquals($this->fmdataapi->errorMessage(), "", 'It must be no error before calling.');
        $this->assertEquals($this->fmdataapi->httpStatus(), 0, 'It must be no status before calling.');
    }

    public function test_Query()
    {
        $result = $this->fmdataapi->person_layout->query();
        $this->assertNotNull($result, 'Returned something.');
        $this->assertEquals($result->count(), 3, 'Checking the record number.');
        $counter = 0;
        foreach ($result as $record) {
            $contacts = $record->Contact;
            if ($counter === 0) {
                $this->assertEquals($record->id, 1, 'Field value has to match with defined value.');
                $this->assertEquals($record->name, 'Masayuki Nii', 'Field value has to match with defined value.');
                $this->assertEquals($record->mail, 'msyk@msyk.net', 'Field value has to match with defined value.');
                $pcounter = 0;
                $this->assertEquals($contacts->count(), 3, 'Checking the record number.');
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
            }
            $counter += 1;
        }
    }

    public function test_ErrorQuery()
    {
        $result = $this->fmdataapi->person_layout2->query();    // The layout 'person_layout2' doesn't exist.
        $this->assertNull($result, 'No results returns.');
        $this->assertEquals($this->fmdataapi->httpStatus(), 500, 'Returns 500 for http status.');
        $this->assertEquals($this->fmdataapi->errorCode(), 105, 'The error code has to be 105.');
    }
}