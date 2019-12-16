function validateFilter() {
    var form = document.forms["filterform"];
    var filter = form["filter"];
    filter.value = filter.value.trim();
    if (!filter.value.match(/^[a-z]+[a-z -]*$/i)) {
        filter.value = "";
        alert("El filtro debe tener únicamente letras, espacios y guiones.");
        return false;
    }
}