# Documentação técnica - GastroTech

Projeto feito em PHP puro com MySQL para uma lanchonete.

## Objetivo

O sistema permite montar pedidos pelo totem e controlar esses pedidos pelo painel administrativo. Também tem tela para cozinha, cadastro de produtos e uma parte simples de financeiro.

## Tecnologias

- PHP
- MySQL/MariaDB
- HTML
- CSS
- JavaScript
- Font Awesome
- XAMPP

## Pastas

```text
app/
database/
docs/
legacy/
src/
```

- `app`: conexão, login e funções usadas pelas telas.
- `database`: arquivo SQL do banco.
- `src/pages`: páginas PHP.
- `src/styles`: CSS.
- `src/assets`: imagens e logo.
- `legacy`: arquivos antigos guardados como backup.

## Banco

Banco usado: `gastrotech_admin`.

Tabelas principais:

- `administradores`
- `configuracoes_sistema`
- `categorias`
- `produtos`
- `totens`
- `pedidos`
- `itens_pedido`
- `caixas`
- `movimentos_caixa`

O arquivo para importar fica em:

```text
database/database.sql
```

## Login

Arquivos:

- `src/pages/login.php`
- `app/auth.php`

Credenciais de teste:

```text
Usuário: admin
Senha: 123
```

O painel chama `exigir_login()` nas telas privadas. Se não tiver sessão ativa, volta para o login.

## Fluxo do pedido

1. O cliente abre o totem.
2. O totem mostra os produtos ativos do banco.
3. O cliente monta o carrinho.
4. O pedido é salvo em `pedidos` e `itens_pedido`.
5. A cozinha vê o pedido.
6. A cozinha muda o status até a retirada.
7. O financeiro soma os pedidos pagos.

## Telas

- `totem.php`: cardápio, carrinho e acompanhamento do pedido.
- `dashboard.php`: resumo geral.
- `produtos.php`: cadastro e edição dos produtos.
- `categorias.php`: cadastro de categorias.
- `cozinha.php`: fila de pedidos.
- `financeiro.php`: caixa e vendas.
- `administradores.php`: usuários do painel.
- `configuracoes.php`: logo, nome da loja e tempo médio.

## CRUD

O projeto faz cadastro, listagem, edição e exclusão de:

- produtos;
- categorias;
- usuários do painel.

Pedidos são criados pelo totem e atualizados pela cozinha.

## Segurança básica

- login com sessão PHP;
- senha salva com hash;
- consultas usando PDO;
- uso de `prepare()` e `execute()`;
- saída HTML tratada com `escapar()`.

## Como rodar

1. Abra o XAMPP.
2. Ligue o MySQL.
3. Importe `database/database.sql`.
4. Abra a pasta no VS Code.
5. Rode:

```text
C:\xampp\php\php.exe -S 127.0.0.1:5520 -t .
```

6. Acesse:

```text
http://127.0.0.1:5520/src/pages/totem.php
```

Painel:

```text
http://127.0.0.1:5520/src/pages/login.php
```

## Testes feitos

- login com `admin / 123`;
- abertura do totem;
- cadastro e edição de produto;
- troca de imagem;
- envio de pedido;
- leitura do pedido na cozinha;
- alteração de status;
- consulta no financeiro.

## Observação

A pasta `legacy` não é a versão principal. Ela ficou no projeto só como backup da versão anterior.
