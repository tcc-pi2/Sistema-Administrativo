<?php
// Produtos e categorias no banco.

function listar_categorias($pdo)
{
    $sql = 'SELECT * FROM categorias ORDER BY ordem ASC, nome ASC';
    return $pdo->query($sql)->fetchAll();
}

function buscar_categoria($pdo, $id)
{
    $stmt = $pdo->prepare('SELECT * FROM categorias WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => (int) $id]);
    return $stmt->fetch();
}

function salvar_categoria($pdo, $dados)
{
    $id = (int) ($dados['id'] ?? 0);
    $params = [
        'nome' => trim($dados['nome'] ?? ''),
        'descricao' => trim($dados['descricao'] ?? ''),
        'ordem' => (int) ($dados['ordem'] ?? 0),
        'status' => ($dados['status'] ?? 'Ativo') === 'Inativo' ? 'Inativo' : 'Ativo',
    ];

    if ($id > 0) {
        $params['id'] = $id;
        $sql = '
            UPDATE categorias SET
                nome = :nome,
                descricao = :descricao,
                ordem = :ordem,
                status = :status
            WHERE id = :id
        ';
    } else {
        $sql = '
            INSERT INTO categorias (nome, descricao, ordem, status)
            VALUES (:nome, :descricao, :ordem, :status)
        ';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

function excluir_categoria($pdo, $id)
{
    $stmt = $pdo->prepare('DELETE FROM categorias WHERE id = :id');
    $stmt->execute(['id' => (int) $id]);
}

function listar_produtos($pdo, $busca = '')
{
    $sql = '
        SELECT produtos.*, categorias.nome AS categoria_nome
        FROM produtos
        LEFT JOIN categorias ON categorias.id = produtos.categoria_id
    ';

    $params = [];

    if ($busca !== '') {
        $sql .= ' WHERE produtos.nome LIKE :busca OR produtos.descricao LIKE :busca OR categorias.nome LIKE :busca';
        $params['busca'] = '%' . $busca . '%';
    }

    $sql .= ' ORDER BY produtos.nome ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function buscar_produto($pdo, $id)
{
    $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

function salvar_upload_imagem($arquivo)
{
    if (empty($arquivo['name']) || $arquivo['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!isset($permitidos[$arquivo['type']])) {
        return null;
    }

    $nomeBase = pathinfo($arquivo['name'], PATHINFO_FILENAME);
    $nomeBase = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nomeBase));
    $extensao = $permitidos[$arquivo['type']];
    $nomeFinal = trim($nomeBase, '-') . '-' . time() . '.' . $extensao;

    $pastaDestino = __DIR__ . '/../src/assets/images/menu';
    $caminhoDestino = $pastaDestino . '/' . $nomeFinal;

    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0777, true);
    }

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
        return null;
    }

    return '../assets/images/menu/' . $nomeFinal;
}

function salvar_produto($pdo, $dados, $arquivo)
{
    $id = (int) ($dados['id'] ?? 0);
    $imagemUpload = salvar_upload_imagem($arquivo);
    $imagem = $imagemUpload ?: trim($dados['imagem_url'] ?? '');

    $params = [
        'categoria_id' => $dados['categoria_id'] !== '' ? (int) $dados['categoria_id'] : null,
        'nome' => trim($dados['nome'] ?? ''),
        'descricao' => trim($dados['descricao'] ?? ''),
        'opcoes' => trim($dados['opcoes'] ?? ''),
        'opcoes_personalizacao' => trim($dados['opcoes_personalizacao'] ?? ''),
        'ingredientes' => trim($dados['ingredientes'] ?? ''),
        'tags' => trim($dados['tags'] ?? ''),
        'alergenos' => trim($dados['alergenos'] ?? ''),
        'porcao' => trim($dados['porcao'] ?? ''),
        'calorias' => $dados['calorias'] !== '' ? (int) $dados['calorias'] : null,
        'imagem_url' => $imagem,
        'estoque' => max(0, (int) ($dados['estoque'] ?? 0)),
        'preco' => max(0, (float) ($dados['preco'] ?? 0)),
        'tempo_preparo_min' => max(1, (int) ($dados['tempo_preparo_min'] ?? 10)),
        'destaque' => isset($dados['destaque']) ? 1 : 0,
        'status' => $dados['status'] === 'Inativo' ? 'Inativo' : 'Ativo',
    ];

    if ($id > 0) {
        $params['id'] = $id;
        $sql = '
            UPDATE produtos SET
                categoria_id = :categoria_id,
                nome = :nome,
                descricao = :descricao,
                opcoes = :opcoes,
                opcoes_personalizacao = :opcoes_personalizacao,
                ingredientes = :ingredientes,
                tags = :tags,
                alergenos = :alergenos,
                porcao = :porcao,
                calorias = :calorias,
                imagem_url = :imagem_url,
                estoque = :estoque,
                preco = :preco,
                tempo_preparo_min = :tempo_preparo_min,
                destaque = :destaque,
                status = :status
            WHERE id = :id
        ';
    } else {
        $sql = '
            INSERT INTO produtos (
                categoria_id, nome, descricao, opcoes, opcoes_personalizacao,
                ingredientes, tags, alergenos, porcao, calorias, imagem_url,
                estoque, preco, tempo_preparo_min, destaque, status
            ) VALUES (
                :categoria_id, :nome, :descricao, :opcoes, :opcoes_personalizacao,
                :ingredientes, :tags, :alergenos, :porcao, :calorias, :imagem_url,
                :estoque, :preco, :tempo_preparo_min, :destaque, :status
            )
        ';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

function excluir_produto($pdo, $id)
{
    $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = :id');
    $stmt->execute(['id' => (int) $id]);
}
