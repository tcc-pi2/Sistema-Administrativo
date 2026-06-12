<?php
// Pedidos do totem e da cozinha.

function codigo_proximo_pedido($pdo)
{
    $proximoId = (int) $pdo->query('SELECT COALESCE(MAX(id), 104) + 1 FROM pedidos')->fetchColumn();
    return 'A' . $proximoId;
}

function buscar_totem_principal($pdo)
{
    $id = $pdo->query('SELECT id FROM totens ORDER BY id ASC LIMIT 1')->fetchColumn();
    return $id ? (int) $id : 1;
}

function criar_pedido_banco($pdo, $cliente, $pagamento, $itens)
{
    if (!is_array($itens) || count($itens) === 0) {
        throw new Exception('Adicione pelo menos um item ao pedido.');
    }

    $pdo->beginTransaction();

    try {
        $ids = array_values(array_unique(array_map(fn($item) => (int) ($item['id'] ?? 0), $itens)));
        $ids = array_filter($ids);

        if (!$ids) {
            throw new Exception('Itens invalidos.');
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id IN ($placeholders) AND status = 'Ativo'");
        $stmt->execute($ids);
        $produtosBanco = [];

        foreach ($stmt->fetchAll() as $produto) {
            $produtosBanco[(int) $produto['id']] = $produto;
        }

        $subtotal = 0;
        $tempoEstimado = 0;
        $itensPedido = [];

        foreach ($itens as $item) {
            $produtoId = (int) ($item['id'] ?? 0);
            $quantidade = max(1, (int) ($item['quantidade'] ?? 1));
            $observacao = trim($item['observacao'] ?? '');

            if (!isset($produtosBanco[$produtoId])) {
                continue;
            }

            $produto = $produtosBanco[$produtoId];

            if ((int) $produto['estoque'] < $quantidade) {
                throw new Exception('Estoque insuficiente para ' . $produto['nome'] . '.');
            }

            $subtotal += (float) $produto['preco'] * $quantidade;
            $tempoEstimado = max($tempoEstimado, (int) $produto['tempo_preparo_min']);
            $itensPedido[] = [
                'produto' => $produto,
                'quantidade' => $quantidade,
                'observacao' => $observacao,
            ];
        }

        if (!$itensPedido) {
            throw new Exception('Nenhum item valido foi encontrado.');
        }

        $codigo = codigo_proximo_pedido($pdo);
        $totemId = buscar_totem_principal($pdo);
        $cliente = trim($cliente) ?: 'Cliente';

        $stmtPedido = $pdo->prepare('
            INSERT INTO pedidos (
                totem_id, codigo_retirada, nome_cliente, status_pedido,
                status_pagamento, forma_pagamento, subtotal, tempo_estimado_min
            ) VALUES (
                :totem_id, :codigo_retirada, :nome_cliente, "Recebido",
                "Pago", :forma_pagamento, :subtotal, :tempo_estimado_min
            )
        ');

        $stmtPedido->execute([
            'totem_id' => $totemId,
            'codigo_retirada' => $codigo,
            'nome_cliente' => $cliente,
            'forma_pagamento' => in_array($pagamento, ['Pix', 'Cartao', 'Dinheiro'], true) ? $pagamento : 'Pix',
            'subtotal' => $subtotal,
            'tempo_estimado_min' => $tempoEstimado,
        ]);

        $pedidoId = (int) $pdo->lastInsertId();
        $stmtItem = $pdo->prepare('
            INSERT INTO itens_pedido (
                pedido_id, produto_id, nome_produto, observacao, quantidade, preco_unitario
            ) VALUES (
                :pedido_id, :produto_id, :nome_produto, :observacao, :quantidade, :preco_unitario
            )
        ');

        $stmtEstoque = $pdo->prepare('UPDATE produtos SET estoque = GREATEST(0, estoque - :quantidade) WHERE id = :id');

        foreach ($itensPedido as $itemPedido) {
            $produto = $itemPedido['produto'];
            $quantidade = $itemPedido['quantidade'];

            $stmtItem->execute([
                'pedido_id' => $pedidoId,
                'produto_id' => $produto['id'],
                'nome_produto' => $produto['nome'],
                'observacao' => $itemPedido['observacao'] ?: null,
                'quantidade' => $quantidade,
                'preco_unitario' => $produto['preco'],
            ]);

            $stmtEstoque->execute([
                'quantidade' => $quantidade,
                'id' => $produto['id'],
            ]);
        }

        $pdo->commit();

        return [
            'id' => $pedidoId,
            'codigo' => $codigo,
            'total' => $subtotal,
            'tempo' => $tempoEstimado,
        ];
    } catch (Exception $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}

function listar_pedidos_banco($pdo, $filtro = 'Todos')
{
    $params = [];
    $sql = 'SELECT * FROM pedidos';

    if ($filtro !== 'Todos') {
        $sql .= ' WHERE status_pedido = :status';
        $params['status'] = $filtro;
    }

    $sql .= ' ORDER BY criado_em DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pedidos = $stmt->fetchAll();

    $stmtItens = $pdo->prepare('SELECT * FROM itens_pedido WHERE pedido_id = :pedido_id ORDER BY id ASC');

    foreach ($pedidos as &$pedido) {
        $stmtItens->execute(['pedido_id' => $pedido['id']]);
        $pedido['itens'] = $stmtItens->fetchAll();
    }

    return $pedidos;
}

function buscar_pedido_por_codigo($pdo, $codigo)
{
    $codigo = trim(strtoupper($codigo));

    if ($codigo === '') {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE codigo_retirada = :codigo LIMIT 1');
    $stmt->execute(['codigo' => $codigo]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        return null;
    }

    $stmtItens = $pdo->prepare('SELECT * FROM itens_pedido WHERE pedido_id = :pedido_id ORDER BY id ASC');
    $stmtItens->execute(['pedido_id' => $pedido['id']]);
    $pedido['itens'] = $stmtItens->fetchAll();

    return $pedido;
}

function contar_pedidos_por_status($pdo)
{
    $contagens = [
        'Todos' => 0,
        'Recebido' => 0,
        'Em preparo' => 0,
        'Pronto' => 0,
        'Retirado' => 0,
    ];

    $linhas = $pdo->query('SELECT status_pedido, COUNT(*) AS total FROM pedidos GROUP BY status_pedido')->fetchAll();

    foreach ($linhas as $linha) {
        $contagens[$linha['status_pedido']] = (int) $linha['total'];
        $contagens['Todos'] += (int) $linha['total'];
    }

    return $contagens;
}

function proximo_status_pedido($status)
{
    $fluxo = ['Recebido', 'Em preparo', 'Pronto', 'Retirado'];
    $indice = array_search($status, $fluxo, true);

    if ($indice === false) {
        return 'Recebido';
    }

    return $fluxo[min($indice + 1, count($fluxo) - 1)];
}

function avancar_status_pedido($pdo, $pedidoId)
{
    $stmt = $pdo->prepare('SELECT status_pedido FROM pedidos WHERE id = :id');
    $stmt->execute(['id' => (int) $pedidoId]);
    $statusAtual = $stmt->fetchColumn();

    if (!$statusAtual) {
        return null;
    }

    $novoStatus = proximo_status_pedido($statusAtual);
    $stmt = $pdo->prepare('UPDATE pedidos SET status_pedido = :status WHERE id = :id');
    $stmt->execute([
        'status' => $novoStatus,
        'id' => (int) $pedidoId,
    ]);

    return $novoStatus;
}
