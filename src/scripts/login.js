document.addEventListener("DOMContentLoaded", () => {
  const AUTH_KEY = "saborFlowAdminAutenticado";
  const params = new URLSearchParams(window.location.search);

  // Sair limpa a sessão do painel.
  if (params.get("logout") === "1") {
    localStorage.removeItem(AUTH_KEY);
  }

  document.body.classList.add("app-ready");
  TotemStore.applyBranding();

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

    // Login simples para apresentação.
    const email = document.querySelector("#email").value.trim().toLowerCase();
    const senha = document.querySelector("#senha").value.trim();
    const usuarioValido = email === "admin" || email === "admin@saborflow.com";

    if (usuarioValido && senha === "123") {
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
