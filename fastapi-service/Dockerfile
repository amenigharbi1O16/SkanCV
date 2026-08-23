FROM python:3.12-slim
WORKDIR /app

ENV PIP_DEFAULT_TIMEOUT=120

COPY requirements.txt .

# Étape séparée : torch seul, mis en cache indépendamment.
# Si l'étape suivante échoue, ce téléchargement ne sera PAS refait.
RUN pip install --no-cache-dir --timeout 120 --retries 10 \
    torch --index-url https://download.pytorch.org/whl/cpu

# Étape séparée : le reste des dépendances, avec plus de tolérance réseau
RUN pip install --no-cache-dir --timeout 120 --retries 10 -r requirements.txt

COPY . .
EXPOSE 8000
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8000"]
