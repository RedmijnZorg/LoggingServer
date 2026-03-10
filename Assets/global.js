/** Globale JS Red mijn Zorg Cloud Engine **/

/**
	** @return void
**/
function expandMenu() {
		menuContainer = $('#menu');
		menuItems = menuContainer.children();
		for(i=0; i<menuItems.length; i++) {
			if(menuItems[i].hasClass("menuitem")) {
				menuItems[i].addClass("show");
			}
		}
		$('#menubutton').click(function(){ collapseMenu()});
}

/**
	** @return void
**/
function collapseMenu() {
		menuContainer = $('#menu');
		menuItems = menuContainer.children();
		for(i=0; i<menuItems.length; i++) {
			if(menuItems[i].hasClass("show")) {
				menuItems[i].removeClass("show");
			}
		}
		$('#menubutton').click(function(){ expandMenu()});
}

/**
	** @return void
**/
function switchOverlay() {
		if(document.getElementById("overlay").style.display == "block") {
				$('#overlay').css("display","none");
			} else {
				$('#overlay').css("display","block");
				$('#overlay').css('height',$(document).height()+'px');
		}
}

/**
	** @param string box
	** @param string contents
	** @return void
**/
function setErrorMessage(box,contents) {
    	$('#'+box).html(contents);
}

/**
	** @param string title
	** @param string contents
	** @return void
**/
function showErrorMessage(title,contents) {
		$('#errorbox').css("display","block");
		$('#errortitle').html(title);
		$('#errormessage').html(contents);
		$('#overlay').css("display","block");
		$('#errorbox').scrollIntoView({ behavior: 'smooth', block: 'start', inline: 'start'});
}

/**
	** @return void
**/
function hideErrorMessage() {
		$('#errorbox').css("display","none");
		$('#errortitle').html("");
		$('#errormessage').html("");
		$('#overlay').css("display","none");
}

/**
	** @param string email
	** @return void
**/
function validateEmail(email) {
    	var re = /\S+@\S+\.\S+/;
    	return re.test(email);
}

/**
	** @return void
**/
function scrollToTop() {
  	document.body.scrollTop = 0; 
  	document.documentElement.scrollTop = 0; 
}