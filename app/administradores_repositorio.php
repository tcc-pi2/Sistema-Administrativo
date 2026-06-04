<?php
// Cadastro dos usuarios que entram no painel.

function listar_administradores($pdo)
{
    return $pdo->query('SELECT * FROM administradores ORDER BY nome ASC')->fetchAll();
}

function buscar_administrador($pdo, $id)
{
    $stmt = $pdo->prepare('SELECT * FROM administradores WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => (int) $id]);
    return $stmt->fetch();
}

function salvar_administrador($pdo, $dados)
{
    $id = (int) ($dados['id'] ?? 0);
    $senha = trim($dados['senha'] ?? '');
    $permissao = in_array($dados['permissao'] ?? '', ['Administrador', 'Cardapio', 'Atendimento', 'Leitura'], true)
        ? $dados['permissao']
        : 'Leitura';
    $status = ($dados['status'] ?? 'Ativo') === 'Inativo' ? 'Inativo' : 'Ativo';

    $params = [
        'nome' => trim($dados['nome'] ?? ''),
        'email' => trim(strtolower($dados['email'] ?? '')),
        'permissao' => $permissao,
        'status' => $status,
    ];

    if ($id > 0) {
        $params['id'] = $id;

        if ($senha !== '') {
            $params['senha_hash'] = password_hash($senha, PASSWORD_DEFAULT);
            $sql = '
                UPDATE administradores SET
                    nome = :nome,
                    email = :email,
                    senha_hash = :senha_hash,
                    permissao = :permissao,
                    status = :status
                WHERE id = :id
            ';
        } else {
            $sql = '
                UPDATE administradores SET
                    nome = :nome,
                    email = :email,
                    permissao = :permissao,
                    status = :status
                WHERE id = :id
            ';
        }
    } else {
        $params['senha_hash'] = password_hash($senha !== '' ? $senha : '123', PASSWORD_DEFAULT);
        $sql = '
            INSERT INTO administradores (nome, email, senha_hash, permissao, status)
            VALUES (:nome, :email, :senha_hash, :permissao, :status)
        ';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

function excluir_administrador($pdo, $id)
{
    $stmt = $pdo->prepare('DELETE FROM administradores WHERE id = :id');
    $stmt->execute(['id' => (int) $id]);
}
