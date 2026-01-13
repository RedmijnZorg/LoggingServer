<?php
require_once __dir__."/../secure/includes.php";
require_once __dir__."/../secure/routes.php";
require_once __dir__."/../Class/AppConfiguration.php";
require_once __dir__."/../Class/CryptoService.php";
require_once __dir__."/../Class/LoggingService.php";

$appConfiguration = new AppConfiguration($database);
$retentionDays = intval($appConfiguration->getConfiguration('LOGGING_RETENTION_DAYS'));
if($retentionDays == 0) {
		echo "No retention configured!";
		exit();
}

$cryptoService = new CryptoService();
$cryptoService->setPrivateKeyLocation($config['crypto']['privatekey']);
$loggingService = new LoggingService($database);
$loggingService->loadCryptoService($cryptoService);

$timestampRetention = date("d-m-Y H:i:s",time() - ($retentionDays * 86400));
echo "Deleting all logs on or before ".$timestampRetention."...\n\n";
$logsDeleted = $loggingService->cleanLogs($retentionDays);
echo $logsDeleted." logs deleted.";
?>