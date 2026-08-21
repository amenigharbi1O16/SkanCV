"""
FakeSkillExtractor — double de test pour SkillExtractor (sans modèle NER).

MISSION :
Même pattern que Laravel FakeFastApiClient / RealFastApiClient, mais côté
Python pour l'étape NER uniquement. Évite de charger ~1 Go de modèle à
chaque run pytest.

RELATION :
- Implémente le même contrat que SkillExtractor (extract, extract_skill_names)
- Injecté via app.dependency_overrides[get_skill_extractor] dans les tests
- JAMAIS utilisé en production
"""

from typing import Dict, List, Optional


class FakeSkillExtractor:
    """
    Simule SkillExtractor sans charger de modèle. Cherche simplement si des
    mots-clés prédéfinis apparaissent dans le texte — comme un mini lexicon
    de test, mais isolé du vrai code de production.
    """

    def __init__(self, fixed_skills: Optional[List[str]] = None):
        self._fixed_skills = fixed_skills or ["Python", "Laravel", "React", "Docker"]

    def extract(self, text: str) -> List[Dict]:
        if not text or not text.strip():
            return []
        found = []
        lowered = text.lower()
        for skill in self._fixed_skills:
            if skill.lower() in lowered:
                found.append({"skill": skill, "score": 0.99})
        return found

    def extract_skill_names(self, text: str) -> List[str]:
        return [entry["skill"] for entry in self.extract(text)]


def get_fake_skill_extractor(fixed_skills: Optional[List[str]] = None) -> FakeSkillExtractor:
    """
    Exemple d'usage dans un test :

        from app.services.skill_extractor import get_skill_extractor
        from app.services.fake_skill_extractor import get_fake_skill_extractor

        app.dependency_overrides[get_skill_extractor] = lambda: get_fake_skill_extractor()
    """
    return FakeSkillExtractor(fixed_skills=fixed_skills)
