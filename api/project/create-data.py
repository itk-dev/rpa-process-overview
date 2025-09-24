# https://sqlmodel.tiangolo.com/tutorial/fastapi/simple-hero-api/#sqlmodel-code-models-engine

from .database import create_db_and_tables, engine
from .models import Process, ProcessStep

from faker import Faker

from sqlmodel import Session

def main():
    create_db_and_tables()

    fake = Faker()
    fake.seed_instance(19750523)

    with Session(engine) as session:
        for i in range(87):
            name = fake.name()
            process = Process(
                name=name,
            )
            for j in range(6):
                step = ProcessStep(index=j, name=fake.name())
                step.process = process
                session.add(step)
                session.add(process)

        session.commit()

if __name__ == '__main__':
    main()
