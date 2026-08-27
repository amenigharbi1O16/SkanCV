"""
Route POST /score — similarité sémantique CV vs offre d'emploi.

MISSION : calculer le score de matching et une justification structurée
pour le HR Staff, consommé par ProcessCvAnalysis via RealFastApiClient.
"""

from fastapi import APIRouter, Depends

from app.dependencies import verify_api_key
from app.models.schemas import ScoreRequest, ScoreResponse
from app.services.scorer import score_skills

router = APIRouter()


@router.post("/score", response_model=ScoreResponse, dependencies=[Depends(verify_api_key)])
def score(payload: ScoreRequest) -> ScoreResponse:
    result = score_skills(payload.cv_skills, payload.required_skills)

    return ScoreResponse(**result)
