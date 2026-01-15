<?php

class LoggingService
{
    private $database;
    private $cryptoService;
    private $ipAddresse;
    private $userAgent;
    private $referrer;

    /**
     * @param mysqli $database
     */
    public function __construct($database) {
        $this->database = $database;
    }
    
     /**
     * @param mysqli $database
     */
    public function loadCryptoService($cryptoService) {
        $this->cryptoService = $cryptoService;
    }

    
     /**
     * @param int $sourceid
     * @param string $timestamp
     * @param string $ip
     * @param string $useragent
     * @param string $referrer
     * @param string $username
     * @param string $page
     * @param string $event
     * @param string $data
     * @param string $pass
     * @return bool
     */
    public function logEvent($sourceid, $timestamp, $ip, $useragent, $referrer, $username, $page, $event, $data, $pass) {
    		$pass = intval($pass);
    		if($pass != 0 AND $pass != 1) {
    				return false;
    		}
    		    		
    		$ipAddress = $this->cryptoService->encryptData(ip2long($ip));
    		$userAgent = $this->cryptoService->encryptData($useragent);
    		$referrer = $this->cryptoService->encryptData($referrer);
    		$username = $this->cryptoService->encryptData($username);
    		$page = $this->cryptoService->encryptData($page);
    		$event = $this->cryptoService->encryptData($event);
    		$data = $this->cryptoService->encryptData($data);
    		
    		$sourceid = $this->database->real_escape_string($sourceid);
    		$pass = $this->database->real_escape_string($pass);
    		$ipAddress = $this->database->real_escape_string($ipAddress);
    		$userAgent = $this->database->real_escape_string($userAgent);
    		$referrer = $this->database->real_escape_string($referrer);
    		$username = $this->database->real_escape_string($username);
    		$page = $this->database->real_escape_string($page);
    		$event = $this->database->real_escape_string($event);
    		$data = $this->database->real_escape_string($data);
    		$timestamp = $this->database->real_escape_string(strtotime($timestamp));
    
    		$additem = $this->database->query("INSERT INTO `logging` (
    		`sourceid`,
    		`timestamp`,
    		`ip`,
    		`useragent`,
    		`referrer`,
    		`username`,
    		`page`,
    		`event`,
    		`data`,
    		`pass`) VALUES (
    		'".$sourceid."',
    		'".$timestamp."',
    		'".$ipAddress."',
    		'".$userAgent."',
    		'".$referrer."',
    		'".$username."',
    		'".$page."',
    		'".$event."',
    		'".$data."',
    		'".$pass."')");
    		return $additem;
    		
    }
    
