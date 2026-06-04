# GastroTech Admin

Sistema de autoatendimento e painel administrativo para lanchonete, com cardapio visual, detalhes dos alimentos, fluxo da cozinha e acompanhamento financeiro.

## Ideia do projeto

O GastroTech resolve um problema simples e real: a lanchonete precisa alterar produtos, precos, categorias, ingredientes, imagens e disponibilidade do cardapio sem mexer diretamente no totem. O cliente usa o totem para escolher o lanche; o administrador usa este painel para manter a operacao organizada.

## Identidade visual

- Nome do sistema: `GastroTech`.
- Logo padrao: `src/assets/brand/gastrotech-logo.jpg`.
- Proposta: mostrar que o pedido "flui" do totem para a cozinha, financeiro e administracao.
- Estilo visual: lanchonete moderna, tons quentes, cards escuros, imagens dos produtos e detalhes claros para o cliente.

## Para que serve

- Cadastrar e consultar itens do cardapio.
- Exibir imagem, ingredientes, alergenos, porcao, calorias e etiquetas dos alimentos.
- Organizar categorias como combos, bebidas, sobremesas e adicionais.
- Controlar estoque/disponibilidade dos itens exibidos no totem.
- Registrar pedidos feitos pelo cliente no autoatendimento.
- Permitir que o cliente acompanhe a situacao do pedido pelo codigo de retirada.
- Acompanhar a fila da cozinha e atualizar o status do pedido.
- Acompanhar faturamento, formas de pagamento e caixa do dia.
- Configurar no dashboard o tempo de espera exibido ao cliente.
- Administrar usuarios com acesso ao painel.
- Preparar a base para conectar PHP e MySQL.

## Como explicar na apresentacao

> Este projeto simula um fluxo real de lanchonete: o cliente faz o pedido no totem, a cozinha acompanha a fila de preparo e o administrador controla o cardapio, categorias, estoque e usuarios do sistema. Se um lanche acaba, ele deixa de aparecer no totem. Se o preco muda, o painel atualiza a informacao. Quando o cliente finaliza o pedido, ele recebe um codigo de retirada e a cozinha consegue avancar o status.

Fluxo principal:

1. Cliente escolhe os produtos no totem.
2. Totem gera um pedido com codigo de retirada.
3. Cliente acompanha a situacao do pedido pelo codigo.
4. Cozinha recebe o pedido e atualiza o status: recebido, em preparo, pronto e retirado.
5. Administrador acompanha indicadores no dashboard.
6. Administrador acompanha financeiro, caixa, produtos, categorias, estoque e acessos.

## Tecnologias usadas

- HTML5
- CSS3
- JavaScript
- Font Awesome para icones
- MySQL/MariaDB como modelo de banco em `database/database.sql`

## Estrutura do projeto

