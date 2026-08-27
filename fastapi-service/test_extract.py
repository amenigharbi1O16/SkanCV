from app.services.skill_extractor import SkillExtractor

text = """Backend-focused engineer with 3 years of experience designing microservices
in Java and Spring Boot for fintech applications. Strong background in relational
database design, message queues, and system reliability.
Designed and maintained microservices in Spring Boot handling transaction processing.
Integrated Apache Kafka for event-driven communication.
Wrote integration tests with JUnit and Testcontainers.
Languages: Java, SQL, Python. Backend: Spring Boot, Spring Security, Hibernate.
Databases: PostgreSQL, Oracle. Messaging: Apache Kafka, RabbitMQ.
Testing: JUnit, Testcontainers, Mockito. Tools: Git, Docker, Jenkins."""

extractor = SkillExtractor(confidence_threshold=0.0)  # seuil à 0 pour tout voir
results = extractor.extract(text)
for r in results:
    print(f"{r['score']:.3f}  {r['skill']}")
