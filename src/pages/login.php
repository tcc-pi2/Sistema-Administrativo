<?php
require_once __DIR__ . '/../../app/conexao.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/funcoes.php';
require_once __DIR__ . '/../../app/configuracoes_repositorio.php';

$erro = '';

if (isset($_GET['logout'])) {
    sair_admin();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (login_admin($pdo, $login, $senha)) {
        header('Location: ./dashboard.php');
        exit;
    }

    $erro = 'Usuário ou senha incorretos.';
}

$configuracoes = buscar_configuracoes($pdo);
$nomeLoja = valor_configuracao($configuracoes, 'nome_loja', 'GastroTech');
$logoLoja = valor_configuracao($configuracoes, 'logo_loja', '../assets/brand/gastrotech-logo.jpg');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | <?= escapar($nomeLoja) ?> Admin</title>
  <link rel="icon" type="image/jpeg" href="<?= escapar($logoLoja) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="../styles/admin.css">
</head>
<body class="login-page app-ready">
  <main class="login-card" aria-labelledby="login-title">
    <div class="login-card__brand">
      <span class="login-card__mark">
        <img src="<?= escapar($logoLoja) ?>" alt="Logo <?= escapar($nomeLoja) ?>">
      </span>
      <div>
        <h1 id="login-title">Acesso ao Painel</h1>
        <p>Painel administrativo <?= escapar($nomeLoja) ?></p>
      </div>
    </div>

    <?php if ($erro): ?>
      <p class="form-hint"><?= escapar($erro) ?></p>
    <?php endif; ?>

    <form class="login-form" method="post">
      <div class="form-group">
        <label for="login">Usuário ou e-mail</label>
        <input class="form-control" type="text" id="login" name="login" autocomplete="username" placeholder="admin" required>
      </div>

      <div class="form-group">
        <label for="senha">Senha</label>
        <input class="form-control" type="password" id="senha" name="senha" autocomplete="current-password" placeholder="Digite sua senha" required>
      </div>

      <button class="button button--primary" type="submit">
        <i class="fa-solid fa-right-to-bracket"></i>
        Entrar
      </button>
    </form>

    <div class="login-footer">
      Acesso de teste: <strong>admin</strong> / <strong>123</strong>
    </div>
  </main>
</body>
</html>
