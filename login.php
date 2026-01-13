<?php
if(!isset($routerActive)) {
    ob_start();
    header("location: /login");
    exit();
}

$loginOperations = new LoginOperations($database);
?>
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
if(isset($_POST["submit"])){
    $login = $loginOperations->login($_POST["email"], $_POST["password"]);
    if($login == false) {
        echo "<script>showErrorMessage('Inloggen mislukt','Jouw accountgegevens zijn onjuist of je account is geblokkeerd!');</script>";
        exit();
    } else {
        ini_set('session.cookie_domain',$config['app']['cookiedomain']);
        session_start();
        $_SESSION["user"] = $login;
        $_SESSION["user"]['2fapass'] = false;

        header("location: /");
    }
}
?>

<script>document.getElementById('emailbox').focus();</script>