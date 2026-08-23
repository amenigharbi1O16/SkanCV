"""
Configuration centralisée du microservice IA.

MISSION :
Lire les variables d'environnement une seule fois et les exposer au reste
de l'application. Évite de disperser os.getenv() dans chaque fichier.

RELATION :
- Utilisé par skill_extractor.py (nom du modèle NER, labels, seuil de confiance)
- Peut être étendu plus tard (modèle d'embeddings, timeouts, etc.)

Exemple réel :
  SKILL_MODEL_NAME=urchade/gliner_multi-v2.1
  → le serveur charge ce modèle GLiNER au démarrage, avec les labels
  définis dans SKILL_LABELS passés à l'inférence (zero-shot).
"""

import os
from dataclasses import dataclass


@dataclass(frozen=True)
class Settings:
    """Paramètres immuables du service (chargés au démarrage)."""

    # Modèle NER HuggingFace — GLiNER multilingue (FR/EN), zero-shot
    skill_model_name: str = os.getenv(
        "SKILL_MODEL_NAME", "urchade/gliner_multi-v2.1"
    )

    # Labels passés à GLiNER au moment de l'inférence (remplace le schéma
    # BIO générique d'escoxlmr par des catégories explicites)
    skill_labels: str = os.getenv(
        "SKILL_LABELS",
        "programming language,framework,library,database,cloud platform,tool,soft skill,methodology",
    )

    # Seuil de confiance : rejeter les détections trop incertaines (bruit)
    skill_confidence_threshold: float = float(
        os.getenv("SKILL_CONFIDENCE_THRESHOLD", "0.4")
    )

    # Modèle sentence-transformers pour le scoring sémantique
    embedding_model_name: str = os.getenv(
        "EMBEDDING_MODEL_NAME", "sentence-transformers/all-MiniLM-L6-v2"
    )

    # "real" = modèle NER HuggingFace | "fake" = liste fixe (dev sans téléchargement)
    skill_extractor_mode: str = os.getenv("SKILL_EXTRACTOR_MODE", "real").lower()


settings = Settings()
