<?php
if (!isset($routerActive)) {
    ob_start();
    header("location: /gebruikers");
    exit();
}
$userOperations = new UserOperations($database);
$cryptoService = new CryptoService();

$usersArray = $userOperations->getAllUsers();
$newGeneratedPassword = $cryptoService->generatePassword();
?>
    <div id="bodycontainer">

<h1>Gebruikers beheren</h1>
<table class="listingtable">
    <tr>
        <th>Naam</th>
        <th>Laatste Login</th>
        <th>Mislukte pogingen</th>
        <th>Authenticator actief</th>
        <th>Geblokkeerd</th>
        <th>Acties</th>
    </tr>
    <?php
    foreach($usersArray as $user){
        echo "<tr>";
        echo "<td>".$user['fullname']."</td>";
        if($user['lastlogin'] != "") {
            $lastloginReadable = date("d-m-Y H:i:s", $user['lastlogin']);
        } else {
            $lastloginReadable = "";
        }
        echo "<td>".$lastloginReadable."</td>";
        echo "<td>".$user['failedlogins']."</td>";

        if($user['2fa'] != "") {
            echo "<td>Ja</td>";
        } else {
            echo "<td>Nee</td>";
        }
        if($user['locked'] == "1") {
            echo "<td>Ja</td>";
        } else {
            echo "<td>Nee</td>";
        }

        echo "<td>";
            echo "<img class='actionbutton' src='images/edit.png' style='width: 20px; height; 20px' onclick=\"switchOverlay(); editUser(".$user['userid'].")\"/>";
            if($user['userid'] != $_SESSION['user']['userid']) {
                echo "<img class='actionbutton' src='images/delete.png' style='width: 20px; height; 20px' onclick=\"switchOverlay(); deleteUser(".$user['userid'].")\" />";
                if($user['locked'] == "0") {
                    echo "<img class='actionbutton' src='images/lock.png' style='width: 20px; height; 20px' onclick=\"switchOverlay(); lockuser(".$user['userid'].")\" />";
                } else {
                    echo "<img class='actionbutton' src='images/unlock.png' style='width: 20px; height; 20px' onclick=\"switchOverlay(); unlockuser(".$user['userid'].")\" />";
                }
            }
            echo "<img class='actionbutton' src='images/2fa.png' style='width: 20px; height; 20px' onclick=\"switchOverlay(); reset2fa(".$user['userid'].")\" />";
            echo "<img class='actionbutton' src='images/reset.png' style='width: 20px; height; 20px' onclick=\"switchOverlay(); resetpassword(".$user['userid'].")\" />";
        echo "</td>";
        echo "</tr>";

    }
    ?>
