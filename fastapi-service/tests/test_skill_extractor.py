"""
Tests pour SkillExtractor (GLiNER) — le modèle est mocké, aucun téléchargement.
"""
import dataclasses
from unittest.mock import MagicMock, patch

import pytest


def make_gliner_entities(entities):
    """Reproduit le format retourné par GLiNER.predict_entities()."""
    return [
        {"text": text, "score": score, "label": "programming language"}
        for text, score in entities
    ]


@pytest.fixture
def mock_gliner():
    """Remplace GLiNER.from_pretrained par un mock."""
    with patch("gliner.GLiNER.from_pretrained") as mock_from_pretrained:
        mock_model = MagicMock()
        mock_from_pretrained.return_value = mock_model
        yield mock_model


class TestSkillExtractor:

    def test_extracts_high_confidence_skills(self, mock_gliner):
        from app.services.skill_extractor import SkillExtractor

        mock_gliner.predict_entities.return_value = make_gliner_entities([
            ("Python", 0.95),
            ("Laravel", 0.91),
            ("Docker", 0.87),
        ])

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.4)
        names = extractor.extract_skill_names("Je maîtrise Python, Laravel et Docker.")

        assert "Python" in names
        assert "Laravel" in names
        assert "Docker" in names

    def test_filters_low_confidence_entities(self, mock_gliner):
        from app.services.skill_extractor import SkillExtractor

        mock_gliner.predict_entities.return_value = make_gliner_entities([
            ("Python", 0.95),
            ("Blabla", 0.10),
        ])

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.4)
        names = extractor.extract_skill_names("texte de test")

        assert "Python" in names
        assert "Blabla" not in names

    def test_deduplicates_by_case(self, mock_gliner):
        from app.services.skill_extractor import SkillExtractor

        mock_gliner.predict_entities.return_value = make_gliner_entities([
            ("Python", 0.95),
            ("python", 0.89),
            ("PYTHON", 0.92),
        ])

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.4)
        names = extractor.extract_skill_names("texte de test")

        assert sum(1 for n in names if n.lower() == "python") == 1

    def test_dedup_keeps_best_score(self, mock_gliner):
        from app.services.skill_extractor import SkillExtractor

        mock_gliner.predict_entities.return_value = make_gliner_entities([
            ("python", 0.60),
            ("Python", 0.95),
        ])

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.4)
        result = extractor.extract("texte de test")

        python_entry = next(e for e in result if e["skill"].lower() == "python")
        assert python_entry["skill"] == "Python"
        assert python_entry["score"] == 0.95

    def test_handles_empty_text(self, mock_gliner):
        from app.services.skill_extractor import SkillExtractor

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.4)
        result = extractor.extract("")

        assert result == []
        mock_gliner.predict_entities.assert_not_called()

    def test_results_sorted_by_score_descending(self, mock_gliner):
        from app.services.skill_extractor import SkillExtractor

        mock_gliner.predict_entities.return_value = make_gliner_entities([
            ("Docker", 0.70),
            ("Python", 0.95),
            ("Laravel", 0.85),
        ])

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.4)
        result = extractor.extract("texte de test")

        scores = [entry["score"] for entry in result]
        assert scores == sorted(scores, reverse=True)


class TestGetSkillExtractorFactory:

    def test_returns_fake_extractor_when_mode_is_fake(self):
        from app.config import settings as real_settings
        from app.services.fake_skill_extractor import FakeSkillExtractor
        from app.services.skill_extractor import get_skill_extractor

        get_skill_extractor.cache_clear()

        fake_settings = dataclasses.replace(real_settings, skill_extractor_mode="fake")

        with patch("app.config.settings", fake_settings):
            extractor = get_skill_extractor()
            assert isinstance(extractor, FakeSkillExtractor)

        get_skill_extractor.cache_clear()
