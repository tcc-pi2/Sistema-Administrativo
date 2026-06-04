<?php
require_once __DIR__ . '/../../app/conexao.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/funcoes.php';
require_once __DIR__ . '/../../app/configuracoes_repositorio.php';
require_once __DIR__ . '/../../app/administradores_repositorio.php';

exigir_login();

$mensagem = '';
$adminEdicao = null;
$usuarioAtual = usuario_logado();

if (isset($_GET['excluir'])) {
    if ((int) $_GET['excluir'] !== (int) $usuarioAtual['id']) {
        excluir_administrador($pdo, $_GET['excluir']);
    }

    header('Location: ./administradores.php?msg=excluido');
    exit;
}

if (isset($_GET['editar'])) {
    $adminEdicao = buscar_administrador($pdo, $_GET['editar']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    salvar_administrador($pdo, $_POST);
    header('Location: ./administradores.php?msg=salvo');
    exit;
}

if (($_GET['msg'] ?? '') === 'salvo') {
    $mensagem = 'Usuario salvo com sucesso.';
}

if (($_GET['msg'] ?? '') === 'excluido') {
    $mensagem = 'Usuario removido quando permitido.';
}

$configuracoes = buscar_configuracoes($pdo);
$nomeLoja = valor_configuracao($configuracoes, 'nome_loja', 'GastroTech');
$logoLoja = valor_configuracao($configuracoes, 'logo_loja', '../assets/brand/gastrotech-logo.jpg');
$administradores = listar_administradores($pdo);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios | <?= escapar($nomeLoja) ?> Admin</title>
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
            <span class="brand-card__role"><?= escapar($usuarioAtual['nome']) ?></span>
          </span>
        </a>

        <nav class="sidebar__nav" aria-label="Menu principal">
          <a class="nav-link" href="./dashboard.php"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></a>
          <a class="nav-link" href="./produtos.php"><i class="fa-solid fa-box"></i><span>Produtos</span></a>
          <a class="nav-link" href="./categorias.php"><i class="fa-solid fa-table-cells-large"></i><span>Categorias</span></a>
          <a class="nav-link" href="./financeiro.php"><i class="fa-solid fa-cash-register"></i><span>Financeiro</span></a>
          <a class="nav-link" href="./cozinha.php"><i class="fa-solid fa-kitchen-set"></i><span>Cozinha</span></a>
          <a class="nav-link nav-link--active" href="./administradores.php"><i class="fa-solid fa-users-gear"></i><span>Usuarios</span></a>
          <a class="nav-link" href="./configuracoes.php"><i class="fa-solid fa-gear"></i><span>Configuracoes</span></a>
          <a class="nav-link" href="./totem.php" target="_blank"><i class="fa-solid fa-display"></i><span>Totem</span></a>
          <a class="nav-link" href="./login.php?logout=1"><i class="fa-solid fa-right-from-bracket"></i><span>Sair</span></a>
        </nav>
      </div>
    </aside>

    <main class="content">
      <header class="topbar">
        <div>
          <p class="topbar__eyebrow">Acesso ao painel</p>
          <h1 class="topbar__title">Usuarios</h1>
        </div>
      </header>

      <section class="page-body">
        <div class="page-stack">
          <?php if ($mensagem): ?>
            <p class="form-hint"><?= escapar($mensagem) ?></p>
          <?php endif; ?>

          <section class="form-card">
            <div class="form-card__header">
              <span class="form-card__icon"><i class="fa-solid fa-user-plus"></i></span>
              <div>
                <h2><?= $adminEdicao ? 'Editar usuario' : 'Novo usuario' ?></h2>
                <p>Cadastre quem pode entrar no painel administrativo.</p>
              </div>
            </div>

            <form class="crud-form" method="post">
              <input type="hidden" name="id" value="<?= escapar($adminEdicao['id'] ?? '') ?>">

              <div class="form-grid">
                <div class="form-group">
                  <label for="nome">Nome</label>
                  <input class="form-control" id="nome" name="nome" type="text" value="<?= escapar($adminEdicao['nome'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                  <label for="email">E-mail</label>
                  <input class="form-control" id="email" name="email" type="email" value="<?= escapar($adminEdicao['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                  <label for="senha">Senha</label>
                  <input class="form-control" id="senha" name="senha" type="password" placeholder="<?= $adminEdicao ? 'Deixe vazio para manter' : 'Padrao 123 se vazio' ?>">
                </div>

                <div class="form-group">
                  <label for="permissao">Permissao</label>
                  <select class="form-control" id="permissao" name="permissao">
                    <?php foreach (['Administrador', 'Cardapio', 'Atendimento', 'Leitura'] as $permissao): ?>
                      <option value="<?= $permissao ?>" <?= ($adminEdicao['permissao'] ?? 'Leitura') === $permissao ? 'selected' : '' ?>>
                        <?= $permissao ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="status">Status</label>
                  <select class="form-control" id="status" name="status">
                    <option value="Ativo" <?= ($adminEdicao['status'] ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="Inativo" <?= ($adminEdicao['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                  </select>
                </div>
              </div>

              <div class="crud-panel__footer">
                <a class="button button--ghost" href="./administradores.php">Limpar</a>
                <button class="button button--primary" type="submit">
                  <i class="fa-solid fa-check"></i>
                  Salvar usuario
                </button>
              </div>
            </form>
          </section>

          <section class="table-card">
            <div class="table-card__header">
              <h3 class="table-card__title">Usuarios cadastrados</h3>
            </div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Permissao</th>
                    <th>Status</th>
                    <th class="text-right">Acoes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($administradores as $admin): ?>
                    <tr>
                      <td><strong><?= escapar($admin['nome']) ?></strong></td>
                      <td><?= escapar($admin['email']) ?></td>
                      <td><?= escapar($admin['permissao']) ?></td>
                      <td>
                        <span class="badge <?= $admin['status'] === 'Ativo' ? 'badge--success' : 'badge--neutral' ?>">
                          <?= escapar($admin['status']) ?>
                        </span>
                      </td>
                      <td class="text-right">
                        <span class="row-actions">
                          <a class="icon-button" href="./administradores.php?editar=<?= $admin['id'] ?>" title="Editar">
                            <i class="fa-solid fa-pen-to-square"></i>
                          </a>
                          <?php if ((int) $admin['id'] !== (int) $usuarioAtual['id']): ?>
                            <a class="icon-button" href="./administradores.php?excluir=<?= $admin['id'] ?>" title="Excluir" onclick="return confirm('Excluir este usuario?')">
                              <i class="fa-solid fa-trash"></i>
                            </a>
                          <?php endif; ?>
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
