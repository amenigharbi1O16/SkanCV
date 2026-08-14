from pydantic import BaseModel


class ScoreRequest(BaseModel):
    cv_skills: list[str]
    required_skills: list[str]
