"""
Calcul du score de similarité sémantique entre compétences CV et offre.

MISSION :
Produire un score 0-1 plus intelligent qu'une simple intersection de chaînes.
Le modèle NER extrait les skills ; CE fichier mesure à quel point elles
matchent l'offre d'emploi.

RELATION :
- Appelé par routers/score.py (POST /score)
- Reçoit les skills extraites par skill_extractor.py (via Laravel ProcessCvAnalysis)
- Utilise sentence-transformers (PAS le modèle NER — tâche différente)

Exemple réel :
  CV skills     : ["Python", "FastAPI", "Docker", "PostgreSQL"]
  Offre skills  : ["Python", "Django", "AWS"]
  → matching exact : ["Python"]
  → missing      : ["Django", "AWS"]
  → score sémantique ~0.75 car "FastAPI" est proche de "Django" en embedding

Pourquoi all-MiniLM-L6-v2 :
  Léger (~80 Mo), gratuit, local, pas de clé API OpenAI.
"""

from functools import lru_cache

import re

from sentence_transformers import SentenceTransformer, util

from app.config import settings


@lru_cache(maxsize=1)
def get_embedding_model() -> SentenceTransformer:
    """
    Singleton : charge le modèle d'embeddings une seule fois au démarrage.

    Pré-chargé dans main.py (lifespan) pour éviter le cold start à la
    première requête /score.
    """
    return SentenceTransformer(settings.embedding_model_name)


def compute_similarity_score(cv_skills: list[str], required_skills: list[str]) -> float:
    """
    Similarité cosinus entre les embeddings des deux listes de compétences.

    On joint les skills en une seule phrase par côté, puis on encode.
    C'est une approximation rapide — une alternative serait d'encoder
    chaque skill individuellement et faire un matching pairwise.
    """
    if not cv_skills or not required_skills:
        return 0.0

    model = get_embedding_model()
    cv_text = " ".join(cv_skills)
    req_text = " ".join(required_skills)

    cv_embedding = model.encode(cv_text, convert_to_tensor=True)
    req_embedding = model.encode(req_text, convert_to_tensor=True)

    similarity = util.cos_sim(cv_embedding, req_embedding).item()

    return round(max(0.0, min(1.0, similarity)), 4)


def build_justification(
    score: float,
    matching_skills: list[str],
    missing_skills: list[str],
    cv_skills: list[str],
    required_skills: list[str],
) -> str:
    """Génère une justification lisible affichée au HR Staff dans SkanCV."""
    return (
        f"Score de similarité sémantique : {score:.0%}. "
        f"Compétences requises : {len(required_skills)}. "
        f"Compétences détectées sur le CV : {len(cv_skills)}. "
        f"Correspondances exactes : {', '.join(matching_skills) if matching_skills else 'aucune'}. "
        f"Compétences manquantes : {', '.join(missing_skills) if missing_skills else 'aucune'}."
    )


def _normalize_skill_list(skills: list[str]) -> list[str]:
    """Split comma-separated entries and dedupe (case preserved)."""
    result: list[str] = []
    seen: set[str] = set()
    for skill in skills:
        for part in re.split(r"\s*,\s*", skill.strip()):
            clean = part.strip()
            if not clean:
                continue
            key = clean.lower()
            if key not in seen:
                seen.add(key)
                result.append(clean)
    return result


def _case_insensitive_intersection(cv_skills: list[str], required_skills: list[str]) -> list[str]:
    cv_lower = {s.lower(): s for s in cv_skills}
    matching: list[str] = []
    for req in required_skills:
        if req.lower() in cv_lower:
            matching.append(req)
    return sorted(set(matching), key=str.lower)


def score_skills(cv_skills: list[str], required_skills: list[str]) -> dict:
    """
    Orchestre scoring embedding + intersection pour matching/missing lisibles.
    """
    cv_skills = _normalize_skill_list(cv_skills)
    required_skills = _normalize_skill_list(required_skills)

    matching = _case_insensitive_intersection(cv_skills, required_skills)
    missing = sorted(
        {req for req in required_skills if req.lower() not in {s.lower() for s in cv_skills}},
        key=str.lower,
    )
    score = compute_similarity_score(cv_skills, required_skills)
    justification = build_justification(score, matching, missing, cv_skills, required_skills)

    return {
        "score": score,
        "justification": justification,
        "matching_skills": matching,
        "missing_skills": missing,
    }
