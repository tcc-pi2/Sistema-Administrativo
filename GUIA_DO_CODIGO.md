# Guia do codigo

Este arquivo e para voce se localizar sem precisar decorar tudo.

## Pastas principais

- `index.html`: primeira pagina. Ela manda o usuario para o totem.
- `src/pages`: telas do sistema.
- `src/styles`: arquivos de aparencia.
- `src/scripts`: regras em JavaScript.
- `src/assets/brand`: logo da loja.
- `src/assets/images/menu`: fotos dos alimentos.
- `database/database.sql`: modelo do banco de dados.

## Telas

- `src/pages/totem.html`: tela do cliente fazer o pedido.
- `src/pages/login.html`: entrada do painel administrativo.
- `src/pages/dashboard.html`: resumo geral do sistema.
- `src/pages/produtos.html`: onde cadastra e edita o cardapio.
- `src/pages/cozinha.html`: fila dos pedidos recebidos.
- `src/pages/financeiro.html`: caixa e faturamento.

## Arquivos JavaScript

- `src/scripts/store.js`: guarda os dados do sistema no navegador.
- `src/scripts/totem.js`: controla carrinho, pedidos e escolhas do cliente.
- `src/scripts/admin.js`: controla dashboard, produtos, categorias, usuarios, logo e caixa.
- `src/scripts/cozinha.js`: mostra os pedidos e avisa quando chega pedido novo.
- `src/scripts/login.js`: controla o login do painel.

Os arquivos principais tambem tem comentarios no proprio codigo. Use `Ctrl + F` no VS Code e procure por palavras como `imagem`, `logo`, `cardapio`, `cozinha` ou `pedido`.

## Onde trocar imagens

Pelo sistema:

1. Entre no painel.
2. Abra `Produtos`.
3. Clique para editar um item.
4. Escolha uma imagem no campo de arquivo.
5. Clique em `Salvar item`.

No codigo:

- As imagens fixas ficam em `src/assets/images/menu`.
- O caminho da imagem de cada produto fica em `src/scripts/store.js`, no campo `image`.

Exemplo:

```js
image: "../assets/images/menu/combo-smash.jpg"
```

## Onde trocar a logo

Pelo sistema:

1. Entre no dashboard.
2. Clique na engrenagem de configuracao.
3. Escolha uma imagem para a logo.
4. Clique em `Salvar configuracoes`.

No codigo:

- A logo padrao fica em `src/assets/brand/gastrotech-logo.jpg`.
- O caminho padrao fica em `src/scripts/store.js`, no campo `storeLogo`.

## Palavras que aparecem em ingles

Alguns nomes ficaram em ingles porque eles sao os nomes internos dos dados. Se trocar tudo de uma vez, pode quebrar o sistema. Para estudar, leia assim:

- `products`: produtos
- `categories`: categorias
- `orders`: pedidos
- `items`: itens do pedido
- `settings`: configuracoes da loja
- `storeName`: nome da loja
- `storeLogo`: logo da loja
- `price`: preco
- `stock`: estoque
- `status`: situacao
- `image`: imagem
- `ingredients`: ingredientes
- `customizations`: escolhas extras, como bebida e gelo
- `localStorage`: lugar onde o navegador salva os dados

As funcoes principais do `TotemStore` foram deixadas em portugues, por exemplo:

- `carregarDados()`: busca os dados salvos.
- `salvarProdutoDados()`: salva produto novo ou editado.
- `criarPedido()`: cria pedido vindo do totem.
- `atualizarStatusPedido()`: muda o status na cozinha.
- `aplicarMarca()`: aplica nome e logo nas telas.

## Fluxo do pedido

1. O cliente escolhe os itens no totem.
2. O JavaScript monta o carrinho.
3. Ao finalizar, o pedido e salvo no `localStorage`.
4. A cozinha le esses pedidos.
5. Quando o status muda na cozinha, o totem consegue acompanhar pelo codigo.

## Imagens usadas agora

- `src/assets/brand/gastrotech-logo.jpg`
- `src/assets/images/menu/combo-smash.jpg`
- `src/assets/images/menu/x-bacon-artesanal.jpg`
- `src/assets/images/menu/batata-suprema.jpg`
- `src/assets/images/menu/bebida-gelada.jpg`
- `src/assets/images/menu/milkshake-chocolate.jpg`
- `src/assets/images/menu/molho-extra.jpg`

## Observacao importante

As imagens enviadas pelo painel ficam salvas no navegador. Para entregar o projeto em outro computador, o mais seguro e deixar as imagens principais dentro da pasta `src/assets/images/menu`, como esta agora.
