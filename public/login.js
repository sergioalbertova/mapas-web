document.getElementById("loginForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const msg = document.getElementById("msg");
    msg.textContent = "Validando...";
    msg.style.color = "#555";

    try {
        const res = await fetch("auth.php", {
            method: "POST",
            body: formData
        });

        // Si el servidor responde algo que no es JSON, esto evita que truene feo
        let data;
        try {
            data = await res.json();
        } catch {
            msg.textContent = "Error de conexión con el servidor";
            msg.style.color = "red";
            return;
        }

        if (data.status === "success") {
            msg.textContent = "Acceso correcto, redirigiendo...";
            msg.style.color = "green";

            setTimeout(() => {
                window.location.href = "index.html";
            }, 800);
        } else {
            msg.textContent = data.message || "Credenciales inválidas";
            msg.style.color = "red";
        }

    } catch (error) {
        console.error("Error en login:", error);
        msg.textContent = "Error de conexión con el servidor";
        msg.style.color = "red";
    }
});
