# FMDataAPI Ver.33 [![Build Status](https://github.com/msyk/FMDataAPI/actions/workflows/php.yml/badge.svg)](https://github.com/msyk/FMDataAPI/actions/workflows/php.yml)

by Masayuki Nii (nii@msyk.net)

FMDataAPI is a class developed in PHP to access FileMaker database
with Claris FileMaker Data API.

## Contributers

They created pull requests. Thanks for cooperating.

- Atsushi Matsuo
- darnel
- Craig Smith
- Bernhard Schulz
- montaniasystemab
- Rickard Andersson
- Julien @AnnoyingTechnology
- Tom Kuijer
- Thijs Meijer
- Patrick Janser
- Roger Engström
- Stathis Askaridis
- filiptorphage-mjuk

## At a Glance

The FileMaker database named "TestDB.fmp12" is hosted on localhost, and
it sets the "fmrest" as access privilege. The account to connect with REST API is "web"
and "password." This database has the layout named "person_layout," and you
can use the layout name as a property of the FMDataAPI instance. The return
value of the "query" method is Iterator and can repeat in the foreach statement
with each record in the query result. This layout has the field named
"FamilyName" and "GivenName," and can use the field name as a property.

```
$fmdb = new FMDataAPI("TestDB", "web", "password");
$result = $fmdb->person_layout->query();
foreach ($result as $record) {
    echo "name: {$record->FamilyName}, {$record->GivenName}";
}
```

For more details, I'd like to read codes and comments in file samples/FMDataAPI_Sample.php.

API Document is here:
https://inter-mediator.info/FMDataAPI/packages/INTER-Mediator-FileMakerServer-RESTAPI.html
## What's This?

The FileMaker Data API is the new feature of FileMaker Server 16,
and it's the API with REST-based database operations.
Although the Custom Web Publishing is the way to access the database
for a long while, FileMaker Inc. has introduced the modern feature to operate
the database. The current version of FMDataAPI works on just FileMaker 18 and 19 platform.

For now, I'm focusing on developing the web application framework "INTER-Mediator"
(https://inter-mediator.com/ or https://github.com/INTER-Mediator/INTER-Mediator.git)
which can develop the core features of database-driven web application
with declarative descriptions. INTER-Mediator has already supported the Custom
Web Publishing with FX.php, and I develop codes here for support REST APIs.

Bug reports and contributions are welcome.

## Installing to Your Project

FMDataAPI has "composer.json," so you can add your composer.json file in your project as below.

```
...
"require": {
  ...
  "inter-mediator/fmdataapi":"33"
} ...
```

## About Files and Directories

- src/FMDataAPI.php
    - The core class, and you just use this for your application.
     This class and supporting classes are object-oriented REST API
     wrappers.
- src/Supporting/**.php
    - The supporting classes for the FMDataAPI class. Perhaps you don't need to create these classes, but you have to handle methods on them.
- composer.json, composer.lock
    - Composer information files.
- Sample_results.ipynb
    - Sample program and results with Jupyter Notebook style. Sorry for slight old version results.
- samples/FMDataAPI_Sample.php and cat.jpg
    - This is the sample program of FMDataAPI class, and shows how to
    use FMDataAPI class. It includes rich comments,
    but Sample_results.ipynb is more informative.
- README.md, .gitignore
    - These are for GitHub.
- test
    - Some files for unit testing.
- .github, docker-compose.yml
    - Files for GitHub Actions to run CI jobs.

## Licence

MIT License

## Acknoledgement

- Thanks to Atsushi Matsuo. Your script is quite helpful to implement the "localserver" feature.
(https://gist.github.com/matsuo/ef5cb7c98bb494d507731886883bcbc1) Moreover, thanks for updating and fixing bugs.
- Thanks to Frank Gonzalez. Your bug report is brilliant, and I could fix it quickly.
- Thanks to base64bits for coding about container field.
- Thanks to phpsa for bug fix.
- Thanks to Flexboom for bug fix.
- Thanks to schube for bug fix.
- Thanks to frankeg for bug fix.

## History

(Previous history is [here](samples/HISTORY.md))

- 2021-02-10: [Ver.22]
  Setting the timeout value about cURL. Thanks to @montaniasystemab. Also thanks to @AnnoyingTechnology for correcting.
- 2021-11-11: [Ver.23]
  File structure is updated for PSR-4. Thanks to tkuijer.
- 2021-12-23: [Ver.24]
  Bug fix for portal limit parameter. Thanks to tkuijer.
- 2022-03-24: [Ver.25]
  Add methods(getFirstRecord, getLastRecord, getRecords) to the FileMakerRelation class.
- 2022-03-26: [Ver.26]
  Add methods(setFieldHTMLEncoding, getFieldHTMLEncoding) to the FMDataAPI class.
  These are going to use for compatibility mode of FileMaker API for PHP.
- 2022-06-06: [Ver.27]
  Dropped the support of PHP5, the minimum version is PHP 7.1, but 7.2 or later is recommended.
- 2022-08-04: [Ver.28]
  Added the getContainerData(URL) method to the FMDataAPI class for accessing container data from the url containing /Streaming/MainDB.
  [BUG FIX] The FileMakerRelation class's toArray method didn't return an array (Thanks to Talwinder Singh).
- 2022-12-28: [Ver.29]
  Fixed the 'HTTP/2 stream 0 was not closed cleanly' problem with the new FileMaker (Thanks to @thijsmeijer).
  Also fixed the getPortalNames issue for single record relation (Thanks to @PGMMattias).
- 2023-06-20: [Ver.30]
  The toArray() method bug fixed. In same cases, it returned []. (Thanks to @PGMMattias).
- 2023-11-24: [Ver.31]
  The curlErrorMessage() method returns the error message from curl (Thanks to @P1-Roger).
  Corrected phpdoc issue (Thanks to @patacra).
- 2024-10-10: [Ver.32]
  From this version, the minimum PHP version is 8.1.
  Fix SSL certificate check errors by using the system's certificate authorities (Thanks to @patacra).
  FileMakerLayout::getMetadataOld and getMetadata methods don't return the false value in the case of log-in error.
  It returns just null.
- 2025-03-19: [Ver.33]
  The query method supports date format parameter (Thanks to @stathisaska).
  The debug property of the CommunicationProvider class initializes the bool false value (Thanks to Bernhard).
- 2025-xx-xx: [Ver.34]
  Bug fix in the sort parameter (Thanks to @filiptorphage-mjuk).