//validação de login para sair de Login para o dashboard

document.getElementById("entrar").addEventListener("click", function() {

    let email = document.getElementById("email").value;
    let senha = document.getElementById("senha").value;

    if (email === "admin" && senha === "123") {
        window.location.href = "dashboard.html";
    } else {
        alert("Usuário ou senha incorretos!");
    }

});