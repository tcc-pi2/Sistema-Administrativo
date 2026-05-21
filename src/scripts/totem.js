let categoriaAtual = "todos";
let pagamentoAtual = "Pix";
const carrinho = new Map();
let produtoPersonalizado = null;
let escolhasPersonalizadas = {};

/*
  Este arquivo controla a tela do cliente.

  Aqui ficam:
  - categorias do lado esquerdo
  - cards dos lanches
  - carrinho
  - escolha de bebida/gelo
  - finalizacao do pedido
  - acompanhamento pelo codigo
*/

// Etapas usadas para mostrar o andamento do pedido para o cliente.
const etapasPedido = [
  { status: "Recebido", label: "Recebido", icon: "fa-receipt" },
  { status: "Em preparo", label: "Em preparo", icon: "fa-kitchen-set" },
  { status: "Pronto", label: "Pronto", icon: "fa-bell-concierge" },
  { status: "Retirado", label: "Retirado", icon: "fa-bag-shopping" }
];
let pedidoConfirmadoId = null;
let codigoAcompanhado = "";
let statusTimer = null;

function textoSeguro(valor) {
  // Protege a tela caso algum texto tenha <, > ou aspas.
  return String(valor ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

// Pequenos marcadores que aparecem nos cards do cardápio.
function listaChipsHtml(items, classe = "product-chip") {
  return (items || [])
    .filter(Boolean)
    .map((item) => `<span class="${classe}">${textoSeguro(item)}</span>`)
    .join("");
}

// Confere se o produto precisa abrir escolhas antes de ir para o carrinho.
function temPersonalizacao(produto) {
  return Array.isArray(produto?.customizations) && produto.customizations.length > 0;
}

// Pega a primeira opção de cada grupo para deixar tudo preenchido.
function escolhasPadrao(produto) {
  return (produto.customizations || []).reduce((resultado, grupo) => {
    resultado[grupo.label] = grupo.choices?.[0] || "";
    return resultado;
  }, {});
}

function textoDasEscolhas(escolhas = {}) {
  // Transforma escolhas em texto. Ex: Bebida: Coca-Cola • Gelo: Sem gelo.
  return Object.entries(escolhas)
    .filter(([, valor]) => valor)
    .map(([nome, valor]) => `${nome}: ${valor}`)
    .join(" • ");
}

function chaveDoItem(produtoId, escolhas = {}) {
  // Faz itens iguais com escolhas diferentes ficarem separados no carrinho.
  const partes = Object.keys(escolhas)
    .sort()
    .map((nome) => `${nome}=${escolhas[nome]}`);
  return `${produtoId}|${partes.join("|")}`;
}

function quantidadeProdutoNoCarrinho(produtoId) {
  // Soma o total daquele produto no carrinho para respeitar o estoque.
  return [...carrinho.values()]
    .filter((item) => item.product.id === produtoId)
    .reduce((total, item) => total + item.quantity, 0);
}

// Escolhe um ícone para a categoria pelo nome dela.
function categoriaIcone(nome) {
  const texto = String(nome || "").toLowerCase();
  if (texto.includes("combo") || texto.includes("hamb")) return "fa-burger";
  if (texto.includes("por")) return "fa-bowl-food";
  if (texto.includes("beb")) return "fa-glass-water";
  if (texto.includes("sobre")) return "fa-ice-cream";
  if (texto.includes("adicion")) return "fa-droplet";
  return "fa-table-cells-large";
}

// Monta os botões de categoria do lado esquerdo.
function renderCategoriasTotem() {
  const lista = document.querySelector("[data-categories]");
  const categorias = [
    { id: "todos", name: "Todos", icon: "fa-table-cells-large" },
    ...TotemStore.categoriasAtivas().map((categoria) => ({
      id: String(categoria.id),
      name: categoria.name,
      icon: categoriaIcone(categoria.name)
    }))
  ];

  lista.innerHTML = categorias.map((categoria) => `
    <button class="category-button ${categoria.id === categoriaAtual ? "is-active" : ""}" type="button" data-category="${categoria.id}">
      <i class="fa-solid ${categoria.icon}"></i>
      <span>${categoria.name}</span>
    </button>
  `).join("");

  lista.querySelectorAll("[data-category]").forEach((botao) => {
    botao.addEventListener("click", () => {
      categoriaAtual = botao.dataset.category;
      renderCategoriasTotem();
      renderProdutosTotem();
    });
  });
}

// Filtra os produtos conforme a categoria escolhida.
function produtosVisiveis() {
  const produtos = TotemStore.produtosAtivos();
  if (categoriaAtual === "todos") return produtos;
  return produtos.filter((produto) => String(produto.categoryId) === categoriaAtual);
}

// Monta os cards dos alimentos no totem.
function renderProdutosTotem() {
  const grid = document.querySelector("[data-products]");
  const lista = produtosVisiveis();

  if (!lista.length) {
    grid.innerHTML = `
      <div class="cart-empty">
        <span>
          <i class="fa-solid fa-circle-info"></i><br><br>
          Nenhum item disponível nessa categoria
        </span>
      </div>
    `;
    return;
  }

  grid.innerHTML = lista.map((produto, index) => `
    <article class="product-card" style="animation-delay: ${index * 45}ms">
      <div class="product-card__media">
        ${produto.image
          ? `<img src="${textoSeguro(produto.image)}" alt="Imagem de ${textoSeguro(produto.name)}">`
          : `<span class="product-card__icon"><i class="fa-solid ${produto.icon || "fa-burger"}"></i></span>`
        }
        <span class="product-card__time">
          <i class="fa-solid fa-clock"></i>
          ${produto.prepTime} min
        </span>
      </div>

      <div class="product-card__content">
        <div class="product-card__heading">
          <h3>${textoSeguro(produto.name)}</h3>
          ${produto.featured ? `<span class="featured-pill">Destaque</span>` : ""}
        </div>
        <p>${textoSeguro(produto.description || TotemStore.nomeCategoria(produto.categoryId))}</p>

        <div class="product-card__chips">
          ${listaChipsHtml(produto.tags)}
          ${temPersonalizacao(produto) ? `<span class="product-chip">Escolha bebida/gelo</span>` : ""}
        </div>

        <div class="ingredient-panel">
          <strong>Ingredientes</strong>
          <span>${(produto.ingredients || []).map(textoSeguro).join(", ") || "Consultar atendimento"}</span>
        </div>

        <div class="product-card__details">
          ${produto.portion ? `<span><i class="fa-solid fa-box-open"></i>${textoSeguro(produto.portion)}</span>` : ""}
          ${produto.calories ? `<span><i class="fa-solid fa-fire"></i>${produto.calories} kcal</span>` : ""}
          ${(produto.allergens || []).length ? `<span><i class="fa-solid fa-circle-info"></i>Contém ${produto.allergens.map(textoSeguro).join(", ")}</span>` : ""}
        </div>

        <div class="product-card__meta">
          <span>
            <strong class="price">${TotemStore.formatarDinheiro(produto.price)}</strong>
          </span>
          <button class="add-button" type="button" data-add="${produto.id}">
            <i class="fa-solid fa-plus"></i>
            ${temPersonalizacao(produto) ? "Escolher" : "Adicionar"}
          </button>
        </div>
      </div>
    </article>
  `).join("");

  grid.querySelectorAll("[data-add]").forEach((botao) => {
    botao.addEventListener("click", () => {
      const id = Number(botao.dataset.add);
      const produto = TotemStore.produtosAtivos().find((item) => item.id === id);
      if (temPersonalizacao(produto)) abrirPersonalizacao(id);
      else adicionarItem(id);
    });
  });
}

// Adiciona o produto no carrinho.
function adicionarItem(id, escolhas = {}) {
  const produto = TotemStore.produtosAtivos().find((item) => item.id === id);
  if (!produto) return;

  if (quantidadeProdutoNoCarrinho(id) >= produto.stock) {
    mostrarToastTotem("Estoque insuficiente", "Não há mais unidades disponíveis desse item.");
    return;
  }

  const chave = chaveDoItem(id, escolhas);
  const atual = carrinho.get(chave) || {
    key: chave,
    product: produto,
    quantity: 0,
    options: escolhas,
    notes: textoDasEscolhas(escolhas)
  };

  atual.quantity += 1;
  carrinho.set(chave, atual);
  renderCarrinho();
  mostrarToastTotem("Item adicionado", `${produto.name} entrou no pedido.`);
}

// Aumenta ou diminui a quantidade no carrinho.
function alterarQuantidade(chave, delta) {
  const item = carrinho.get(chave);
  if (!item) return;

  if (delta > 0 && quantidadeProdutoNoCarrinho(item.product.id) >= item.product.stock) {
    mostrarToastTotem("Estoque insuficiente", "Não há mais unidades disponíveis desse item.");
    return;
  }

  item.quantity += delta;
  if (item.quantity <= 0) carrinho.delete(chave);
  renderCarrinho();
}

// Calcula total, quantidade e tempo previsto.
function resumoCarrinho() {
  const items = [...carrinho.values()];
  const total = items.reduce((soma, item) => soma + item.product.price * item.quantity, 0);
  const quantity = items.reduce((soma, item) => soma + item.quantity, 0);
  const prepTime = quantity ? TotemStore.pegarTempoEspera() : 0;
  return { items, total, quantity, prepTime };
}

// Atualiza o carrinho na tela.
function renderCarrinho() {
  const lista = document.querySelector("[data-cart-items]");
  const total = document.querySelector("[data-total]");
  const finish = document.querySelector("[data-finish]");
  const { items, total: valorTotal, quantity, prepTime } = resumoCarrinho();

  if (!items.length) {
    lista.innerHTML = `
      <div class="cart-empty">
        <span>
          <i class="fa-solid fa-basket-shopping"></i><br><br>
          Seu pedido ainda está vazio
        </span>
      </div>
    `;
  } else {
    lista.innerHTML = items.map((item) => `
      <div class="cart-item">
        <div class="cart-item__top">
          <span class="cart-item__name">
            ${item.product.image ? `<img src="${textoSeguro(item.product.image)}" alt="">` : ""}
            <strong>
              ${textoSeguro(item.product.name)}
              ${item.notes ? `<small class="cart-item__options">${textoSeguro(item.notes)}</small>` : ""}
            </strong>
          </span>
          <span>${TotemStore.formatarDinheiro(item.product.price * item.quantity)}</span>
        </div>
        <div class="cart-item__actions">
          <span>${TotemStore.formatarDinheiro(item.product.price)} cada</span>
          <div>
            <button class="quantity-button" type="button" data-dec="${textoSeguro(item.key)}">-</button>
            <strong>${item.quantity}</strong>
            <button class="quantity-button" type="button" data-inc="${textoSeguro(item.key)}">+</button>
          </div>
        </div>
      </div>
    `).join("");
  }

  lista.querySelectorAll("[data-dec]").forEach((botao) => {
    botao.addEventListener("click", () => alterarQuantidade(botao.dataset.dec, -1));
  });

  lista.querySelectorAll("[data-inc]").forEach((botao) => {
    botao.addEventListener("click", () => alterarQuantidade(botao.dataset.inc, 1));
  });

  total.textContent = TotemStore.formatarDinheiro(valorTotal);
  finish.disabled = quantity === 0;
  document.querySelector("[data-cart-summary]").textContent =
    quantity ? `${quantity} item(ns) no carrinho` : "Escolha os itens do pedido";
}

// Seleciona a forma de pagamento.
function configurarPagamento() {
  document.querySelectorAll("[data-payment]").forEach((botao) => {
    botao.addEventListener("click", () => {
      pagamentoAtual = botao.dataset.payment;
      document.querySelectorAll("[data-payment]").forEach((item) => item.classList.remove("is-active"));
      botao.classList.add("is-active");
    });
  });
}

// Abre as escolhas extras de bebidas e gelo.
function abrirPersonalizacao(id) {
  produtoPersonalizado = TotemStore.produtosAtivos().find((produto) => produto.id === id);
  if (!produtoPersonalizado) return;

  escolhasPersonalizadas = escolhasPadrao(produtoPersonalizado);
  renderPersonalizacao();
  document.querySelector("[data-options-modal]")?.classList.add("is-open");
}

function fecharPersonalizacao() {
  produtoPersonalizado = null;
  escolhasPersonalizadas = {};
  document.querySelector("[data-options-modal]")?.classList.remove("is-open");
}

function renderPersonalizacao() {
  const titulo = document.querySelector("[data-options-title]");
  const lista = document.querySelector("[data-options-list]");
  if (!produtoPersonalizado || !titulo || !lista) return;

  titulo.textContent = `Escolha: ${produtoPersonalizado.name}`;
  lista.innerHTML = produtoPersonalizado.customizations.map((grupo) => `
    <div class="option-group">
      <strong>${textoSeguro(grupo.label)}</strong>
      <div class="option-buttons">
        ${(grupo.choices || []).map((opcao) => `
          <button class="option-button ${escolhasPersonalizadas[grupo.label] === opcao ? "is-active" : ""}" type="button" data-option-group="${textoSeguro(grupo.label)}" data-option-choice="${textoSeguro(opcao)}">
            ${textoSeguro(opcao)}
          </button>
        `).join("")}
      </div>
    </div>
  `).join("");

  lista.querySelectorAll("[data-option-choice]").forEach((botao) => {
    botao.addEventListener("click", () => {
      escolhasPersonalizadas[botao.dataset.optionGroup] = botao.dataset.optionChoice;
      renderPersonalizacao();
    });
  });
}

function confirmarPersonalizacao() {
  if (!produtoPersonalizado) return;

  adicionarItem(produtoPersonalizado.id, { ...escolhasPersonalizadas });
  fecharPersonalizacao();
}

function configurarPersonalizacao() {
  document.querySelector("[data-options-confirm]")?.addEventListener("click", confirmarPersonalizacao);
  document.querySelector("[data-close-options]")?.addEventListener("click", fecharPersonalizacao);
  document.querySelector("[data-options-modal]")?.addEventListener("click", (event) => {
    if (event.target.matches("[data-options-modal]")) fecharPersonalizacao();
  });
}

function indiceEtapa(status) {
  const index = etapasPedido.findIndex((etapa) => etapa.status === status);
  return index >= 0 ? index : 0;
}

function mensagemStatus(pedido) {
  if (!pedido) return "Informe o codigo do pedido para consultar.";

  if (pedido.status === "Recebido") {
    return "Pedido recebido. A cozinha ja pode iniciar o preparo.";
  }

  if (pedido.status === "Em preparo") {
    return "Seu pedido esta em preparo. Aguarde ser chamado pelo codigo.";
  }

  if (pedido.status === "Pronto") {
    return "Pedido pronto para retirada no balcao.";
  }

  if (pedido.status === "Retirado") {
    return "Pedido retirado. Obrigado pela preferencia.";
  }

  return "Procure o atendimento para verificar esse pedido.";
}

function progressoHtml(status) {
  const atual = indiceEtapa(status);

  return etapasPedido.map((etapa, index) => {
    const classe = index < atual ? "is-done" : index === atual ? "is-current" : "";
    return `
      <div class="status-step ${classe}">
        <i class="fa-solid ${etapa.icon}"></i>
        <span>${etapa.label}</span>
      </div>
    `;
  }).join("");
}

function renderProgresso(container, status) {
  if (!container) return;
  container.innerHTML = progressoHtml(status);
}

function buscarPedidoPorCodigo(codigo) {
  const normalizado = String(codigo || "").trim().toUpperCase();
  if (!normalizado) return null;

  return TotemStore.carregarDados().orders.find((pedido) => String(pedido.code).toUpperCase() === normalizado);
}

function atualizarStatusConfirmacao(pedido) {
  renderProgresso(document.querySelector("[data-order-progress]"), pedido?.status || "Recebido");
  const texto = document.querySelector("[data-order-status-text]");
  if (texto) texto.textContent = mensagemStatus(pedido);
}

function renderResultadoAcompanhamento(pedido) {
  const resultado = document.querySelector("[data-tracker-result]");
  if (!resultado || !pedido) return;

  resultado.innerHTML = `
    <div class="tracker-summary">
      <div>
        <strong>${textoSeguro(pedido.customer)}</strong>
        <span>${textoSeguro(pedido.payment)} • ${pedido.prepTime} min estimados</span>
      </div>
      <div class="tracker-summary__code">${textoSeguro(pedido.code)}</div>
    </div>

    <div class="status-progress">
      ${progressoHtml(pedido.status)}
    </div>

    <p class="tracker-message">${mensagemStatus(pedido)}</p>

    <div class="tracker-items">
      ${pedido.items.map((item) => `
        <div class="tracker-item">
          <span>
            ${item.quantity}x ${textoSeguro(item.name)}
            ${item.notes ? `<small>${textoSeguro(item.notes)}</small>` : ""}
          </span>
          <strong>${TotemStore.formatarDinheiro(item.price * item.quantity)}</strong>
        </div>
      `).join("")}
    </div>
  `;
}

// Procura pedido pelo código de retirada.
function consultarPedido(codigo) {
  const valor = String(codigo || "").trim().toUpperCase();
  const resultado = document.querySelector("[data-tracker-result]");

  if (!valor) {
    if (resultado) {
      resultado.innerHTML = `<span class="tracker-empty">Informe o codigo que apareceu ao finalizar o pedido.</span>`;
    }
    return;
  }

  codigoAcompanhado = valor;
  const pedido = buscarPedidoPorCodigo(valor);

  if (!pedido) {
    if (resultado) {
      resultado.innerHTML = `<span class="tracker-empty">Pedido ${textoSeguro(valor)} nao encontrado.</span>`;
    }
    return;
  }

  renderResultadoAcompanhamento(pedido);
}

function atualizarAcompanhamento() {
  if (pedidoConfirmadoId) {
    const pedido = TotemStore.carregarDados().orders.find((item) => item.id === pedidoConfirmadoId);
    if (pedido) atualizarStatusConfirmacao(pedido);
  }

  const modalConsultaAberto = document.querySelector("[data-tracker-modal]")?.classList.contains("is-open");
  if (modalConsultaAberto && codigoAcompanhado) {
    const pedido = buscarPedidoPorCodigo(codigoAcompanhado);
    if (pedido) renderResultadoAcompanhamento(pedido);
  }
}

function iniciarAtualizacaoStatus() {
  if (statusTimer) return;
  statusTimer = setInterval(atualizarAcompanhamento, 1600);
}

function abrirAcompanhamento() {
  const modal = document.querySelector("[data-tracker-modal]");
  const input = document.querySelector("[data-tracker-code]");
  if (!modal || !input) return;

  modal.classList.add("is-open");
  input.value = codigoAcompanhado;
  consultarPedido(input.value);
  input.focus();
  iniciarAtualizacaoStatus();
}

function fecharAcompanhamento() {
  document.querySelector("[data-tracker-modal]")?.classList.remove("is-open");
}

function configurarAcompanhamento() {
  document.querySelectorAll("[data-open-tracker]").forEach((botao) => {
    botao.addEventListener("click", abrirAcompanhamento);
  });

  document.querySelector("[data-close-tracker]")?.addEventListener("click", fecharAcompanhamento);
  document.querySelector("[data-tracker-modal]")?.addEventListener("click", (event) => {
    if (event.target.matches("[data-tracker-modal]")) fecharAcompanhamento();
  });

  document.querySelector("[data-tracker-form]")?.addEventListener("submit", (event) => {
    event.preventDefault();
    consultarPedido(document.querySelector("[data-tracker-code]")?.value);
    iniciarAtualizacaoStatus();
  });
}

function finalizarPedido() {
  const { quantity, total, prepTime, items } = resumoCarrinho();
  if (!quantity) {
    mostrarToastTotem("Carrinho vazio", "Adicione pelo menos um item para finalizar.");
    return;
  }

  const customer = document.querySelector("[data-customer]").value.trim() || "Cliente";
  const order = TotemStore.criarPedido({ customer, payment: pagamentoAtual, total, prepTime, items });
  pedidoConfirmadoId = order.id;
  codigoAcompanhado = order.code;

  document.querySelector("[data-order-code]").textContent = order.code;
  document.querySelector("[data-order-text]").textContent =
    `${customer}, seu pedido de ${TotemStore.formatarDinheiro(total)} foi registrado em ${pagamentoAtual}. Tempo estimado: ${prepTime} min.`;
  document.querySelector("[data-tracker-code]").value = order.code;
  atualizarStatusConfirmacao(order);
  iniciarAtualizacaoStatus();
  document.querySelector("[data-modal]").classList.add("is-open");
}

// Limpa tudo para o próximo cliente.
function reiniciarPedido() {
  carrinho.clear();
  pedidoConfirmadoId = null;
  codigoAcompanhado = "";
  document.querySelector("[data-customer]").value = "";
  document.querySelector("[data-modal]").classList.remove("is-open");
  document.querySelector("[data-tracker-code]").value = "";
  renderCategoriasTotem();
  renderProdutosTotem();
  renderCarrinho();
  mostrarToastTotem("Pedido reiniciado", "O totem está pronto para o próximo cliente.");
}

function mostrarToastTotem(titulo, mensagem) {
  const existente = document.querySelector(".totem-toast");
  if (existente) existente.remove();

  const toast = document.createElement("div");
  toast.className = "totem-toast";
  toast.innerHTML = `<strong>${titulo}</strong><span>${mensagem}</span>`;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 2400);
}

document.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("totem-ready");
  TotemStore.aplicarMarca();
  renderCategoriasTotem();
  renderProdutosTotem();
  renderCarrinho();
  configurarPagamento();
  configurarPersonalizacao();
  configurarAcompanhamento();
  atualizarStatusConfirmacao({ status: "Recebido" });

  document.querySelector("[data-finish]").addEventListener("click", finalizarPedido);
  document.querySelector("[data-new-order]").addEventListener("click", reiniciarPedido);
});