    /**
     * @param int $yearFilter
     * @param int $monthFilter
     * @param int $dayFilter
     * @param int $sourceFilter
     * @param string $eventFilter
     * @param string $textFilter
     * @param int $allowedFilter
     * @param string $sortField
     * @param string $sortDirection
     * @return array
     */
    public function getLogs($yearFilter = 0, $monthFilter = 0, $dayFilter = 0, $sourceFilter = 0, $eventFilter = "", $textFilter = "", $allowedFilter = 2, $sortField = "timestamp", $sortDirection = "ASC") {
    		if($sortDirection == "DESC") {
    				$sortDirection = "DESC";
    			} else {
    				$sortDirection = "ASC";
    		}
    		
    		if($sortField == "ip") {
    				$sortField = "ip";		
    			} elseif($sortField == "pass") {
    				$sortField = "pass"; 
    			} else {
    				$sortField = "timestamp"; 
    		}
    		$timestart = 0;
    		$timeend = 0;
    		if($yearFilter != 0 AND $monthFilter == 0) {
    			$timestart = strtotime($yearFilter."-01-01 00:00:00");
    			$timeend = strtotime($yearFilter."-12-31 23:59:59");
    		} elseif($yearFilter != 0 AND $monthFilter != 0 AND $dayFilter == 0) {
    			$timestart = strtotime($yearFilter."-".$monthFilter."-01 00:00:00");
    			$lastDay = date('Y-m-t', strtotime($yearFilter."-".$monthFilter."-01"));
				$timeend = strtotime($lastDay." 23:59:59");
    		} elseif($yearFilter != 0 AND $monthFilter != 0 AND $dayFilter != 0) {
				$timestart = strtotime($yearFilter."-".$monthFilter."-".$dayFilter." 00:00:00");
				$timeend = strtotime($yearFilter."-".$monthFilter."-".$dayFilter." 23:59:59");
			}    		
    		if($timestart > 0) {
    		 		$getitems = $this->database->query("SELECT * FROM `logging` WHERE `timestamp` >= '$timestart' AND `timestamp` <= '$timeend' ORDER BY `".$sortField."` ".$sortDirection);
    			} else {
    		 		$getitems = $this->database->query("SELECT * FROM `logging` ORDER BY `".$sortField."` ".$sortDirection);
    		}
    		if($getitems->num_rows == NULL) {
    			return array();
    		}
    		$returnArray = array();
    		while($itemDetails = $getitems->fetch_assoc()) {
    				if($allowedFilter == 0 OR $allowedFilter == 1) {
    					if($itemDetails['pass'] != $allowedFilter) {
    							continue;
    					}
    				}
    				$itemDetails['timestamp'] = date(DATE_ATOM,$itemDetails['timestamp']);
    				$itemDetails['ip'] = long2ip(intval($this->cryptoService->decryptData($itemDetails['ip'])));
    				$itemDetails['useragent'] = $this->cryptoService->decryptData($itemDetails['useragent']);
    				$itemDetails['referrer'] = $this->cryptoService->decryptData($itemDetails['referrer']);
    				$itemDetails['username'] = $this->cryptoService->decryptData($itemDetails['username']);
    				$itemDetails['page'] = $this->cryptoService->decryptData($itemDetails['page']);
    				$itemDetails['event'] = $this->cryptoService->decryptData($itemDetails['event']);
    				$itemDetails['data'] = $this->cryptoService->decryptData($itemDetails['data']);
    				if($sourceFilter != 0 AND $itemDetails['sourceid'] != $sourceFilter) {
    						continue;
    				}
    				if($eventFilter != "" AND $itemDetails['event'] != $eventFilter) {
    						continue;
    				}
    				if($textFilter != "") {
    						$matchFound = false;
    						if(stristr($itemDetails['ip'],$textFilter)) {
    								$matchFound = true;
    						}
    						if(stristr($itemDetails['useragent'],$textFilter)) {
    								$matchFound = true;
    						}
    						if(stristr($itemDetails['referrer'],$textFilter)) {
    								$matchFound = true;
    						}
    						if(stristr($itemDetails['username'],$textFilter)) {
    								$matchFound = true;
    						}
    						if(stristr($itemDetails['page'],$textFilter)) {
    								$matchFound = true;
    						}
    						if(stristr($itemDetails['event'],$textFilter)) {
    								$matchFound = true;
    						}
    						if(stristr($itemDetails['data'],$textFilter)) {
    								$matchFound = true;
    						}
    						if($matchFound == false) {
    								continue;
    						}
    				}
    				$returnArray[] = $itemDetails;
    		}
    		return $returnArray;
    }
    
    /**
     * @param int $retentionDays
     * @return bool
     */
    function cleanLogs($retentionDays) {
    	$retentionDays = $this->database->real_escape_string(intval($retentionDays));
    	$timestampRetention = time() - ($retentionDays * 86400);
    	$findlogs = $this->database->query("SELECT `logid` FROM `logging` WHERE `timestamp` <= '$timestampRetention'");
    	if($findlogs->num_rows == NULL) {
    			return 0;
    		} else {
    			$cleanLogs = $this->database->query("DELETE FROM `logging` WHERE `timestamp` <= '$timestampRetention'");
    			if($cleanLogs) {
    					return $findlogs->num_rows;
    				} else {
    				return 0;
    			}
    	}
    }
}
