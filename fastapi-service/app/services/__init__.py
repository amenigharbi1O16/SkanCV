"""
Services métier IA — extraction PDF, NER, scoring.

Chaque fichier = une responsabilité :
  pdf_extractor.py      → PDF binaire → texte
  skill_extractor.py    → texte → compétences (NER)
  fake_skill_extractor.py → double de test (sans modèle)
  scorer.py             → compétences → score sémantique
"""
