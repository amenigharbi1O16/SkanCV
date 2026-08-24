"""
conftest.py — MISSION : rendre le package `app` importable depuis les tests.

CONTEXTE : docker-compose ne monte en bind que fastapi-service/app et
fastapi-service/tests vers /app (voir docker inspect), pas la racine
fastapi-service/. Un pytest.ini à la racine ne serait donc jamais visible
dans le conteneur sans rebuild d'image. Ce fichier vit dans tests/, qui LUI
est monté en live, donc il prend effet immédiatement.

Ajoute /app (parent de tests/) à sys.path pour que `from app.main import app`
fonctionne dans test_extract_route.py et test_score_route.py.
"""
import sys
import os

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
