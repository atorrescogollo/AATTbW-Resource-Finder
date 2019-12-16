function validateLogin() {
    var form = document.forms["loginform"];
    var user = form["username"];
    user.value = user.value.trim();

    var pass = form["password"];
    pass.value = pass.value.trim();

    if (!pass.value.match(/^[a-z0-9_-]{6,10}$/i)) {
        pass.value = "";
        alert("La contraseña tiene que tener entre 6 y 10 caracteres.");
        return false;
    }
}