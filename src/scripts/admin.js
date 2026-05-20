const AUTH_KEY = "saborFlowAdminAutenticado";
const itensPorPagina = 5;
let paginaProdutos = 1;
let produtosFiltrados = [];

// Se não estiver logado, volta para a tela de login.
const acessoPainelBloqueado = (() => {
  const autenticado = localStorage.getItem(AUTH_KEY) === "true";

  if (!autenticado) {
    const paginaAtual = `${window.location.pathname.split("/").pop()}${window.location.search}`;
    window.location.replace(`./login.html?next=${encodeURIComponent(paginaAtual)}`);
    return true;
  }

  return false;
})();

function prepararPagina() {
  document.body.classList.add("app-ready");
  TotemStore.applyBranding();
}

function animarElementos() {
  const elementos = document.querySelectorAll(".section-heading, .metric-card, .quick-card, .operation-panel, .table-card, .form-card, .danger-zone, .kiosk-panel, .flow-card");
  elementos.forEach((elemento, index) => {
    elemento.classList.add("animate-in");
    elemento.style.animationDelay = `${Math.min(index * 55, 360)}ms`;
  });
}

function animarLinhas(container) {
  container.querySelectorAll("tr").forEach((linha, index) => {
    linha.classList.remove("row-enter");
    linha.style.animationDelay = `${index * 45}ms`;
    requestAnimationFrame(() => linha.classList.add("row-enter"));
  });
}

function animarContadores() {
  document.querySelectorAll("[data-count]").forEach((elemento) => {
    const destino = Number(elemento.dataset.count);
    if (!Number.isFinite(destino)) return;

    const sufixo = elemento.dataset.suffix || "";
    const duracao = 800;
    const inicio = performance.now();

    function atualizar(agora) {
      const progresso = Math.min((agora - inicio) / duracao, 1);
      const suavizado = 1 - Math.pow(1 - progresso, 3);
      elemento.textContent = `${Math.round(destino * suavizado)}${sufixo}`;
      if (progresso < 1) requestAnimationFrame(atualizar);
    }

    requestAnimationFrame(atualizar);
  });
}

function configurarRipple() {
  document.querySelectorAll(".button").forEach((botao) => {
    botao.addEventListener("click", (event) => {
      const rect = botao.getBoundingClientRect();
      const ripple = document.createElement("span");
      ripple.className = "button__ripple";
      ripple.style.left = `${event.clientX - rect.left}px`;
      ripple.style.top = `${event.clientY - rect.top}px`;
      botao.appendChild(ripple);
      setTimeout(() => ripple.remove(), 600);
    });
  });
}

function mostrarToast(titulo, mensagem, icone = "fa-circle-check") {
  let stack = document.querySelector(".toast-stack");

  if (!stack) {
    stack = document.createElement("div");
    stack.className = "toast-stack";
    document.body.appendChild(stack);
  }

  const toast = document.createElement("div");
  toast.className = "toast";
  toast.innerHTML = `
    <i class="fa-solid ${icone}"></i>
    <div>
      <strong>${titulo}</strong>
      <span>${mensagem}</span>
    </div>
  `;

  stack.appendChild(toast);
  setTimeout(() => {
    toast.classList.add("is-leaving");
    setTimeout(() => toast.remove(), 220);
  }, 2600);
}

