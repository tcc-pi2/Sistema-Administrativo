<?php
require_once __DIR__ . '/../../app/conexao.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/funcoes.php';
require_once __DIR__ . '/../../app/configuracoes_repositorio.php';
require_once __DIR__ . '/../../app/pedidos_repositorio.php';

exigir_login();

$filtroAtual = $_GET['status'] ?? 'Todos';
$statusValidos = ['Todos', 'Recebido', 'Em preparo', 'Pronto', 'Retirado'];

if (!in_array($filtroAtual, $statusValidos, true)) {
    $filtroAtual = 'Todos';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    avancar_status_pedido($pdo, $_POST['pedido_id'] ?? 0);
    header('Location: ./cozinha.php?status=' . urlencode($filtroAtual));
    exit;
}

$contagens = contar_pedidos_por_status($pdo);
$pedidos = listar_pedidos_banco($pdo, $filtroAtual);
$configuracoes = buscar_configuracoes($pdo);
$nomeLoja = valor_configuracao($configuracoes, 'nome_loja', 'GastroTech');
$logoLoja = valor_configuracao($configuracoes, 'logo_loja', '../assets/brand/gastrotech-logo.jpg');
$novosRecebidos = (int) ($contagens['Recebido'] ?? 0);
$ultimoPedidoRecebido = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM pedidos WHERE status_pedido = "Recebido"')->fetchColumn();
$totalAtrasados = (int) $pdo->query('
    SELECT COUNT(*)
    FROM pedidos
    WHERE status_pedido IN ("Recebido", "Em preparo")
      AND TIMESTAMPDIFF(MINUTE, criado_em, NOW()) > tempo_estimado_min
')->fetchColumn();
$maiorAtraso = (int) $pdo->query('
    SELECT COALESCE(MAX(TIMESTAMPDIFF(MINUTE, criado_em, NOW()) - tempo_estimado_min), 0)
    FROM pedidos
    WHERE status_pedido IN ("Recebido", "Em preparo")
      AND TIMESTAMPDIFF(MINUTE, criado_em, NOW()) > tempo_estimado_min
')->fetchColumn();

function classe_status_cozinha($status)
{
    if ($status === 'Recebido') {
        return 'badge badge--received';
    }

    if ($status === 'Em preparo') {
        return 'badge badge--preparing';
    }

    if ($status === 'Pronto') {
        return 'badge badge--ready';
    }

    return 'badge badge--done';
}

function texto_botao_status($status)
{
    if ($status === 'Recebido') {
        return 'Iniciar preparo';
    }

    if ($status === 'Em preparo') {
        return 'Marcar pronto';
    }

    if ($status === 'Pronto') {
        return 'Confirmar retirada';
    }

    return 'Finalizado';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cozinha | <?= escapar($nomeLoja) ?> Admin</title>
  <link rel="icon" type="image/jpeg" href="<?= escapar($logoLoja) ?>">
  <meta http-equiv="refresh" content="12">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="../styles/cozinha.css">
</head>
<body class="ready" data-novos-pedidos="<?= $novosRecebidos ?>" data-ultimo-recebido="<?= $ultimoPedidoRecebido ?>">
  <div class="kitchen-shell">
    <header class="kitchen-topbar">
      <div class="kitchen-heading">
        <span class="kitchen-brand">
          <img src="<?= escapar($logoLoja) ?>" alt="Logo <?= escapar($nomeLoja) ?>">
          <span>
            <strong><?= escapar($nomeLoja) ?></strong>
            <small>Cozinha</small>
          </span>
        </span>
        <div>
          <h1>Fila da cozinha</h1>
          <p>Acompanhe os pedidos do totem e avance o status até a retirada.</p>
        </div>
      </div>

      <div class="top-actions">
        <a class="button" href="./dashboard.php">
          <i class="fa-solid fa-chart-pie"></i>
          Dashboard
        </a>
        <a class="button" href="./produtos.php">
          <i class="fa-solid fa-box"></i>
          Produtos
        </a>
        <a class="button" href="./financeiro.php">
          <i class="fa-solid fa-cash-register"></i>
          Financeiro
        </a>
        <a class="button" href="./totem.php" target="_blank">
          <i class="fa-solid fa-display"></i>
          Totem
        </a>
      </div>
    </header>

    <main class="kitchen-body">
      <?php if ($novosRecebidos > 0): ?>
        <section class="kitchen-notice" aria-label="Aviso de pedidos novos">
          <span class="kitchen-notice__icon">
            <i class="fa-solid fa-bell"></i>
          </span>
          <div>
            <strong><?= $novosRecebidos ?> pedido(s) aguardando preparo</strong>
            <p>Pedidos recebidos ficam destacados até alguém iniciar o preparo.</p>
          </div>
          <button class="button sound-toggle" type="button" data-sound-toggle>
            <i class="fa-solid fa-volume-high"></i>
            Ativar aviso sonoro
          </button>
        </section>
      <?php endif; ?>

      <?php if ($totalAtrasados > 0): ?>
        <section class="kitchen-notice kitchen-notice--late" aria-label="Aviso de pedidos atrasados">
          <span class="kitchen-notice__icon">
            <i class="fa-solid fa-stopwatch"></i>
          </span>
          <div>
            <strong><?= $totalAtrasados ?> pedido(s) atrasado(s)</strong>
            <p>Maior atraso: <?= texto_minutos($maiorAtraso) ?> além do tempo estimado.</p>
          </div>
          <a class="button" href="./cozinha.php?status=Todos">
            <i class="fa-solid fa-list-check"></i>
            Ver fila completa
          </a>
        </section>
      <?php endif; ?>

      <nav class="status-tabs" aria-label="Filtros de status">
        <?php foreach ($statusValidos as $status): ?>
          <a class="tab-button <?= $status === $filtroAtual ? 'is-active' : '' ?>" href="./cozinha.php?status=<?= urlencode($status) ?>">
            <?= escapar($status) ?> (<?= (int) ($contagens[$status] ?? 0) ?>)
          </a>
        <?php endforeach; ?>
      </nav>

      <section class="orders-board" aria-label="Pedidos da cozinha">
        <?php if (!$pedidos): ?>
          <div class="empty-state">
            <span>
              <i class="fa-solid fa-clipboard-check"></i><br><br>
              Nenhum pedido nessa etapa
            </span>
          </div>
        <?php endif; ?>

        <?php foreach ($pedidos as $pedido): ?>
          <?php
            $minutosDecorridos = minutos_desde($pedido['criado_em']);
            $minutosAtraso = atraso_pedido($pedido);
          ?>
          <article class="order-card <?= $pedido['status_pedido'] === 'Recebido' ? 'is-new' : '' ?> <?= $minutosAtraso > 0 ? 'is-late' : '' ?>">
            <div class="order-card__header">
              <div>
                <span class="order-code"><?= escapar($pedido['codigo_retirada']) ?></span>
              </div>
              <span class="<?= classe_status_cozinha($pedido['status_pedido']) ?>">
                <?= escapar($pedido['status_pedido']) ?>
              </span>
            </div>

            <div>
              <h2><?= escapar(texto_visivel($pedido['nome_cliente'] ?: 'Cliente')) ?></h2>
                <p><?= escapar(texto_pagamento($pedido['forma_pagamento'])) ?> • <?= (int) $pedido['tempo_estimado_min'] ?> min estimados</p>
            </div>

            <div class="order-timing <?= $minutosAtraso > 0 ? 'is-late' : '' ?>">
              <span>
                <i class="fa-solid fa-clock"></i>
                Recebido às <?= horario_curto($pedido['criado_em']) ?>
              </span>
              <?php if (in_array($pedido['status_pedido'], ['Recebido', 'Em preparo'], true)): ?>
                <span>
                  <i class="fa-solid fa-hourglass-half"></i>
                  <?= texto_minutos($minutosDecorridos) ?> na cozinha
                </span>
                <?php if ($minutosAtraso > 0): ?>
                  <strong>
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Atrasado há <?= texto_minutos($minutosAtraso) ?>
                  </strong>
                <?php endif; ?>
              <?php elseif ($pedido['status_pedido'] === 'Pronto'): ?>
                <span>
                  <i class="fa-solid fa-bell-concierge"></i>
                  Saiu da cozinha às <?= horario_curto($pedido['atualizado_em']) ?>
                </span>
                <strong>
                  <i class="fa-solid fa-circle-check"></i>
                  Aguardando retirada
                </strong>
              <?php elseif ($pedido['status_pedido'] === 'Retirado'): ?>
                <span>
                  <i class="fa-solid fa-bag-shopping"></i>
                  Retirado às <?= horario_curto($pedido['atualizado_em']) ?>
                </span>
                <strong>
                  <i class="fa-solid fa-circle-check"></i>
                  Pedido entregue ao cliente
                </strong>
              <?php endif; ?>
            </div>

            <div class="order-items">
              <?php foreach ($pedido['itens'] as $item): ?>
                <div class="order-item">
                  <span>
                    <?= (int) $item['quantidade'] ?>x <?= escapar($item['nome_produto']) ?>
                    <?php if ($item['observacao']): ?>
                      <small><?= escapar(texto_visivel($item['observacao'])) ?></small>
                    <?php endif; ?>
                  </span>
                  <strong><?= dinheiro((float) $item['preco_unitario'] * (int) $item['quantidade']) ?></strong>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="order-card__footer">
              <span class="order-total"><?= dinheiro($pedido['subtotal']) ?></span>
              <form method="post">
                <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                <button class="button button--primary" type="submit" <?= $pedido['status_pedido'] === 'Retirado' ? 'disabled' : '' ?>>
                  <?= texto_botao_status($pedido['status_pedido']) ?>
                </button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    </main>
  </div>

  <script>
    const corpo = document.body;
    const novosPedidos = Number(corpo.dataset.novosPedidos || 0);
    const ultimoRecebido = Number(corpo.dataset.ultimoRecebido || 0);
    const chaveSom = "gastrotechAvisoCozinha";
    const chaveUltimo = "gastrotechUltimoPedidoRecebido";
    const botaoSom = document.querySelector("[data-sound-toggle]");

    function tocarAvisoCozinha() {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      if (!AudioContext) return;

      const contexto = new AudioContext();
      const ganho = contexto.createGain();
      ganho.gain.value = 0.08;
      ganho.connect(contexto.destination);

      [0, 0.16].forEach((atraso) => {
        const oscilador = contexto.createOscillator();
        oscilador.type = "sine";
        oscilador.frequency.value = 880;
        oscilador.connect(ganho);
        oscilador.start(contexto.currentTime + atraso);
        oscilador.stop(contexto.currentTime + atraso + 0.1);
      });
    }

    function atualizarBotaoSom() {
      if (!botaoSom) return;

      const ligado = localStorage.getItem(chaveSom) === "ligado";
      botaoSom.innerHTML = ligado
        ? '<i class="fa-solid fa-volume-high"></i> Aviso sonoro ligado'
        : '<i class="fa-solid fa-volume-xmark"></i> Ativar aviso sonoro';
      botaoSom.classList.toggle("is-active", ligado);
    }

    botaoSom?.addEventListener("click", () => {
      const ligado = localStorage.getItem(chaveSom) === "ligado";
      localStorage.setItem(chaveSom, ligado ? "desligado" : "ligado");
      atualizarBotaoSom();

      if (!ligado) {
        tocarAvisoCozinha();
      }
    });

    atualizarBotaoSom();

    if (novosPedidos > 0 && localStorage.getItem(chaveSom) === "ligado") {
      const ultimoAvisado = Number(localStorage.getItem(chaveUltimo) || 0);

      if (ultimoRecebido > ultimoAvisado) {
        tocarAvisoCozinha();
        localStorage.setItem(chaveUltimo, String(ultimoRecebido));
      }
    }
  </script>
</body>
</html>
