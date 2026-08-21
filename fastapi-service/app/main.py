"""
Point d'entrée du microservice IA SkanCV.

DÉMARRAGE :
  Par défaut, les modèles se chargent à la PREMIÈRE requête (/extract ou /score).
  → /health répond immédiatement (utile si HuggingFace est lent ou timeout).

  Pour pré-charger au boot (prod) : PRELOAD_MODELS=true dans .env
"""

import logging
import os
from contextlib import asynccontextmanager

from fastapi import FastAPI

from app.routers import extract, score
from app.services.scorer import get_embedding_model
from app.services.skill_extractor import get_skill_extractor

logger = logging.getLogger(__name__)


@asynccontextmanager
async def lifespan(app: FastAPI):
    """
    Pré-chargement optionnel des modèles.

    PRELOAD_MODELS=true  → charge NER + embeddings au démarrage (cold start évité)
    PRELOAD_MODELS=false → /health immédiat, 1ère requête plus lente (défaut)
    """
    if os.getenv("PRELOAD_MODELS", "false").lower() == "true":
        logger.info("Pré-chargement des modèles IA...")
        get_skill_extractor()
        get_embedding_model()
        logger.info("Modèles IA prêts.")
    else:
        logger.info("Chargement paresseux — modèles chargés à la 1ère requête.")
    yield


app = FastAPI(title="SkanCV AI Service", lifespan=lifespan)

app.include_router(extract.router)
app.include_router(score.router)


@app.get("/health")
def health():
    """Healthcheck rapide — ne vérifie PAS si les modèles sont chargés."""
    return {"status": "ok"}


@app.get("/health/ready")
def health_ready():
    """Vérifie que les deux modèles sont en mémoire (après 1ère requête ou preload)."""
    ner_loaded = get_skill_extractor.cache_info().currsize > 0
    emb_loaded = get_embedding_model.cache_info().currsize > 0
    return {
        "status": "ok" if ner_loaded and emb_loaded else "loading",
        "ner_model": ner_loaded,
        "embedding_model": emb_loaded,
    }
