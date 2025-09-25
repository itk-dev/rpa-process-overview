# https://sqlmodel.tiangolo.com/tutorial/fastapi/simple-hero-api/#sqlmodel-code-models-engine

from .database import create_db_and_tables, engine
from .models import Process, ProcessStep, ProcessRun, ProcessStepRun
from datetime import timedelta

from faker import Faker

from sqlmodel import Session

STATUS_PENDING = 'PENDING'
STATUS_SUCCESS = 'SUCCESS'
STATUS_FAILED = 'FAILED'

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
                meta=meta,
            )
            session.add(process)

            number_of_steps = fake.pyint(2, 7)
            steps = []
            for j in range(number_of_steps):
                step = ProcessStep(
                    process = process,
                    index=j,
                    name=fake.sentence(2),
                )
                session.add(step)
                steps.append(step)

            for j in range(60):
                meta = {
                    'cpr': str(fake.random_number(10, fix_len=True)),
                    'name': fake.name(),
                    'branch': fake.company(),
                }

                run = ProcessRun(
                    process=process,
                    meta=meta,
                )
                session.add(run)

                failed_step_index = fake.pyint(0, number_of_steps+1)
                started_at = fake.past_datetime()
                for index in range(0, number_of_steps):
                    status = STATUS_SUCCESS if index < failed_step_index else STATUS_PENDING
                    started_at = started_at + timedelta(seconds=fake.pyint(1, 1000))
                    finished_at = started_at + timedelta(seconds=fake.pyint(3, 42))
                    failure = None
                    if index == failed_step_index:
                        status = STATUS_FAILED
                        occurred_at = (finished_at - timedelta(seconds=-fake.pyint(0, 3))).isoformat()
                        finished_at = None
                        failure = {
                            'code': fake.pyint(1, 7),
                            'message': fake.sentence(),
                            'retryable': fake.boolean(),
                            'occurred_at': occurred_at,
                        }
                    step_run = ProcessStepRun(
                        status=status,
                        started_at=started_at if status != STATUS_PENDING else None,
                        finished_at=finished_at if status != STATUS_PENDING else None,
                        process=process,
                        run=run,
                        step=steps[index],
                        failure=failure,
                    )
                    session.add(step_run)

                    if status == STATUS_FAILED:
                        break

        session.commit()

if __name__ == '__main__':
    main()
