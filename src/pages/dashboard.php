<?php
require_once __DIR__ . '/../../app/conexao.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/funcoes.php';
require_once __DIR__ . '/../../app/configuracoes_repositorio.php';
require_once __DIR__ . '/../../app/financeiro_repositorio.php';

exigir_login();

$admin = usuario_logado();
$configuracoes = buscar_configuracoes($pdo);
$nomeLoja = valor_configuracao($configuracoes, 'nome_loja', 'GastroTech');
$logoLoja = valor_configuracao($configuracoes, 'logo_loja', '../assets/brand/gastrotech-logo.jpg');
$totalProdutos = (int) $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
$totalPedidos = (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();
$tempoEspera = valor_configuracao($configuracoes, 'tempo_espera_min', '18');
$resumoFinanceiro = resumo_financeiro($pdo);
$limiteEstoqueBaixo = 10;
$produtosEstoqueBaixo = $pdo->query('
    SELECT produtos.*, categorias.nome AS categoria_nome
    FROM produtos
    LEFT JOIN categorias ON categorias.id = produtos.categoria_id
    WHERE produtos.estoque <= ' . $limiteEstoqueBaixo . '
    ORDER BY produtos.estoque ASC, produtos.nome ASC
    LIMIT 6
')->fetchAll();
$pedidos = $pdo->query('SELECT * FROM pedidos ORDER BY criado_em DESC LIMIT 6')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | <?= escapar($nomeLoja) ?> Admin</title>
  <link rel="icon" type="image/jpeg" href="<?= escapar($logoLoja) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="../styles/admin.css">
</head>
<body class="app-ready">
  <div class="app-shell">
    <aside class="sidebar">
      <div class="sidebar__top">
        <a class="brand-card" href="./dashboard.php">
          <img class="brand-card__avatar" src="<?= escapar($logoLoja) ?>" alt="Logo <?= escapar($nomeLoja) ?>">
          <span>
            <strong class="brand-card__name"><?= escapar($nomeLoja) ?></strong>
            <span class="brand-card__role"><?= escapar($admin['nome']) ?></span>
          </span>
        </a>

        <nav class="sidebar__nav" aria-label="Menu principal">
          <a class="nav-link nav-link--active" href="./dashboard.php"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></a>
          <a class="nav-link" href="./produtos.php"><i class="fa-solid fa-box"></i><span>Produtos</span></a>
          <a class="nav-link" href="./categorias.php"><i class="fa-solid fa-table-cells-large"></i><span>Categorias</span></a>
          <a class="nav-link" href="./financeiro.php"><i class="fa-solid fa-cash-register"></i><span>Financeiro</span></a>
          <a class="nav-link" href="./cozinha.php"><i class="fa-solid fa-kitchen-set"></i><span>Cozinha</span></a>
          <a class="nav-link" href="./administradores.php"><i class="fa-solid fa-users-gear"></i><span>Usuarios</span></a>
          <a class="nav-link" href="./configuracoes.php"><i class="fa-solid fa-gear"></i><span>Configuracoes</span></a>
          <a class="nav-link" href="./totem.php" target="_blank"><i class="fa-solid fa-display"></i><span>Totem</span></a>
          <a class="nav-link" href="./login.php?logout=1"><i class="fa-solid fa-right-from-bracket"></i><span>Sair</span></a>
        </nav>
      </div>
    </aside>

    <main class="content">
      <header class="topbar">
        <div>
          <p class="topbar__eyebrow">Operacao do totem</p>
          <h1 class="topbar__title">Dashboard</h1>
        </div>
        <div class="topbar__actions">
          <a class="button button--ghost" href="./configuracoes.php">
            <i class="fa-solid fa-gear"></i>
            Configuracoes
          </a>
          <a class="button button--primary" href="./totem.php" target="_blank">
            <i class="fa-solid fa-display"></i>
            Abrir totem
          </a>
        </div>
      </header>

      <section class="page-body">
        <div class="page-stack">
          <div class="metric-grid metric-grid--four">
            <article class="metric-card">
              <div class="metric-card__top">
                <p class="metric-card__label">Itens do cardapio</p>
                <span class="metric-card__icon"><i class="fa-solid fa-burger"></i></span>
              </div>
              <p class="metric-card__value"><?= $totalProdutos ?></p>
              <p class="metric-card__note">Produtos disponiveis no totem</p>
            </article>

            <article class="metric-card">
              <div class="metric-card__top">
                <p class="metric-card__label">Pedidos hoje</p>
                <span class="metric-card__icon"><i class="fa-solid fa-receipt"></i></span>
              </div>
              <p class="metric-card__value"><?= $totalPedidos ?></p>
              <p class="metric-card__note">Pedidos registrados no sistema</p>
            </article>

            <article class="metric-card">
              <div class="metric-card__top">
                <p class="metric-card__label">Tempo medio</p>
                <span class="metric-card__icon"><i class="fa-solid fa-clock"></i></span>
              </div>
              <p class="metric-card__value"><?= escapar($tempoEspera) ?> min</p>
              <p class="metric-card__note">Previsao exibida ao cliente</p>
            </article>

            <article class="metric-card">
              <div class="metric-card__top">
                <p class="metric-card__label">Faturamento</p>
                <span class="metric-card__icon"><i class="fa-solid fa-sack-dollar"></i></span>
              </div>
              <p class="metric-card__value metric-card__value--money"><?= dinheiro($resumoFinanceiro['vendas']) ?></p>
              <p class="metric-card__note">Pedidos pagos no sistema</p>
            </article>
          </div>

          <?php if ($produtosEstoqueBaixo): ?>
            <section class="table-card">
              <div class="table-card__header">
                <div>
                  <h3 class="table-card__title">Atenção ao estoque</h3>
                  <p class="form-hint">Itens com poucas unidades ou sem estoque.</p>
                </div>
                <a class="button button--ghost" href="./produtos.php">Ajustar produtos</a>
              </div>

              <div class="stock-list">
                <?php foreach ($produtosEstoqueBaixo as $produto): ?>
                  <article class="stock-alert-card <?= (int) $produto['estoque'] <= 0 ? 'is-empty' : '' ?>">
                    <span class="stock-alert-card__icon">
                      <i class="fa-solid fa-triangle-exclamation"></i>
                    </span>
                    <div>
                      <strong><?= escapar($produto['nome']) ?></strong>
                      <span><?= escapar($produto['categoria_nome'] ?? 'Sem categoria') ?></span>
                    </div>
                    <span class="badge <?= (int) $produto['estoque'] <= 0 ? 'badge--danger' : 'badge--warning' ?>">
                      <?= (int) $produto['estoque'] <= 0 ? 'Sem estoque' : (int) $produto['estoque'] . ' un.' ?>
                    </span>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <section class="table-card">
            <div class="table-card__header">
              <h3 class="table-card__title">Pedidos recentes</h3>
              <a class="button button--primary" href="./cozinha.php">Abrir cozinha</a>
            </div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Codigo</th>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Pagamento</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                      <td><strong><?= escapar($pedido['codigo_retirada']) ?></strong></td>
                      <td><?= escapar($pedido['nome_cliente']) ?></td>
                      <td><?= escapar($pedido['status_pedido']) ?></td>
                      <td><?= dinheiro($pedido['subtotal']) ?></td>
                      <td><?= escapar($pedido['forma_pagamento']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
