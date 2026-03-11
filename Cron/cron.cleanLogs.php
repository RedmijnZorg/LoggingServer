<?php
/** Oude logs verwijderen **/

// Vereisten laden
require_once __dir__."/../secure/includes.php";
require_once __dir__."/../secure/routes.php";
require_once __dir__."/../Class/AppConfiguration.php";
require_once __dir__."/../Class/CryptoService.php";
require_once __dir__."/../Class/LoggingService.php";

// Retentie opvragen in configuratie
$appConfiguration = new AppConfiguration($database);
$retentionDays = intval($appConfiguration->getConfiguration('LOGGING_RETENTION_DAYS'));

// Is de retentie 0, dan doen we niets
if($retentionDays == 0) {
		echo "No retention configured!";
		exit();
}


$cryptoService = new CryptoService();
$cryptoService->setPrivateKeyLocation($config['crypto']['privatekey']);

// Logging openen
$loggingService = new LoggingService($database);
$loggingService->loadCryptoService($cryptoService);

// Tijd van retentie tonen in console
$timestampRetention = date("d-m-Y H:i:s",time() - ($retentionDays * 86400));
echo "Deleting all logs on or before ".$timestampRetention."...\n\n";

// Logs verwijderen
$logsDeleted = $loggingService->cleanLogs($retentionDays);

// Resultaat tonen in console
echo $logsDeleted." logs deleted.";
?>