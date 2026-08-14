"""
Détection de compétences techniques dans le texte d'un CV.

MISSION : trouver les skills présents dans le texte extrait via une liste
de mots-clés connus. MVP volontairement simple — améliorable avec NLP/LLM plus tard.
"""

import re

# Compétences courantes dans les offres Anypli / tech (extensible sans changer le contrat HTTP)
KNOWN_SKILLS = [
    "PHP", "Laravel", "Symfony", "JavaScript", "TypeScript", "React", "ReactJS",
    "Vue.js", "Vue", "Angular", "Node.js", "Python", "Django", "FastAPI",
    "Java", "Spring", "C#", ".NET", "Go", "Rust", "Docker", "Kubernetes",
    "AWS", "Azure", "GCP", "MySQL", "PostgreSQL", "MongoDB", "Redis",
    "Git", "CI/CD", "Linux", "HTML", "CSS", "Tailwind", "Bootstrap",
    "REST", "GraphQL", "Microservices", "Agile", "Scrum", "TensorFlow",
    "PyTorch", "Machine Learning", "SQL", "NoSQL", "Elasticsearch",
]


def detect_skills(text: str) -> list[str]:
    """
    Recherche insensible à la casse avec frontières de mots.
    Exemple : "expérience en laravel et react" → ["Laravel", "React"]
    """
    found: list[str] = []

    for skill in KNOWN_SKILLS:
        pattern = rf"\b{re.escape(skill)}\b"
        if re.search(pattern, text, re.IGNORECASE):
            found.append(skill)

    return sorted(set(found), key=str.lower)
