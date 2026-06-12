<?php
require_once __DIR__ . '/../../app/conexao.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/funcoes.php';
require_once __DIR__ . '/../../app/configuracoes_repositorio.php';
require_once __DIR__ . '/../../app/produtos_repositorio.php';
require_once __DIR__ . '/../../app/pedidos_repositorio.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itens = json_decode($_POST['carrinho_json'] ?? '[]', true);

    try {
        $pedidoCriado = criar_pedido_banco(
            $pdo,
            $_POST['cliente'] ?? '',
            $_POST['pagamento'] ?? 'Pix',
            $itens
        );

        header('Location: ./totem.php?pedido=' . urlencode($pedidoCriado['codigo']));
        exit;
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$categoriaAtual = isset($_GET['categoria']) ? (int) $_GET['categoria'] : 0;
$pedidoCodigo = $_GET['pedido'] ?? '';
$codigoAcompanhamento = $_GET['acompanhar'] ?? '';
$pedidoAcompanhado = buscar_pedido_por_codigo($pdo, $codigoAcompanhamento);
$pedidoConfirmado = buscar_pedido_por_codigo($pdo, $pedidoCodigo);
$configuracoes = buscar_configuracoes($pdo);
$nomeLoja = valor_configuracao($configuracoes, 'nome_loja', 'GastroTech');
$fraseTotem = valor_configuracao($configuracoes, 'frase_totem', 'Monte seu pedido com todos os detalhes.');
$logoLoja = valor_configuracao($configuracoes, 'logo_loja', '../assets/brand/gastrotech-logo.jpg');
$produtos = listar_produtos($pdo);
$categorias = listar_categorias($pdo);
$produtosVisiveis = [];

foreach ($produtos as $produto) {
    if ($produto['status'] !== 'Ativo' || (int) $produto['estoque'] <= 0) {
        continue;
    }

    if ($categoriaAtual && (int) $produto['categoria_id'] !== $categoriaAtual) {
        continue;
    }

    $produtosVisiveis[] = $produto;
}

$produtosJs = array_map(function ($produto) {
    $personalizacoes = array_map(function ($grupo) {
        $grupo['nome'] = texto_visivel($grupo['nome']);
        $grupo['opcoes'] = array_map('texto_visivel', $grupo['opcoes']);
        return $grupo;
    }, personalizacoes_produto($produto['opcoes_personalizacao'] ?? ''));

    return [
        'id' => (int) $produto['id'],
        'nome' => $produto['nome'],
        'preco' => (float) $produto['preco'],
        'imagem' => imagem_produto($produto),
        'estoque' => (int) $produto['estoque'],
        'personalizacoes' => $personalizacoes,
    ];
}, $produtosVisiveis);

$etapasPedido = ['Recebido', 'Em preparo', 'Pronto', 'Retirado'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pedido no Totem | <?= escapar($nomeLoja) ?></title>
  <link rel="icon" type="image/jpeg" href="<?= escapar($logoLoja) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="../styles/totem.css">
</head>
<body class="totem-ready">
  <div class="totem-shell">
    <aside class="totem-sidebar">
      <div>
        <div class="totem-brand">
          <span class="totem-brand__mark">
            <img src="<?= escapar($logoLoja) ?>" alt="Logo <?= escapar($nomeLoja) ?>">
          </span>
          <div>
            <h1><?= escapar($nomeLoja) ?></h1>
            <p><?= escapar($fraseTotem) ?></p>
          </div>
        </div>

        <nav class="category-list" aria-label="Categorias do cardápio">
          <a class="category-button <?= $categoriaAtual === 0 ? 'is-active' : '' ?>" href="./totem.php">
            <i class="fa-solid fa-table-cells-large"></i>
            <span>Todos</span>
          </a>
          <?php foreach ($categorias as $categoria): ?>
            <?php if (($categoria['status'] ?? 'Ativo') !== 'Ativo') continue; ?>
            <a class="category-button <?= $categoriaAtual === (int) $categoria['id'] ? 'is-active' : '' ?>" href="./totem.php?categoria=<?= $categoria['id'] ?>">
              <i class="fa-solid <?= escapar(icone_categoria($categoria['nome'])) ?>"></i>
              <span><?= escapar(texto_categoria($categoria['nome'])) ?></span>
            </a>
          <?php endforeach; ?>
        </nav>

        <div class="totem-sidebar__footer">
          <a class="track-order-button" href="./totem.php#acompanhar">
            <i class="fa-solid fa-magnifying-glass"></i>
            Acompanhar pedido
          </a>
          <a class="back-office" href="./login.php?logout=1" target="_blank" rel="noopener">
            <i class="fa-solid fa-user-gear"></i>
            Painel administrativo
          </a>
        </div>
      </div>
    </aside>

    <main class="totem-main">
      <header class="totem-header">
        <div>
          <h2>Escolha seu sabor</h2>
          <p>Veja ingredientes, porções e detalhes antes de finalizar seu pedido.</p>
        </div>
        <span class="order-chip">
          <i class="fa-solid fa-circle-check"></i>
          Totem 01 online
        </span>
      </header>

      <section class="customer-status customer-status--inline" id="acompanhar">
        <h3>Acompanhar retirada</h3>
        <p>Digite o código do pedido para ver em qual etapa ele está.</p>
        <form class="tracking-form" method="get">
          <input class="customer-field" type="text" name="acompanhar" value="<?= escapar($codigoAcompanhamento) ?>" placeholder="Ex: A105">
          <button class="finish-button finish-button--secondary" type="submit">Consultar</button>
        </form>

        <?php if ($codigoAcompanhamento && !$pedidoAcompanhado): ?>
          <span class="tracker-empty">Pedido não encontrado.</span>
        <?php endif; ?>

        <?php if ($pedidoAcompanhado): ?>
          <?php $indiceAtual = array_search($pedidoAcompanhado['status_pedido'], $etapasPedido, true); ?>
          <?php $indiceAtual = $indiceAtual === false ? 0 : $indiceAtual; ?>
          <div class="tracker-summary">
            <span>
              <strong><?= escapar(texto_visivel($pedidoAcompanhado['nome_cliente'] ?: 'Cliente')) ?></strong>
              <span><?= dinheiro($pedidoAcompanhado['subtotal']) ?> • <?= escapar(texto_pagamento($pedidoAcompanhado['forma_pagamento'])) ?></span>
            </span>
            <strong class="tracker-summary__code"><?= escapar($pedidoAcompanhado['codigo_retirada']) ?></strong>
          </div>
          <div class="status-progress">
            <?php foreach ($etapasPedido as $indice => $etapa): ?>
              <span class="status-step <?= $indice < $indiceAtual ? 'is-done' : '' ?> <?= $indice === $indiceAtual ? 'is-current' : '' ?>">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= escapar($etapa) ?></span>
              </span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <?php if ($pedidoConfirmado): ?>
        <section class="receipt-card" aria-label="Comprovante do pedido">
          <div class="receipt-card__header">
            <span class="receipt-card__icon">
              <i class="fa-solid fa-receipt"></i>
            </span>
            <div>
              <h3>Pedido confirmado</h3>
              <p>Guarde este código para retirar no balcão.</p>
            </div>
            <strong class="receipt-card__code"><?= escapar($pedidoConfirmado['codigo_retirada']) ?></strong>
          </div>

          <div class="receipt-card__info">
            <span>
              <small>Cliente</small>
              <strong><?= escapar(texto_visivel($pedidoConfirmado['nome_cliente'] ?: 'Cliente')) ?></strong>
            </span>
            <span>
              <small>Pagamento</small>
              <strong><?= escapar(texto_pagamento($pedidoConfirmado['forma_pagamento'])) ?></strong>
            </span>
            <span>
              <small>Previsão</small>
              <strong><?= (int) $pedidoConfirmado['tempo_estimado_min'] ?> min</strong>
            </span>
          </div>

          <div class="receipt-items">
            <?php foreach ($pedidoConfirmado['itens'] as $item): ?>
              <div class="receipt-item">
                <span>
                  <strong><?= (int) $item['quantidade'] ?>x <?= escapar($item['nome_produto']) ?></strong>
                  <?php if ($item['observacao']): ?>
                    <small><?= escapar(texto_visivel($item['observacao'])) ?></small>
                  <?php endif; ?>
                </span>
                <strong><?= dinheiro((float) $item['preco_unitario'] * (int) $item['quantidade']) ?></strong>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="receipt-card__footer">
            <a class="finish-button finish-button--secondary" href="./totem.php?acompanhar=<?= urlencode($pedidoConfirmado['codigo_retirada']) ?>#acompanhar">
              <i class="fa-solid fa-magnifying-glass"></i>
              Acompanhar pedido
            </a>
            <span>
              <small>Total</small>
              <strong><?= dinheiro($pedidoConfirmado['subtotal']) ?></strong>
            </span>
          </div>
        </section>
      <?php elseif ($pedidoCodigo): ?>
        <div class="cart-empty" style="margin-bottom: 16px; min-height: 90px;">
          <span>Pedido <strong><?= escapar($pedidoCodigo) ?></strong> confirmado.</span>
        </div>
      <?php endif; ?>

      <?php if ($erro): ?>
        <div class="cart-empty" style="margin-bottom: 16px; min-height: 90px;">
          <span><?= escapar($erro) ?></span>
        </div>
      <?php endif; ?>

      <section class="menu-grid" aria-label="Itens do cardápio">
        <?php foreach ($produtosVisiveis as $produto): ?>
          <article class="product-card">
            <div class="product-card__media">
              <img src="<?= escapar(imagem_produto($produto)) ?>" alt="Imagem de <?= escapar($produto['nome']) ?>">
              <span class="product-card__time">
                <i class="fa-solid fa-clock"></i>
                <?= (int) $produto['tempo_preparo_min'] ?> min
              </span>
            </div>

            <div class="product-card__content">
              <div class="product-card__heading">
                <h3><?= escapar($produto['nome']) ?></h3>
                <?php if ((int) $produto['destaque'] === 1): ?>
                  <span class="featured-pill">Destaque</span>
                <?php endif; ?>
              </div>

              <p><?= escapar(texto_visivel($produto['descricao'])) ?></p>

              <div class="product-card__chips">
                <?php foreach (lista_texto($produto['tags']) as $tag): ?>
                  <span class="product-chip"><?= escapar(texto_visivel($tag)) ?></span>
                <?php endforeach; ?>
              </div>

              <div class="ingredient-panel">
                <strong>Ingredientes</strong>
                <span><?= escapar(texto_visivel($produto['ingredientes'])) ?></span>
              </div>

              <div class="product-card__details">
                <span><i class="fa-solid fa-box-open"></i><?= escapar(texto_visivel($produto['porcao'])) ?></span>
                <span><i class="fa-solid fa-fire"></i><?= (int) $produto['calorias'] ?> kcal</span>
                <span><i class="fa-solid fa-boxes-stacked"></i><?= (int) $produto['estoque'] ?> em estoque</span>
                <?php if (!empty($produto['alergenos'])): ?>
                  <span><i class="fa-solid fa-circle-info"></i>Contém <?= escapar(texto_visivel($produto['alergenos'])) ?></span>
                <?php endif; ?>
              </div>

              <div class="product-card__meta">
                <strong class="price"><?= dinheiro($produto['preco']) ?></strong>
                <button class="add-button" type="button" data-add-product="<?= (int) $produto['id'] ?>">
                  <i class="fa-solid fa-plus"></i>
                  <?= personalizacoes_produto($produto['opcoes_personalizacao'] ?? '') ? 'Escolher' : 'Adicionar' ?>
                </button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>

        <?php if (!$produtosVisiveis): ?>
          <div class="menu-empty">
            <i class="fa-solid fa-circle-info"></i>
            <strong>Nenhum item disponível nesta categoria.</strong>
            <span>Verifique o estoque ou escolha outra categoria.</span>
          </div>
        <?php endif; ?>
      </section>
    </main>

    <aside class="totem-cart">
      <header class="cart-header">
        <h2>Seu pedido</h2>
        <p data-cart-summary>Escolha os itens do pedido</p>
      </header>

      <div class="cart-items" data-cart-items>
        <div class="cart-empty">
          <span>
            <i class="fa-solid fa-basket-shopping"></i><br><br>
            Seu pedido ainda está vazio
          </span>
        </div>
      </div>

      <form class="cart-footer" method="post" data-order-form>
        <input class="customer-field" type="text" name="cliente" placeholder="Nome para retirada">
        <input type="hidden" name="pagamento" value="Pix" data-payment-value>
        <input type="hidden" name="carrinho_json" value="[]" data-cart-json>

        <div class="payment-grid" aria-label="Forma de pagamento">
          <button class="payment-button is-active" type="button" data-payment="Pix">
            <i class="fa-solid fa-qrcode"></i><br>Pix
          </button>
          <button class="payment-button" type="button" data-payment="Cartao">
            <i class="fa-solid fa-credit-card"></i><br>Cartão
          </button>
          <button class="payment-button" type="button" data-payment="Dinheiro">
            <i class="fa-solid fa-money-bill"></i><br>Dinheiro
          </button>
        </div>

        <div class="cart-total">
          <span>Total</span>
          <strong data-total>R$ 0,00</strong>
        </div>

        <button class="finish-button" type="submit" data-finish disabled>
          Finalizar pedido
        </button>
      </form>
    </aside>
  </div>

  <div class="order-modal" data-options-modal>
    <div class="order-card order-card--options">
      <button class="modal-close" type="button" data-close-options aria-label="Fechar">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <span class="order-card__icon order-card__icon--orange">
        <i class="fa-solid fa-sliders"></i>
      </span>
      <h2 data-options-title>Escolha os detalhes</h2>
      <div class="option-list" data-options-list></div>
      <button class="finish-button" type="button" data-confirm-options>Adicionar ao pedido</button>
    </div>
  </div>

  <script>
    // Carrinho do totem.
    const produtos = <?= json_encode($produtosJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const carrinho = new Map();
    let produtoPersonalizado = null;
    let escolhasPersonalizadas = {};

    function textoSeguro(valor) {
      const div = document.createElement("div");
      div.textContent = valor || "";
      return div.innerHTML;
    }

    function dinheiro(valor) {
      return Number(valor || 0).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    }

    function produtoPorId(id) {
      return produtos.find((produto) => produto.id === Number(id));
    }

    function quantidadeNoCarrinho(id) {
      return [...carrinho.values()]
        .filter((item) => item.id === Number(id))
        .reduce((total, item) => total + item.quantidade, 0);
    }

    function montarObservacao(escolhas) {
      return Object.entries(escolhas)
        .map(([grupo, opcao]) => `${grupo}: ${opcao}`)
        .join(" | ");
    }

    function adicionarProduto(produto, observacao = "") {
      if (quantidadeNoCarrinho(produto.id) >= produto.estoque) {
        alert("Estoque insuficiente para este item.");
        return;
      }

      const chave = `${produto.id}::${observacao}`;
      const atual = carrinho.get(chave) || { ...produto, chave, observacao, quantidade: 0 };
      atual.quantidade += 1;
      carrinho.set(chave, atual);
      renderCarrinho();
    }

    function abrirPersonalizacao(produto) {
      produtoPersonalizado = produto;
      escolhasPersonalizadas = {};

      const modal = document.querySelector("[data-options-modal]");
      const titulo = document.querySelector("[data-options-title]");
      const lista = document.querySelector("[data-options-list]");

      titulo.textContent = produto.nome;
      lista.innerHTML = produto.personalizacoes.map((grupo, indiceGrupo) => {
        const opcoes = grupo.opcoes.map((opcao, indiceOpcao) => {
          if (indiceOpcao === 0) escolhasPersonalizadas[grupo.nome] = opcao;

          return `
            <button class="option-button ${indiceOpcao === 0 ? "is-active" : ""}" type="button" data-option-group="${indiceGrupo}" data-option-index="${indiceOpcao}">
              ${textoSeguro(opcao)}
            </button>
          `;
        }).join("");

        return `
          <div class="option-group">
            <strong>${textoSeguro(grupo.nome)}</strong>
            <div class="option-buttons">${opcoes}</div>
          </div>
        `;
      }).join("");

      modal.classList.add("is-open");
    }

    function fecharPersonalizacao() {
      document.querySelector("[data-options-modal]").classList.remove("is-open");
      produtoPersonalizado = null;
      escolhasPersonalizadas = {};
    }

    function renderCarrinho() {
      const lista = document.querySelector("[data-cart-items]");
      const totalEl = document.querySelector("[data-total]");
      const resumo = document.querySelector("[data-cart-summary]");
      const finalizar = document.querySelector("[data-finish]");
      const itens = [...carrinho.values()];
      const total = itens.reduce((soma, item) => soma + item.preco * item.quantidade, 0);
      const quantidade = itens.reduce((soma, item) => soma + item.quantidade, 0);

      if (!itens.length) {
        lista.innerHTML = `
          <div class="cart-empty">
            <span>
              <i class="fa-solid fa-basket-shopping"></i><br><br>
              Seu pedido ainda está vazio
            </span>
          </div>
        `;
      } else {
        lista.innerHTML = itens.map((item) => `
          <div class="cart-item">
            <div class="cart-item__top">
              <span class="cart-item__name">
                <img src="${item.imagem}" alt="">
                <strong>
                  ${textoSeguro(item.nome)}
                  ${item.observacao ? `<small class="cart-item__options">${textoSeguro(item.observacao)}</small>` : ""}
                </strong>
              </span>
              <span>${dinheiro(item.preco * item.quantidade)}</span>
            </div>
            <div class="cart-item__actions">
              <span>${dinheiro(item.preco)} cada</span>
              <div>
                <button class="quantity-button" type="button" data-dec="${encodeURIComponent(item.chave)}">-</button>
                <strong>${item.quantidade}</strong>
                <button class="quantity-button" type="button" data-inc="${encodeURIComponent(item.chave)}">+</button>
              </div>
            </div>
          </div>
        `).join("");
      }

      totalEl.textContent = dinheiro(total);
      resumo.textContent = quantidade ? `${quantidade} item(ns) no carrinho` : "Escolha os itens do pedido";
      finalizar.disabled = quantidade === 0;
      document.querySelector("[data-cart-json]").value = JSON.stringify(itens.map((item) => ({
        id: item.id,
        quantidade: item.quantidade,
        observacao: item.observacao || ""
      })));
    }

    document.addEventListener("click", (event) => {
      const add = event.target.closest("[data-add-product]");
      const inc = event.target.closest("[data-inc]");
      const dec = event.target.closest("[data-dec]");
      const payment = event.target.closest("[data-payment]");
      const option = event.target.closest("[data-option-index]");
      const closeOptions = event.target.closest("[data-close-options]");
      const confirmOptions = event.target.closest("[data-confirm-options]");

      if (add) {
        const produto = produtoPorId(add.dataset.addProduct);
        if (!produto) return;

        if (produto.personalizacoes && produto.personalizacoes.length) {
          abrirPersonalizacao(produto);
        } else {
          adicionarProduto(produto);
        }
      }

      if (inc) {
        const chave = decodeURIComponent(inc.dataset.inc);
        const atual = carrinho.get(chave);
        const produto = atual ? produtoPorId(atual.id) : null;
        if (!produto || !atual) return;

        if (quantidadeNoCarrinho(produto.id) >= produto.estoque) {
          alert("Estoque insuficiente para este item.");
          return;
        }

        atual.quantidade += 1;
        renderCarrinho();
      }

      if (dec) {
        const chave = decodeURIComponent(dec.dataset.dec);
        const atual = carrinho.get(chave);
        if (!atual) return;

        atual.quantidade -= 1;
        if (atual.quantidade <= 0) carrinho.delete(chave);
        renderCarrinho();
      }

      if (payment) {
        document.querySelectorAll("[data-payment]").forEach((botao) => botao.classList.remove("is-active"));
        payment.classList.add("is-active");
        document.querySelector("[data-payment-value]").value = payment.dataset.payment;
      }

      if (option && produtoPersonalizado) {
        const indiceGrupo = Number(option.dataset.optionGroup);
        const indiceOpcao = Number(option.dataset.optionIndex);
        const grupo = produtoPersonalizado.personalizacoes[indiceGrupo];
        escolhasPersonalizadas[grupo.nome] = grupo.opcoes[indiceOpcao];

        option.closest(".option-buttons").querySelectorAll(".option-button").forEach((botao) => botao.classList.remove("is-active"));
        option.classList.add("is-active");
      }

      if (closeOptions) {
        fecharPersonalizacao();
      }

      if (confirmOptions && produtoPersonalizado) {
        adicionarProduto(produtoPersonalizado, montarObservacao(escolhasPersonalizadas));
        fecharPersonalizacao();
      }
    });

    document.querySelector("[data-order-form]").addEventListener("submit", (event) => {
      if (!carrinho.size) {
        event.preventDefault();
        alert("Adicione pelo menos um item ao pedido.");
      }
    });
  </script>
</body>
</html>
