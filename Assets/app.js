function moveGroups(sourceBox,targetBox) {
    sourceBox = document.getElementById(sourceBox);
    targetBox = document.getElementById(targetBox);

    const removeNodes = [];
    removeNodesCounter = 0;

    for(i=0;i<sourceBox.options.length;i++){
        if(sourceBox.options[i].selected === true) {
            sourceBox.options[i].selected = false;
            optionElement = document.createElement("option");
            optionElement.text = sourceBox.options[i].text;
            optionElement.value = sourceBox.options[i].value;
            removeNodes[removeNodesCounter] = sourceBox.options[i];
            removeNodesCounter++;
            targetBox.sourceendChild(optionElement);
        }
    }

    for(i=0;i<removeNodes.length;i++){
        removeNodes[i].remove();
    }
}

function resetAllGroups(sourceBox,targetBox) {
    sourceBox = document.getElementById(sourceBox);
    targetBox = document.getElementById(targetBox);
    for(i=0;i<targetBox.options.length;i++){
        targetBox.options[i].selected = false;
        sourceBox.sourceendChild(targetBox.options[i]);
        targetBox.remove(targetBox.options[i]);
    }
}

function editUser(userid) {
    $.ajax({

        // Our sample url to make request
        url:
            '/ajax/getuserdetails?userid='+userid,

        // Type of Request
        type: "GET",

        // Function to call when to
        // request is ok
        success: function (data) {
            fullname = data.fullname;
            email = data.email;

            document.getElementById("useridedit").value=userid;
            document.getElementById("fullnameedit").value=decodeURIComponent(fullname);
            document.getElementById("emailedit").value=decodeURIComponent(email);

            savebutton = document.getElementById("saveexistinguserbutton");
            savebutton.disabled = false;
            savebutton.classList.remove("disabled");
            fullnamebox = document.getElementById("fullnameedit");
            emailbox = document.getElementById("emailedit");
            fullnamebox.classList.remove("error");
            emailbox.classList.remove("error");
            document.getElementById("edituser").style.display="block";
            fullnamebox.focus();

        },

        // Error handling
        error: function (error) {
            showErrorMessage('Fout','Er ging iets fout!');
        }
    });
}

function deleteUser(userid) {
    document.getElementById("useriddelete").value=userid;
    document.getElementById("deleteuser").style.display="block";
}

function reset2fa(userid) {
    document.getElementById("userid2fa").value=userid;
    document.getElementById("reset2fa").style.display="block";
}

function lockuser(userid) {
    document.getElementById("useridlock").value=userid;
    document.getElementById("lockuser").style.display="block";
}

function unlockuser(userid) {
    document.getElementById("useridunlock").value=userid;
    document.getElementById("unlockuser").style.display="block";
}

function selectAllOptions(box) {
    boxitem = document.getElementById(box);
    for(i=0;i<boxitem.options.length;i++){
        boxitem.options[i].selected=true;
    }
}

function resetpassword(userid) {
    document.getElementById("useridreset").value=userid;
    document.getElementById("resetpassword").style.display="block";
}

function verifyAdduser() {

    fullnamebox = document.getElementById("fullname");
    emailbox = document.getElementById("email");
    passwordbox = document.getElementById("password");
    savebutton = document.getElementById("savenewuserbutton");

    error = true;

    if(fullnamebox.value == "") {
        fullnamebox.classList.add("error");
    } else {
        fullnamebox.classList.remove("error");
    }

    if(emailbox.value == "" || validateEmail(emailbox.value) !== true) {
        emailbox.classList.add("error");
    } else {
        emailbox.classList.remove("error");
    }

    if(passwordbox.value == "") {
        passwordbox.classList.add("error");
    } else {
        passwordbox.classList.remove("error");
    }

    if(fullnamebox.value != "" && emailbox.value != "" && passwordbox.value != "" && validateEmail(emailbox.value) === true) {
        error = false;
        fullnamebox.classList.remove("error");
        emailbox.classList.remove("error");
        passwordbox.classList.remove("error");
    }

    if(error === true) {
        savebutton.disabled = true;
        savebutton.classList.add("disabled");
    } else {
        savebutton.disabled = false;
        savebutton.classList.remove("disabled");
    }
}

function verifyEdituser() {

    fullnamebox = document.getElementById("fullnameedit");
    emailbox = document.getElementById("emailedit");
    savebutton = document.getElementById("saveexistinguserbutton");

    error = true;

    if(fullnamebox.value == "") {
        fullnamebox.classList.add("error");
    } else {
        fullnamebox.classList.remove("error");
    }

    if(emailbox.value == "" || validateEmail(emailbox.value) !== true) {
        emailbox.classList.add("error");
    } else {
        emailbox.classList.remove("error");
    }

    if(fullnamebox.value != "" && emailbox.value != "" && validateEmail(emailbox.value) === true) {
        error = false;
        fullnamebox.classList.remove("error");
        emailbox.classList.remove("error");
        emailbox.classList.remove("error");
    }

    if(error === true) {
        savebutton.disabled = true;
        savebutton.classList.add("disabled");
    } else {
        savebutton.disabled = false;
        savebutton.classList.remove("disabled");
    }
}

function deleteSource(sourceid) {
    document.getElementById("sourceiddelete").value=sourceid;
    document.getElementById("deletesource").style.display="block";
}

function verifyAddsource() {

    sourcenamebox = document.getElementById("sourcename");
    savebutton = document.getElementById("savenewsourcebutton");

    error = true;

    if(sourcenamebox.value == "") {
        sourcenamebox.classList.add("error");
        error = true;
    } else {
        sourcenamebox.classList.remove("error");
        error = false;
    }

    if(error === true) {
        savebutton.disabled = true;
        savebutton.classList.add("disabled");
    } else {
        savebutton.disabled = false;
        savebutton.classList.remove("disabled");
    }
}

function sessionKeepAlive() {
    $.ajax({

        // Our sample url to make request
        url:
            '/ajax/sessionkeepalive',

        // Type of Request
        type: "GET",

        // Function to call when to
        // request is ok
        success: function (data) {
           setTimeout(sessionKeepAlive,60000);
        }
    });
}
