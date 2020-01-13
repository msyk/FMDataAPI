<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/04/24
 * Time: 17:41
 */
session_start();
unset($_SESSION['oAuthRequestId']);
unset($_SESSION['privider']);
$headerHTML = '';
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

    // If you call with true, the certificate from the server is going to verify.
    // In case of self-signed one (usually default situation), you don't have to call this method.
    //$fmdb->setCertValidating(true);

    // Don't call "$fmdb->setDebug(true);".
//     $fmdb->setDebug(true);
    $redirectTo = "https://homeserver.msyk.net/FMDataAPI/samples/oauth_redirect.php";
    $moveTo = $fmdb->useOAuth("Google", $redirectTo);
//    echo $moveTo;
    $headerHTML .= "<script>location.href='{$moveTo}';</script>";
} catch (Exception $e) {
    echo '<div><h3>例外発生</h3>', $e->getMessage(), "<div>";
}
?>
<html>
<head>
<?php echo $headerHTML; ?>
</head>
<body>
</body>
</html>
