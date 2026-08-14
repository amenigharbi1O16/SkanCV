"""
Extraction de texte brut depuis un PDF CV.

MISSION : lire le contenu textuel d'un PDF uploadé par Laravel.
Pourquoi PyMuPDF (fitz) et pas Tesseract OCR : les CVs SkanCV sont des PDFs
texte natifs ; l'OCR serait plus lourd et inutile pour le MVP.
"""

import fitz


def extract_text_from_pdf(pdf_bytes: bytes) -> str:
    """Extrait le texte de toutes les pages d'un PDF."""
    document = fitz.open(stream=pdf_bytes, filetype="pdf")
    pages_text = [page.get_text() for page in document]
    document.close()

    return "\n".join(pages_text).strip()


def guess_candidate_name(text: str) -> str | None:
    """
    Heuristique MVP : la première ligne non vide ressemble souvent au nom du candidat.
    Exemple réel : "Jean Dupont" en tête du CV avant l'email ou l'adresse.
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
