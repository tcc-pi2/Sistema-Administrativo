# Guia do codigo

Este arquivo e para voce se localizar sem precisar decorar tudo.

## Pastas principais

- `index.html` e `index.php`: primeira pagina. Elas mandam o usuario para o totem em PHP.
- `src/pages`: telas do sistema.
- `src/styles`: arquivos de aparencia.
- `src/scripts`: regras em JavaScript.
- `src/assets/brand`: logo da loja.
- `src/assets/images/menu`: fotos dos alimentos.
- `database/database.sql`: modelo do banco de dados.

## Telas

- `src/pages/totem.php`: tela do cliente fazer o pedido.
- `src/pages/login.php`: entrada do painel administrativo.
- `src/pages/dashboard.php`: resumo geral do sistema.
- `src/pages/produtos.php`: onde cadastra e edita o cardapio.
- `src/pages/categorias.php`: onde cadastra e organiza categorias.
- `src/pages/financeiro.php`: caixa e faturamento.
- `src/pages/cozinha.php`: fila dos pedidos recebidos.
- `src/pages/administradores.php`: usuarios que acessam o painel.
- `src/pages/configuracoes.php`: nome da loja, logo e tempo medio.

As telas antigas em HTML ficaram guardadas em `src/pages/versao-antiga-html`. Elas servem como backup, mas a versao principal do projeto agora e a `.php`.

## Arquivos PHP principais

- `app/conexao.php`: conecta no MySQL.
- `app/auth.php`: controla login e sessao.
- `app/funcoes.php`: funcoes pequenas usadas em varias telas.
- `app/produtos_repositorio.php`: produtos e categorias.
- `app/pedidos_repositorio.php`: pedidos e cozinha.
- `app/financeiro_repositorio.php`: caixa e movimentos financeiros.
- `app/configuracoes_repositorio.php`: nome, logo e tempo medio.
- `app/administradores_repositorio.php`: usuarios do painel.

## Arquivos JavaScript antigos

Esses arquivos ficaram guardados em `src/scripts/versao-antiga-js`.
Eles sao da versao antiga em HTML. A versao principal agora usa PHP.
No `totem.php` ainda existe um pedaco de JavaScript dentro da propria pagina para controlar o carrinho antes de enviar o pedido.

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
- O caminho da imagem de cada produto fica no banco, na tabela `produtos`, campo `imagem_url`.

Exemplo:

```sql
imagem_url = "../assets/images/menu/combo 2.jpg"
```

## Onde trocar a logo

Pelo sistema:

1. Entre no dashboard.
2. Abra `Configuracoes`.
3. Escolha uma imagem para a logo.
4. Clique em `Salvar configuracoes`.

No codigo:

- A logo padrao fica em `src/assets/brand/gastrotech-logo.jpg`.
- O caminho padrao fica no banco, na tabela `configuracoes_sistema`, chave `logo_loja`.

## Fluxo do pedido

1. O cliente escolhe os itens no totem.
2. O JavaScript do `totem.php` monta o carrinho na tela.
3. Ao finalizar, o PHP salva o pedido no MySQL.
4. A cozinha le esses pedidos do MySQL.
5. Quando o status muda na cozinha, o totem consegue acompanhar pelo codigo.

## Imagens usadas agora

- `src/assets/brand/gastrotech-logo.jpg`
- `src/assets/images/menu/combo 2.jpg`
- `src/assets/images/menu/x-bacon-artesanal.jpg`
- `src/assets/images/menu/batata-suprema.jpg`
- `src/assets/images/menu/bebida-gelada.jpg`
- `src/assets/images/menu/milkshake-chocolate.jpg`
- `src/assets/images/menu/molho-extra.jpg`

## Observacao importante

As imagens enviadas pelo painel ficam salvas nas pastas do projeto. Para entregar em outro computador, leve a pasta inteira junto com `src/assets`.

## Parte PHP

- `app/conexao.php`: conexao com MySQL.
- `app/auth.php`: login com sessao.
- `app/funcoes.php`: funcoes pequenas de apoio.
- `app/produtos_repositorio.php`: produtos no banco.
- `app/configuracoes_repositorio.php`: logo, nome e tempo medio.
- `app/financeiro_repositorio.php`: caixa e financeiro.
- `app/administradores_repositorio.php`: usuarios do painel.
- `src/pages/login.php`: login PHP.
- `src/pages/dashboard.php`: resumo vindo do banco.
- `src/pages/produtos.php`: cadastro/lista de produtos no MySQL.
- `src/pages/categorias.php`: categorias no MySQL.
- `src/pages/financeiro.php`: financeiro no MySQL.
- `src/pages/administradores.php`: usuarios no MySQL.
- `src/pages/configuracoes.php`: configuracoes no MySQL.
- `src/pages/cozinha.php`: fila da cozinha lendo pedidos do MySQL.
- `src/pages/totem.php`: vitrine lendo produtos do MySQL.

Para essa parte funcionar, precisa ligar Apache/MySQL no XAMPP e importar `database/database.sql`.

No PHP, o JavaScript ainda aparece no `totem.php`: ele controla o carrinho na tela. Quando finaliza, o PHP salva o pedido no banco.
