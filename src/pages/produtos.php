<?php
require_once __DIR__ . '/../../app/conexao.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/funcoes.php';
require_once __DIR__ . '/../../app/configuracoes_repositorio.php';
require_once __DIR__ . '/../../app/produtos_repositorio.php';

exigir_login();

$mensagem = '';
$busca = trim($_GET['busca'] ?? '');
$produtoEdicao = null;

if (isset($_GET['excluir'])) {
    excluir_produto($pdo, $_GET['excluir']);
    header('Location: ./produtos.php?msg=excluido');
    exit;
}

if (isset($_GET['editar'])) {
    $produtoEdicao = buscar_produto($pdo, $_GET['editar']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    salvar_produto($pdo, $_POST, $_FILES['imagem_arquivo'] ?? []);
    header('Location: ./produtos.php?msg=salvo');
    exit;
}

if (($_GET['msg'] ?? '') === 'salvo') {
    $mensagem = 'Produto salvo com sucesso.';
}

if (($_GET['msg'] ?? '') === 'excluido') {
    $mensagem = 'Produto removido com sucesso.';
}

$categorias = listar_categorias($pdo);
$produtos = listar_produtos($pdo, $busca);
$admin = usuario_logado();
$configuracoes = buscar_configuracoes($pdo);
$nomeLoja = valor_configuracao($configuracoes, 'nome_loja', 'GastroTech');
$logoLoja = valor_configuracao($configuracoes, 'logo_loja', '../assets/brand/gastrotech-logo.jpg');
$limiteEstoqueBaixo = 10;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Produtos | <?= escapar($nomeLoja) ?> Admin</title>
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
            <span class="brand-card__role"><?= escapar(texto_visivel($admin['nome'])) ?></span>
          </span>
        </a>

        <nav class="sidebar__nav" aria-label="Menu principal">
          <a class="nav-link" href="./dashboard.php"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></a>
          <a class="nav-link nav-link--active" href="./produtos.php"><i class="fa-solid fa-box"></i><span>Produtos</span></a>
          <a class="nav-link" href="./categorias.php"><i class="fa-solid fa-table-cells-large"></i><span>Categorias</span></a>
          <a class="nav-link" href="./financeiro.php"><i class="fa-solid fa-cash-register"></i><span>Financeiro</span></a>
          <a class="nav-link" href="./cozinha.php"><i class="fa-solid fa-kitchen-set"></i><span>Cozinha</span></a>
          <a class="nav-link" href="./administradores.php"><i class="fa-solid fa-users-gear"></i><span>Usuários</span></a>
          <a class="nav-link" href="./configuracoes.php"><i class="fa-solid fa-gear"></i><span>Configurações</span></a>
          <a class="nav-link" href="./totem.php" target="_blank"><i class="fa-solid fa-display"></i><span>Totem</span></a>
          <a class="nav-link" href="./login.php?logout=1"><i class="fa-solid fa-right-from-bracket"></i><span>Sair</span></a>
        </nav>
      </div>
    </aside>

    <main class="content">
      <header class="topbar">
        <div>
          <p class="topbar__eyebrow">Cardápio do totem</p>
          <h1 class="topbar__title">Cardápio</h1>
        </div>
        <div class="topbar__actions">
          <a class="button button--primary" href="./produtos.php#produto-form">
            <i class="fa-solid fa-plus"></i>
            Novo produto
          </a>
        </div>
      </header>

      <section class="page-body">
        <div class="page-stack page-stack--wide">
          <?php if ($mensagem): ?>
            <p class="form-hint"><?= escapar($mensagem) ?></p>
          <?php endif; ?>

          <div class="product-admin-layout">
            <section class="table-card product-list-panel">
              <div class="table-card__header">
                <div>
                  <h3 class="table-card__title">Produtos cadastrados</h3>
                  <p class="form-hint"><?= count($produtos) ?> item(ns) encontrados</p>
                </div>
                <form class="toolbar" method="get">
                  <label class="search" aria-label="Buscar produtos">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="busca" value="<?= escapar($busca) ?>" placeholder="Buscar item ou categoria">
                  </label>
                  <button class="button button--ghost" type="submit">Buscar</button>
                </form>
              </div>

              <div class="product-list">
                <?php foreach ($produtos as $produto): ?>
                  <article class="product-row <?= (int) $produto['estoque'] <= $limiteEstoqueBaixo ? 'is-low-stock' : '' ?>">
                    <span class="product-thumb product-row__image">
                      <img src="<?= escapar(imagem_produto($produto)) ?>" alt="">
                    </span>

                    <div class="product-row__content">
                      <div class="product-row__header">
                        <div>
                          <h3><?= escapar($produto['nome']) ?></h3>
                          <p><?= escapar(texto_visivel($produto['descricao'])) ?></p>
                        </div>
                        <strong class="product-row__price"><?= dinheiro($produto['preco']) ?></strong>
                      </div>

                      <div class="product-row__meta">
                        <span><i class="fa-solid fa-layer-group"></i><?= escapar(texto_categoria($produto['categoria_nome'] ?? 'Sem categoria')) ?></span>
                        <span><i class="fa-solid fa-boxes-stacked"></i><?= (int) $produto['estoque'] ?> un.</span>
                        <span><i class="fa-solid fa-clock"></i><?= (int) $produto['tempo_preparo_min'] ?> min</span>
                        <?php if ((int) $produto['estoque'] <= 0): ?>
                          <span class="badge badge--danger">Sem estoque</span>
                        <?php elseif ((int) $produto['estoque'] <= $limiteEstoqueBaixo): ?>
                          <span class="badge badge--warning">Estoque baixo</span>
                        <?php endif; ?>
                        <span class="badge <?= $produto['status'] === 'Ativo' ? 'badge--success' : 'badge--neutral' ?>">
                          <?= escapar($produto['status']) ?>
                        </span>
                      </div>
                    </div>

                    <div class="product-row__actions">
                      <a class="icon-button" href="./produtos.php?editar=<?= $produto['id'] ?>#produto-form" title="Editar">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </a>
                      <a class="icon-button" href="./produtos.php?excluir=<?= $produto['id'] ?>" title="Excluir" onclick="return confirm('Excluir este produto?')">
                        <i class="fa-solid fa-trash"></i>
                      </a>
                    </div>
                  </article>
                <?php endforeach; ?>

                <?php if (!$produtos): ?>
                  <div class="empty-card">
                    Nenhum produto encontrado.
                  </div>
                <?php endif; ?>
              </div>
            </section>

            <aside class="form-card product-side-panel" id="produto-form">
              <div class="form-card__header">
                <span class="form-card__icon"><i class="fa-solid fa-burger"></i></span>
                <div>
                  <h2><?= $produtoEdicao ? 'Editar produto' : 'Novo produto' ?></h2>
                  <p><?= $produtoEdicao ? 'Altere os dados e salve.' : 'Adicione um item ao cardápio.' ?></p>
                </div>
              </div>

              <form class="crud-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= escapar($produtoEdicao['id'] ?? '') ?>">

                <div class="form-grid">
                  <div class="form-group form-group--wide">
                    <label for="nome">Nome</label>
                    <input class="form-control" id="nome" name="nome" type="text" required value="<?= escapar($produtoEdicao['nome'] ?? '') ?>">
                  </div>

                  <div class="form-group form-group--wide">
                    <label for="categoria">Categoria</label>
                    <select class="form-control" id="categoria" name="categoria_id">
                      <option value="">Sem categoria</option>
                      <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['id'] ?>" <?= (string) ($produtoEdicao['categoria_id'] ?? '') === (string) $categoria['id'] ? 'selected' : '' ?>>
                          <?= escapar(texto_categoria($categoria['nome'])) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="form-group form-group--stock">
                    <label for="estoque">Estoque</label>
                    <input class="form-control" id="estoque" name="estoque" type="number" min="0" value="<?= escapar($produtoEdicao['estoque'] ?? 0) ?>">
                  </div>

                  <div class="form-group">
                    <label for="preco">Preço</label>
                    <input class="form-control" id="preco" name="preco" type="number" min="0" step="0.01" value="<?= escapar($produtoEdicao['preco'] ?? '') ?>">
                  </div>

                  <div class="form-group">
                    <label for="tempo">Preparo min.</label>
                    <input class="form-control" id="tempo" name="tempo_preparo_min" type="number" min="1" value="<?= escapar($produtoEdicao['tempo_preparo_min'] ?? 10) ?>">
                  </div>

                  <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                      <option value="Ativo" <?= ($produtoEdicao['status'] ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                      <option value="Inativo" <?= ($produtoEdicao['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                    </select>
                  </div>

                  <div class="form-group form-group--wide">
                    <label for="descricao">Descrição</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= escapar($produtoEdicao['descricao'] ?? '') ?></textarea>
                  </div>

                  <div class="form-group form-group--wide">
                    <label for="imagem_url">Imagem atual ou caminho manual</label>
                    <input class="form-control" id="imagem_url" name="imagem_url" type="text" value="<?= escapar($produtoEdicao['imagem_url'] ?? '') ?>" placeholder="../assets/images/menu/combo 2.jpg">
                  </div>

                  <div class="form-group form-group--wide">
                    <label for="imagem_arquivo">Enviar nova imagem</label>
                    <input class="form-control" id="imagem_arquivo" name="imagem_arquivo" type="file" accept="image/png,image/jpeg,image/webp">
                    <p class="form-hint">A imagem enviada fica salva em src/assets/images/menu.</p>
                  </div>

                  <?php if ($produtoEdicao): ?>
                    <div class="form-group form-group--wide">
                      <label>Imagem atual</label>
                      <span class="image-preview">
                        <img src="<?= escapar(imagem_produto($produtoEdicao)) ?>" alt="Imagem atual">
                      </span>
                    </div>
                  <?php endif; ?>

                  <div class="form-group form-group--wide">
                    <label for="ingredientes">Ingredientes</label>
                    <textarea class="form-control" id="ingredientes" name="ingredientes" rows="3"><?= escapar($produtoEdicao['ingredientes'] ?? '') ?></textarea>
                  </div>

                  <div class="form-group">
                    <label for="opcoes">Itens inclusos</label>
                    <input class="form-control" id="opcoes" name="opcoes" type="text" value="<?= escapar($produtoEdicao['opcoes'] ?? '') ?>">
                  </div>

                  <div class="form-group">
                    <label for="opcoes_personalizacao">Opções de bebida/gelo</label>
                    <textarea class="form-control" id="opcoes_personalizacao" name="opcoes_personalizacao" rows="2"><?= escapar($produtoEdicao['opcoes_personalizacao'] ?? '') ?></textarea>
                    <p class="form-hint">Ex: Bebida: Coca-Cola, Guaraná, Suco de uva | Gelo: Com gelo, Sem gelo</p>
                  </div>

                  <div class="form-group">
                    <label for="tags">Tags</label>
                    <input class="form-control" id="tags" name="tags" type="text" value="<?= escapar($produtoEdicao['tags'] ?? '') ?>">
                  </div>

                  <div class="form-group">
                    <label for="alergenos">Alérgenos</label>
                    <input class="form-control" id="alergenos" name="alergenos" type="text" value="<?= escapar($produtoEdicao['alergenos'] ?? '') ?>">
                  </div>

                  <div class="form-group">
                    <label for="porcao">Porção</label>
                    <input class="form-control" id="porcao" name="porcao" type="text" value="<?= escapar($produtoEdicao['porcao'] ?? '') ?>">
                  </div>

                  <div class="form-group">
                    <label for="calorias">Calorias</label>
                    <input class="form-control" id="calorias" name="calorias" type="number" min="0" value="<?= escapar($produtoEdicao['calorias'] ?? '') ?>">
                  </div>

                  <label class="check-row form-group--wide">
                    <input type="checkbox" name="destaque" <?= !empty($produtoEdicao['destaque']) ? 'checked' : '' ?>>
                    Destacar produto
                  </label>
                </div>

                <div class="crud-panel__footer">
                  <a class="button button--ghost" href="./produtos.php#produto-form">Limpar</a>
                  <button class="button button--primary" type="submit">
                    <i class="fa-solid fa-check"></i>
                    Salvar item
                  </button>
                </div>
              </form>
            </aside>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
