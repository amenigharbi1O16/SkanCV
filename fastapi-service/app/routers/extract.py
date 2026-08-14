"""
Route POST /extract — extraction PDF → texte + compétences.

MISSION : recevoir le PDF en multipart (comme RealFastApiClient l'envoie)
et retourner le JSON attendu par Laravel.
"""

from fastapi import APIRouter, File, HTTPException, UploadFile

from app.models.schemas import ExtractResponse
from app.services.pdf_extractor import extract_text_from_pdf, guess_candidate_name
from app.services.skill_detector import detect_skills

router = APIRouter()


@router.post("/extract", response_model=ExtractResponse)
async def extract(file: UploadFile = File(...)) -> ExtractResponse:
    content = await file.read()

    if not content:
        raise HTTPException(status_code=422, detail="Fichier PDF vide.")

    text = extract_text_from_pdf(content)

    if not text:
        raise HTTPException(
            status_code=422,
            detail="Impossible d'extraire du texte (PDF scanné ou illisible).",
        )

    skills = detect_skills(text)
    candidate_name = guess_candidate_name(text)

    return ExtractResponse(
        text=text,
        skills=skills,
        candidate_name=candidate_name,
    )
