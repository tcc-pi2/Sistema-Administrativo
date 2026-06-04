<?php
require_once __DIR__ . '/../../app/conexao.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/funcoes.php';
require_once __DIR__ . '/../../app/configuracoes_repositorio.php';
require_once __DIR__ . '/../../app/produtos_repositorio.php';

exigir_login();

$mensagem = '';
$categoriaEdicao = null;

if (isset($_GET['excluir'])) {
    excluir_categoria($pdo, $_GET['excluir']);
    header('Location: ./categorias.php?msg=excluido');
    exit;
}

if (isset($_GET['editar'])) {
    $categoriaEdicao = buscar_categoria($pdo, $_GET['editar']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    salvar_categoria($pdo, $_POST);
    header('Location: ./categorias.php?msg=salvo');
    exit;
}

if (($_GET['msg'] ?? '') === 'salvo') {
    $mensagem = 'Categoria salva com sucesso.';
}

if (($_GET['msg'] ?? '') === 'excluido') {
    $mensagem = 'Categoria removida com sucesso.';
}

$admin = usuario_logado();
$configuracoes = buscar_configuracoes($pdo);
$nomeLoja = valor_configuracao($configuracoes, 'nome_loja', 'GastroTech');
$logoLoja = valor_configuracao($configuracoes, 'logo_loja', '../assets/brand/gastrotech-logo.jpg');
$categorias = listar_categorias($pdo);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categorias | <?= escapar($nomeLoja) ?> Admin</title>
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
          <a class="nav-link nav-link--active" href="./categorias.php"><i class="fa-solid fa-table-cells-large"></i><span>Categorias</span></a>
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
          <p class="topbar__eyebrow">Organizacao do cardapio</p>
          <h1 class="topbar__title">Categorias</h1>
        </div>
      </header>

      <section class="page-body">
        <div class="page-stack">
          <?php if ($mensagem): ?>
            <p class="form-hint"><?= escapar($mensagem) ?></p>
          <?php endif; ?>

          <section class="form-card">
            <div class="form-card__header">
              <span class="form-card__icon"><i class="fa-solid fa-layer-group"></i></span>
              <div>
                <h2><?= $categoriaEdicao ? 'Editar categoria' : 'Nova categoria' ?></h2>
                <p>Use categorias para separar combos, bebidas, sobremesas e adicionais.</p>
              </div>
            </div>

            <form class="crud-form" method="post">
              <input type="hidden" name="id" value="<?= escapar($categoriaEdicao['id'] ?? '') ?>">

              <div class="form-grid">
                <div class="form-group">
                  <label for="nome">Nome</label>
                  <input class="form-control" id="nome" name="nome" type="text" value="<?= escapar($categoriaEdicao['nome'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                  <label for="ordem">Ordem</label>
                  <input class="form-control" id="ordem" name="ordem" type="number" min="0" value="<?= escapar($categoriaEdicao['ordem'] ?? 0) ?>">
                </div>

                <div class="form-group form-group--wide">
                  <label for="descricao">Descricao</label>
                  <input class="form-control" id="descricao" name="descricao" type="text" value="<?= escapar($categoriaEdicao['descricao'] ?? '') ?>">
                </div>

                <div class="form-group">
                  <label for="status">Status</label>
                  <select class="form-control" id="status" name="status">
                    <option value="Ativo" <?= ($categoriaEdicao['status'] ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="Inativo" <?= ($categoriaEdicao['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                  </select>
                </div>
              </div>

              <div class="crud-panel__footer">
                <a class="button button--ghost" href="./categorias.php">Limpar</a>
                <button class="button button--primary" type="submit">
                  <i class="fa-solid fa-check"></i>
                  Salvar categoria
                </button>
              </div>
            </form>
          </section>

          <section class="table-card">
            <div class="table-card__header">
              <h3 class="table-card__title">Categorias cadastradas</h3>
            </div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Descricao</th>
                    <th class="text-right">Ordem</th>
                    <th>Status</th>
                    <th class="text-right">Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($categorias as $categoria): ?>
                    <tr>
                      <td><strong><?= escapar($categoria['nome']) ?></strong></td>
                      <td><?= escapar($categoria['descricao']) ?></td>
                      <td class="text-right"><?= (int) $categoria['ordem'] ?></td>
                      <td>
                        <span class="badge <?= $categoria['status'] === 'Ativo' ? 'badge--success' : 'badge--neutral' ?>">
                          <?= escapar($categoria['status']) ?>
                        </span>
                      </td>
                      <td class="text-right">
                        <span class="row-actions">
                          <a class="icon-button" href="./categorias.php?editar=<?= $categoria['id'] ?>" title="Editar">
                            <i class="fa-solid fa-pen-to-square"></i>
                          </a>
                          <a class="icon-button" href="./categorias.php?excluir=<?= $categoria['id'] ?>" title="Excluir" onclick="return confirm('Excluir esta categoria?')">
                            <i class="fa-solid fa-trash"></i>
                          </a>
                        </span>
                      </td>
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
