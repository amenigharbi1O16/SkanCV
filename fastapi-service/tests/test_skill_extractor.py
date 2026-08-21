"""
Tests unitaires pour SkillExtractor et FakeSkillExtractor.

MISSION : valider que le nettoyage, la déduplication et le filtrage par score
fonctionnent correctement, SANS dépendre du vrai modèle NER (trop lent pour
tourner à chaque test). Le vrai modèle sera testé séparément, manuellement,
sur de vrais CV (voir la section "test manuel" de la conversation).
"""

from app.services.fake_skill_extractor import FakeSkillExtractor


def test_fake_extractor_finds_known_skills():
    extractor = FakeSkillExtractor(fixed_skills=["Python", "Laravel"])
    text = "J'ai 3 ans d'expérience en Python et en Laravel."

    result = extractor.extract_skill_names(text)

    assert "Python" in result
    assert "Laravel" in result


def test_fake_extractor_ignores_missing_skills():
    extractor = FakeSkillExtractor(fixed_skills=["Kubernetes"])
    text = "J'ai de l'expérience en Python uniquement."

    result = extractor.extract_skill_names(text)

    assert result == []


def test_fake_extractor_handles_empty_text():
    extractor = FakeSkillExtractor()
    assert extractor.extract("") == []
    assert extractor.extract("   ") == []
