"""
Point d'entrée du microservice IA SkanCV.

MISSION : exposer les endpoints /extract et /score consommés par Laravel
via RealFastApiClient quand FASTAPI_MODE=real.
"""

from contextlib import asynccontextmanager

from fastapi import FastAPI

from app.routers import extract, score
from app.services.scorer import get_embedding_model


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Pré-charge le modèle d'embeddings au démarrage pour éviter le cold start."""
    get_embedding_model()
    yield


app = FastAPI(title="SkanCV AI Service", lifespan=lifespan)

app.include_router(extract.router)
app.include_router(score.router)


@app.get("/health")
def health():
    """Healthcheck utilisé par Docker et les vérifications manuelles."""
    return {"status": "ok"}
