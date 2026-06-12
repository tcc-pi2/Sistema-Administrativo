# Guia do código

Esse arquivo é só para lembrar onde cada coisa fica.

## Pastas

- `app`: arquivos PHP de apoio.
- `src/pages`: telas do sistema.
- `src/styles`: CSS.
- `src/assets`: imagens e logo.
- `database`: arquivo do banco.
- `legacy`: versão antiga do projeto, guardada como backup.

## Telas principais

- `src/pages/totem.php`: cardápio e carrinho do cliente.
- `src/pages/login.php`: login do painel.
- `src/pages/dashboard.php`: resumo do sistema.
- `src/pages/produtos.php`: lista, cadastro e edição de produtos.
- `src/pages/categorias.php`: categorias do cardápio.
- `src/pages/cozinha.php`: pedidos recebidos.
- `src/pages/financeiro.php`: caixa.
- `src/pages/administradores.php`: usuários do painel.
- `src/pages/configuracoes.php`: nome da loja, logo e tempo médio.

## Arquivos do PHP

- `app/conexao.php`: conecta no MySQL.
- `app/auth.php`: login, logout e sessão.
- `app/funcoes.php`: funções pequenas usadas nas telas.
- `app/produtos_repositorio.php`: produtos e categorias.
- `app/pedidos_repositorio.php`: pedidos e itens do pedido.
- `app/financeiro_repositorio.php`: caixa e movimentos.
- `app/configuracoes_repositorio.php`: logo, nome e frase da loja.
- `app/administradores_repositorio.php`: usuários do painel.

## Onde trocar imagens

Pelo painel:

1. Entre no painel.
2. Abra **Produtos**.
3. Edite o produto.
4. Use **Enviar nova imagem**.
5. Salve.

No código, as imagens ficam em:

```text
src/assets/images/menu
```

O caminho salvo no banco fica no campo `imagem_url` da tabela `produtos`.

## Onde trocar a logo

Pelo painel:

1. Entre no painel.
2. Abra **Configurações**.
3. Escolha a logo nova.
4. Salve.

No código, a logo padrão fica em:

```text
src/assets/brand/gastrotech-logo.jpg
```

## Como o pedido funciona

1. O cliente escolhe os itens no totem.
2. O carrinho fica montado na tela.
3. Ao finalizar, o PHP salva o pedido no MySQL.
4. A cozinha lê os pedidos do banco.
5. Quando a cozinha muda o status, o cliente consegue acompanhar pelo código.

## Imagens usadas

- `src/assets/brand/gastrotech-logo.jpg`
- `src/assets/images/menu/combo 2.jpg`
- `src/assets/images/menu/x-bacon-artesanal.jpg`
- `src/assets/images/menu/batata-suprema.jpg`
- `src/assets/images/menu/bebida-gelada.jpg`
- `src/assets/images/menu/milkshake-chocolate.jpg`
- `src/assets/images/menu/molho-extra.jpg`

## Observações

- Para o projeto funcionar, o MySQL precisa estar ligado.
- O banco usado está em `database/database.sql`.
- O login de teste é `admin / 123`.
- O JavaScript ficou dentro do `totem.php` para controlar o carrinho.
- A pasta `legacy` não é a versão principal, é só backup.
