"""
Contrats Pydantic — frontière de validation entre FastAPI et Laravel.

MISSION : garantir que les requêtes/réponses respectent exactement le contrat
défini dans Laravel FastApiClientInterface (extract + score).
"""

from pydantic import BaseModel, Field


class ScoreRequest(BaseModel):
    """Corps JSON de POST /score envoyé par RealFastApiClient."""

    cv_skills: list[str] = Field(default_factory=list)
    required_skills: list[str] = Field(default_factory=list)


class ExtractResponse(BaseModel):
    """Réponse JSON de POST /extract consommée par ProcessCvAnalysis."""

    text: str
    skills: list[str]
    candidate_name: str | None = None


class ScoreResponse(BaseModel):
    """Réponse JSON de POST /score consommée par ProcessCvAnalysis."""

    score: float
    justification: str
    matching_skills: list[str]
    missing_skills: list[str]
