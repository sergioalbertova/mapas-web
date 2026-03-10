document.getElementById("loginForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);

    try {
        const res = await fetch("auth.php", {
            method: "POST",
            body: formData
        });

        const data = await res.json();
        const msg = document.getElementById("msg");

        if (data.status === "success") {
            msg.textContent = "Acceso correcto, redirigiendo...";
            msg.style.color = "green";

            setTimeout(() => {
                window.location.href = "index.html";  // ← AQUÍ VA TU NUEVO SISTEMA
            }, 800);
        } else {
            msg.textContent = data.message;
            msg.style.color = "red";
        }

    } catch (error) {
        console.error("Error en login:", error);
        document.getElementById("msg").textContent = "Error de conexión con el servidor";
        document.getElementById("msg").style.color = "red";
    }
});
