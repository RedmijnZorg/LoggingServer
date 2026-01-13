<?php
if (!isset($routerActive)) {
    ob_start();
    header("location: /");
    exit();
}
require_once("secure/includes.php");
$sourceManager = new SourceManager($database);
$sourcesArray = $sourceManager->getAllsources();
$sourcesArrayPreloaded = array();
foreach($sourcesArray as $source) {
	$sourcesArrayPreloaded[$source['sourceid']] = $source['name'];
}
?>
<div id="bodycontainer">

<h1>Centraal Logboek</h1>
<form method='post'>
<table>
	<tr>
		<th>Datum</th>
		<th>Bron</th>
		<th>Gebeurtenis</th>
		<th>Gelukt</th>
		<th>Tekst</th>
	</tr>
	<tr>
		<?php 
		if(!isset($_POST['dateYear'])) {
				$_POST['dateYear'] = date('Y',time());
		}
		if(!isset($_POST['dateMonth'])) {
				$_POST['dateMonth'] = date('m',time());
		}
		?>
		<td>
			<input type='text' name='dateYear' style='width: 50px;' value="<?php echo $_POST['dateYear']; ?>"> - 
			<input type='text' name='dateMonth' style='width: 30px;' value="<?php echo $_POST['dateMonth']; ?>"> - 
			<input type='text' name='dateDay' style='width: 30px;' value="<?php echo $_POST['dateDay']; ?>">
		</td>
		<td>
			<select name='sourceFilter'>
				<?php
				echo "<option value='0'></option>";
				foreach($sourcesArrayPreloaded as $sourceid => $source) {
						if(isset($_POST['sourceFilter']) AND $_POST['sourceFilter'] == $sourceid) {
								echo "<option value='".$sourceid."' SELECTED>".$source."</option>";
							} else {
								echo "<option value='".$sourceid."'>".$source."</option>";
						}
				}
				?>
			</select>
		</td>
		<td>
			<select name='eventFilter'>
				<?php
						if(isset($_POST['eventFilter']) AND $_POST['eventFilter'] == 'account') {
								echo "
								<option value=''></option>
								<option value='account' SELECTED>Account</option>
								<option value='action'>Action</option>
								<option value='ajax'>AJAX</option>
								<option value='attachment'>Attachment</option>
								<option value='view'>View</option>
								<option value='other'>Other</option>
								";
							} elseif(isset($_POST['eventFilter']) AND $_POST['eventFilter'] == 'action') {
								echo "
								<option value=''></option>
								<option value='account'>Account</option>
								<option value='action' SELECTED>Action</option>
								<option value='ajax'>AJAX</option>
								<option value='attachment'>Attachment</option>
								<option value='view'>View</option>
								<option value='other'>Other</option>
								";
							} elseif(isset($_POST['eventFilter']) AND $_POST['eventFilter'] == 'ajax') {
								echo "
								<option value=''></option>
								<option value='account'>Account</option>
								<option value='action'>Action</option>
								<option value='ajax' SELECTED>AJAX</option>
								<option value='attachment'>Attachment</option>
								<option value='view'>View</option>
								<option value='other'>Other</option>
								";
							} elseif(isset($_POST['eventFilter']) AND $_POST['eventFilter'] == 'attachment') {
								echo "
								<option value=''></option>
								<option value='account'>Account</option>
								<option value='action'>Action</option>
								<option value='ajax'>AJAX</option>
								<option value='attachment' SELECTED>Attachment</option>
								<option value='view'>View</option>
								<option value='other'>Other</option>
								";
							} elseif(isset($_POST['eventFilter']) AND $_POST['eventFilter'] == 'view') {
								echo "
								<option value=''></option>
								<option value='account'>Account</option>
								<option value='action'>Action</option>
								<option value='ajax'>AJAX</option>
								<option value='attachment'>Attachment</option>
								<option value='view' SELECTED>View</option>
								<option value='other'>Other</option>
								";
							} elseif(isset($_POST['eventFilter']) AND $_POST['eventFilter'] == 'other') {
								echo "
								<option value=''></option>
								<option value='account'>Account</option>
								<option value='action'>Action</option>
								<option value='ajax'>AJAX</option>
								<option value='attachment'>Attachment</option>
								<option value='view'>View</option>
								<option value='other' SELECTED>Other</option>
								";
							} else {
							echo "
								<option value='' SELECTED></option>
								<option value='account'>Account</option>
								<option value='action'>Action</option>
								<option value='ajax'>AJAX</option>
								<option value='attachment'>Attachment</option>
								<option value='view'>View</option>
								<option value='other'>Other</option>
								";
						}
				?>
			</select>	
		</td>
		<td>
			<select name='allowedFilter'>
			<?php
				if(isset($_POST['allowedFilter']) AND $_POST['allowedFilter'] == '0') {
						echo "
							<option value='2'></option>
							<option value='0' SELECTED>Nee</option>
							<option value='1'>Ja</option>
							";
					} elseif(isset($_POST['allowedFilter']) AND $_POST['allowedFilter'] == '1') {
						echo "
							<option value='2'></option>
							<option value='0'>Nee</option>
							<option value='1' SELECTED>Ja</option>
							";
					} else {
							echo "
							<option value='2' SELECTED></option>
							<option value='0'>Nee</option>
							<option value='1'>Ja</option>
							";
				}		
						?>
			</select>	
		</td>
		<td><input type='text' name='textFilter' value="<?php echo $_POST['textFilter']; ?>"></td>
	</tr>
	<tr>
		<th>Sorteren op</th>
		<th>Richting</th>
	</tr>
	<tr>
		<td>
			<select name='sortColumn'>
			<?php
				if(isset($_POST['sortColumn']) AND $_POST['sortColumn'] == 'ip') {
						echo "
							<option value='ip' SELECTED>IP adres</option>
							<option value='timestamp'>Tijd</option>
							<option value='pass'>Toegestaan</option>
							";
					} elseif(isset($_POST['sortColumn']) AND $_POST['sortColumn'] == 'pass') {
						echo "
							<option value='ip'>IP adres</option>
							<option value='timestamp'>Tijd</option>
							<option value='pass' SELECTED>Toegestaan</option>
							";
					} else {
							echo "
							<option value='ip'>IP adres</option>
							<option value='timestamp' SELECTED>Tijd</option>
							<option value='pass'>Toegestaan</option>
							";
				}		
						?>
			</select>	
		</td>
		<td>
			<select name='sortDirection'>
			<?php
				if(isset($_POST['sortDirection']) AND $_POST['sortDirection'] == 'ASC') {
						echo "
							<option value='ASC' SELECTED>Oplopend</option>
							<option value='DESC'>Aflopend</option>
							";
					} else {
							echo "
							<option value='ASC'>Oplopend</option>
							<option value='DESC' SELECTED>Aflopend</option>
							";
				}		
						?>
			</select>	
		</td>
		<td><input type='submit' name='setFilter' value='Zoeken'></td>
	</tr>
