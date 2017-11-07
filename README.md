# FMDataAPI Ver.4

by Masayuki Nii (nii@msyk.net)

FMDataAPI is a class developed by PHP to access FileMaker database 
with FileMaker Data API.

## At a Glance

The FileMaker database named "TestDB.fmp12" is hosted on localhost, and
it set the "fmrest" as access privilege. The account to connect with REST API is "web"
and "password". This database has the layout named "person_layout" and you
can use the layout name as a property of the FMDataAPI instance. The return
value of the "query" method is Iterator and can repeat in foreach statement
with each record in the query result. This layout has the field named 
"FamilyName" and "GivenName," and also can use the field name as a property.

```
$fmdb = new FMDataAPI("TestDB", "web", "password");
$result = $fmdb->person_layout->query();
foreach ($result as $record) {
    echo "name: {$record->FamilyName}, {$record->GivenName}";
}
```

For more deitals, I'd like to read codes and comments in file FMDataAPI_Sample.php.

API Document is here:
http://inter-mediator.info/FMDataAPI/namespaces/INTERMediator.FileMakerServer.RESTAPI.html

## What's This?

The FileMaker Data API is the new feature of FileMaker Server 16,
and it's a API with REST-based database operations.
Although the Custom Web Publishing is the way to access the database 
for a long while, FileMaker Inc. has introduced modern feature to operate
database.

For now I'm focusing to develop the web application framework "INTER-Mediator"
(https://inter-mediator.org/ or https://github.com/INTER-Mediator/INTER-Mediator.git)
which can develop the core features of database-driven web application 
with declarative descriptions. INTER-Mediator has already supported the Custom
Web Publishing with FX.php, and I develop codes here for support REST APIs.

Bug reports and contribution are welcome.

## About Files and Directories

- FMDataAPI.php
    - The core class, and you just use this for your application.
     This class and inner private classes are object-oriented REST API
     wrappers.
- FMDataAPI_Sample.php
    - This is the sample program of FMDataAPI class, and shows how to
    use FMDataAPI class. Rich comments are described.
- FMDataAPITrial and files in it
    - This directory contains just my "trial" for newly released
    File Maker's Rest APIs to understand how to handle these APIs.
    All explanations are described in "index.html" file in it.
- README.md, .gitignore
    - These are for GitHub.

## What's Finished and To Do

### Already Finished

- Understand the FileMaker's REST API for me.
- Support all API calls in FileMaker Data API.

### What's To Do

- Create unit test suite.
- Check any layout and field names without variable friendly name.
- OAuth integrations.
- Error handling for wider cases with exception based.
- Documentation is always issue to do.

## Licence

MIT License

## Acknoledgement

- Thanks to Atsushi Matsuo. Your script is quite helpful to implement the "localserver" feature.
(https://gist.github.com/matsuo/ef5cb7c98bb494d507731886883bcbc1) Moreover thanks for updating and fixing bugs.
- Thanks to Frank Gonzalez. Your bug report is brilliant and I could fix it quickly.

## History

- April 2017: Start to create these classes and codes.
- 2017-05-05: Described README.md
- 2017-05-26: [Ver.2] Support the "localserver" as host name.
- 2017-05-31: [Ver.3] The query() method of FileMakerLayout class is fixed.
'Offset' and 'range' parameters could not set as an integer value.
- 2017-11-06: [Ver.4] The getFieldNames() and getPortalNames() methods are added.
