<?php
if (!isset($routerActive)) {
    ob_start();
    header("location: /bronnen");
    exit();
}
$sourceManager = new SourceManager($database);

$sourcesArray = $sourceManager->getAllsources();
?>
<div id="bodycontainer">
<h1>Bronnen beheren</h1>
<table class="listingtable">
    <tr>
        <th>Naam</th>
        <th>Token</th>
        <th>Acties</th>
    </tr>
    <?php
    foreach($sourcesArray as $source){
        echo "<tr>";
        echo "<td>".$source['name']."</td>";
        echo "<td>".$source['token']."</td>";
        echo "<td><img class='actionbutton' src='images/delete.png' style='width: 20px; height; 20px' onclick=\"switchOverlay(); deleteSource(".$source['sourceid'].")\" /></td>";
        echo "</tr>";
    }
    ?>
</table><br>
    <button type="button" class="button" onclick="switchOverlay(); document.getElementById('addsource').style.display='block'; document.getElementById('sourcename').focus();">Bron toevoegen</button>
</div>
    <div class="message-container" id="addsource" style="display:none; height: 300px; width: 600px;">
        <p class="message-title">Bron toevoegen</p>

    <form method="post" action="" id="addsourceform">
        <input type="hidden" name="addnewsource" value="1">
        <table>
            <tr>
                <th>Naam</th>
                <td><input type="text" name="sourcename" id="sourcename" onkeyup="verifyAddsource();"></td>
            </tr>
        </table>
        <span class="errormessage" id="erroradd"></span>
        <div class="buttons-container">
            <button type="button" class="button disabled" id='savenewsourcebutton' onclick="document.getElementById('addsourceform').submit();" disabled>
                Opslaan
            </button>
            <button type="button" class="button" onclick="switchOverlay(); document.getElementById('addsource').style.display='none';">
                Annuleren
            </button>
        </div>
    </form>
    </div>

    <div class="message-container" id="deletesource" style="display:none; height: 300px;">
        <p class="message-title">Bron verwijderen</p>
        <form method="post" action="" id="deletesourceform">
            <input type="hidden" name="sourceidDelete" id="sourceiddelete" />
            <div class="input-container" style="text-align: center; padding-bottom: 15px">
                Weet u zeker dat u deze bron wilt verwijderen?
            </div>
            <div class="buttons-container">
                <button type="button" class="button" onclick="document.getElementById('deletesourceform').submit();">
                    Ja
                </button>
                <button type="button" class="button" onclick="switchOverlay(); document.getElementById('deletesource').style.display='none';">
                    Nee
                </button>
            </div>
        </form>
    </div>


<?php
echo $keyrenders;
if(isset($_POST['addnewsource'])) {
    $name = $_POST['sourcename'];

    if($name == "") {
        echo "<script>showErrorMessage('Naam ontbreekt','Vul a.u.b. een naam in!');</script>";
        exit();
    }

    $addsource = $sourceManager->addSource($name);
    if($addsource) {
        header("location: /bronnen");
    } else {
        echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
        exit();
    }
}

if(isset($_POST['sourceidDelete'])) {
    $sourceid_found = $_POST['sourceidDelete'];
    $deletesource = $sourceManager->deleteSource($sourceid_found);
    if($deletesource) {
        header("location: /bronnen");
    } else {
        echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
        exit();    }
}

?>