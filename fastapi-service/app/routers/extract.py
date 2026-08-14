from fastapi import APIRouter, UploadFile, File

router = APIRouter()


@router.post("/extract")
async def extract(file: UploadFile = File(...)):
    content = await file.read()
    # STUB — sera remplacé par PyMuPDF + détection de skills réelle (Phase 2.2)
    return {
        "text": f"[STUB] {len(content)} octets lus depuis {file.filename}",
        "skills": ["PHP", "Laravel", "MySQL", "Docker"],
        "candidate_name": "Extraction non implémentée (stub)",
    }
