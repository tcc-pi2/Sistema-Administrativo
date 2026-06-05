# Documentacao Tecnica - GastroTech Admin

Projeto Final de Semestre - PHP Puro + MySQL, sem framework.

Data de entrega: 05/06/2026

## 1. Visao geral

O GastroTech Admin e um sistema de autoatendimento e administracao para uma lanchonete. O cliente faz pedidos no totem, a cozinha acompanha a fila de preparo e o administrador gerencia cardapio, categorias, configuracoes, caixa, pedidos e acessos.

O projeto integra:

- Frontend em HTML, CSS e JavaScript.
- Backend em PHP puro.
- Banco de dados MySQL.
- Conexao segura com PDO.
- Persistencia real de dados no banco.

## 2. Tecnologias utilizadas

- PHP puro 8.x, sem Laravel, CodeIgniter, Symfony ou outro framework.
- MySQL/MariaDB no XAMPP.
- PDO para conexao e comandos preparados.
- HTML5, CSS3 e JavaScript.
- Font Awesome para icones.

## 3. Estrutura do projeto

```text
GastroTech/
|-- app/
|   |-- conexao.php
|   |-- auth.php
|   |-- funcoes.php
|   |-- produtos_repositorio.php
|   |-- pedidos_repositorio.php
|   |-- financeiro_repositorio.php
|   |-- configuracoes_repositorio.php
|   `-- administradores_repositorio.php
|-- database/
|   `-- database.sql
|-- docs/
|   |-- documentacao_tecnica_gastrotech.md
|   `-- documentacao_tecnica_gastrotech.pdf
|-- legacy/
|   |-- html/
|   |-- js/
|   `-- images/
|-- src/
|   |-- assets/
|   |-- pages/
|   `-- styles/
|-- index.html
|-- index.php
|-- README.md
`-- GUIA_DO_CODIGO.md
```

## 4. Banco de dados

O banco principal se chama `gastrotech_admin`. O arquivo de entrega fica em `database/database.sql`.

Tabelas principais:

- `administradores`: usuario administrador, senha criptografada e permissao.
- `configuracoes_sistema`: nome da loja, frase do totem, logo e tempo medio.
- `categorias`: organizacao do cardapio.
- `produtos`: itens do cardapio, preco, estoque, imagem e detalhes.
- `totens`: totens disponiveis.
- `pedidos`: pedidos criados pelo cliente.
- `itens_pedido`: produtos dentro de cada pedido.
- `caixas`: caixa aberto/fechado.
- `movimentos_caixa`: entradas e saidas manuais.

Relacionamentos:

- `produtos.categoria_id` referencia `categorias.id`.
- `pedidos.totem_id` referencia `totens.id`.
- `itens_pedido.pedido_id` referencia `pedidos.id`.
- `itens_pedido.produto_id` referencia `produtos.id`.
- `movimentos_caixa.caixa_id` referencia `caixas.id`.

## 5. Conexao com PDO

A conexao reutilizavel fica em `app/conexao.php`.

O sistema usa:

- `new PDO(...)` para conectar ao MySQL.
- `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` para tratamento de erros.
- `prepare()` e `execute()` nos comandos de leitura e gravacao.
- Parametros nomeados ou posicionais para evitar SQL Injection.

## 6. Autenticacao do administrador

Arquivos envolvidos:

- `src/pages/login.php`: tela de login.
- `app/auth.php`: sessao, validacao, logout e protecao de paginas.
- `database/database.sql`: administrador inicial com senha criptografada.

Fluxo:

1. O usuario acessa `login.php`.
2. O PHP recebe `login` e `senha` via POST.
3. O sistema busca o administrador ativo no banco.
4. A senha e conferida com `password_verify()`.
5. Se estiver correta, os dados do admin ficam na sessao PHP.
6. Paginas privadas chamam `exigir_login()`.
7. O logout limpa a sessao e volta para o login.

Credenciais de entrega:

```text
Usuario: admin
Senha: 123
```

## 7. Fluxo do sistema

