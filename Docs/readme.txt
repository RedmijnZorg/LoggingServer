-- Red mijn Zorg Log Sercer --

- Beschrijving -
	Centrale logserver, gemaakt voor de Red mijn Zorg Cloud apps.

- Vereisten -
	- PHP 8.3 of hoger
	- GD
	- OpenSSL
	- Mail
	- MySQL
	
- Installatie -
	1. Maak een database en bewaar de naam, gebruikersnaam en wachtwoord.
	2. Importeer schema.sql in de database
	3. Plaats alle bestanden behalve de Docs directory op de web server
	4. Nagiveer naar [webroot]/Install om de installatie te starten
	5. Vul het formulier in. Gegevens database van stap 1 opgeven.
	6. Verwijder de Install directory
	
- Inloggen -
	1. Log in met het ingestelde account tijdens de installatie
	2. Stel een nieuw wachtwoord in
	3. Stel 2FA in
	
- Gebruik -
	1. Configureer gebruikers
	2. Voeg iedere bron toe via Beheer > Bronnen. 
	3. Noteer de token, deze kan je bij de installatie van de desbetreffende app opgeven

- Authenticatieproces gebruikers -
	1. Gebruiker logt in
	2. Is het de eerste keer of staat 'changepassword' in de database op 1? Gebruiker wordt doorgestuurd naar de wijzigingspagina.
	3. Is er geen 2FA secret ingesteld? '2fa' is dan leeg. Gebruiker wordt doorgestuurd naar de 2FA instelpagina.
	4. Staat de sessie '2fapass' op 'false'? Gebruiker wordt doorgestuurd naar de 2FA controlepagina.
	
- Verwerkingsproces logs -
	1. 	App maakt payload van ongesyncte logs in json:
		[
			{
				"timestamp", "2020-12-20T15:45:40+00:00",
				"referrer", "website.com",
				"username", "user@mail.com",
				"page", "page.php",
				"event", "view",
				"data", "Something went good",
				"pass", 1,
				"logID", 1,
				"ipAddress", "10.20.30.40",
				"userAgent", "Mozilla/5.0",
			},
			{
				"timestamp", "2020-12-20T16:20:40+00:00",
				"referrer", "otherwebsite.com",
				"username", "useruser@mail.com",
				"page", "page2.php",
				"event", "account",
				"data", "Something went bad",
				"pass", 0,
				"logID", 2,
				"ipAddress", "10.20.30.49",
				"userAgent", "Mozilla/5.0",
			}
		]
	
	2. App voegt de header X-APP-TOKEN toe met de API key	
	3. App stuurt payload naar [webroot]/api/receiver
    4. Log server reageert als volgt:
    	- 	Bij succes:
    		    JSON response:
    		    	{
						"amountProcessed":2,
						"amountPassed":2,
						"amountFailed":0,
						"logsPassed":[
										{"logID":"1"},
										{"logID":"2"}
									]
					}
			
		-	API key ongeldig: HTTP 403 error
		-	Lege payload: HTTP 500 error
	5. App registreert verwerkte logs als gesync
	
	