</table>
</form>
<?php
if(isset($_POST['setFilter'])) {


		if(isset($_POST['dateYear']) AND $_POST['dateYear'] != "") {
				$yearFilter = intval($_POST['dateYear']);
			} else {
				$yearFilter = 0;
		}
		if(isset($_POST['dateMonth']) AND $_POST['dateMonth'] != "" AND $yearFilter != 0) {
				$monthFilter = intval($_POST['dateMonth']);
			} else {
				$monthFilter = 0;
		}
		if(isset($_POST['dateDay']) AND $_POST['dateDay'] != "" AND $yearFilter != 0 AND $monthFilter != 0) {
				$dayFilter = intval($_POST['dateDay']);
			} else {
				$dayFilter = 0;
		}
		if(isset($_POST['dateYear']) AND $_POST['dateYear'] != "") {
				$yearFilter = intval($_POST['dateYear']);
			} else {
				$yearFilter = 0;
		}
		if(isset($_POST['sourceFilter']) AND $_POST['sourceFilter'] != "0") {
				$sourceFilter = intval($_POST['sourceFilter']);
			} else {
				$sourceFilter = 0;
		}
		if(isset($_POST['eventFilter']) AND $_POST['eventFilter'] != "") {
				$eventFilter = $_POST['eventFilter'];
			} else {
				$eventFilter = "";
		}
		if(isset($_POST['allowedFilter']) AND $_POST['allowedFilter'] != "") {
				$allowedFilter = intval($_POST['allowedFilter']);
			} else {
				$allowedFilter = 2;
		}
		if(isset($_POST['sortColumn']) AND $_POST['sortColumn'] != "") {
				$sortColumn = $_POST['sortColumn'];
			} else {
				$sortColumn = "timestamp";
		}
		if(isset($_POST['sortDirection']) AND $_POST['sortDirection'] != "") {
				$sortDirection = $_POST['sortDirection'];
			} else {
				$sortDirection = "DESC";
		}
		$logsArray = $loggingService->getLogs($yearFilter,$monthFilter,$dayFilter, $sourceFilter, $eventFilter, $_POST['textFilter'], $allowedFilter, $sortColumn, $sortDirection);

	} else {
		$logsArray = $loggingService->getLogs(date('Y',time()),date('m',time()),0, 0, "" ,"",2, "timestamp", "DESC");
}

