<?php
// Configurações simples da loja: nome, logo e tempo de espera.

function buscar_configuracoes($pdo)
{
    $linhas = $pdo->query('SELECT chave, valor FROM configuracoes_sistema')->fetchAll();
    $configuracoes = [];

    foreach ($linhas as $linha) {
        $configuracoes[$linha['chave']] = $linha['valor'];
    }

    return $configuracoes;
}

function valor_configuracao($configuracoes, $chave, $padrao = '')
{
    return $configuracoes[$chave] ?? $padrao;
}

function salvar_configuracao($pdo, $chave, $valor, $descricao = '')
{
    $sql = '
        INSERT INTO configuracoes_sistema (chave, valor, descricao)
        VALUES (:chave, :valor, :descricao)
        ON DUPLICATE KEY UPDATE
            valor = VALUES(valor),
            descricao = VALUES(descricao)
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'chave' => $chave,
        'valor' => $valor,
        'descricao' => $descricao,
    ]);
}

function salvar_upload_logo($arquivo)
{
    if (empty($arquivo['name']) || $arquivo['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!isset($permitidos[$arquivo['type']])) {
        return null;
    }

    $extensao = $permitidos[$arquivo['type']];
    $nomeFinal = 'logo-loja-' . time() . '.' . $extensao;
    $pastaDestino = __DIR__ . '/../src/assets/brand';
    $caminhoDestino = $pastaDestino . '/' . $nomeFinal;

    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0777, true);
    }

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
        return null;
    }

    return '../assets/brand/' . $nomeFinal;
}

function salvar_configuracoes_loja($pdo, $dados, $arquivoLogo)
{
    $nome = trim($dados['nome_loja'] ?? 'GastroTech') ?: 'GastroTech';
    $frase = trim($dados['frase_totem'] ?? 'Monte seu pedido com todos os detalhes.');
    $tempo = max(1, (int) ($dados['tempo_espera_min'] ?? 18));
    $logoUpload = salvar_upload_logo($arquivoLogo);
    $logo = $logoUpload ?: trim($dados['logo_atual'] ?? '../assets/brand/gastrotech-logo.jpg');

    salvar_configuracao($pdo, 'nome_loja', $nome, 'Nome exibido no totem e no painel');
    salvar_configuracao($pdo, 'frase_totem', $frase, 'Frase curta exibida abaixo da logo no totem');
    salvar_configuracao($pdo, 'tempo_espera_min', (string) $tempo, 'Previsão em minutos exibida ao cliente');
    salvar_configuracao($pdo, 'logo_loja', $logo, 'Logo exibida no sistema');
}
