<?php
if(!isset($routerActive)) {
    ob_start();
    header("location: /reset");
    exit();
}

if(!isset($_GET['token'])) {
    echo "Ongeldige code!";
    exit();
}

$loginOperations = new LoginOperations($database);
$userOperations = new UserOperations($database);

$userDetails = $userOperations->getUserByResetToken($_GET['token']);
if($userDetails == false) {
    echo "Ongeldige code!";
    exit();
}

?>
<form method="post">
    <div class="message-container" style="height: 350px;">
        <p class="message-title">Wachtwoord wijzigen</p>
        <div class="input-container">
            <input type="password" id='newpassword' name="newpassword" placeholder="Nieuw wachtwoord">
        </div>
        <div class="input-container">
            <input type="password" name="newpassword2" placeholder="Nieuw wachtwoord nogmaals">
        </div>
        <button type="submit" name="submit" class="submit">
            Wachtwoord wijzigen
        </button>

        <span class="errorinline" id="errorinline"></span>

    </div>
</form>

<?php
if (isset($_POST["submit"])) {

   if ($_POST["newpassword"] == $_POST["newpassword2"]) {
       if (trim($_POST["newpassword"], 'a..z') != '' && trim($_POST["newpassword"], 'A..Z') != '' && strlen($_POST["newpassword"]) >= 8) {
                $changepassword = $userOperations->changePassword($userDetails['userid'], $_POST["newpassword"]);
                header("location: /");
            } else {
                echo "<script type='text/javascript'>setErrorMessage('errorinline','Wachtwoord moet 8 tekens of meer bevatten, met minstens een kleine letter en een hoofdletter!');</script>";
            }
        } else {
            echo "<script type='text/javascript'>setErrorMessage('errorinline','Wachtwoorden komen niet overeen!');</script>";
        }
}
?>
<script>document.getElementById('newpassword').focus();</script>