?>
<table class="listingtable">
	<tr>
		<th>Tijdstip</th>
		<th>Bron</th>
		<th>IP adres</th>
		<th>Gebruiker</th>
		<th>Gebeurtenis</th>
		<th>Gelukt</th>
	</tr>
   <?php
   foreach($logsArray as $logitem) {
   		if($logitem['pass'] == 1) {
   				$actionPassed = true;
   				$passvalue = "Ja";
   			} else {
   				$actionPassed = false;
   				$passvalue = "Nee";
   		}
   		if($actionPassed == false) {
   				echo "<tr style='color: red;' onclick='toggleLogDetails(".$logitem['logid'].")'>";
   			} else {
   				echo "<tr onclick='toggleLogDetails(".$logitem['logid'].")'>";
   		}
   			echo "<td>".date("d-m-Y H:i:s",strtotime($logitem['timestamp']))."</td>";
   			echo "<td>".$sourcesArrayPreloaded[$logitem['sourceid']]."</td>";
   			echo "<td>".$logitem['ip']."</td>";
   			echo "<td>".$logitem['username']."</td>";
   			echo "<td>".$logitem['event']."</td>";
   			echo "<td>".$passvalue."</td>";
   		echo "</tr>";
   		echo "<tr class='detailsRow_".$logitem['logid']."' style='display: none;'>";
   			echo "<th>Browser</th>";
   			echo "<td colspan='5'><pre>".$logitem['useragent']."</pre></td>";
   		echo "</tr>";
   		echo "<tr class='detailsRow_".$logitem['logid']."' style='display: none;'>";
   			echo "<th>Bron</th>";
   			echo "<td colspan='5'><pre>".$logitem['referrer']."</pre></td>";
   		echo "</tr>";
   		echo "<tr class='detailsRow_".$logitem['logid']."' style='display: none;'>";
   			echo "<th>Pagina</th>";
   			echo "<td colspan='5'><pre>".$logitem['page']."</pre></td>";
   		echo "</tr>";
   		echo "<tr class='detailsRow_".$logitem['logid']."' style='display: none;'>";
   			echo "<th>Data</th>";
   			echo "<td colspan='5'><pre>".$logitem['data']."</pre></td>";
   		echo "</tr>";
   		echo "<tr class='detailsRow_".$logitem['logid']."'  style='display: none;>";
   			echo "<td colspan='6'>&nbsp;</td>";
   		echo "</tr>";
   }
    ?>
</table>
<script type='text/javascript'>
function toggleLogDetails(logid) {
		if($('.detailsRow_'+logid).css('display') == 'table-row') {
				$('.detailsRow_'+logid).css('display','none');
			} else {
				$('.detailsRow_'+logid).css('display','table-row');
		}
}
</script>