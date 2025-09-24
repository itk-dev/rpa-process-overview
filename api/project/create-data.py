# https://sqlmodel.tiangolo.com/tutorial/fastapi/simple-hero-api/#sqlmodel-code-models-engine

from .database import create_db_and_tables, engine
from .models import Process, ProcessStep, ProcessRun
from datetime import timedelta

import json

from faker import Faker

from sqlmodel import Session

def main():
    create_db_and_tables()

    fake = Faker()
    fake.seed_instance(19750523)

    with Session(engine) as session:
        for i in range(87):
            meta = {
              'cpr': 'string',
              'name': 'string',
              'branch': 'string',
            }
            process = Process(
                name=fake.sentence(4),
                meta=json.dumps(meta),
            )
            number_of_steps = fake.pyint(2, 7)
            for j in range(number_of_steps):
                step = ProcessStep(
                    index=j,
                    name=fake.sentence(2),
                )
                step.process = process
                session.add(step)

            for j in range(60):
                meta = {
                    'cpr': fake.random_number(10, fix_len=True),
                    'name': fake.name(),
                    'branch': fake.company(),
                }
                steps = []

                failed_step_index = fake.pyint(0, number_of_steps+1)
                started_at = fake.past_datetime()
                for index in range(0, number_of_steps):
                    started_at = started_at + timedelta(seconds=fake.pyint(1, 1000))
                    finished_at = started_at + timedelta(seconds=fake.pyint(3, 42))
                    status = 'SUCCESS' if index < failed_step_index else 'PENDING'
                    failure = None
                    if index == failed_step_index:
                        status = 'FAILED'
                        failure = {
                            'code': 'Fejlkode',
                            'message': 'Fejlbesked',
                            'retryable': fake.boolean(),
                            'occurred_at': (finished_at - timedelta(seconds=-fake.pyint(0, 3))).isoformat(),
                        }
                    steps.append({
                        'status': status,
                        'started_at': started_at.isoformat(),
                        'finished_at': finished_at.isoformat(),
                        'failure': failure,
                    })

                run = ProcessRun(
                    meta=json.dumps(meta),
                    steps=json.dumps(steps),
                )
                run.process = process
                session.add(run)

            session.add(process)

        session.commit()

if __name__ == '__main__':
    main()
