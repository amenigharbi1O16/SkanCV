"""Test POST /score — similarité sémantique entre listes de compétences."""

from fastapi.testclient import TestClient

from app.main import app


def test_score_returns_matching_and_missing_skills():
    client = TestClient(app)

    response = client.post(
        "/score",
        json={
            "cv_skills": ["PHP", "Laravel", "Docker"],
            "required_skills": ["PHP", "Laravel", "React"],
        },
    )

    assert response.status_code == 200
    data = response.json()

    assert "score" in data
    assert 0.0 <= data["score"] <= 1.0
    assert "PHP" in data["matching_skills"]
    assert "Laravel" in data["matching_skills"]
    assert "React" in data["missing_skills"]
    assert "justification" in data
