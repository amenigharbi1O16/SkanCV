from fastapi import FastAPI
from app.routers import extract, score

app = FastAPI(title="SkanCV AI Service")

app.include_router(extract.router)
app.include_router(score.router)


@app.get("/health")
def health():
    return {"status": "ok"}
