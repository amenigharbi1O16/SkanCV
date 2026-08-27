"""Dépendances de sécurité partagées par les routes FastAPI."""

import os

from fastapi import Header, HTTPException, status

API_KEY = os.getenv("FASTAPI_API_KEY", "")


async def verify_api_key(x_api_key: str | None = Header(default=None)) -> None:
    """
    Protège /extract et /score derrière une clé API partagée avec Laravel.
    Si FASTAPI_API_KEY est vide (dev local), la vérification est désactivée.
    """
    if not API_KEY:
        return

    if not x_api_key or x_api_key != API_KEY:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Clé API invalide ou manquante.",
        )
