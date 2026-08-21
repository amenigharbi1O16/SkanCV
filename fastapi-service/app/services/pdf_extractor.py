"""
Extraction de texte brut depuis un PDF CV.

MISSION :
Transformer le fichier PDF binaire (envoyé par Laravel via multipart) en
texte brut que le modèle NER pourra analyser.

RELATION :
- Appelé par routers/extract.py (étape 1 du pipeline /extract)
- Sa sortie alimente skill_extractor.py (étape 2) et guess_candidate_name()

Pourquoi PyMuPDF (fitz) et pas Tesseract OCR :
Les CV SkanCV sont des PDFs texte natifs (export Word/LaTeX). L'OCR serait
plus lourd, plus lent et inutile pour le MVP.

Exemple réel :
  HR upload "cv_marie_dupont.pdf"
  → extract_text_from_pdf() retourne :
    "Marie Dupont\nmarie@email.com\nCompétences : Python, Laravel..."
"""

import fitz


def extract_text_from_pdf(pdf_bytes: bytes) -> str:
    """
    Extrait le texte de toutes les pages d'un PDF.

    Args:
        pdf_bytes: contenu binaire du PDF (lu depuis UploadFile par la route)

    Returns:
        Texte concaténé de toutes les pages, ou chaîne vide si illisible.
    """
    document = fitz.open(stream=pdf_bytes, filetype="pdf")
    pages_text = [page.get_text() for page in document]
    document.close()

    return "\n".join(pages_text).strip()


def guess_candidate_name(text: str) -> str | None:
    """
    Heuristique MVP : la première ligne non vide ressemble souvent au nom.

    Exemple réel sur un CV tunisien :
      "Ahmed Ben Salah"
      "ahmed.bensalah@gmail.com"
      → retourne "Ahmed Ben Salah"

    On ignore les lignes avec @, http ou chiffres (email, URL, téléphone).
    """
    for line in text.splitlines():
        cleaned = line.strip()
        if not cleaned or len(cleaned) > 80:
            continue
        if "@" in cleaned or "http" in cleaned.lower():
            continue
        if any(char.isdigit() for char in cleaned):
            continue
        return cleaned

    return None
