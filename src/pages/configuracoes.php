<?php
require_once __DIR__ . '/../../app/conexao.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/funcoes.php';
require_once __DIR__ . '/../../app/configuracoes_repositorio.php';

exigir_login();

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    salvar_configuracoes_loja($pdo, $_POST, $_FILES['logo_arquivo'] ?? []);
    header('Location: ./configuracoes.php?msg=salvo');
    exit;
}

if (($_GET['msg'] ?? '') === 'salvo') {
    $mensagem = 'Configuracoes salvas com sucesso.';
}

$admin = usuario_logado();
$configuracoes = buscar_configuracoes($pdo);
$nomeLoja = valor_configuracao($configuracoes, 'nome_loja', 'GastroTech');
$fraseTotem = valor_configuracao($configuracoes, 'frase_totem', 'Monte seu pedido com todos os detalhes.');
$logoLoja = valor_configuracao($configuracoes, 'logo_loja', '../assets/brand/gastrotech-logo.jpg');
$tempoEspera = valor_configuracao($configuracoes, 'tempo_espera_min', '18');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configuracoes | <?= escapar($nomeLoja) ?> Admin</title>
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
          <a class="nav-link" href="./financeiro.php"><i class="fa-solid fa-cash-register"></i><span>Financeiro</span></a>
          <a class="nav-link" href="./cozinha.php"><i class="fa-solid fa-kitchen-set"></i><span>Cozinha</span></a>
          <a class="nav-link" href="./administradores.php"><i class="fa-solid fa-users-gear"></i><span>Usuarios</span></a>
          <a class="nav-link nav-link--active" href="./configuracoes.php"><i class="fa-solid fa-gear"></i><span>Configuracoes</span></a>
          <a class="nav-link" href="./totem.php" target="_blank"><i class="fa-solid fa-display"></i><span>Totem</span></a>
          <a class="nav-link" href="./login.php?logout=1"><i class="fa-solid fa-right-from-bracket"></i><span>Sair</span></a>
        </nav>
      </div>
    </aside>

    <main class="content">
      <header class="topbar">
        <div>
          <p class="topbar__eyebrow">Identidade da loja</p>
          <h1 class="topbar__title">Configuracoes</h1>
        </div>
        <a class="button button--ghost" href="./totem.php" target="_blank">
          <i class="fa-solid fa-display"></i>
          Ver totem
        </a>
      </header>

      <section class="page-body">
        <div class="page-stack page-stack--narrow">
          <?php if ($mensagem): ?>
            <p class="form-hint"><?= escapar($mensagem) ?></p>
          <?php endif; ?>

          <section class="form-card">
            <div class="form-card__header">
              <span class="form-card__icon"><i class="fa-solid fa-store"></i></span>
              <div>
                <h2>Dados exibidos no sistema</h2>
                <p>Essas informacoes aparecem no totem e no painel.</p>
              </div>
            </div>

            <form class="crud-form" method="post" enctype="multipart/form-data">
              <input type="hidden" name="logo_atual" value="<?= escapar($logoLoja) ?>">

              <div class="form-grid">
                <div class="form-group">
                  <label for="nome_loja">Nome da loja</label>
                  <input class="form-control" id="nome_loja" name="nome_loja" type="text" value="<?= escapar($nomeLoja) ?>" required>
                </div>

                <div class="form-group">
                  <label for="tempo_espera_min">Tempo medio de espera</label>
                  <input class="form-control" id="tempo_espera_min" name="tempo_espera_min" type="number" min="1" value="<?= escapar($tempoEspera) ?>">
                </div>

                <div class="form-group form-group--wide">
                  <label for="frase_totem">Frase do totem</label>
                  <input class="form-control" id="frase_totem" name="frase_totem" type="text" value="<?= escapar($fraseTotem) ?>">
                </div>

                <div class="form-group form-group--wide">
                  <label for="logo_arquivo">Trocar logo</label>
                  <input class="form-control" id="logo_arquivo" name="logo_arquivo" type="file" accept="image/png,image/jpeg,image/webp">
                  <p class="form-hint">A logo enviada fica salva em src/assets/brand.</p>
                </div>

                <div class="form-group form-group--wide">
                  <label>Logo atual</label>
                  <span class="image-preview image-preview--logo">
                    <img src="<?= escapar($logoLoja) ?>" alt="Logo atual">
                  </span>
                </div>
              </div>

              <div class="crud-panel__footer">
                <button class="button button--primary" type="submit">
                  <i class="fa-solid fa-check"></i>
                  Salvar configuracoes
                </button>
              </div>
            </form>
          </section>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