```text
Sistema Administrativo/
|-- index.html
|-- index.php
|-- README.md
|-- GUIA_DO_CODIGO.md
|-- database/
|   `-- database.sql
|-- src/
|   |-- assets/
|   |   |-- brand/
|   |   `-- images/
|   |       `-- menu/
|   |-- styles/
|   |-- scripts/
|   `-- pages/
`-- .vscode/
```

- `index.html` e `index.php`: entrada principal do projeto, redirecionando para o totem em PHP.
- `database/database.sql`: modelo inicial do banco de dados.
- `src/assets/brand`: logo e arquivos da identidade visual.
- `src/assets/images/menu`: imagens dos alimentos exibidos no totem.
- `src/styles`: arquivos de estilo.
- `src/scripts`: regras e interacoes em JavaScript.
- `src/pages`: telas HTML do sistema.

## Onde alterar imagens e logo

Pelo sistema, sem mexer no codigo:

1. Entre no painel administrativo.
2. Abra **Cardapio** para cadastrar ou editar um produto.
3. No campo **Imagem**, use **Enviar imagem** para escolher a foto do lanche.
4. Abra o **Dashboard** e clique na engrenagem de configuracoes.
5. Em **Logo da loja**, use **Enviar logo** para trocar a logo.

No codigo, caso queira estudar:

- `src/scripts/store.js`: guarda os dados iniciais do sistema.
- Em `settings`, ficam nome, frase, tempo e logo padrao da loja.
- Em cada produto, o campo `image` guarda a imagem que aparece no cardapio.
- `src/assets/brand`: pasta para logos.
- `src/assets/images/menu`: pasta para imagens dos alimentos.

Imagens oficiais usadas agora:

- `src/assets/brand/gastrotech-logo.jpg`
- `src/assets/images/menu/combo 2.jpg`
- `src/assets/images/menu/x-bacon-artesanal.jpg`
- `src/assets/images/menu/batata-suprema.jpg`
- `src/assets/images/menu/bebida-gelada.jpg`
- `src/assets/images/menu/milkshake-chocolate.jpg`
- `src/assets/images/menu/molho-extra.jpg`

Depois que voce muda pelo painel, o caminho fica salvo no MySQL. Para entregar o projeto, leve a pasta inteira com `src/assets`, assim o professor abre e as imagens continuam aparecendo.

## Funcionalidades prontas

- Login obrigatÃ³rio para o painel administrativo.
- Cadastro, ediÃ§Ã£o e exclusÃ£o de itens do cardÃ¡pio.
- CardÃ¡pio enriquecido com imagem, ingredientes, alÃ©rgenos, porÃ§Ã£o, calorias e tags.
- Troca ou cadastro de imagem do produto direto pelo painel, sem editar cÃ³digo.
- Troca da logo, nome da loja e frase do totem direto pelo dashboard.
- Cadastro, ediÃ§Ã£o e exclusÃ£o de categorias.
- Cadastro, ediÃ§Ã£o, bloqueio e exclusÃ£o de administradores.
- Totem do cliente com categorias, carrinho, pagamento simulado e cÃ³digo de retirada.
- Escolha de bebida no totem: refrigerante ou suco, com gelo ou sem gelo.
- Consulta de situacao do pedido no totem pelo codigo de retirada.
- Pedidos do totem salvos e exibidos no dashboard.
- Tela da cozinha para acompanhar e avancar o status dos pedidos.
- NotificaÃ§Ã£o visual na cozinha quando um novo pedido chega.
- Tela financeira com faturamento, caixa, entradas, saidas e vendas por pagamento.
- Configuracao de tempo medio de espera pelo painel administrativo.
- Estoque atualizado quando um pedido Ã© finalizado.
- ExportaÃ§Ã£o CSV de cardÃ¡pio, categorias, administradores e financeiro.

Os dados principais ficam salvos no MySQL. O arquivo `database/database.sql` cria a estrutura inicial para importar no phpMyAdmin.

## Melhorias visuais

- Animacao de entrada das telas.
- Contadores animados no dashboard.
- Linhas de tabela com entrada suave.
- Feedback por notificacoes na tela.
- Efeito visual nos botoes.
- Padrao unico de icones e componentes.
- Logo propria do GastroTech.
- Cards de produtos com imagem, ingredientes e detalhes nutricionais simples.

## Telas

- Login
- Dashboard do totem
- Financeiro e caixa
- Produtos do cardapio
- Categorias
- Adicionar categoria
- Editar categoria
- Confirmar exclusao
- Administradores
- Totem do cliente com carrinho
- Cozinha/atendimento com fila de pedidos

## Acesso de teste

```text
Usuario: admin
Senha: 123
```

## Acesso administrativo

O totem do cliente fica aberto para simular o autoatendimento. Ja o painel administrativo exige login: ao tentar abrir dashboard, cozinha, financeiro, cardapio, categorias ou administradores sem sessao ativa, o sistema redireciona para `login.php`.

O botao **Sair** encerra a sessao e volta para a tela de login.

## Como abrir

Com PHP, abra pelo servidor local:

```text
C:\xampp\php\php.exe -S 127.0.0.1:5520 -t .
```

Depois acesse:

```text
http://127.0.0.1:5520/src/pages/totem.php
```

Tambem e possivel colocar a pasta `Sistema Administrativo` dentro do `htdocs` do XAMPP e acessar pelo navegador com Apache e MySQL ligados.

## Como abrir no VS Code

1. Abra o VS Code.
2. Va em `File > Open Folder`.
3. Selecione a pasta `Sistema Administrativo` que tem `index.html`, `src` e `database` dentro.
4. Va em `Terminal > Run Task` e escolha `Iniciar servidor PHP`.
5. Abra `http://127.0.0.1:5520/src/pages/totem.php`.

