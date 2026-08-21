"""
Tests pour SkillExtractor (le vrai modèle NER) — le pipeline HuggingFace
est mocké ici, donc ces tests tournent sans jamais télécharger ni charger
le vrai modèle en RAM.
"""
import pytest
from unittest.mock import patch, MagicMock


def make_fake_ner_output(entities):
    """
    entities: liste de tuples (word, score)
    Reproduit le format retourné par pipeline("token-classification",
    aggregation_strategy="simple") : le modèle renvoie une liste de dicts
    avec au moins "word" et "score".
    """
    return [
        {"word": word, "score": score, "entity_group": "SKILL", "start": 0, "end": len(word)}
        for word, score in entities
    ]


@pytest.fixture
def mock_pipeline():
    """Remplace transformers.pipeline() par un mock — aucun modèle n'est chargé."""
    with patch("app.services.skill_extractor.pipeline") as mock_pipe_factory:
        mock_pipe_instance = MagicMock()
        mock_pipe_factory.return_value = mock_pipe_instance
        yield mock_pipe_instance


class TestSkillExtractor:

    def test_extracts_high_confidence_skills(self, mock_pipeline):
        from app.services.skill_extractor import SkillExtractor

        mock_pipeline.return_value = make_fake_ner_output([
            ("Python", 0.95),
            ("Laravel", 0.91),
            ("Docker", 0.87),
        ])

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.5)
        names = extractor.extract_skill_names("Je maîtrise Python, Laravel et Docker.")

        assert "Python" in names
        assert "Laravel" in names
        assert "Docker" in names

    def test_filters_low_confidence_entities(self, mock_pipeline):
        """Une entité sous le seuil de confidence ne doit pas apparaître."""
        from app.services.skill_extractor import SkillExtractor

        mock_pipeline.return_value = make_fake_ner_output([
            ("Python", 0.95),
            ("Blabla", 0.20),  # sous le seuil
        ])

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.5)
        names = extractor.extract_skill_names("texte de test")

        assert "Python" in names
        assert "Blabla" not in names

    def test_deduplicates_by_case(self, mock_pipeline):
        """'Python', 'python', 'PYTHON' doivent devenir une seule entrée."""
        from app.services.skill_extractor import SkillExtractor

        mock_pipeline.return_value = make_fake_ner_output([
            ("Python", 0.95),
            ("python", 0.89),
            ("PYTHON", 0.92),
        ])

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.5)
        names = extractor.extract_skill_names("texte de test")

        assert sum(1 for n in names if n.lower() == "python") == 1

    def test_dedup_keeps_best_score(self, mock_pipeline):
        """En cas de doublon, la version avec le meilleur score doit être gardée."""
        from app.services.skill_extractor import SkillExtractor

        mock_pipeline.return_value = make_fake_ner_output([
            ("python", 0.60),
            ("Python", 0.95),  # meilleur score, doit gagner
        ])

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.5)
        result = extractor.extract("texte de test")

        python_entry = next(e for e in result if e["skill"].lower() == "python")
        assert python_entry["skill"] == "Python"
        assert python_entry["score"] == 0.95

    def test_cleans_subword_artifacts(self, mock_pipeline):
        """Les artefacts '##' de tokenization doivent être nettoyés."""
        from app.services.skill_extractor import SkillExtractor

        mock_pipeline.return_value = make_fake_ner_output([
            ("React Nat##ive", 0.90),
        ])

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.5)
        names = extractor.extract_skill_names("texte de test")

        assert any("React Native" in n or "React Nat ive" in n for n in names)

    def test_handles_empty_text(self, mock_pipeline):
        from app.services.skill_extractor import SkillExtractor

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.5)
        result = extractor.extract("")

        assert result == []
        mock_pipeline.assert_not_called()  # pas d'appel au pipeline sur texte vide

    def test_results_sorted_by_score_descending(self, mock_pipeline):
        from app.services.skill_extractor import SkillExtractor

        mock_pipeline.return_value = make_fake_ner_output([
            ("Docker", 0.70),
            ("Python", 0.95),
            ("Laravel", 0.85),
        ])

        extractor = SkillExtractor(model_name="fake-model", confidence_threshold=0.5)
        result = extractor.extract("texte de test")

        scores = [entry["score"] for entry in result]
        assert scores == sorted(scores, reverse=True)


class TestGetSkillExtractorFactory:
    def test_returns_fake_extractor_when_mode_is_fake(self):
        """Vérifie que get_skill_extractor() respecte SKILL_EXTRACTOR_MODE=fake."""
        import dataclasses
        from app.config import settings as real_settings
        from app.services.skill_extractor import get_skill_extractor
        from app.services.fake_skill_extractor import FakeSkillExtractor

        get_skill_extractor.cache_clear()  # lru_cache — reset entre tests

        # Settings est un dataclass frozen : on ne peut pas modifier un champ,
        # donc on remplace l'objet entier par une copie avec le champ voulu.
        fake_settings = dataclasses.replace(real_settings, skill_extractor_mode="fake")

        with patch("app.config.settings", fake_settings):
            extractor = get_skill_extractor()
            assert isinstance(extractor, FakeSkillExtractor)

        get_skill_extractor.cache_clear()