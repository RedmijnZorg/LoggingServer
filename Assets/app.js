/** Specifieke JS voor app **/

/**
	** @param string sourceBox
	** @param string targetBox
	** @return void
**/
function moveGroups(sourceBox,targetBox) {
		// Iedere group uit de bronlijst nalopen
    	$("#"+sourceBox+" option").each(function() {
    			// Is deze groep geselecteerd?
				if($(this).is(":selected") === true) {
						// Voeg toe aan de doellijst
						$("#"+targetBox).append($('<option>', {
								value: $(this).val(),
								text: $(this).text()
						}));
						// Verwijder van de bronlijst
						$(this).remove();
				}
		});
}

/**
	** @param string sourceBox
	** @param string targetBox
	** @return void
**/
function resetAllGroups(sourceBox,targetBox) {
		// Iedere group uit de doellijst nalopen
		$("#"+targetBox+" option").each(function() {
				// Groep deselecteren
				$(this).attr("selected",false);
				// Voeg toe aan de bronlijst
				$("#"+sourceBox).append($(this));
				// Verwijder van de doellijst
				$(this).remove();
		});
}

/**
	** @param string box
	** @return void
**/
function selectAllOptions(box) {
		// Selecteer ieder item uit de select
		$("#"+box+" option").prop('selected', true);
}

/**
	** @param int userid
	** @return void
**/
function editUser(userid) {
		$.ajax({
			url:
				'/ajax/getuserdetails?userid='+userid,
			type: "GET",
			success: function (data) {
					// ID, naam en mailadres invullen
					$("#useridedit").val(userid);
					$("#fullnameedit").val(decodeURIComponent(data.fullname));
					$("#emailedit").val(decodeURIComponent(data.email));
					// Formulier is compleet ingevuld, alle blokkades opheffen
					$("#saveexistinguserbutton").attr("disabled",false);
					$("#saveexistinguserbutton").removeClass("disabled");
					$("#fullnameedit").removeClass("error");
					$("#emailedit").removeClass("error");
					
					// Venster tonen
					$("#edituser").css("display","block");
					
					// Naamvakje selecteren
					$("#fullnameedit").focus();
			},
			error: function (error) {
					showErrorMessage('Fout','Er ging iets fout!');
			}
		});
}

/**
	** @param int userid
	** @return void
**/
function deleteUser(userid) {
    	$("#useriddelete").val(userid);
		$("#deleteuser").css("display","block");
}

/**
	** @param int userid
	** @return void
**/
function reset2fa(userid) {
    	$("#userid2fa").val(userid);
		$("#reset2fa").css("display","block");
}

/**
	** @param int userid
	** @return void
**/
function lockuser(userid) {
    	$("#useridlock").val(userid);
		$("#lockuser").css("display","block");
}

/**
	** @param int userid
	** @return void
**/
function unlockuser(userid) {
	    $("#useridunlock").val(userid);
		$("#unlockuser").css("display","block");
}

/**
	** @param int userid
	** @return void
**/
function resetpassword(userid) {
    	$("#useridreset").val(userid);
		$("#resetpassword").css("display","block");
}

/**
	** @return void
**/
function verifyAdduser() {
		error = true;
		
		// Controleren of naam is ingevuld
		if($("#fullname").val() == "") {
				$("#fullname").addClass("error");
			} else {
				$("#fullname").removeClass("error");
		}
	
		// Controleren of email is ingevuld en geldig is
		if($("#email").val() != "" && validateEmail($("#email").val()) !== true) {
				$("#email").addClass("error");
			} else {
				$("#email").removeClass("error");
		}
		
		// Controleren of wachtwoord is ingevuld
		if($("#password").val() == "") {
				$("#password").addClass("error");
			} else {
				$("#password").removeClass("error");
		}
	
		// Controleren of alles goed is ingevuld
		if($("#fullname").val() != "" && $("#password").val() != "" && $("#email").val() != "" && validateEmail($("#email").val()) === true) {
				// Zo ja, haal alle rode randen weg
				error = false;
				$("#fullname").removeClass("error");
				$("#email").removeClass("error");
				$("#password").removeClass("error");
			} else {
				error = true;
		}
		if(error === true) {
				// Blokkeren bij validatieprobleem
				$("#savenewuserbutton").attr("disabled",true);
				$("#savenewuserbutton").addClass("disabled");
			} else {
				// Deblokkeren bij geen validatieprobleem
				$("#savenewuserbutton").attr("disabled",false);        
				$("#savenewuserbutton").removeClass("disabled");
		}
}

/**
	** @return void
**/
function verifyEdituser() {
		error = true;
		
		// Controleren of naam is ingevuld
		if($("#fullnameedit").val() == "") {
				$("#fullnameedit").addClass("error");
			} else {
				$("#fullnameedit").removeClass("error");
		}
		
		// Controleren of email is ingevuld en geldig is
		if($("#emailedit").val() != "" && validateEmail($("#emailedit").val()) !== true) {
				$("#emailedit").addClass("error");
			} else {
				$("#emailedit").removeClass("error");
		}
		
		// Controleren of alles goed is ingevuld
		if($("#fullnameedit").val() != "" && $("#emailedit").val() != "" && validateEmail($("#emailedit").val()) === true) {
					// Zo ja, haal alle rode randen weg
					error = false;
					$("#fullnameedit").removeClass("error");
					$("#emailedit").removeClass("error");			
				} else {
					error = true;
		}
		if(error === true) {
				// Blokkeren bij validatieprobleem
				$("#saveexistinguserbutton").attr("disabled",true);
				$("#saveexistinguserbutton").addClass("disabled");
			} else {
				// Deblokkeren bij geen validatieprobleem
				$("#savenewusergroupbutton").attr("disabled",false);        
				$("#saveexistinguserbutton").removeClass("disabled");
		}
}

/**
	** @param int sourceid
	** @return void
**/
function deleteSource(sourceid) {
		$("#sourceiddelete").val(sourceid);
		$("#deletesource").css("display","block");
}

/**
	** @return void
**/
function verifyAddsource() {
		error = true;
		
		// Als de naam niet is ingevuld, toon een fout
		if($("#appname").val() == "") {
				$("#sourcename").addClass("error");
				error = true;
			} else {
				$("#sourcename").removeClass("error");
				error = false;
		}
		
		// Formulier blokkeren bij validatiefouten
		if(error === true) {
				$("#savenewsourcebutton").attr("disabled",true);
				$("#savenewsourcebutton").addClass("disabled");
			} else {
				$("#savenewsourcebutton").attr("disabled",false);        
				$("#savenewsourcebutton").removeClass("disabled");
		}
}


/**
	** @return void
**/
function sessionKeepAlive() {
		$.ajax({
			url:
				'/ajax/sessionkeepalive',
			type: "GET",
			success: function (data) {
					setTimeout(sessionKeepAlive,60000);
			}
		});
}