<?php
// Funções pequenas usadas em várias páginas PHP.

function escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function dinheiro($valor)
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

function texto_pagamento($valor)
{
    $mapa = [
        'Cartao' => 'Cartão',
        'Pix' => 'Pix',
        'Dinheiro' => 'Dinheiro',
    ];

    return $mapa[$valor] ?? $valor;
}

function texto_tipo_movimento($valor)
{
    $mapa = [
        'Entrada' => 'Entrada',
        'Saida' => 'Saída',
    ];

    return $mapa[$valor] ?? $valor;
}

function texto_permissao($valor)
{
    $mapa = [
        'Cardapio' => 'Cardápio',
        'Administrador' => 'Administrador',
        'Atendimento' => 'Atendimento',
        'Leitura' => 'Leitura',
    ];

    return $mapa[$valor] ?? $valor;
}

function texto_categoria($valor)
{
    $mapa = [
        'Hamburgueres' => 'Hambúrgueres',
        'Porcoes' => 'Porções',
        'Opcoes' => 'Opções',
        'Cardapio' => 'Cardápio',
    ];

    return $mapa[$valor] ?? $valor;
}

function texto_visivel($valor)
{
    $mapa = [
        'Hamburgueres' => 'Hambúrgueres',
        'hamburgueres' => 'hambúrgueres',
        'Porcoes' => 'Porções',
        'porcoes' => 'porções',
        'Opcoes' => 'Opções',
        'opcoes' => 'opções',
        'Cardapio' => 'Cardápio',
        'cardapio' => 'cardápio',
        'Porcao' => 'Porção',
        'porcao' => 'porção',
        'Media' => 'Média',
        'media' => 'média',
        'Codigo' => 'Código',
        'codigo' => 'código',
        'Situacao' => 'Situação',
        'situacao' => 'situação',
        'Preco' => 'Preço',
        'preco' => 'preço',
        'Informacao' => 'Informação',
        'informacao' => 'informação',
        'Configuracoes' => 'Configurações',
        'configuracoes' => 'configurações',
        'Usuario' => 'Usuário',
        'usuario' => 'usuário',
        'Balcao' => 'Balcão',
        'balcao' => 'balcão',
        'Guarana' => 'Guaraná',
        'guarana' => 'guaraná',
        'Acai' => 'Açaí',
        'acai' => 'açaí',
        'Pao' => 'Pão',
        'pao' => 'pão',
        'Hamburguer' => 'Hambúrguer',
        'hamburguer' => 'hambúrguer',
        'Gluten' => 'Glúten',
        'gluten' => 'glúten',
        'Rapido' => 'Rápido',
        'rapido' => 'rápido',
        'Promocoes' => 'Promoções',
        'promocoes' => 'promoções',
        'Reforcado' => 'Reforçado',
        'reforcado' => 'reforçado',
        'Saida' => 'Saída',
        'saida' => 'saída',
    ];

    return str_replace(array_keys($mapa), array_values($mapa), (string) $valor);
}

function horario_curto($valor)
{
    if (!$valor) {
        return '-';
    }

    try {
        return (new DateTime((string) $valor))->format('H:i');
    } catch (Exception $erro) {
        return '-';
    }
}

function minutos_desde($valor)
{
    if (!$valor) {
        return 0;
    }

    try {
        $inicio = new DateTime((string) $valor);
        $agora = new DateTime();
        $segundos = $agora->getTimestamp() - $inicio->getTimestamp();

        return max(0, (int) floor($segundos / 60));
    } catch (Exception $erro) {
        return 0;
    }
}

function texto_minutos($minutos)
{
    $minutos = max(0, (int) $minutos);

    if ($minutos <= 0) {
        return 'agora';
    }

    if ($minutos < 60) {
        return $minutos === 1 ? '1 min' : $minutos . ' min';
    }

    $dias = intdiv($minutos, 1440);
    $horas = intdiv($minutos % 1440, 60);
    $restoMinutos = $minutos % 60;

    if ($dias > 0) {
        $textoDias = $dias === 1 ? '1 dia' : $dias . ' dias';

        if ($horas > 0) {
            return $textoDias . ' ' . $horas . 'h';
        }

        return $textoDias;
    }

    if ($restoMinutos > 0) {
        return $horas . 'h ' . $restoMinutos . 'min';
    }

    return $horas . 'h';
}

function atraso_pedido($pedido)
{
    $status = $pedido['status_pedido'] ?? '';

    if (!in_array($status, ['Recebido', 'Em preparo'], true)) {
        return 0;
    }

    $decorrido = minutos_desde($pedido['criado_em'] ?? null);
    $estimado = (int) ($pedido['tempo_estimado_min'] ?? 0);

    return max(0, $decorrido - $estimado);
}

function lista_texto($valor)
{
    if (!$valor) {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode(',', $valor))));
}

function personalizacoes_produto($valor)
{
    if (!$valor) {
        return [];
    }

    $grupos = [];
    $partes = array_filter(array_map('trim', explode('|', $valor)));

    foreach ($partes as $parte) {
        $pedacos = array_map('trim', explode(':', $parte, 2));

        if (count($pedacos) !== 2) {
            continue;
        }

        $opcoes = lista_texto($pedacos[1]);

        if ($opcoes) {
            $grupos[] = [
                'nome' => $pedacos[0],
                'opcoes' => $opcoes,
            ];
        }
    }

    return $grupos;
}

function icone_categoria($nome)
{
    // Escolhe o ícone da lateral do totem conforme o nome da categoria.
    $texto = strtolower((string) $nome);
    $texto = strtr($texto, [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
        'é' => 'e', 'ê' => 'e',
        'í' => 'i',
        'ó' => 'o', 'õ' => 'o', 'ô' => 'o',
        'ú' => 'u',
        'ç' => 'c',
    ]);

    if (strpos($texto, 'combo') !== false) {
        return 'fa-layer-group';
    }

    if (strpos($texto, 'hamb') !== false || strpos($texto, 'lanche') !== false) {
        return 'fa-burger';
    }

    if (strpos($texto, 'porc') !== false || strpos($texto, 'batata') !== false) {
        return 'fa-bowl-food';
    }

    if (strpos($texto, 'bebida') !== false || strpos($texto, 'suco') !== false || strpos($texto, 'refri') !== false) {
        return 'fa-glass-water';
    }

    if (strpos($texto, 'sobremesa') !== false || strpos($texto, 'doce') !== false) {
        return 'fa-ice-cream';
    }

    if (strpos($texto, 'adicion') !== false || strpos($texto, 'extra') !== false || strpos($texto, 'molho') !== false) {
        return 'fa-droplet';
    }

    return 'fa-utensils';
}

function imagem_produto($produto)
{
    if (!empty($produto['imagem_url'])) {
        return $produto['imagem_url'];
    }

    return '../assets/images/menu/combo 2.jpg';
}
