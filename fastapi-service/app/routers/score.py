from fastapi import APIRouter
from app.models.schemas import ScoreRequest

router = APIRouter()


@router.post("/score")
def score(payload: ScoreRequest):
    matching = [s for s in payload.cv_skills if s in payload.required_skills]
    missing = [s for s in payload.required_skills if s not in payload.cv_skills]
    score_value = (
        round(len(matching) / len(payload.required_skills), 4)
        if payload.required_skills else 0.0
    )
    return {
        "score": score_value,
        "justification": f"[STUB] {len(matching)}/{len(payload.required_skills)} compétences matchées.",
        "matching_skills": matching,
        "missing_skills": missing,
    }
