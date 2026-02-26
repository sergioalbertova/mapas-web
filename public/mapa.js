document.getElementById("selectPiso").addEventListener("change", async function () {
    const idpiso = this.value;

    if (idpiso === "") {
        document.getElementById("mapa").src = "";
        return;
    }

    const res = await fetch("cargarPiso.php?idpiso=" + idpiso);
    const data = await res.json();

    if (data.status === "success") {
        document.getElementById("mapa").src = data.imagen;
    } else {
        alert(data.message);
    }
});
