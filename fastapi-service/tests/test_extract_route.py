"""
Tests de la route POST /extract avec FakeSkillExtractor.

MISSION :
Vérifier le contrat HTTP sans charger le vrai modèle NER (~1 Go, 10+ secondes).
On remplace get_skill_extractor par get_fake_skill_extractor via dependency_overrides.
"""

from fastapi.testclient import TestClient

from app.main import app
from app.services.fake_skill_extractor import get_fake_skill_extractor
from app.services.skill_extractor import get_skill_extractor


def test_extract_returns_skills_from_fake_ner():
    """Simule un CV texte minimal encodé en PDF-like flow (texte injecté via fake)."""
    app.dependency_overrides[get_skill_extractor] = lambda: get_fake_skill_extractor(
        fixed_skills=["Python", "Laravel"]
    )

    client = TestClient(app)

    # PDF minimal valide (1 page vide avec structure PDF basique)
    # On utilise un PDF réel minimal — fitz peut lire un PDF avec du texte
    import fitz

    doc = fitz.open()
    page = doc.new_page()
    page.insert_text((72, 72), "Jean Dupont\nPython et Laravel")
    pdf_bytes = doc.tobytes()
    doc.close()

    response = client.post(
        "/extract",
        files={"file": ("cv_test.pdf", pdf_bytes, "application/pdf")},
    )

    app.dependency_overrides.clear()

    assert response.status_code == 200
    data = response.json()
    assert "Python" in data["skills"]
    assert "Laravel" in data["skills"]
    assert "Jean Dupont" in data["text"]