1. O cliente abre o totem em `src/pages/totem.php`.
2. O totem busca categorias e produtos ativos no MySQL.
3. O cliente monta o carrinho e finaliza o pedido.
4. O PHP cria registro em `pedidos` e `itens_pedido`.
5. O estoque do produto e atualizado.
6. A cozinha visualiza o pedido em `src/pages/cozinha.php`.
7. A cozinha avanca o status: Recebido, Em preparo, Pronto e Retirado.
8. O financeiro soma vendas pagas e movimentos de caixa.
9. O administrador edita produtos, categorias, configuracoes e usuarios.

## 8. CRUD implementado

| Area | Create | Read | Update | Delete | Arquivos |
|---|---:|---:|---:|---:|---|
| Produtos | Sim | Sim | Sim | Sim | `src/pages/produtos.php`, `app/produtos_repositorio.php` |
| Categorias | Sim | Sim | Sim | Sim | `src/pages/categorias.php`, `app/produtos_repositorio.php` |
| Administrador/usuarios | Sim | Sim | Sim | Sim | `src/pages/administradores.php`, `app/administradores_repositorio.php` |
| Pedidos | Sim | Sim | Sim | Nao essencial | `src/pages/totem.php`, `src/pages/cozinha.php`, `app/pedidos_repositorio.php` |
| Financeiro | Sim | Sim | Sim por filtros | Nao essencial | `src/pages/financeiro.php`, `app/financeiro_repositorio.php` |
| Configuracoes | Sim | Sim | Sim | Nao essencial | `src/pages/configuracoes.php`, `app/configuracoes_repositorio.php` |

## 9. Integracao frontend/backend

As telas em `src/pages` exibem dados reais do banco. Os formularios usam POST/GET e sao processados no proprio PHP ou em repositorios dentro de `app`.

Exemplos:

- Cadastro de produto: formulario em `produtos.php` chama `salvar_produto()`.
- Cadastro de categoria: formulario em `categorias.php` chama `salvar_categoria()`.
- Pedido do totem: carrinho vira JSON e `totem.php` chama `criar_pedido_banco()`.
- Cozinha: POST em `cozinha.php` chama `avancar_status_pedido()`.
- Financeiro: POST em `financeiro.php` chama `registrar_movimento_caixa()`.

## 10. Seguranca basica

- Paginas privadas usam sessao PHP e `exigir_login()`.
- Logout usa `session_destroy()`.
- Senha do administrador e armazenada com `password_hash()`.
- Login usa `password_verify()`.
- SQL usa PDO, `prepare()` e `execute()`.
- Campos vindos do usuario sao tratados antes de salvar.
- Saida HTML usa funcao `escapar()` para evitar exibicao insegura.

## 11. Como executar

1. Abrir o XAMPP.
2. Ligar Apache e MySQL.
3. Importar `database/database.sql` no phpMyAdmin.
4. Abrir o projeto no VS Code.
5. Rodar o servidor PHP:

```text
C:\xampp\php\php.exe -S 127.0.0.1:5520 -t .
```

6. Acessar:

```text
http://127.0.0.1:5520/src/pages/totem.php
http://127.0.0.1:5520/src/pages/login.php
```

## 12. Checklist dos requisitos

| Requisito | Status |
|---|---|
| PHP puro, sem framework | Atendido |
| MySQL | Atendido |
| PDO | Atendido |
| Arquivo de conexao reutilizavel | Atendido |
| Login do administrador | Atendido |
| Sessao PHP | Atendido |
| Logout | Atendido |
| Protecao de paginas privadas | Atendido |
| CRUD completo nas tabelas principais | Atendido |
| Dados dinamicos no frontend | Atendido |
| Relacionamentos e JOIN | Atendido |
| Senha criptografada | Atendido |
| Banco .sql de entrega | Atendido |

## 13. Testes finais realizados

- Login com `admin` e senha `123`.
- Protecao de dashboard sem sessao.
- Cadastro, leitura, edicao e exclusao de categorias.
- Cadastro, leitura, edicao e exclusao de produtos.
- Criacao de pedido pelo totem.
- Leitura de pedido na cozinha.
- Atualizacao de status do pedido.
- Registro de movimento financeiro.
- Importacao do SQL em banco temporario para validar estrutura.

## 14. Observacao sobre arquivos antigos

A pasta `legacy` guarda a versao antiga em HTML/JS apenas como backup e consulta. A versao oficial para entrega e a versao PHP integrada ao MySQL, localizada em `src/pages` e `app`.
