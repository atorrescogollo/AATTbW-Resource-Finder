function validateEmail(mail) {
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail)) {
        return (true)
    }
    return (false)
}

function validateLogin() {
    var form = document.forms["loginform"];
    var user = form["username"];
    user.value = user.value.trim();

    var pass = form["password"];
    pass.value = pass.value.trim();

    if (!validateEmail(user.value)) {
        user.value = "";
        pass.value = "";
        alert("El login debe hacerse con una cuenta de correo.");
        return false;
    }
    if (!pass.value.match(/^[a-z0-9_-]{6,10}$/i)) {
        pass.value = "";
        alert("La contraseña tiene que tener entre 6 y 10 caracteres.");
        return false;
    }
}

function popupdetail(url) {
    var w = 700;
    var h = 600;
    var centerLeft = (screen.width / 2) - (w / 2);
    var centerTop = (screen.height / 2) - (h / 2);

    var windowFeatures = 'width=' + w + ', height=' + h + ', top=' + centerTop + ', left=' + centerLeft;
    return window.open(url, 'Vista Detalle', windowFeatures);
}