</table><br>
    <button type="button" class="button" onclick="switchOverlay(); document.getElementById('adduser').style.display='block'; document.getElementById('fullname').focus();">Gebruiker toevoegen</button>
    </div>
    <div class="message-container" id="adduser" style="display:none; height: 550px; width: 780px;">
        <p class="message-title">Gebruiker toevoegen</p>

    <form method="post" action="" id="adduserform">
        <input type="hidden" name="addnewuser" value="1">
        <table>
            <tr>
                <th>Volledige naam</th>
                <td><input type="text" name="fullname" id="fullname" onkeyup="verifyAdduser();"></td>
            </tr>
            <tr>
                <th style="min-width: 200px;">Tijdelijk wachtwoord</th>
                <td><input type="text" name="password" onkeyup="verifyAdduser();" id="password" value="<?php echo $newGeneratedPassword; ?>"/></td>
            </tr>
            <tr>
                <th>E-mail</th>
                <td><input type="text" name="email" id="email" onkeyup="verifyAdduser();"></td>
            </tr>
        </table>
        <span class="errormessage" id="erroradd"></span>
        <div class="buttons-container">
            <button type="button" class="button disabled" id='savenewuserbutton' onclick="document.getElementById('adduserform').submit();" disabled>
                Opslaan
            </button>
            <button type="button" class="button" onclick="switchOverlay(); document.getElementById('adduser').style.display='none';">
                Annuleren
            </button>
        </div>
    </form>
    </div>
    <div class="message-container" id="edituser" style="display:none;  height: 550px; width: 780px;">
        <p class="message-title">Gebruiker bewerken</p>

    <form method="post" action="" id="edituserform">
        <input type="hidden" name="useridEdit" id="useridedit" />
        <table>
            <tr>
                <th>Volledige naam</th>
                <td><input type="text" name="fullnameEdit" id="fullnameedit" onkeyup="verifyEdituser();"></td>
            </tr>
            <tr>
                <th>E-mail</th>
                <td><input type="text" name="emailEdit" id="emailedit"  onkeyup="verifyEdituser();"></td>
            </tr>
        </table>
        <span class="errormessage" id="erroredit"></span>

        <div class="buttons-container">
            <button type="button" class="button" id='saveexistinguserbutton' onclick="document.getElementById('edituserform').submit();">
                Opslaan
            </button>
            <button type="button" class="button" onclick="switchOverlay(); document.getElementById('edituser').style.display='none';">
                Annuleren
            </button>
        </div>
    </form>
    </div>

    <div class="message-container" id="deleteuser" style="display:none; height: 300px;">
        <p class="message-title">Gebruiker verwijderen</p>
        <form method="post" action="" id="deleteuserform">
            <input type="hidden" name="useridDelete" id="useriddelete" />
            <div class="input-container" style="text-align: center; padding-bottom: 15px">
                Weet u zeker dat u deze gebruiker wilt verwijderen?
            </div>
            <div class="buttons-container">
                <button type="button" class="button" onclick="document.getElementById('deleteuserform').submit();">
                    Ja
                </button>
                <button type="button" class="button" onclick="switchOverlay(); document.getElementById('deleteuser').style.display='none';">
                    Nee
                </button>
            </div>
        </form>
    </div>

    <div class="message-container" id="reset2fa" style="display:none; height: 300px;">
        <p class="message-title">Authenticator resetten</p>
        <form method="post" action="" id="reset2faform">
            <input type="hidden" name="userid2fa" id="userid2fa" />
            <div class="input-container" style="text-align: center; padding-bottom: 15px">
                Weet u zeker dat u de authenticator wilt resetten?
            </div>
            <div class="buttons-container">
                <button type="button" class="button" onclick="document.getElementById('reset2faform').submit();">
                    Ja
                </button>
                <button type="button" class="button" onclick="switchOverlay(); document.getElementById('reset2fa').style.display='none';">
                    Nee
                </button>
            </div>
        </form>
    </div>

    <div class="message-container" id="lockuser" style="display:none; height: 300px;">
        <p class="message-title">Gebruiker blokkeren</p>
        <form method="post" action="" id="lockuserform">
            <input type="hidden" name="lockuser" id="useridlock" />
            <div class="input-container" style="text-align: center; padding-bottom: 15px">
                Weet u zeker dat u deze gebruiker wilt blokkeren?
            </div>
            <div class="buttons-container">
                <button type="button" class="button" onclick="document.getElementById('lockuserform').submit();">
                    Ja
                </button>
                <button type="button" class="button" onclick="switchOverlay(); document.getElementById('lockuser').style.display='none';">
                    Nee
                </button>
            </div>
        </form>
    </div>

    <div class="message-container" id="unlockuser" style="display:none; height: 300px;">
        <p class="message-title">Gebruiker deblokkeren</p>
        <form method="post" action="" id="unlockuserform">
            <input type="hidden" name="unlockuser" id="useridunlock" />
            <div class="input-container" style="text-align: center; padding-bottom: 15px">
                Weet u zeker dat u deze gebruiker wilt deblokkeren?
            </div>
            <div class="buttons-container">
                <button type="button" class="button" onclick="document.getElementById('unlockuserform').submit();">
                    Ja
                </button>
                <button type="button" class="button" onclick="switchOverlay(); document.getElementById('unlockuser').style.display='none';">
                    Nee
                </button>
            </div>
        </form>
    </div>

    <div class="message-container" id="resetpassword" style="display:none; height: 300px;">
        <p class="message-title">Wachtwoord opnieuw instellen</p>
        <form method="post" action="" id="resetpasswordform">
            <input type="hidden" name="useridreset" id="useridreset" />
        <div class="input-container" style="text-align: center; padding-bottom: 15px">
            Weet u zeker dat u een nieuw wachtwoord wilt instellen?
        </div>
            <div class="buttons-container">
            <button type="button" class="button" onclick="document.getElementById('resetpasswordform').submit();">
                Ja
            </button>
            <button type="button" class="button" onclick="switchOverlay(); document.getElementById('resetpassword').style.display='none';">
                Nee
            </button>
            </div>
        </form>
    </div>

