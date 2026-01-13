function expandMenu() {
    menuContainer = document.getElementById("menu");
    menuItems = menuContainer.childNodes;
    for(i=0; i<menuItems.length; i++) {
        if(menuItems[i].className == "menuitem") {
            menuItems[i].classList.add("show");
        }
    }
    document.getElementById("menubutton").onclick = function(){ collapseMenu()};
}
function collapseMenu() {
    menuContainer = document.getElementById("menu");
    menuItems = menuContainer.childNodes;
    for(i=0; i<menuItems.length; i++) {
        if(menuItems[i].className == "menuitem show") {
            menuItems[i].classList.remove("show");
        }
    }
    document.getElementById("menubutton").onclick = function(){ expandMenu()};
}

function switchOverlay() {
    if(document.getElementById("overlay").style.display == "block") {
        document.getElementById("overlay").style.display = "none";
    } else {
        document.getElementById("overlay").style.display = "block";
    }
}

function setErrorMessage(box,contents) {
    document.getElementById(box).innerHTML = contents;
}

function showErrorMessage(title,contents) {
    document.getElementById('errorbox').style.display = "block";
    document.getElementById('errortitle').innerHTML = title;
    document.getElementById('errormessage').innerHTML = contents;
    document.getElementById("overlay").style.display = "block";
}

function hideErrorMessage() {
    document.getElementById('errorbox').style.display = "none";
    document.getElementById('errortitle').innerHTML = "";
    document.getElementById('errormessage').innerHTML = "";
    document.getElementById("overlay").style.display = "none";
}
function validateEmail(email) {
    var re = /\S+@\S+\.\S+/;
    return re.test(email);
}