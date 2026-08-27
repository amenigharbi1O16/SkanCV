#!/usr/bin/env python3
"""
Génère un PDF CV minimal pour tester POST /extract sans fichier réel.

Usage :
  python3 scripts/generate_test_cv.py
  → crée fastapi-service/tests/fixtures/cv_test.pdf
"""

from pathlib import Path

import fitz

OUTPUT = Path(__file__).resolve().parent.parent / "tests" / "fixtures" / "cv_test.pdf"

CV_TEXT = """Ahmed Ben Salah
ahmed.bensalah@gmail.com
Développeur Full Stack — 3 ans d'expérience

Compétences :
Python, Laravel, React, Docker, PostgreSQL, Git, Agile

Expérience :
- Développement API REST avec FastAPI et Laravel
- Déploiement sur AWS avec Docker et Kubernetes
"""


def main() -> None:
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)

    doc = fitz.open()
    page = doc.new_page()
    page.insert_text((72, 72), CV_TEXT, fontsize=11)
    doc.save(OUTPUT)
    doc.close()

    print(f"PDF créé : {OUTPUT}")


if __name__ == "__main__":
    main()