function textoSeguro(valor) {
  return String(valor ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function exportarCsv(tipo) {
  const data = TotemStore.load();
  const linhas = {
    products: [
      ["nome", "categoria", "descricao", "ingredientes", "alergenos", "porcao", "calorias", "estoque", "preco", "status"],
      ...data.products.map((produto) => [
        produto.name,
        TotemStore.categoryName(produto.categoryId),
        produto.description,
        (produto.ingredients || []).join(" | "),
        (produto.allergens || []).join(" | "),
        produto.portion || "",
        produto.calories || "",
        produto.stock,
        produto.price,
        produto.status
      ])
    ],
    categories: [
      ["nome", "descricao", "ordem", "status"],
      ...data.categories.map((categoria) => [
        categoria.name,
        categoria.description,
        categoria.order,
        categoria.status
      ])
    ],
    admins: [
      ["nome", "email", "permissao", "status"],
      ...data.admins.map((admin) => [
        admin.name,
        admin.email,
        admin.role,
        admin.status
      ])
    ],
    finance: [
      ["tipo", "descricao", "forma", "valor", "data"],
      ["Abertura", "Saldo inicial", "Dinheiro", data.cash?.openingBalance || 0, data.cash?.openedAt || ""],
      ...(data.movements || []).map((movement) => [
        movement.type,
        movement.description,
        movement.payment,
        movement.amount,
        movement.createdAt
      ])
    ]
  }[tipo];

  if (!linhas) return;

  const csv = linhas
    .map((linha) => linha.map((campo) => `"${String(campo ?? "").replaceAll('"', '""')}"`).join(";"))
    .join("\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = `${tipo}-totem-lanche.csv`;
  link.click();
  URL.revokeObjectURL(url);
  mostrarToast("Arquivo gerado", "A exportação CSV foi criada.");
}

function badgeEstoque(qtd) {
  if (qtd > 10) return "badge badge--success";
  if (qtd > 5) return "badge badge--warning";
  return "badge badge--danger";
}

function badgeStatus(status) {
  if (status === "Ativo" || status === "Pronto" || status === "Retirado") return "badge badge--success";
  if (status === "Recebido" || status === "Em preparo") return "badge badge--warning";
  if (status === "Cancelado") return "badge badge--danger";
  return "badge badge--neutral";
}

// Preenche os selects de categoria nos formulários.
function preencherSelectCategorias(select, selectedId) {
  if (!select) return;

  const categorias = TotemStore.load().categories
    .slice()
    .sort((a, b) => a.order - b.order || a.name.localeCompare(b.name));

  select.innerHTML = [
    `<option value="">Sem categoria</option>`,
    ...categorias.map((categoria) => `
      <option value="${categoria.id}" ${Number(selectedId) === categoria.id ? "selected" : ""}>
        ${categoria.name}
      </option>
    `)
  ].join("");
}

// Atualiza o resumo da operação no dashboard.
function renderOperacao() {
  const grid = document.querySelector("[data-operation-grid]");
  if (!grid) return;

  const pedidos = TotemStore.load().orders;
  const statusCards = [
    { label: "Recebidos", status: "Recebido", icon: "fa-receipt" },
    { label: "Em preparo", status: "Em preparo", icon: "fa-kitchen-set" },
    { label: "Prontos", status: "Pronto", icon: "fa-bell-concierge" },
    { label: "Retirados", status: "Retirado", icon: "fa-bag-shopping" }
  ];

  grid.innerHTML = statusCards.map((card) => {
    const total = pedidos.filter((pedido) => pedido.status === card.status).length;
    return `
      <article class="operation-card">
        <div class="operation-card__top">
          <p class="operation-card__label">${card.label}</p>
          <span class="operation-card__icon"><i class="fa-solid ${card.icon}"></i></span>
        </div>
        <p class="operation-card__value">${total}</p>
      </article>
    `;
  }).join("");

  document.querySelector("[data-wait-display]")?.replaceChildren(
    document.createTextNode(`${TotemStore.getWaitTime()} min`)
  );
}

// Atualiza os números principais do dashboard.
function atualizarDashboard() {
  const data = TotemStore.load();
  const metricas = document.querySelectorAll("[data-count]");
  if (!metricas.length) return;

  const pedidosHoje = data.orders.filter((pedido) => {
    const hoje = new Date().toDateString();
    return new Date(pedido.createdAt).toDateString() === hoje;
  });
  const tempoMedio = TotemStore.getWaitTime();

  const valores = [data.products.length, pedidosHoje.length || data.orders.length, tempoMedio];
  metricas.forEach((elemento, index) => {
    elemento.dataset.count = valores[index] || 0;
    elemento.textContent = "0";
  });

  renderPedidosRecentes();
  renderOperacao();
}

function renderPedidosRecentes() {
  const tbody = document.querySelector("[data-orders-body]");
  if (!tbody) return;

  const pedidos = TotemStore.load().orders.slice(0, 6);

  tbody.innerHTML = pedidos.map((pedido) => `
    <tr>
      <td><strong>${pedido.code}</strong></td>
      <td>${pedido.customer}</td>
      <td>${pedido.items.reduce((total, item) => total + item.quantity, 0)} item(ns)</td>
      <td>${TotemStore.money(pedido.total)}</td>
      <td><span class="${badgeStatus(pedido.status)}">${pedido.status}</span></td>
      <td class="text-right">
        <button class="button button--ghost" type="button" data-next-order="${pedido.id}">
          Avançar
        </button>
      </td>
    </tr>
  `).join("");

  tbody.querySelectorAll("[data-next-order]").forEach((botao) => {
    botao.addEventListener("click", () => avancarPedido(Number(botao.dataset.nextOrder)));
  });

  animarLinhas(tbody);
}

function avancarPedido(id) {
  const ordem = ["Recebido", "Em preparo", "Pronto", "Retirado"];
  const pedido = TotemStore.load().orders.find((item) => item.id === id);
  if (!pedido) return;

  const atual = ordem.indexOf(pedido.status);
  const proximo = ordem[Math.min(atual + 1, ordem.length - 1)];
  TotemStore.updateOrderStatus(id, proximo);
  renderPedidosRecentes();
  mostrarToast("Pedido atualizado", `Pedido ${pedido.code} agora está como ${proximo}.`);
}

// Mostra uma prévia rápida dos itens que aparecem no totem.
function atualizarPreviaTotem() {
  const preview = document.querySelector("[data-kiosk-preview]");
  if (!preview) return;

  const produtos = TotemStore.activeProducts().slice(0, 2);
  preview.innerHTML = produtos.map((produto) => `
    <div class="kiosk-preview__item">
      <span class="product-thumb">
        ${produto.image
          ? `<img src="${textoSeguro(produto.image)}" alt="">`
          : TotemStore.initials(produto.name)
        }
      </span>
      <div>
        <strong>${textoSeguro(produto.name)}</strong>
        <span>${textoSeguro(produto.description)}</span>
      </div>
      <div class="kiosk-preview__price">${TotemStore.money(produto.price)}</div>
    </div>
  `).join("");
}

// Filtra os produtos pelo texto digitado na busca.
function carregarProdutosFiltrados() {
  const termo = document.querySelector("[data-products-search]")?.value.trim().toLowerCase() || "";
  const data = TotemStore.load();

  produtosFiltrados = data.products.filter((produto) => {
    const categoria = TotemStore.categoryName(produto.categoryId);
    const busca = [
      produto.name,
      categoria,
      produto.description,
      ...(produto.ingredients || []),
      ...(produto.tags || [])
    ].join(" ").toLowerCase();
    return busca.includes(termo);
  });
}

// Monta a tabela de produtos do cardápio.
function renderProdutos() {
  const tbody = document.querySelector("[data-products-body]");
  if (!tbody) return;

  carregarProdutosFiltrados();
  const inicio = (paginaProdutos - 1) * itensPorPagina;
  const pagina = produtosFiltrados.slice(inicio, inicio + itensPorPagina);

  tbody.innerHTML = pagina.map((produto) => `
    <tr>
      <td>
        <span class="product-thumb">
          ${produto.image
            ? `<img src="${textoSeguro(produto.image)}" alt="">`
            : TotemStore.initials(produto.name)
          }
        </span>
      </td>
      <td>
        <strong>${textoSeguro(produto.name)}</strong><br>
        <span class="muted">${TotemStore.categoryName(produto.categoryId)}</span>
      </td>
      <td>
        <span class="tag-list">
          ${(produto.tags?.length ? produto.tags : produto.options).map((opcao) => `<span class="tag">${textoSeguro(opcao)}</span>`).join("")}
        </span>
        <br>
        <span class="muted">${textoSeguro((produto.ingredients || []).slice(0, 4).join(", "))}</span>
      </td>
      <td class="text-right"><span class="${badgeEstoque(produto.stock)}">${produto.stock} un.</span></td>
      <td class="text-right"><strong>${TotemStore.money(produto.price)}</strong></td>
      <td class="text-center">
        <span class="row-actions">
          <button class="icon-button" type="button" title="Editar ${produto.name}" data-edit-product="${produto.id}">
            <i class="fa-solid fa-pen-to-square"></i>
          </button>
          <button class="icon-button" type="button" title="Excluir ${produto.name}" data-delete-product="${produto.id}">
            <i class="fa-solid fa-trash"></i>
          </button>
        </span>
      </td>
    </tr>
  `).join("");

  tbody.querySelectorAll("[data-edit-product]").forEach((botao) => {
    botao.addEventListener("click", () => abrirProdutoForm(Number(botao.dataset.editProduct)));
  });

  tbody.querySelectorAll("[data-delete-product]").forEach((botao) => {
    botao.addEventListener("click", () => excluirProduto(Number(botao.dataset.deleteProduct)));
  });

  const total = Math.max(1, Math.ceil(produtosFiltrados.length / itensPorPagina));
  const info = document.querySelector("[data-products-info]");
  const paginas = document.querySelector("[data-products-pages]");

  if (info) {
    const fim = Math.min(inicio + itensPorPagina, produtosFiltrados.length);
    info.textContent = produtosFiltrados.length
      ? `Mostrando ${inicio + 1}-${fim} de ${produtosFiltrados.length} itens`
      : "Nenhum item encontrado";
  }

  if (paginas) {
    paginas.innerHTML = Array.from({ length: total }, (_, i) => i + 1).map((paginaNumero) => `
      <button class="button ${paginaNumero === paginaProdutos ? "button--primary" : "button--ghost"}" type="button" data-product-page="${paginaNumero}">
        ${paginaNumero}
      </button>
    `).join("");

    paginas.querySelectorAll("[data-product-page]").forEach((botao) => {
      botao.addEventListener("click", () => {
        paginaProdutos = Number(botao.dataset.productPage);
        renderProdutos();
      });
    });
  }

  animarLinhas(tbody);
}

// Mostra a imagem escolhida antes de salvar.
function atualizarPreviaImagemProduto(valor = "") {
  const preview = document.querySelector("[data-product-image-preview]");
  if (!preview) return;

  const imagem = String(valor || "").trim();
  preview.innerHTML = imagem
    ? `<img src="${textoSeguro(imagem)}" alt="Prévia da imagem do produto">`
    : "<span>Prévia da imagem</span>";
}

// Permite enviar imagem do produto pelo próprio painel.
function configurarImagemProduto() {
  const form = document.querySelector("[data-product-form]");
  if (!form) return;

  const campoImagem = form.elements.image;
  const campoArquivo = form.elements.imageFile;
  const botaoLimpar = document.querySelector("[data-clear-product-image]");

  campoImagem?.addEventListener("input", () => atualizarPreviaImagemProduto(campoImagem.value));

  campoArquivo?.addEventListener("change", () => {
    const arquivo = campoArquivo.files?.[0];
    if (!arquivo) return;

    if (!arquivo.type.startsWith("image/")) {
      mostrarToast("Arquivo inválido", "Escolha uma imagem em PNG, JPG, WEBP ou SVG.", "fa-triangle-exclamation");
      campoArquivo.value = "";
      return;
    }

    if (arquivo.size > 900 * 1024) {
      mostrarToast("Imagem muito grande", "Escolha uma imagem com até 900 KB para salvar no navegador.", "fa-triangle-exclamation");
      campoArquivo.value = "";
      return;
    }

    const leitor = new FileReader();
    leitor.addEventListener("load", () => {
      campoImagem.value = leitor.result;
      atualizarPreviaImagemProduto(campoImagem.value);
      mostrarToast("Imagem carregada", "A prévia já está pronta. Clique em Salvar item para aplicar.");
    });
    leitor.readAsDataURL(arquivo);
  });

  botaoLimpar?.addEventListener("click", () => {
    campoImagem.value = "";
    if (campoArquivo) campoArquivo.value = "";
    atualizarPreviaImagemProduto("");
  });
}

function abrirProdutoForm(id) {
  const modal = document.querySelector("[data-product-modal]");
  const form = document.querySelector("[data-product-form]");
  if (!modal || !form) return;

  const produto = TotemStore.load().products.find((item) => item.id === Number(id));
  form.reset();
  preencherSelectCategorias(form.elements.categoryId, produto?.categoryId);

  form.elements.id.value = produto?.id || "";
  form.elements.name.value = produto?.name || "";
  form.elements.description.value = produto?.description || "";
  form.elements.image.value = produto?.image || "";
  form.elements.options.value = produto?.options?.join(", ") || "";
  form.elements.ingredients.value = produto?.ingredients?.join(", ") || "";
  form.elements.tags.value = produto?.tags?.join(", ") || "";
  form.elements.allergens.value = produto?.allergens?.join(", ") || "";
  form.elements.portion.value = produto?.portion || "";
  form.elements.calories.value = produto?.calories ?? "";
  form.elements.stock.value = produto?.stock ?? 0;
  form.elements.price.value = produto?.price ?? "";
  form.elements.prepTime.value = produto?.prepTime ?? 10;
  form.elements.icon.value = produto?.icon || "fa-burger";
  form.elements.status.value = produto?.status || "Ativo";
  form.elements.featured.checked = Boolean(produto?.featured);
  if (form.elements.imageFile) form.elements.imageFile.value = "";
  atualizarPreviaImagemProduto(form.elements.image.value);

  modal.classList.add("is-open");
}

function fecharProdutoForm() {
  document.querySelector("[data-product-modal]")?.classList.remove("is-open");
}

// Salva produto novo ou editado.
function salvarProduto(event) {
  event.preventDefault();
  const form = event.currentTarget;
  const nome = form.elements.name.value.trim();

  if (!nome) {
    mostrarToast("Campo obrigatório", "Informe o nome do item.", "fa-triangle-exclamation");
    return;
  }

  TotemStore.upsertProduct({
    id: form.elements.id.value,
    name: nome,
    categoryId: form.elements.categoryId.value,
    description: form.elements.description.value,
    image: form.elements.image.value,
    options: form.elements.options.value,
    ingredients: form.elements.ingredients.value,
    tags: form.elements.tags.value,
    allergens: form.elements.allergens.value,
    portion: form.elements.portion.value,
    calories: form.elements.calories.value,
    stock: form.elements.stock.value,
    price: form.elements.price.value,
    prepTime: form.elements.prepTime.value,
    icon: form.elements.icon.value,
    status: form.elements.status.value,
    featured: form.elements.featured.checked
  });

  fecharProdutoForm();
  renderProdutos();
  mostrarToast("Item salvo", "O cardápio do totem foi atualizado.");
}

function excluirProduto(id) {
  const produto = TotemStore.load().products.find((item) => item.id === Number(id));
  if (!produto) return;

  if (confirm(`Excluir "${produto.name}" do cardápio?`)) {
    TotemStore.deleteProduct(id);
    renderProdutos();
    mostrarToast("Item excluído", "O item foi removido do cardápio.", "fa-trash");
  }
}

// Liga os botões da tela de produtos.
function configurarProdutos() {
  const search = document.querySelector("[data-products-search]");
  if (search) {
    search.addEventListener("input", () => {
      paginaProdutos = 1;
      renderProdutos();
    });
  }

  document.querySelector("[data-open-product-form]")?.addEventListener("click", () => abrirProdutoForm());
  document.querySelectorAll("[data-close-product-form]").forEach((botao) => {
    botao.addEventListener("click", fecharProdutoForm);
  });
  document.querySelector("[data-product-form]")?.addEventListener("submit", salvarProduto);
}

function renderCategorias() {
  const tbody = document.querySelector("[data-categories-body]");
  if (!tbody) return;

  const data = TotemStore.load();
  const categorias = data.categories.slice().sort((a, b) => a.order - b.order || a.name.localeCompare(b.name));

  tbody.innerHTML = categorias.map((categoria) => {
    const total = data.products.filter((produto) => produto.categoryId === categoria.id).length;
    return `
      <tr>
        <td class="muted">#CAT-${String(categoria.id).padStart(4, "0")}</td>
        <td>
          <strong>${categoria.name}</strong><br>
          <span class="muted">${categoria.description || "Sem descrição"}</span>
        </td>
        <td>${total} item(ns)</td>
        <td><span class="${badgeStatus(categoria.status)}">${categoria.status}</span></td>
        <td class="text-right">
          <span class="row-actions">
            <a class="icon-button" title="Editar ${categoria.name}" href="./editar_categoria.html?id=${categoria.id}">
              <i class="fa-solid fa-pen-to-square"></i>
            </a>
            <a class="icon-button" title="Excluir ${categoria.name}" href="./excluir.html?id=${categoria.id}">
              <i class="fa-solid fa-trash"></i>
            </a>
          </span>
        </td>
      </tr>
    `;
  }).join("");

  document.querySelector("[data-categories-info]")?.replaceChildren(document.createTextNode(`Mostrando ${categorias.length} categoria(s) do cardápio`));
  animarLinhas(tbody);
}

function carregarCategoriaParaEdicao() {
  const form = document.querySelector("[data-category-form]");
  if (!form) return;

  const params = new URLSearchParams(window.location.search);
  const id = Number(params.get("id"));
  const categoria = TotemStore.load().categories.find((item) => item.id === id);

  if (form.dataset.mode === "edit" && !categoria) {
    alert("Categoria não encontrada.");
    window.location.href = "./lista-de-categoria.html";
    return;
  }

  form.elements.id.value = categoria?.id || "";
  form.elements.name.value = categoria?.name || "";
  form.elements.description.value = categoria?.description || "";
  form.elements.order.value = categoria?.order || "";
  form.elements.status.value = categoria?.status || "Ativo";

  document.querySelector("[data-category-subtitle]")?.replaceChildren(
    document.createTextNode(categoria ? `ID da categoria: #CAT-${String(categoria.id).padStart(4, "0")}` : "Nova categoria")
  );
  document.querySelector("[data-current-category]")?.replaceChildren(
    document.createTextNode(categoria ? `Editar: ${categoria.name}` : "Adicionar")
  );

  const deleteLink = document.querySelector("#delete-category-link");
  if (deleteLink && categoria) deleteLink.href = `./excluir.html?id=${categoria.id}`;
}

function salvarCategoria(event) {
  event.preventDefault();
  const form = event.currentTarget;
  const nome = form.elements.name.value.trim();

  if (!nome) {
    alert("Preencha o nome da categoria.");
    form.elements.name.focus();
    return;
  }

  TotemStore.upsertCategory({
    id: form.elements.id.value,
    name: nome,
    description: form.elements.description.value,
    order: form.elements.order.value,
    status: form.elements.status.value
  });

  mostrarToast("Categoria salva", `Categoria "${nome}" registrada com sucesso.`);
  setTimeout(() => {
    window.location.href = "./lista-de-categoria.html";
  }, 650);
}

function confirmarExclusao() {
  const button = document.querySelector("[data-confirm-delete]");
  if (!button) return;

  const params = new URLSearchParams(window.location.search);
  const id = Number(params.get("id"));
  const categoria = TotemStore.load().categories.find((item) => item.id === id);
  document.querySelector("[data-delete-name]")?.replaceChildren(document.createTextNode(categoria?.name || "categoria selecionada"));

  button.addEventListener("click", () => {
    if (categoria) TotemStore.deleteCategory(categoria.id);
    mostrarToast("Categoria excluída", "Os itens vinculados ficaram sem categoria.", "fa-trash");
    setTimeout(() => {
      window.location.href = "./lista-de-categoria.html";
    }, 650);
  });
}

function renderAdministradores() {
  const tbody = document.querySelector("[data-admins-body]");
  if (!tbody) return;

  const admins = TotemStore.load().admins;
  tbody.innerHTML = admins.map((admin) => `
    <tr>
      <td><span class="avatar-mini">${TotemStore.initials(admin.name)}</span></td>
      <td>
        <strong>${admin.name}</strong><br>
        <span class="muted">${admin.email}</span>
      </td>
      <td>${admin.role}</td>
      <td><span class="${badgeStatus(admin.status)}">${admin.status}</span></td>
      <td class="text-right">
        <span class="row-actions">
          <button class="icon-button" type="button" title="Editar ${admin.name}" data-edit-admin="${admin.id}">
            <i class="fa-solid fa-pen-to-square"></i>
          </button>
          <button class="icon-button" type="button" title="Ativar ou bloquear ${admin.name}" data-toggle-admin="${admin.id}">
            <i class="fa-solid fa-user-lock"></i>
          </button>
          <button class="icon-button" type="button" title="Excluir ${admin.name}" data-delete-admin="${admin.id}">
            <i class="fa-solid fa-trash"></i>
          </button>
        </span>
      </td>
    </tr>
  `).join("");

  document.querySelector("[data-admins-info]")?.replaceChildren(document.createTextNode(`Mostrando ${admins.length} administrador(es)`));

  tbody.querySelectorAll("[data-edit-admin]").forEach((botao) => {
    botao.addEventListener("click", () => abrirAdminForm(Number(botao.dataset.editAdmin)));
  });

  tbody.querySelectorAll("[data-toggle-admin]").forEach((botao) => {
    botao.addEventListener("click", () => {
      const admin = TotemStore.toggleAdminStatus(Number(botao.dataset.toggleAdmin));
      renderAdministradores();
      if (admin) mostrarToast("Acesso atualizado", `${admin.name} agora está ${admin.status}.`);
    });
  });

  tbody.querySelectorAll("[data-delete-admin]").forEach((botao) => {
    botao.addEventListener("click", () => excluirAdmin(Number(botao.dataset.deleteAdmin)));
  });

  animarLinhas(tbody);
}

function abrirAdminForm(id) {
  const modal = document.querySelector("[data-admin-modal]");
  const form = document.querySelector("[data-admin-form]");
  if (!modal || !form) return;

  const admin = TotemStore.load().admins.find((item) => item.id === Number(id));
  form.reset();
  form.elements.id.value = admin?.id || "";
  form.elements.name.value = admin?.name || "";
  form.elements.email.value = admin?.email || "";
  form.elements.role.value = admin?.role || "Leitura";
  form.elements.status.value = admin?.status || "Ativo";
  modal.classList.add("is-open");
}

function fecharAdminForm() {
  document.querySelector("[data-admin-modal]")?.classList.remove("is-open");
}

function salvarAdmin(event) {
  event.preventDefault();
  const form = event.currentTarget;

  if (!form.elements.name.value.trim() || !form.elements.email.value.trim()) {
    mostrarToast("Campos obrigatórios", "Informe nome e e-mail do administrador.", "fa-triangle-exclamation");
    return;
  }

  TotemStore.upsertAdmin({
    id: form.elements.id.value,
    name: form.elements.name.value,
    email: form.elements.email.value,
    role: form.elements.role.value,
    status: form.elements.status.value
  });

  fecharAdminForm();
  renderAdministradores();
  mostrarToast("Administrador salvo", "O acesso foi registrado no painel.");
}

function excluirAdmin(id) {
  const admin = TotemStore.load().admins.find((item) => item.id === Number(id));
  if (!admin) return;

  if (confirm(`Excluir o administrador "${admin.name}"?`)) {
    TotemStore.deleteAdmin(id);
    renderAdministradores();
    mostrarToast("Administrador excluído", "O usuário foi removido da lista.", "fa-trash");
  }
}

function configurarAdministradores() {
  document.querySelector("[data-open-admin-form]")?.addEventListener("click", () => abrirAdminForm());
  document.querySelectorAll("[data-close-admin-form]").forEach((botao) => {
    botao.addEventListener("click", fecharAdminForm);
  });
  document.querySelector("[data-admin-form]")?.addEventListener("submit", salvarAdmin);
}

function dataEhHoje(valor) {
  return new Date(valor).toDateString() === new Date().toDateString();
}

function dataCurta(valor) {
  return new Date(valor).toLocaleString("pt-BR", {
    day: "2-digit",
    month: "2-digit",
    hour: "2-digit",
    minute: "2-digit"
  });
}

function resumoFinanceiro() {
  const data = TotemStore.load();
  const pedidosHoje = data.orders.filter((pedido) => dataEhHoje(pedido.createdAt) && pedido.status !== "Cancelado");
  const movimentosHoje = (data.movements || []).filter((movement) => dataEhHoje(movement.createdAt));
  const formas = ["Pix", "Cartão", "Dinheiro"];
  const pagamentos = formas.map((forma) => {
    const pedidos = pedidosHoje.filter((pedido) => pedido.payment === forma);
    return {
      forma,
      pedidos: pedidos.length,
      total: pedidos.reduce((soma, pedido) => soma + Number(pedido.total || 0), 0)
    };
  });
  const faturamento = pagamentos.reduce((soma, item) => soma + item.total, 0);
  const entradas = movimentosHoje
    .filter((movement) => movement.type === "Entrada")
    .reduce((soma, movement) => soma + Number(movement.amount || 0), 0);
  const saidas = movimentosHoje
    .filter((movement) => movement.type === "Saida")
    .reduce((soma, movement) => soma + Number(movement.amount || 0), 0);
  const vendasDinheiro = pagamentos.find((item) => item.forma === "Dinheiro")?.total || 0;
  const abertura = Number(data.cash?.openingBalance || 0);
  const saldo = abertura + vendasDinheiro + entradas - saidas;

  return { data, pedidosHoje, movimentosHoje, pagamentos, faturamento, entradas, saidas, vendasDinheiro, abertura, saldo };
}

function renderFinanceiro() {
  const salesEl = document.querySelector("[data-finance-sales]");
  if (!salesEl) return;

  const resumo = resumoFinanceiro();
  salesEl.textContent = TotemStore.money(resumo.faturamento);
  document.querySelector("[data-finance-balance]").textContent = TotemStore.money(resumo.saldo);
  document.querySelector("[data-finance-income]").textContent = TotemStore.money(resumo.entradas);
  document.querySelector("[data-finance-expense]").textContent = TotemStore.money(resumo.saidas);

  const cashForm = document.querySelector("[data-cash-form]");
  if (cashForm) {
    cashForm.elements.openingBalance.value = resumo.data.cash?.openingBalance || 0;
    cashForm.elements.status.value = resumo.data.cash?.status || "Aberto";
  }

  document.querySelector("[data-cash-subtitle]")?.replaceChildren(
    document.createTextNode(`Status: ${resumo.data.cash?.status || "Aberto"} • aberto em ${dataCurta(resumo.data.cash?.openedAt || new Date())}`)
  );

  renderMovimentosFinanceiros(resumo.movimentosHoje);
  renderResumoPagamentos(resumo.pagamentos);
}

function renderMovimentosFinanceiros(movimentos) {
  const tbody = document.querySelector("[data-movements-body]");
  if (!tbody) return;

  if (!movimentos.length) {
    tbody.innerHTML = `
      <tr>
        <td class="text-center muted" colspan="6">Nenhum movimento manual registrado hoje</td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = movimentos.map((movement) => `
    <tr>
      <td><span class="${movement.type === "Entrada" ? "badge badge--success" : "badge badge--danger"}">${movement.type === "Saida" ? "Saída" : "Entrada"}</span></td>
      <td>${textoSeguro(movement.description)}</td>
      <td>${textoSeguro(movement.payment)}</td>
      <td>${dataCurta(movement.createdAt)}</td>
      <td class="text-right"><strong>${TotemStore.money(movement.amount)}</strong></td>
      <td class="text-right">
        <button class="icon-button" type="button" title="Excluir movimento" data-delete-movement="${movement.id}">
          <i class="fa-solid fa-trash"></i>
        </button>
      </td>
    </tr>
  `).join("");

  tbody.querySelectorAll("[data-delete-movement]").forEach((botao) => {
    botao.addEventListener("click", () => {
      TotemStore.deleteCashMovement(Number(botao.dataset.deleteMovement));
      renderFinanceiro();
      mostrarToast("Movimento removido", "O lançamento foi retirado do caixa.", "fa-trash");
    });
  });

  animarLinhas(tbody);
}

function renderResumoPagamentos(pagamentos) {
  const tbody = document.querySelector("[data-payment-summary]");
  if (!tbody) return;

  tbody.innerHTML = pagamentos.map((item) => `
    <tr>
      <td><strong>${item.forma}</strong></td>
      <td>${item.pedidos} pedido(s)</td>
      <td class="text-right"><strong>${TotemStore.money(item.total)}</strong></td>
    </tr>
  `).join("");

  animarLinhas(tbody);
}

function salvarCaixa(event) {
  event.preventDefault();
  const form = event.currentTarget;
  TotemStore.updateCash({
    openingBalance: form.elements.openingBalance.value,
    status: form.elements.status.value
  });
  renderFinanceiro();
  mostrarToast("Caixa atualizado", "As informações do caixa foram salvas.");
}

function salvarMovimento(event) {
  event.preventDefault();
  const form = event.currentTarget;
  const amount = Number(form.elements.amount.value);
  const description = form.elements.description.value.trim();

  if (!Number.isFinite(amount) || amount <= 0 || !description) {
    mostrarToast("Dados incompletos", "Informe uma descrição e um valor maior que zero.", "fa-triangle-exclamation");
    return;
  }

  TotemStore.addCashMovement({
    type: form.elements.type.value,
    description,
    amount,
    payment: form.elements.payment.value
  });

  form.reset();
  renderFinanceiro();
  mostrarToast("Movimento registrado", "O lançamento entrou no caixa.");
}

function configurarFinanceiro() {
  document.querySelector("[data-cash-form]")?.addEventListener("submit", salvarCaixa);
  document.querySelector("[data-movement-form]")?.addEventListener("submit", salvarMovimento);
}

function configurarExportacoes() {
  document.querySelectorAll("[data-export]").forEach((botao) => {
    botao.addEventListener("click", () => exportarCsv(botao.dataset.export));
  });
}

// Abre o modal onde muda tempo, nome e logo da loja.
function abrirConfiguracoes() {
  const modal = document.querySelector("[data-settings-modal]");
  const form = document.querySelector("[data-settings-form]");
  if (!modal || !form) return;

  const settings = TotemStore.getSettings();
  form.elements.waitTime.value = settings.waitTime;
  form.elements.storeName.value = settings.storeName;
  form.elements.storeSubtitle.value = settings.storeSubtitle;
  form.elements.storeLogo.value = settings.storeLogo;
  if (form.elements.storeLogoFile) form.elements.storeLogoFile.value = "";
  atualizarPreviaLogoLoja(settings.storeLogo);
  modal.classList.add("is-open");
}

function fecharConfiguracoes() {
  document.querySelector("[data-settings-modal]")?.classList.remove("is-open");
}

// Salva as configurações gerais da loja.
function salvarConfiguracoes(event) {
  event.preventDefault();
  const form = event.currentTarget;
  const waitTime = Number(form.elements.waitTime.value);

  if (!Number.isFinite(waitTime) || waitTime < 1) {
    mostrarToast("Tempo inválido", "Informe uma previsão de pelo menos 1 minuto.", "fa-triangle-exclamation");
    return;
  }

  TotemStore.updateSettings({
    waitTime,
    storeName: form.elements.storeName.value,
    storeSubtitle: form.elements.storeSubtitle.value,
    storeLogo: form.elements.storeLogo.value
  });
  fecharConfiguracoes();
  TotemStore.applyBranding();
  atualizarDashboard();
  animarContadores();
  mostrarToast("Configurações salvas", "Nome, logo e tempo de espera foram atualizados.");
}

// Mostra a logo antes de salvar.
function atualizarPreviaLogoLoja(valor = "") {
  const preview = document.querySelector("[data-store-logo-preview]");
  if (!preview) return;

  const logo = String(valor || "").trim();
  preview.innerHTML = logo
    ? `<img src="${textoSeguro(logo)}" alt="Prévia da logo da loja">`
    : "<span>Prévia da logo</span>";
}

// Permite enviar a logo pelo painel.
function configurarLogoLoja() {
  const form = document.querySelector("[data-settings-form]");
  if (!form) return;

  const campoLogo = form.elements.storeLogo;
  const campoArquivo = form.elements.storeLogoFile;
  const botaoPadrao = document.querySelector("[data-clear-store-logo]");

  campoLogo?.addEventListener("input", () => atualizarPreviaLogoLoja(campoLogo.value));

  campoArquivo?.addEventListener("change", () => {
    const arquivo = campoArquivo.files?.[0];
    if (!arquivo) return;

    if (!arquivo.type.startsWith("image/")) {
      mostrarToast("Arquivo inválido", "Escolha uma imagem para a logo.", "fa-triangle-exclamation");
      campoArquivo.value = "";
      return;
    }

    if (arquivo.size > 700 * 1024) {
      mostrarToast("Logo muito grande", "Escolha uma imagem com até 700 KB.", "fa-triangle-exclamation");
      campoArquivo.value = "";
      return;
    }

    const leitor = new FileReader();
    leitor.addEventListener("load", () => {
      campoLogo.value = leitor.result;
      atualizarPreviaLogoLoja(campoLogo.value);
      mostrarToast("Logo carregada", "Agora clique em Salvar configurações.");
    });
    leitor.readAsDataURL(arquivo);
  });

  botaoPadrao?.addEventListener("click", () => {
    campoLogo.value = "../assets/brand/saborflow-logo.svg";
    if (campoArquivo) campoArquivo.value = "";
    atualizarPreviaLogoLoja(campoLogo.value);
  });
}

// Liga os botões de configuração do dashboard.
function configurarTempoEspera() {
  document.querySelectorAll("[data-open-settings]").forEach((botao) => {
    botao.addEventListener("click", abrirConfiguracoes);
  });

  document.querySelectorAll("[data-close-settings]").forEach((botao) => {
    botao.addEventListener("click", fecharConfiguracoes);
  });

  document.querySelector("[data-settings-modal]")?.addEventListener("click", (event) => {
    if (event.target.matches("[data-settings-modal]")) fecharConfiguracoes();
  });

  document.querySelector("[data-settings-form]")?.addEventListener("submit", salvarConfiguracoes);
  configurarLogoLoja();
}

document.addEventListener("DOMContentLoaded", () => {
  if (acessoPainelBloqueado) return;

  prepararPagina();
  atualizarDashboard();
  renderProdutos();
  renderCategorias();
  renderAdministradores();
  renderFinanceiro();
  carregarCategoriaParaEdicao();
  confirmarExclusao();
  configurarProdutos();
  configurarAdministradores();
  configurarFinanceiro();
  configurarExportacoes();
  configurarTempoEspera();
  configurarImagemProduto();
  animarElementos();
  animarContadores();
  configurarRipple();

  document.querySelector("[data-category-form]")?.addEventListener("submit", salvarCategoria);
});