## Guia para estudar

O arquivo `GUIA_DO_CODIGO.md` explica, de forma mais simples, onde ficam as telas, os scripts, as imagens, a logo e os nomes internos usados no JavaScript.

## Banco de dados

O arquivo `database/database.sql` contem uma estrutura inicial com:

- administradores
- configuracoes_sistema
- caixas
- movimentos_caixa
- categorias
- produtos
- pedidos
- itens_pedido

## Totem do cliente

A tela `src/pages/totem.php` simula a parte que o cliente usaria no autoatendimento:

- filtro por categorias;
- cards de lanches e bebidas;
- carrinho com quantidade;
- forma de pagamento simulada;
- confirmacao com codigo de retirada;
- acompanhamento da situacao do pedido com barra de progresso.

Quando o pedido e confirmado, ele aparece no dashboard e na tela `src/pages/cozinha.php`. No proprio totem, o cliente pode tocar em **Acompanhar pedido** e consultar o status usando o codigo de retirada.

O tempo de espera mostrado ao cliente e configurado no dashboard, pelo botao de engrenagem ou pelo botao **Ajustar** no card de tempo medio.

## Cozinha/atendimento

A tela `src/pages/cozinha.php` representa a area interna da lanchonete:

- filtra pedidos por status;
- mostra codigo, cliente, itens e total;
- avanca o pedido de recebido para em preparo, pronto e retirado;
- ajuda a explicar a utilidade pratica do sistema durante a apresentacao.

## Financeiro

A tela `src/pages/financeiro.php` representa o controle de caixa:

- mostra faturamento do dia usando os pedidos do totem;
- separa vendas por Pix, cartao e dinheiro;
- calcula o saldo atual do caixa com saldo inicial, entradas e saidas;
- permite registrar movimentos manuais, como reforco de troco ou compra de embalagens;
- exporta os dados financeiros em CSV.

Para usar no phpMyAdmin:

1. Abra o phpMyAdmin.
2. Clique em Importar.
3. Selecione o arquivo `database/database.sql`.
4. Execute a importacao.

## Primeira versao em PHP

A pasta `app` tem os arquivos de apoio do PHP:

- `app/conexao.php`: conecta no MySQL.
- `app/auth.php`: controla login e sessao.
- `app/funcoes.php`: funcoes pequenas como escapar texto e formatar dinheiro.
- `app/produtos_repositorio.php`: busca, salva e exclui produtos no banco.

As primeiras telas PHP ficam em `src/pages`:

- `src/pages/login.php`
- `src/pages/dashboard.php`
- `src/pages/produtos.php`
- `src/pages/cozinha.php`
- `src/pages/totem.php`

Para abrir a versao PHP:

1. Ligue Apache e MySQL no XAMPP.
2. Importe `database/database.sql` no phpMyAdmin.
3. Acesse `http://localhost/Sistema Administrativo/src/pages/login.php`, se a pasta estiver no `htdocs`.
4. Acesso de teste: `admin` / `123`.

Se usar o servidor PHP embutido:

```text
C:\xampp\php\php.exe -S 127.0.0.1:5520 -t .
```

Depois abra:

```text
http://127.0.0.1:5520/src/pages/login.php
```

## Versao PHP atual

A versao principal agora esta em PHP com MySQL. Login, dashboard, produtos, categorias, financeiro, usuarios, configuracoes, cozinha e totem ja usam os dados do banco. O JavaScript ficou apenas para a interacao do carrinho no `totem.php`.
