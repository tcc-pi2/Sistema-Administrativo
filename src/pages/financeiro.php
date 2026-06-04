<?php
require_once __DIR__ . '/../../app/conexao.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/funcoes.php';
require_once __DIR__ . '/../../app/configuracoes_repositorio.php';
require_once __DIR__ . '/../../app/financeiro_repositorio.php';

exigir_login();

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    registrar_movimento_caixa($pdo, $_POST);
    header('Location: ./financeiro.php?msg=salvo');
    exit;
}

if (($_GET['msg'] ?? '') === 'salvo') {
    $mensagem = 'Movimento registrado com sucesso.';
}

$filtrosFinanceiro = [
    'data_inicio' => $_GET['data_inicio'] ?? '',
    'data_fim' => $_GET['data_fim'] ?? '',
    'forma_pagamento' => $_GET['forma_pagamento'] ?? '',
];

$admin = usuario_logado();
$configuracoes = buscar_configuracoes($pdo);
$nomeLoja = valor_configuracao($configuracoes, 'nome_loja', 'GastroTech');
$logoLoja = valor_configuracao($configuracoes, 'logo_loja', '../assets/brand/gastrotech-logo.jpg');
$resumo = resumo_financeiro($pdo, $filtrosFinanceiro);
$movimentos = listar_movimentos_caixa($pdo, $filtrosFinanceiro);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Financeiro | <?= escapar($nomeLoja) ?> Admin</title>
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
          <a class="nav-link" href="./dashboard.php"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></a>
          <a class="nav-link" href="./produtos.php"><i class="fa-solid fa-box"></i><span>Produtos</span></a>
          <a class="nav-link" href="./categorias.php"><i class="fa-solid fa-table-cells-large"></i><span>Categorias</span></a>
          <a class="nav-link nav-link--active" href="./financeiro.php"><i class="fa-solid fa-cash-register"></i><span>Financeiro</span></a>
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
          <p class="topbar__eyebrow">Controle de caixa</p>
          <h1 class="topbar__title">Financeiro</h1>
        </div>
      </header>

      <section class="page-body">
        <div class="page-stack">
          <?php if ($mensagem): ?>
            <p class="form-hint"><?= escapar($mensagem) ?></p>
          <?php endif; ?>

          <section class="table-card">
            <div class="table-card__header">
              <div>
                <h3 class="table-card__title">Filtros do financeiro</h3>
                <p class="form-hint">Use datas e forma de pagamento para conferir um periodo.</p>
              </div>
            </div>

            <form class="filter-form" method="get">
              <div class="form-group">
                <label for="data_inicio">Data inicial</label>
                <input class="form-control" id="data_inicio" name="data_inicio" type="date" value="<?= escapar($filtrosFinanceiro['data_inicio']) ?>">
              </div>

              <div class="form-group">
                <label for="data_fim">Data final</label>
                <input class="form-control" id="data_fim" name="data_fim" type="date" value="<?= escapar($filtrosFinanceiro['data_fim']) ?>">
              </div>

              <div class="form-group">
                <label for="forma_pagamento_filtro">Forma</label>
                <select class="form-control" id="forma_pagamento_filtro" name="forma_pagamento">
                  <option value="">Todas</option>
                  <?php foreach (['Pix', 'Cartao', 'Dinheiro'] as $forma): ?>
                    <option value="<?= $forma ?>" <?= $filtrosFinanceiro['forma_pagamento'] === $forma ? 'selected' : '' ?>>
                      <?= $forma ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="filter-form__actions">
                <a class="button button--ghost" href="./financeiro.php">Limpar</a>
                <button class="button button--primary" type="submit">
                  <i class="fa-solid fa-filter"></i>
                  Filtrar
                </button>
              </div>
            </form>
          </section>

          <div class="metric-grid metric-grid--four">
            <article class="metric-card">
              <div class="metric-card__top">
                <p class="metric-card__label">Vendas registradas</p>
                <span class="metric-card__icon"><i class="fa-solid fa-receipt"></i></span>
              </div>
              <p class="metric-card__value metric-card__value--money"><?= dinheiro($resumo['vendas']) ?></p>
              <p class="metric-card__note">Total dos pedidos pagos</p>
            </article>

            <article class="metric-card">
              <div class="metric-card__top">
                <p class="metric-card__label">Pix</p>
                <span class="metric-card__icon"><i class="fa-solid fa-qrcode"></i></span>
              </div>
              <p class="metric-card__value metric-card__value--money"><?= dinheiro($resumo['pix']) ?></p>
              <p class="metric-card__note">Pedidos pagos por Pix</p>
            </article>

            <article class="metric-card">
              <div class="metric-card__top">
                <p class="metric-card__label">Cartao</p>
                <span class="metric-card__icon"><i class="fa-solid fa-credit-card"></i></span>
              </div>
              <p class="metric-card__value metric-card__value--money"><?= dinheiro($resumo['cartao']) ?></p>
              <p class="metric-card__note">Pedidos pagos no cartao</p>
            </article>

            <article class="metric-card">
              <div class="metric-card__top">
                <p class="metric-card__label">Saldo em dinheiro</p>
                <span class="metric-card__icon"><i class="fa-solid fa-money-bill-wave"></i></span>
              </div>
              <p class="metric-card__value metric-card__value--money"><?= dinheiro($resumo['saldo']) ?></p>
              <p class="metric-card__note">Troco + entradas - saidas</p>
            </article>
          </div>

          <div class="finance-layout">
            <section class="form-card">
              <div class="form-card__header">
                <span class="form-card__icon"><i class="fa-solid fa-plus-minus"></i></span>
                <div>
                  <h2>Novo movimento</h2>
                  <p>Registre entrada ou saida manual do caixa.</p>
                </div>
              </div>

              <form class="crud-form" method="post">
                <div class="form-grid form-grid--single">
                  <div class="form-group">
                    <label for="tipo">Tipo</label>
                    <select class="form-control" id="tipo" name="tipo">
                      <option value="Entrada">Entrada</option>
                      <option value="Saida">Saida</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="forma_pagamento">Forma</label>
                    <select class="form-control" id="forma_pagamento" name="forma_pagamento">
                      <option value="Dinheiro">Dinheiro</option>
                      <option value="Pix">Pix</option>
                      <option value="Cartao">Cartao</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="descricao">Descricao</label>
                    <input class="form-control" id="descricao" name="descricao" type="text" placeholder="Ex: Compra de embalagens" required>
                  </div>

                  <div class="form-group">
                    <label for="valor">Valor</label>
                    <input class="form-control" id="valor" name="valor" type="number" min="0" step="0.01" required>
                  </div>
                </div>

                <div class="crud-panel__footer">
                  <button class="button button--primary" type="submit">
                    <i class="fa-solid fa-check"></i>
                    Registrar
                  </button>
                </div>
              </form>
            </section>

            <section class="table-card">
              <div class="table-card__header">
                <h3 class="table-card__title">Movimentos do caixa</h3>
              </div>

              <div class="table-wrapper">
                <table>
                  <thead>
                    <tr>
                      <th>Tipo</th>
                      <th>Descricao</th>
                      <th>Forma</th>
                      <th class="text-right">Valor</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($movimentos as $movimento): ?>
                      <tr>
                        <td>
                          <span class="badge <?= $movimento['tipo'] === 'Entrada' ? 'badge--success' : 'badge--danger' ?>">
                            <?= escapar($movimento['tipo']) ?>
                          </span>
                        </td>
                        <td><?= escapar($movimento['descricao']) ?></td>
                        <td><?= escapar($movimento['forma_pagamento']) ?></td>
                        <td class="text-right"><strong><?= dinheiro($movimento['valor']) ?></strong></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </section>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
