const AUTH_KEY = "saborFlowAdminAutenticado";

// Bloqueia a cozinha se não tiver login no painel.
if (localStorage.getItem(AUTH_KEY) !== "true") {
  window.location.replace("./login.html?next=cozinha.html");
}

let filtroAtual = "Todos";
const filtros = ["Todos", "Recebido", "Em preparo", "Pronto", "Retirado"];
const pedidosConhecidos = new Set();
const pedidosNovos = new Set();
let tituloOriginal = document.title;

function dinheiro(valor) {
  return TotemStore.money(valor);
}

// Define a cor do status do pedido.
function badgeClass(status) {
  if (status === "Recebido") return "badge badge--received";
  if (status === "Em preparo") return "badge badge--preparing";
  if (status === "Pronto") return "badge badge--ready";
  return "badge badge--done";
}

// Ordem dos status da cozinha.
function proximoStatus(status) {
  const fluxo = ["Recebido", "Em preparo", "Pronto", "Retirado"];
  const index = fluxo.indexOf(status);
  return fluxo[Math.min(index + 1, fluxo.length - 1)];
}

function textoAcao(status) {
  if (status === "Recebido") return "Iniciar preparo";
  if (status === "Em preparo") return "Marcar pronto";
  if (status === "Pronto") return "Confirmar retirada";
  return "Finalizado";
}

function registrarPedidosAtuais() {
  TotemStore.load().orders.forEach((pedido) => pedidosConhecidos.add(Number(pedido.id)));
}

// Monta as abas de filtro: todos, recebido, preparo, pronto e retirado.
function renderTabs() {
  const wrapper = document.querySelector("[data-status-tabs]");
  const pedidos = TotemStore.load().orders;

  wrapper.innerHTML = filtros.map((filtro) => {
    const total = filtro === "Todos" ? pedidos.length : pedidos.filter((pedido) => pedido.status === filtro).length;
    return `
      <button class="tab-button ${filtro === filtroAtual ? "is-active" : ""}" type="button" data-filter="${filtro}">
        ${filtro} (${total})
      </button>
    `;
  }).join("");

  wrapper.querySelectorAll("[data-filter]").forEach((botao) => {
    botao.addEventListener("click", () => {
      filtroAtual = botao.dataset.filter;
      renderCozinha();
    });
  });
}

// Monta os cards dos pedidos da cozinha.
function renderPedidos() {
  const board = document.querySelector("[data-orders-board]");
  const pedidos = TotemStore.load().orders
    .filter((pedido) => filtroAtual === "Todos" || pedido.status === filtroAtual)
    .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

  if (!pedidos.length) {
    board.innerHTML = `
      <div class="empty-state">
        <span>
          <i class="fa-solid fa-clipboard-check"></i><br><br>
          Nenhum pedido nessa etapa
        </span>
      </div>
    `;
    return;
  }

  board.innerHTML = pedidos.map((pedido, index) => `
    <article class="order-card ${pedido.status === "Recebido" && pedidosNovos.has(Number(pedido.id)) ? "is-new" : ""}" style="animation-delay: ${index * 45}ms">
      <div class="order-card__header">
        <div>
          <span class="order-code">${pedido.code}</span>
        </div>
        <span class="${badgeClass(pedido.status)}">${pedido.status}</span>
      </div>

      <div>
        <h2>${pedido.customer}</h2>
        <p>${pedido.payment} • ${pedido.prepTime} min estimados</p>
      </div>

      <div class="order-items">
        ${pedido.items.map((item) => `
          <div class="order-item">
            <span>${item.quantity}x ${item.name}</span>
            <strong>${dinheiro(item.price * item.quantity)}</strong>
          </div>
        `).join("")}
      </div>

      <div class="order-card__footer">
        <span class="order-total">${dinheiro(pedido.total)}</span>
        <button class="button button--primary" type="button" data-next="${pedido.id}" ${pedido.status === "Retirado" ? "disabled" : ""}>
          ${textoAcao(pedido.status)}
        </button>
      </div>
    </article>
  `).join("");

  board.querySelectorAll("[data-next]").forEach((botao) => {
    botao.addEventListener("click", () => {
      const pedido = TotemStore.load().orders.find((item) => item.id === Number(botao.dataset.next));
      if (!pedido) return;

      const status = proximoStatus(pedido.status);
      TotemStore.updateOrderStatus(pedido.id, status);
      pedidosNovos.delete(Number(pedido.id));
      mostrarToast(`Pedido ${pedido.code}: ${status}`);
      renderCozinha();
    });
  });
}

function mostrarToast(texto) {
  const atual = document.querySelector(".toast");
  if (atual) atual.remove();

  const toast = document.createElement("div");
  toast.className = "toast";
  toast.textContent = texto;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 2200);
}

// Som simples quando chega pedido novo.
function tocarAlertaCozinha() {
  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;

    const audio = new AudioContext();
    const oscillator = audio.createOscillator();
    const gain = audio.createGain();

    oscillator.type = "sine";
    oscillator.frequency.setValueAtTime(880, audio.currentTime);
    oscillator.frequency.setValueAtTime(660, audio.currentTime + 0.12);
    gain.gain.setValueAtTime(0.0001, audio.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.16, audio.currentTime + 0.02);
    gain.gain.exponentialRampToValueAtTime(0.0001, audio.currentTime + 0.32);

    oscillator.connect(gain);
    gain.connect(audio.destination);
    oscillator.start();
    oscillator.stop(audio.currentTime + 0.34);
    setTimeout(() => audio.close(), 500);
  } catch {
    // O navegador pode bloquear audio automatico; a notificacao visual continua funcionando.
  }
}

// Aviso visual de pedido novo.
function mostrarNotificacaoNovoPedido(pedido) {
  const alertaAnterior = document.querySelector(".kitchen-alert");
  if (alertaAnterior) alertaAnterior.remove();

  const alerta = document.createElement("div");
  alerta.className = "kitchen-alert";
  alerta.innerHTML = `
    <span class="kitchen-alert__icon">
      <i class="fa-solid fa-bell"></i>
    </span>
    <span>
      <strong>Novo pedido na cozinha</strong>
      <small>${pedido.code} • ${pedido.customer} • ${dinheiro(pedido.total)}</small>
    </span>
  `;
  document.body.appendChild(alerta);

  document.title = `Novo pedido ${pedido.code} | Cozinha`;
  tocarAlertaCozinha();
  setTimeout(() => {
    alerta.classList.add("is-leaving");
    setTimeout(() => alerta.remove(), 240);
    document.title = tituloOriginal;
  }, 5200);
}

// Verifica se entrou pedido novo no localStorage.
function verificarNovosPedidos() {
  const pedidos = TotemStore.load().orders;
  const novos = pedidos.filter((pedido) => !pedidosConhecidos.has(Number(pedido.id)));

  if (!novos.length) return;

  novos.forEach((pedido) => {
    pedidosConhecidos.add(Number(pedido.id));
    if (pedido.status === "Recebido") {
      pedidosNovos.add(Number(pedido.id));
      mostrarNotificacaoNovoPedido(pedido);
    }
  });

  renderCozinha();
}

function configurarNotificacoesPedidos() {
  registrarPedidosAtuais();

  window.addEventListener("storage", (event) => {
    if (event.key === TotemStore.KEY) verificarNovosPedidos();
  });

  setInterval(verificarNovosPedidos, 2500);
}

function renderCozinha() {
  renderTabs();
  renderPedidos();
}

document.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("ready");
  TotemStore.applyBranding();
  configurarNotificacoesPedidos();
  renderCozinha();
});
