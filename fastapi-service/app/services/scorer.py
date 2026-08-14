"""
Calcul du score de similarité sémantique entre compétences CV et offre.

MISSION : produire un score 0-1 plus intelligent qu'une simple intersection
de chaînes. Exemple réel : "ReactJS" sur le CV vs "React" sur l'offre —
les embeddings capturent la proximité sémantique.

Pourquoi all-MiniLM-L6-v2 : léger (~80 Mo), gratuit, local, pas de clé API.
Alternative rejetée : GPT-4 API (coût, latence, dépendance externe).
"""

from functools import lru_cache

from sentence_transformers import SentenceTransformer, util


@lru_cache(maxsize=1)
def get_embedding_model() -> SentenceTransformer:
    """Charge le modèle une seule fois au premier appel (singleton)."""
    return SentenceTransformer("sentence-transformers/all-MiniLM-L6-v2")


def compute_similarity_score(cv_skills: list[str], required_skills: list[str]) -> float:
    """Similarité cosinus entre les embeddings des deux listes de compétences."""
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
    """Génère une justification lisible pour le HR Staff."""
    return (
        f"Score de similarité sémantique : {score:.0%}. "
        f"Compétences requises : {len(required_skills)}. "
        f"Compétences détectées sur le CV : {len(cv_skills)}. "
        f"Correspondances exactes : {', '.join(matching_skills) if matching_skills else 'aucune'}. "
        f"Compétences manquantes : {', '.join(missing_skills) if missing_skills else 'aucune'}."
    )


def score_skills(cv_skills: list[str], required_skills: list[str]) -> dict:
    """Orchestre scoring embedding + intersection pour matching/missing lisibles."""
    matching = sorted(set(cv_skills) & set(required_skills), key=str.lower)
    missing = sorted(set(required_skills) - set(cv_skills), key=str.lower)
    score = compute_similarity_score(cv_skills, required_skills)
    justification = build_justification(score, matching, missing, cv_skills, required_skills)

    return {
        "score": score,
        "justification": justification,
        "matching_skills": matching,
        "missing_skills": missing,
    }
