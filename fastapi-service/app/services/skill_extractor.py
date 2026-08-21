"""
SkillExtractor — Extraction de compétences via modèle NER pré-entraîné (Option A).

MISSION :
Remplace l'ancienne approche lexicon-based (KNOWN_SKILLS + regex).
Un modèle BERT/XLM-R fine-tuné sur SkillSpan lit le texte et repère
lui-même les compétences, y compris celles absentes de toute liste fixe.

RELATION :
- Reçoit le texte brut depuis pdf_extractor.py
- Sa sortie (liste de skills) est persistée par Laravel, puis envoyée à scorer.py
- Appelé depuis routers/extract.py via Depends(get_skill_extractor)

Exemple réel :
  Texte CV : "3 ans d'expérience en développement Laravel et React Native"
  → extract_skill_names() retourne : ["Laravel", "React Native"]
  (même si "React Native" n'était pas dans l'ancienne liste KNOWN_SKILLS)
"""

import logging
import re
from functools import lru_cache
from typing import Dict, List, Protocol

from transformers import Pipeline, pipeline

from app.config import settings

logger = logging.getLogger(__name__)


class SkillExtractorProtocol(Protocol):
    """Contrat commun entre SkillExtractor (prod) et FakeSkillExtractor (tests)."""

    def extract(self, text: str) -> List[Dict]:
        ...

    def extract_skill_names(self, text: str) -> List[str]:
        ...


class SkillExtractor:
    """
    Wrapper autour du pipeline HuggingFace token-classification.

    Le modèle travaille au niveau sous-mot (subword). Le paramètre
    aggregation_strategy="simple" recombine automatiquement les tokens
    en entités complètes ("Kubernetes" au lieu de "Kuber" + "##netes").

    IMPORTANT : instancié UNE SEULE FOIS via get_skill_extractor() —
    le chargement prend 5-15 secondes et ~1 Go RAM (XLM-RoBERTa).
    """

    def __init__(
        self,
        model_name: str | None = None,
        confidence_threshold: float | None = None,
    ):
        self.model_name = model_name or settings.skill_model_name
        self.confidence_threshold = (
            confidence_threshold
            if confidence_threshold is not None
            else settings.skill_confidence_threshold
        )

        logger.info("[SkillExtractor] Chargement du modèle NER: %s", self.model_name)
        self._pipe: Pipeline = pipeline(
            task="token-classification",
            model=self.model_name,
            aggregation_strategy="simple",
        )
        logger.info("[SkillExtractor] Modèle chargé avec succès.")

    def extract(self, text: str) -> List[Dict]:
        """
        Extrait les skills avec leur score de confiance.

        Returns:
            [{"skill": "Python", "score": 0.94}, ...] trié par score décroissant
        """
        if not text or not text.strip():
            return []

        raw_entities = self._pipe(text)

        results: List[Dict] = []
        for entity in raw_entities:
            score = float(entity.get("score", 0.0))
            if score < self.confidence_threshold:
                continue

            skill_text = self._clean_entity_text(entity.get("word", ""))
            if not skill_text:
                continue

            results.append({"skill": skill_text, "score": round(score, 4)})

        deduped = self._deduplicate(results)
        return sorted(deduped, key=lambda entry: entry["score"], reverse=True)

    def extract_skill_names(self, text: str) -> List[str]:
        """
        Version simplifiée : noms uniquement, sans scores.

        C'est cette méthode que routers/extract.py appelle pour remplir
        ExtractResponse.skills — le contrat HTTP Laravel n'a pas besoin des scores.
        """
        return [entry["skill"] for entry in self.extract(text)]

    @staticmethod
    def _clean_entity_text(raw: str) -> str:
        """
        Nettoie les artefacts de tokenization subword.

        Exemple : "React Nat ive" (mal recombiné) → "React Native"
        """
        cleaned = raw.replace("##", "")
        cleaned = re.sub(r"\s+", " ", cleaned).strip()

        if len(cleaned) < 2:
            return ""
        if not re.search(r"[A-Za-zÀ-ÿ0-9]", cleaned):
            return ""
        return cleaned

    @staticmethod
    def _deduplicate(entries: List[Dict]) -> List[Dict]:
        """
        Déduplique par casse : "Python", "PYTHON", "python" → une seule entrée.

        Garde la variante avec le meilleur score de confiance.
        """
        best_by_key: Dict[str, Dict] = {}
        for entry in entries:
            key = entry["skill"].lower()
            if key not in best_by_key or entry["score"] > best_by_key[key]["score"]:
                best_by_key[key] = entry
        return list(best_by_key.values())


@lru_cache(maxsize=1)
def get_skill_extractor() -> SkillExtractor:
    """
    Factory singleton — injectée dans FastAPI via Depends().

    SKILL_EXTRACTOR_MODE=fake → FakeSkillExtractor (tests/dev, pas de HuggingFace)
    SKILL_EXTRACTOR_MODE=real → vrai modèle NER (production)
    """
    from app.config import settings
    from app.services.fake_skill_extractor import FakeSkillExtractor

    if settings.skill_extractor_mode == "fake":
        logger.info("[SkillExtractor] Mode FAKE — pas de modèle NER chargé.")
        return FakeSkillExtractor(  # type: ignore[return-value]
            fixed_skills=[
                "Python", "Laravel", "React", "Docker", "PostgreSQL",
                "Kubernetes", "FastAPI", "Git", "Agile", "AWS",
            ]
        )

    return SkillExtractor()
