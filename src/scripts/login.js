document.addEventListener("DOMContentLoaded", () => {
  /*
    Login simples para a apresentacao.
    Usuario principal: admin
    Senha: 123

    Cada usuario usa uma base separada no navegador.
    Assim o admin fica com o cardapio dele e outra conta comeca separada.
  */

  const AUTH_KEY = "gastroTechAdminAutenticado";
  const params = new URLSearchParams(window.location.search);

  // Sair limpa a sessão do painel.
  if (params.get("logout") === "1") {
    localStorage.removeItem(AUTH_KEY);
  }

  document.body.classList.add("app-ready");
  TotemStore.aplicarMarca();

  const form = document.querySelector("#login-form");
  if (!form) return;

  function destinoSeguro(valor) {
    if (!valor || valor.includes("://") || valor.startsWith("/") || valor.includes("\\")) {
      return "./dashboard.html";
    }

    return `./${valor.replace(/^\.\//, "")}`;
  }

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    // Qualquer usuario com senha 123 entra em uma base propria.
    const email = document.querySelector("#email").value.trim().toLowerCase();
    const senha = document.querySelector("#senha").value.trim();
    const usuarioValido = email.length > 0;

    if (usuarioValido && senha === "123") {
      TotemStore.definirContaAtual(email);
      TotemStore.carregarDados();
      localStorage.setItem(AUTH_KEY, "true");
      window.location.href = destinoSeguro(params.get("next"));
      return;
    }

    const card = document.querySelector(".login-card");
    card.classList.remove("is-shaking");
    requestAnimationFrame(() => card.classList.add("is-shaking"));
    alert("Usuário ou senha incorretos.");
  });
});
