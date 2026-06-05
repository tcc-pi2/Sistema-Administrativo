from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import LETTER
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import (
    ListFlowable,
    ListItem,
    PageBreak,
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs" / "documentacao_tecnica_gastrotech.pdf"


def stylesheet():
    base = getSampleStyleSheet()
    base.add(
        ParagraphStyle(
            name="DocTitle",
            parent=base["Title"],
            fontName="Helvetica-Bold",
            fontSize=22,
            leading=26,
            textColor=colors.HexColor("#1F3A5F"),
            spaceAfter=12,
            alignment=TA_LEFT,
        )
    )
    base.add(
        ParagraphStyle(
            name="Subtitle",
            parent=base["Normal"],
            fontName="Helvetica",
            fontSize=10,
            leading=14,
            textColor=colors.HexColor("#52606D"),
            spaceAfter=14,
        )
    )
    base.add(
        ParagraphStyle(
            name="SectionTitle",
            parent=base["Heading1"],
            fontName="Helvetica-Bold",
            fontSize=14,
            leading=18,
            textColor=colors.HexColor("#2E74B5"),
            spaceBefore=12,
            spaceAfter=6,
        )
    )
    base.add(
        ParagraphStyle(
            name="SmallTitle",
            parent=base["Heading2"],
            fontName="Helvetica-Bold",
            fontSize=11,
            leading=14,
            textColor=colors.HexColor("#1F4D78"),
            spaceBefore=8,
            spaceAfter=4,
        )
    )
    base.add(
        ParagraphStyle(
            name="Body",
            parent=base["BodyText"],
            fontName="Helvetica",
            fontSize=9.5,
            leading=13,
            textColor=colors.HexColor("#1F2933"),
            spaceAfter=6,
        )
    )
    base.add(
        ParagraphStyle(
            name="CodeBlock",
            parent=base["BodyText"],
            fontName="Courier",
            fontSize=8.5,
            leading=11,
            leftIndent=10,
            rightIndent=10,
            backColor=colors.HexColor("#F2F4F7"),
            borderColor=colors.HexColor("#D0D7DE"),
            borderWidth=0.4,
            borderPadding=6,
            spaceBefore=4,
            spaceAfter=8,
        )
    )
    base.add(
        ParagraphStyle(
            name="TableText",
            parent=base["BodyText"],
            fontName="Helvetica",
            fontSize=8.2,
            leading=10.5,
        )
    )
    base.add(
        ParagraphStyle(
            name="TableHeader",
            parent=base["BodyText"],
            fontName="Helvetica-Bold",
            fontSize=8.5,
            leading=10.5,
            textColor=colors.HexColor("#0B2545"),
        )
    )
    return base


def p(text, style):
    return Paragraph(text, style)


def bullets(items, styles):
    return ListFlowable(
        [ListItem(p(item, styles["Body"]), leftIndent=12) for item in items],
        bulletType="bullet",
        start="circle",
        leftIndent=16,
        bulletFontName="Helvetica",
        bulletFontSize=8,
    )


def numbered(items, styles):
    return ListFlowable(
        [ListItem(p(item, styles["Body"]), leftIndent=14) for item in items],
        bulletType="1",
        leftIndent=18,
        bulletFontName="Helvetica",
        bulletFontSize=8.5,
    )


def make_table(rows, widths, styles):
    prepared = []
    for row_index, row in enumerate(rows):
        row_style = styles["TableHeader"] if row_index == 0 else styles["TableText"]
        prepared.append([p(str(cell), row_style) for cell in row])

    table = Table(prepared, colWidths=widths, hAlign="LEFT", repeatRows=1)
    table.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#E8EEF5")),
                ("GRID", (0, 0), (-1, -1), 0.35, colors.HexColor("#C9D2DC")),
                ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
                ("TOPPADDING", (0, 0), (-1, -1), 5),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
                ("LEFTPADDING", (0, 0), (-1, -1), 6),
                ("RIGHTPADDING", (0, 0), (-1, -1), 6),
            ]
        )
    )
    return table


def page_footer(canvas, doc):
    canvas.saveState()
    canvas.setFont("Helvetica", 8)
    canvas.setFillColor(colors.HexColor("#52606D"))
    canvas.drawString(inch, 0.55 * inch, "GastroTech Admin - Documentacao tecnica")
    canvas.drawRightString(7.5 * inch, 0.55 * inch, f"Pagina {doc.page}")
    canvas.restoreState()


