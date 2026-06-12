from html import escape
from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import cm
from reportlab.platypus import Paragraph, SimpleDocTemplate, Spacer


ROOT = Path(__file__).resolve().parents[1]
MD = ROOT / "docs" / "documentacao_tecnica_gastrotech.md"
PDF = ROOT / "docs" / "documentacao_tecnica_gastrotech.pdf"


def estilos():
    base = getSampleStyleSheet()
    base.add(
        ParagraphStyle(
            name="Titulo",
            parent=base["Title"],
            fontName="Helvetica-Bold",
            fontSize=20,
            leading=24,
            textColor=colors.HexColor("#1f2933"),
            alignment=TA_LEFT,
            spaceAfter=10,
        )
    )
    base.add(
        ParagraphStyle(
            name="Secao",
            parent=base["Heading1"],
            fontName="Helvetica-Bold",
            fontSize=13,
            leading=17,
            textColor=colors.HexColor("#2e5f8a"),
            spaceBefore=12,
            spaceAfter=6,
        )
    )
    base.add(
        ParagraphStyle(
            name="Texto",
            parent=base["BodyText"],
            fontName="Helvetica",
            fontSize=9.5,
            leading=13,
            spaceAfter=6,
        )
    )
    base.add(
        ParagraphStyle(
            name="Lista",
            parent=base["BodyText"],
            fontName="Helvetica",
            fontSize=9.5,
            leading=13,
            leftIndent=12,
            firstLineIndent=-8,
            spaceAfter=3,
        )
    )
    base.add(
        ParagraphStyle(
            name="Codigo",
            parent=base["BodyText"],
            fontName="Courier",
            fontSize=8.5,
            leading=11,
            backColor=colors.HexColor("#f2f2f2"),
            borderColor=colors.HexColor("#d0d0d0"),
            borderWidth=0.4,
            borderPadding=5,
            spaceBefore=3,
            spaceAfter=7,
        )
    )
    return base


def limpar_linha(linha):
    linha = linha.strip()
    linha = linha.replace("`", "")
    linha = linha.replace("**", "")
    return escape(linha)


def montar_pdf():
    styles = estilos()
    doc = SimpleDocTemplate(
        str(PDF),
        pagesize=A4,
        rightMargin=2 * cm,
        leftMargin=2 * cm,
        topMargin=1.7 * cm,
        bottomMargin=1.7 * cm,
        title="Documentação técnica - GastroTech",
    )

    partes = []
    em_codigo = False
    bloco_codigo = []

    for linha in MD.read_text(encoding="utf-8").splitlines():
        texto = linha.strip()

        if texto.startswith("```"):
            if em_codigo:
                partes.append(Paragraph("<br/>".join(map(escape, bloco_codigo)), styles["Codigo"]))
                bloco_codigo = []
                em_codigo = False
            else:
                em_codigo = True
            continue

        if em_codigo:
            bloco_codigo.append(linha)
            continue

        if not texto:
            partes.append(Spacer(1, 4))
            continue

        if texto.startswith("# "):
            partes.append(Paragraph(limpar_linha(texto[2:]), styles["Titulo"]))
            continue

        if texto.startswith("## "):
            partes.append(Paragraph(limpar_linha(texto[3:]), styles["Secao"]))
            continue

        if texto.startswith("- "):
            partes.append(Paragraph("• " + limpar_linha(texto[2:]), styles["Lista"]))
            continue

        if len(texto) > 2 and texto[0].isdigit() and ". " in texto[:4]:
            partes.append(Paragraph(limpar_linha(texto), styles["Lista"]))
            continue

        partes.append(Paragraph(limpar_linha(texto), styles["Texto"]))

    doc.build(partes)


if __name__ == "__main__":
    montar_pdf()
    print(PDF)
