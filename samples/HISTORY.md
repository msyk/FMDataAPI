# FMDataAPI's previous history 

- April 2017: Start to create these classes and codes.
- 2017-05-05: README.md added.
- 2017-05-26: [Ver.2] Support the "localserver" as host name.
- 2017-05-31: [Ver.3] The query() method of FileMakerLayout class fixed.
  'Offset' and 'range' parameters could not set as an integer value.
- 2017-11-06: [Ver.4] The getFieldNames() and getPortalNames() methods added.
- 2018-02-03: [Ver.5] Bug fix of sorting parameters in query method.
- 2018-02-18: [Ver.6] Bug fix of creating record with no default value.
- 2018-03-25: [Ver.7] getSessionToken method added. OAuth handling implemented but not well debugged.
- 2018-05-09: The Version 7 is the last version which supports FileMaker 16-based Data API.
- 2018-05-15: [Ver.8] Update for FileMaker 17. FileMaker Data API v1 is supported from this version.
  The preview version of FileMaker Data API doesn't support anymore.
- 2018-05-27: [Ver.9] composer.json is added, and can install "inter-mediator/fmdataapi".
  FMDataAPITrial directory deleted because it's already discontinued api.
  Add the "samples" directory and move sample files into it.
- 2018-06-22: [Ver.10] Added the getContainerData method (Thanks to base64bits!),
  bug fix (Thanks to phpsa!).
- 2018-07-22: [Ver.11] Global field methods bug fixed and were available in FMDataAPI class (Tanks to Mr.Matsuo).
  The script errors and results can get from methods in FMLayout class.
- 2018-07-29: [Ver.12] Bug fix for UUID Supporting (Thanks to Mr.Matsuo).
  Unit tests implemented but now for limited methods, als integrating Travis CI.
- 2018-11-13: [Ver.13]
  Added getDebugInfo method (Thanks to Mr.Matsuo),
  modified and fixed the getFieldNames method (Thanks to phpsa),
  fixed handling portal object name (Thanks to Mr.Matsuo)
  fixed the getModId method (Thanks to Flexboom)
- 2018-11-17: [Ver.15]
  Jupyter Notebook style sample and results.
- 2019-05-19: [Ver.16]
  This is the final version for FileMaker 17 platform, and bug fix (Thanks to darnel)
- 2019-05-20: [Ver.17]
  Support the FileMaker 18 platform.
  Add getMetadataOld() and getMetadata() to FileMakerLayout class.
  Add getProductInfo(), getDatabaseNames(), getLayoutNames() and getScriptNames() to FMDataAPI class.
- 2019-05-27: [Ver.18]
  Add getTargetTable(), getTotalCount(), getFoundCount(), getReturnedCount() to FileMakerRelation class.
  Add getTargetTable(), getTotalCount(), getFoundCount(), getReturnedCount() to FMDataAPI class.
- 2019-09-12: [Ver.19]
  Add the duplicate() method to the FileMakerLayout class. Thanks to schube.
- 2019-09-16: [Ver.20]
  The default values  of limit and range parameters changed to 0 and both just applied for over 0 values. Thanks to schube.
- 2020-08-23: [Ver.21]
  Bug fix about the field referencing of a related field without any portals. Thanks to frankeg.
  Checked on the FileMaker Server 19.

(History of recent date is [here](../README.md))

## API Differences between ver.8 and 7.
### FMDataAPI class
The setAPIVersion method added. This is for a future update of FileMaker Data API.
As far as FMDataAPI Ver.8 goes, This isn't required.
- public function __construct($solution, $user, $password, $host = NULL, $port = NULL, $protocol = NULL, [New]$fmDataSource = null)
- [New]public function setAPIVersion($vNum)

### FileMakerRelation class
The following methods added to script parameters. See the query method's document for specifying it.
Twe methods added to portal parameter.

- public function query($condition = NULL, $sort = NULL, $offset = -1, $range = -1, $portal = null, [New]$script = null)
- public function getRecord($recordId, $portal = null, [New]$script = null)
- public function create($data = null, [New]$portal = null, [New]$script = null)
- public function delete($recordId, [New]$script = null)
- public function update($recordId, $data, $modId = -1, [New]$portal = null, [New]$script = null)
- [New]public function uploadFile($filePath, $recordId, $containerFieldName, $containerFieldRepetition = null, $fileName = null)
