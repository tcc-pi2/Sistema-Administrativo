<?php
// Funcoes do financeiro: caixa, movimentos e resumo das vendas.

function caixa_atual($pdo)
{
    $caixa = $pdo->query('SELECT * FROM caixas ORDER BY id DESC LIMIT 1')->fetch();

    if ($caixa) {
        return $caixa;
    }

    $pdo->exec('INSERT INTO caixas (saldo_inicial, status) VALUES (0.00, "Aberto")');
    return $pdo->query('SELECT * FROM caixas ORDER BY id DESC LIMIT 1')->fetch();
}

function registrar_movimento_caixa($pdo, $dados)
{
    $caixa = caixa_atual($pdo);
    $tipo = ($dados['tipo'] ?? 'Entrada') === 'Saida' ? 'Saida' : 'Entrada';
    $forma = in_array($dados['forma_pagamento'] ?? '', ['Dinheiro', 'Pix', 'Cartao'], true)
        ? $dados['forma_pagamento']
        : 'Dinheiro';

    $stmt = $pdo->prepare('
        INSERT INTO movimentos_caixa (caixa_id, tipo, descricao, forma_pagamento, valor)
        VALUES (:caixa_id, :tipo, :descricao, :forma_pagamento, :valor)
    ');

    $stmt->execute([
        'caixa_id' => $caixa['id'],
        'tipo' => $tipo,
        'descricao' => trim($dados['descricao'] ?? 'Movimento manual') ?: 'Movimento manual',
        'forma_pagamento' => $forma,
        'valor' => max(0, (float) ($dados['valor'] ?? 0)),
    ]);
}

function filtro_forma_pagamento($valor)
{
    return in_array($valor, ['Pix', 'Cartao', 'Dinheiro'], true) ? $valor : '';
}

function montar_filtro_pedidos_financeiro($filtros, &$params)
{
    $where = ['status_pagamento = "Pago"'];
    $forma = filtro_forma_pagamento($filtros['forma_pagamento'] ?? '');

    if (!empty($filtros['data_inicio'])) {
        $where[] = 'DATE(criado_em) >= :data_inicio';
        $params['data_inicio'] = $filtros['data_inicio'];
    }

    if (!empty($filtros['data_fim'])) {
        $where[] = 'DATE(criado_em) <= :data_fim';
        $params['data_fim'] = $filtros['data_fim'];
    }

    if ($forma) {
        $where[] = 'forma_pagamento = :forma_pagamento';
        $params['forma_pagamento'] = $forma;
    }

    return implode(' AND ', $where);
}

function montar_filtro_movimentos_financeiro($filtros, &$params)
{
    $where = ['caixa_id = :caixa_id'];
    $forma = filtro_forma_pagamento($filtros['forma_pagamento'] ?? '');

    if (!empty($filtros['data_inicio'])) {
        $where[] = 'DATE(criado_em) >= :mov_data_inicio';
        $params['mov_data_inicio'] = $filtros['data_inicio'];
    }

    if (!empty($filtros['data_fim'])) {
        $where[] = 'DATE(criado_em) <= :mov_data_fim';
        $params['mov_data_fim'] = $filtros['data_fim'];
    }

    if ($forma) {
        $where[] = 'forma_pagamento = :mov_forma_pagamento';
        $params['mov_forma_pagamento'] = $forma;
    }

    return implode(' AND ', $where);
}

function somar_pedidos_financeiro($pdo, $filtros = [], $forma = '')
{
    $formaSelecionada = filtro_forma_pagamento($filtros['forma_pagamento'] ?? '');

    if ($forma && $formaSelecionada && $formaSelecionada !== $forma) {
        return 0;
    }

    if ($forma) {
        $filtros['forma_pagamento'] = $forma;
    }

    $params = [];
    $where = montar_filtro_pedidos_financeiro($filtros, $params);
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(subtotal), 0) FROM pedidos WHERE {$where}");
    $stmt->execute($params);

    return (float) $stmt->fetchColumn();
}

function resumo_financeiro($pdo, $filtros = [])
{
    $caixa = caixa_atual($pdo);
    $vendas = somar_pedidos_financeiro($pdo, $filtros);
    $pix = somar_pedidos_financeiro($pdo, $filtros, 'Pix');
    $cartao = somar_pedidos_financeiro($pdo, $filtros, 'Cartao');
    $dinheiro = somar_pedidos_financeiro($pdo, $filtros, 'Dinheiro');

    $paramsMovimentos = [];
    $stmt = $pdo->prepare('
        SELECT
            COALESCE(SUM(CASE WHEN tipo = "Entrada" THEN valor ELSE 0 END), 0) AS entradas,
            COALESCE(SUM(CASE WHEN tipo = "Saida" THEN valor ELSE 0 END), 0) AS saidas
        FROM movimentos_caixa
        WHERE ' . montar_filtro_movimentos_financeiro($filtros, $paramsMovimentos) . '
    ');
    $paramsMovimentos['caixa_id'] = $caixa['id'];
    $stmt->execute($paramsMovimentos);
    $movimentos = $stmt->fetch();

    $saldo = (float) $caixa['saldo_inicial']
        + (float) $dinheiro
        + (float) $movimentos['entradas']
        - (float) $movimentos['saidas'];

    return [
        'caixa' => $caixa,
        'vendas' => (float) $vendas,
        'pix' => (float) $pix,
        'cartao' => (float) $cartao,
        'dinheiro' => (float) $dinheiro,
        'entradas' => (float) $movimentos['entradas'],
        'saidas' => (float) $movimentos['saidas'],
        'saldo' => $saldo,
    ];
}

function listar_movimentos_caixa($pdo, $filtros = [])
{
    $caixa = caixa_atual($pdo);
    $params = ['caixa_id' => $caixa['id']];
    $where = montar_filtro_movimentos_financeiro($filtros, $params);
    $stmt = $pdo->prepare("SELECT * FROM movimentos_caixa WHERE {$where} ORDER BY criado_em DESC");
    $stmt->execute($params);
    return $stmt->fetchAll();
}
