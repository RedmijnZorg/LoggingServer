<?php
// Wordt dit script direct geopend zonder router? Redirect dan naar de juiste pagina.
if(!isset($routerActive)) {
    ob_start();
    header("location: /login");
    exit();
}
// Classes laden
$loginOperations = new LoginOperations($database);
$loginOperations->setSalt($config['crypto']['salt']);
?>

<!-- login formulier -->
<form method="post">
<div class="message-container">
    <p class="message-title"><?php echo $config['app']['name'];?></p>
    <div class="input-container">
        <input type="email" name="email" id='emailbox' placeholder="E-mailadres">
        <span>
          </span>
    </div>
    <div class="input-container">
        <input type="password" name="password" placeholder="Wachtwoord">
    </div>
    <button type="submit" name="submit" class="submit">
        Inloggen
    </button><br>
    <p class="reset-link">
        <a href="/resetrequest">Wachtwoord vergeten?</a></p>
</div>
</form>

<?php
// Formulier ingevuld
if(isset($_POST["submit"])){

	// E-mail en wachtwoord combinatie controlerren
    $login = $loginOperations->login($_POST["email"], $_POST["password"]);
    
    // Combinatie onjuist?
    if($login == false) {
    	//  toon foutmelding
        echo "<script>showErrorMessage('Inloggen mislukt','Jouw accountgegevens zijn onjuist of je account is geblokkeerd!');</script>";
        exit();
        // Combinatie juist?
    } else {
    	// Stel cookiedomein in
        ini_set('session.cookie_domain',$config['app']['cookiedomain']);
        
        // Start de sessie
        session_start();
        
        // Bewaar gegevens van gebruiker in sessie
        $_SESSION["user"] = $login;
        
        // 2FA controle moet nog plaatsvinden, dus deze moet op 'false' staan
        $_SESSION["user"]['2fapass'] = false;

		// Doorsturen naar startpagina, dit wordt waarschijnlijk de 2FA verificatie
        header("location: /");
    }
}
?>
<script>
// E-mail selecteren
$('#emailbox').focus();
</script>