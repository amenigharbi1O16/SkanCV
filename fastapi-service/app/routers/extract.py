"""
Route POST /extract — PDF → texte + compétences + nom candidat.

MISSION :
Point d'entrée HTTP pour l'extraction IA. Laravel (RealFastApiClient) envoie
le PDF en multipart ; on retourne le JSON attendu par ProcessCvAnalysis.

FLOW INTERNE (3 étapes) :
  1. pdf_extractor   → texte brut du CV
  2. skill_extractor → compétences via NER (remplace l'ancien KNOWN_SKILLS)
  3. pdf_extractor   → guess_candidate_name() (heuristique sur le texte)

RELATION :
- Consommé par Laravel RealFastApiClient::extract()
- Réponse validée par ExtractResponse (models/schemas.py)
"""

from fastapi import APIRouter, Depends, File, HTTPException, UploadFile

from app.dependencies import verify_api_key
from app.models.schemas import ExtractResponse
from app.services.pdf_extractor import extract_text_from_pdf, guess_candidate_name
from app.services.skill_extractor import SkillExtractor, get_skill_extractor

router = APIRouter()


@router.post("/extract", response_model=ExtractResponse, dependencies=[Depends(verify_api_key)])
async def extract(
    file: UploadFile = File(...),
    extractor: SkillExtractor = Depends(get_skill_extractor),
) -> ExtractResponse:
    """
    Extrait texte et compétences d'un CV PDF.

    Exemple réel :
      HR upload cv_jean.pdf pour un poste Laravel/React
      → Laravel appelle POST /extract avec le PDF
      → retourne {"text": "...", "skills": ["Laravel", "React", "Docker"], "candidate_name": "Jean Martin"}
    """
    content = await file.read()

    if not content:
        raise HTTPException(status_code=422, detail="Fichier PDF vide.")

    # Étape 1 : PDF → texte
    text = extract_text_from_pdf(content)

    if not text:
        raise HTTPException(
            status_code=422,
            detail="Impossible d'extraire du texte (PDF scanné ou illisible).",
        )

    # Étape 2 : texte → skills (NER — remplace detect_skills / KNOWN_SKILLS)
    skills = extractor.extract_skill_names(text)

    # Étape 3 : heuristique nom candidat
    candidate_name = guess_candidate_name(text)

    return ExtractResponse(
        text=text,
        skills=skills,
        candidate_name=candidate_name,
    )