<?php
if(isset($_POST['addnewuser'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    if($fullname == "") {
        echo "<script>showErrorMessage('Naam ontbreekt','Vul a.u.b. een naam in!');</script>";
        exit();
    }
    if($email == "") {
        echo "<script>showErrorMessage('E-mail ontbreekt','Vul a.u.b. een e-mailadres in!');</script>";
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>showErrorMessage('Ongeldig e-mailadres','Vul a.u.b. een geldig e-mailadres in!');</script>";
        exit();
    }

    $findexistinguser = $userOperations->findUserByEmail($email);
    if($findexistinguser) {
        echo "<script>showErrorMessage('Gebruiker bestaat al','Deze gebruiker bestaat al!');</script>";
        exit();
    }
    $adduser = $userOperations->addUser($email, $password, $fullname);
    if($adduser) {
        $mailerService = new MailerService();
        $mailerService->setFromAddress($config['email']['from']);
        $mailerService->setToAddress($email);
        $mailerService->setSubject('Nieuwe gebruiker');
        $renderService = new RenderService();
        $renderService->setTitle('Nieuwe gebruiker');
        $emailbody = "Beste ".$fullname.",<br>";
        $emailbody .= "Er is een nieuw gebruikersaccount voor u aangemaakt:<br>";
        $emailbody .= "<table>";
        $emailbody .= "<tr>";
        $emailbody .= "<th style='text-align: left;'>Gebruikersnaam</th>";
        $emailbody .= "<td>".$email."</td>";
        $emailbody .= "</tr>";
        $emailbody .= "<tr>";
        $emailbody .= "<th style='text-align: left;'>Tijdelijk wachtwoord</th>";
        $emailbody .= "<td>".$password."</td>";
        $emailbody .= "</tr>";
        $emailbody .= "</table>";
        $emailbottom = "Let op: Om in te loggen heb je ook een Authenticator app nodig op je telefoon, zoals:<br>";
        $emailbottom .= "<ul><li>Microsoft Authenticator</li><li>Google Authenticator</li><li>Authy</li></ul>";
        $renderService->setContenttop($emailbody);
        $renderService->setContentbottom($emailbottom);
        $renderService->setButtonvalue("Inloggen");
        $renderService->setbuttonurl($config['app']['hostname']);
        $renderService->setFootertext($config['app']['name']);
        $emailcontents = $renderService->renderMail(true);
        if($emailcontents == false) {
            echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
            exit();
        }
        $mailerService->setFromAddress($config['email']['from']);
        $mailerService->setMailbody($emailcontents);
        $mailerService->sendHTML();
        header("location: /gebruikers");
    } else {
        echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
        exit();
    }
}

if(isset($_POST['useridEdit'])) {
    $userid_found = $_POST['useridEdit'];
    $fullname = $_POST['fullnameEdit'];
    $email = $_POST['emailEdit'];
    if($fullname == "") {
        echo "<script>showErrorMessage('Naam ontbreekt','Vul a.u.b. een naam in!');</script>";
        exit();
    }
    if($email == "") {
        echo "<script>showErrorMessage('E-mail ontbreekt','Vul a.u.b. een e-mailadres in!');</script>";
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>showErrorMessage('Ongeldig e-mailadres','Vul a.u.b. een geldig e-mailadres in!');</script>";
        exit();
    }
    $findexistinguser = $userOperations->findUserByEmail($email);
    if($findexistinguser AND $findexistinguser != $userid_found) {
        echo "<script>showErrorMessage('Gebruiker bestaat al','Deze gebruiker bestaat al!');</script>";
        exit();
    }
    $edituser = $userOperations->updateUser($userid_found, $fullname,$email);
    if($edituser) {
       header("location: /gebruikers");
    } else {
        echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
        exit();    }
}
if(isset($_POST['useridDelete'])) {
    $userid_found = $_POST['useridDelete'];
    $deleteuser = $userOperations->deleteUser($userid_found);
    if($deleteuser) {
        header("location: /gebruikers");
    } else {
        echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
        exit();    }
}
if(isset($_POST['userid2fa'])) {
    $userid_found = $_POST['userid2fa'];
    $reset2fa = $userOperations->setUser2FASecret($userid_found,"");
    if($reset2fa) {
        header("location: /gebruikers");
    } else {
        echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
        exit();    }
}

if(isset($_POST['lockuser'])) {
    $useridfound = $_POST['lockuser'];
    $lockuser = $userOperations->lockUser($useridfound);
    if($lockuser) {
        header("location: /gebruikers");
    } else {
        echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
        exit();    }
}

if(isset($_POST['unlockuser'])) {
    $useridfound = $_POST['unlockuser'];
    $unlockuser = $userOperations->unlockUser($useridfound);
    if($unlockuser) {
        header("location: /gebruikers");
    } else {
        echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
        exit();    }
}
if(isset($_POST['useridreset'])) {
    $useridfound = $_POST['useridreset'];
    $userdetails = $userOperations->getUserDetails($useridfound);
    $changepassword = $userOperations->changePassword($useridfound,$newGeneratedPassword,true);
    if($changepassword) {
        $renderService = new RenderService();
        $renderService->setTitle('Nieuw wachtwoord ingesteld');
        $mailerService = new MailerService();
        $mailerService->setFromAddress($config['email']['from']);
        $mailerService->setToAddress($userdetails['email']);
        $mailerService->setSubject('Nieuw wachtwoord ingesteld');
        $emailbody = "Beste ".$userdetails['fullname'].",<br>";
        $emailbody .= "Er is een nieuw wachtwoord voor u aangevraagd:<br>";
        $emailbody .= "<table>";
        $emailbody .= "<tr>";
        $emailbody .= "<th style='text-align: left;'>Nieuw tijdelijk wachtwoord</th>";
        $emailbody .= "<td>".$newGeneratedPassword."</td>";
        $emailbody .= "</tr>";
        $emailbody .= "</table>";
        $renderService->setContenttop($emailbody);
        $renderService->setButtonvalue("Inloggen");
        $renderService->setbuttonurl($config['app']['hostname']);
        $renderService->setFootertext($config['app']['name']);
        $emailcontents = $renderService->renderMail(true);
        if($emailcontents == false) {
            echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
            exit();
        }

        $mailerService->setFromAddress($config['email']['from']);
        $mailerService->setMailbody($emailcontents);
        $mailerService->sendHTML();
        header("location: /gebruikers");
    } else {
        echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
        exit();
    }
}
?>