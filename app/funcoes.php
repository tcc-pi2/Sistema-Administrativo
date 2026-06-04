<?php
// Funcoes pequenas usadas em varias paginas PHP.

function escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function dinheiro($valor)
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
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
    // Escolhe o icone da lateral do totem conforme o nome da categoria.
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
