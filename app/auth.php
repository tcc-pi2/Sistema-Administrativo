<?php
// Controle de sessao do painel PHP.

if (session_status() === PHP_SESSION_NONE) {
    $pastaSessoes = __DIR__ . '/sessoes';

    if (!is_dir($pastaSessoes)) {
        mkdir($pastaSessoes, 0777, true);
    }

    session_save_path($pastaSessoes);
    session_start();
}

function usuario_logado()
{
    return $_SESSION['admin'] ?? null;
}

function exigir_login()
{
    if (!usuario_logado()) {
        header('Location: ./login.php');
        exit;
    }
}

function login_admin($pdo, $login, $senha)
{
    $login = trim(strtolower($login));

    if ($login === 'admin') {
        $login = 'admin@gastrotech.com';
    }

    $sql = 'SELECT * FROM administradores WHERE email = :email AND status = "Ativo" LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $login]);
    $admin = $stmt->fetch();

    if (!$admin) {
        return false;
    }

    $hash = $admin['senha_hash'] ?? '';
    $senha_correta = password_verify($senha, $hash);

    // O SQL de exemplo ainda usa um texto no lugar do hash.
    // Para apresentacao, admin/123 continua funcionando.
    if (!$senha_correta && strpos($hash, 'troque-este-hash') !== false) {
        $senha_correta = $senha === '123';
    }

    if (!$senha_correta) {
        return false;
    }

    $_SESSION['admin'] = [
        'id' => $admin['id'],
        'nome' => $admin['nome'],
        'email' => $admin['email'],
        'permissao' => $admin['permissao'],
    ];

    return true;
}

function sair_admin()
{
    $_SESSION = [];
    session_destroy();
}