def build():
    styles = stylesheet()
    doc = SimpleDocTemplate(
        str(OUT),
        pagesize=LETTER,
        rightMargin=inch,
        leftMargin=inch,
        topMargin=0.82 * inch,
        bottomMargin=0.82 * inch,
        title="Documentacao Tecnica - GastroTech Admin",
        author="GastroTech",
    )

    story = []
    story.append(p("Documentacao Tecnica - GastroTech Admin", styles["DocTitle"]))
    story.append(
        p(
            "Projeto Final de Semestre - PHP Puro + MySQL, sem framework<br/>"
            "Entrega final: 05/06/2026<br/>"
            "Credenciais: usuario <b>admin</b> / senha <b>123</b>",
            styles["Subtitle"],
        )
    )

    story.append(p("1. Visao geral", styles["SectionTitle"]))
    story.append(
        p(
            "O GastroTech Admin e um sistema de autoatendimento e administracao para lanchonete. "
            "O cliente realiza pedidos no totem, a cozinha acompanha a fila de preparo e o administrador "
            "gerencia cardapio, categorias, configuracoes, caixa, pedidos e acessos. A versao de entrega "
            "usa dados reais vindos do MySQL.",
            styles["Body"],
        )
    )
    story.append(
        bullets(
            [
                "Frontend em HTML, CSS e JavaScript.",
                "Backend em PHP puro.",
                "Banco de dados MySQL/MariaDB.",
                "Conexao com PDO e comandos preparados.",
                "Persistencia real de cadastros, pedidos e configuracoes.",
            ],
            styles,
        )
    )

    story.append(p("2. Estrutura do projeto", styles["SectionTitle"]))
    story.append(
        p(
            "A organizacao separa backend, telas, estilos, banco, documentacao e arquivos antigos. "
            "A pasta legacy guarda somente a versao antiga em HTML/JS para consulta.",
            styles["Body"],
        )
    )
    story.append(
        p(
            "app/ conexao, autenticacao e repositorios<br/>"
            "database/ database.sql<br/>"
            "docs/ documentacao tecnica<br/>"
            "legacy/ backup antigo HTML/JS<br/>"
            "src/pages/ telas PHP ativas<br/>"
            "src/styles/ CSS<br/>"
            "src/assets/ imagens e logo",
            styles["CodeBlock"],
        )
    )

    story.append(p("3. Banco de dados e relacionamentos", styles["SectionTitle"]))
    story.append(
        p(
            "O banco principal se chama <b>gastrotech_admin</b>. O arquivo de entrega fica em "
            "<b>database/database.sql</b> e cria tabelas, chaves estrangeiras e dados iniciais.",
            styles["Body"],
        )
    )
    story.append(
        make_table(
            [
                ["Tabela", "Finalidade"],
                ["administradores", "Login do administrador, permissao, status e senha criptografada."],
                ["configuracoes_sistema", "Nome da loja, frase do totem, logo e tempo medio."],
                ["categorias", "Organizacao do cardapio."],
                ["produtos", "Itens do cardapio, preco, estoque, imagem e detalhes."],
                ["pedidos / itens_pedido", "Pedidos do totem e itens vinculados."],
                ["caixas / movimentos_caixa", "Controle financeiro e movimentos manuais."],
                ["totens", "Totens cadastrados para os pedidos."],
            ],
            [1.7 * inch, 4.65 * inch],
            styles,
        )
    )
    story.append(Spacer(1, 6))
    story.append(
        bullets(
            [
                "produtos.categoria_id referencia categorias.id.",
                "pedidos.totem_id referencia totens.id.",
                "itens_pedido.pedido_id referencia pedidos.id.",
                "itens_pedido.produto_id referencia produtos.id.",
                "movimentos_caixa.caixa_id referencia caixas.id.",
            ],
            styles,
        )
    )

    story.append(p("4. Autenticacao", styles["SectionTitle"]))
    story.append(
        p(
            "A autenticacao fica em src/pages/login.php e app/auth.php. O administrador inicial e "
            "criado pelo SQL com senha armazenada por password_hash(). O login usa password_verify(), "
            "cria sessao PHP e as paginas privadas chamam exigir_login(). O logout encerra a sessao.",
            styles["Body"],
        )
    )

    story.append(p("5. CRUD e integracao", styles["SectionTitle"]))
    story.append(
        make_table(
            [
                ["Area", "Create", "Read", "Update", "Delete", "Arquivos principais"],
                ["Produtos", "Sim", "Sim", "Sim", "Sim", "produtos.php / produtos_repositorio.php"],
                ["Categorias", "Sim", "Sim", "Sim", "Sim", "categorias.php / produtos_repositorio.php"],
                ["Administrador", "Sim", "Sim", "Sim", "Sim", "administradores.php / administradores_repositorio.php"],
                ["Pedidos", "Sim", "Sim", "Sim", "Fluxo finaliza por status", "totem.php / cozinha.php / pedidos_repositorio.php"],
                ["Financeiro", "Sim", "Sim", "Filtros e resumo", "Nao essencial", "financeiro.php / financeiro_repositorio.php"],
                ["Configuracoes", "Sim", "Sim", "Sim", "Nao essencial", "configuracoes.php / configuracoes_repositorio.php"],
            ],
            [1.15 * inch, 0.55 * inch, 0.55 * inch, 0.75 * inch, 0.85 * inch, 2.47 * inch],
            styles,
        )
    )

    story.append(p("6. Fluxo do sistema", styles["SectionTitle"]))
    story.append(
        numbered(
            [
                "Cliente acessa o totem em src/pages/totem.php.",
                "O totem busca categorias e produtos ativos no MySQL.",
                "Cliente monta o carrinho e finaliza o pedido.",
                "PHP grava pedidos e itens_pedido e atualiza estoque.",
                "Cozinha visualiza e avanca status: Recebido, Em preparo, Pronto e Retirado.",
                "Financeiro soma pedidos pagos e movimentos de caixa.",
                "Administrador gerencia produtos, categorias, configuracoes e usuarios.",
            ],
            styles,
        )
    )

    story.append(PageBreak())
    story.append(p("7. Seguranca e PDO", styles["SectionTitle"]))
    story.append(
        bullets(
            [
                "Arquivo app/conexao.php reutiliza a conexao PDO.",
                "PDO usa modo de erro por excecao.",
                "Consultas de entrada do usuario usam prepare() e execute().",
                "Senha do administrador usa password_hash() no cadastro e password_verify() no login.",
                "Paginas privadas validam a sessao com exigir_login().",
                "Saida HTML usa escapar() para reduzir risco de exibicao insegura.",
            ],
            styles,
        )
    )

    story.append(p("8. Como executar", styles["SectionTitle"]))
    story.append(
        numbered(
            [
                "Abrir o XAMPP.",
                "Ligar Apache e MySQL.",
                "Importar database/database.sql no phpMyAdmin.",
                "Abrir a pasta do projeto no VS Code.",
                "Rodar: C:\\xampp\\php\\php.exe -S 127.0.0.1:5520 -t .",
                "Acessar http://127.0.0.1:5520/src/pages/totem.php ou login.php.",
            ],
            styles,
        )
    )

    story.append(p("9. Checklist dos requisitos", styles["SectionTitle"]))
    story.append(
        make_table(
            [
                ["Requisito", "Status"],
                ["PHP puro, sem framework", "Atendido"],
                ["MySQL", "Atendido"],
                ["PDO", "Atendido"],
                ["Arquivo de conexao reutilizavel", "Atendido"],
                ["Login/logout e sessao PHP", "Atendido"],
                ["Protecao de paginas privadas", "Atendido"],
                ["CRUD completo nas tabelas principais", "Atendido"],
                ["Frontend exibindo dados dinamicos", "Atendido"],
                ["Relacionamentos e JOIN", "Atendido"],
                ["Senha criptografada", "Atendido"],
                ["Banco .sql atualizado", "Atendido"],
            ],
            [4.7 * inch, 1.65 * inch],
            styles,
        )
    )

    story.append(p("10. Testes finais realizados", styles["SectionTitle"]))
    story.append(
        bullets(
            [
                "Login com admin / 123.",
                "Bloqueio de dashboard sem sessao.",
                "Cadastro, leitura, edicao e exclusao de categorias.",
                "Cadastro, leitura, edicao e exclusao de produtos.",
                "Criacao de pedido pelo totem.",
                "Leitura e mudanca de status na cozinha.",
                "Registro de movimento financeiro.",
                "Importacao do SQL em banco temporario para validar estrutura.",
            ],
            styles,
        )
    )

    story.append(p("11. Observacao de entrega", styles["SectionTitle"]))
    story.append(
        p(
            "A pasta legacy nao e a versao oficial do sistema; ela serve apenas como backup historico. "
            "A versao entregue e a integracao PHP + MySQL em app, src/pages, src/styles, src/assets e database.",
            styles["Body"],
        )
    )

    doc.build(story, onFirstPage=page_footer, onLaterPages=page_footer)


if __name__ == "__main__":
    build()
    print(OUT)
