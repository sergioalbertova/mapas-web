document.getElementById("loginForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);

    const res = await fetch("login.php", {
        method: "POST",
        body: formData
    });

    const data = await res.json();
    const msg = document.getElementById("msg");

    if (data.status === "success") {
        msg.textContent = "Acceso correcto, redirigiendo...";
        msg.style.color = "green";

        setTimeout(() => {
            window.location.href = "dashboard.php";
        }, 800);
    } else {
        msg.textContent = data.message;
        msg.style.color = "red";
    }
});
