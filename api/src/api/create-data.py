# https://sqlmodel.tiangolo.com/tutorial/fastapi/simple-hero-api/#sqlmodel-code-models-engine

from datetime import timedelta

from faker import Faker
from sqlmodel import Session, delete

from .database import create_db_and_tables, engine
from .models import (Process, ProcessRun, ProcessStep, ProcessStepRun,
                     StepRunStatus)


def delete_data():
    with Session(engine) as session:
        for c in [
                ProcessStepRun,
                ProcessRun,
                ProcessStep,
                Process,
        ]:
            statement = delete(c)
            session.exec(statement)
            session.commit()


def create_data():
    fake = Faker()
    fake.seed_instance(19750523)

    with Session(engine) as session:
        number_of_processes = fake.pyint(50, 200)
        for _ in range(number_of_processes):
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
            for index in range(number_of_steps):
                step = ProcessStep(
                    process=process,
                    index=index,
                    name=fake.sentence(2),
                )
                session.add(step)
                steps.append(step)

            number_of_runs = fake.pyint(0, 100)
            for _ in range(number_of_runs):
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
                    status = StepRunStatus.SUCCESS if index < failed_step_index else StepRunStatus.PENDING
                    started_at = started_at + timedelta(seconds=fake.pyint(1, 1000))
                    finished_at = started_at + timedelta(seconds=fake.pyint(3, 42))
                    failure = None
                    if index == failed_step_index:
                        status = StepRunStatus.FAILED
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
                        started_at=started_at if status != StepRunStatus.PENDING else None,
                        finished_at=finished_at if status != StepRunStatus.PENDING else None,
                        process=process,
                        run=run,
                        step=steps[index],
                        step_index=steps[index].index,
                        failure=failure,
                    )
                    session.add(step_run)

                    if status == StepRunStatus.FAILED:
                        break

        session.commit()


def main():
    # /app/src/api/create-data.py:26: SAWarning: relationship 'ProcessStepRun.run' will copy column process_run.id to column process_step_run.run_id, which conflicts with relationship(s): 'ProcessRun.steps'
    # (copies process_run.id to process_step_run.run_id).
    # If this is not the intention, consider if these relationships should be linked with back_populates, or if viewonly=True should be applied to one or more if they are read-only.
    # For the less common case that foreign key constraints are partially overlapping, the orm.foreign() annotation can be used to isolate the columns that should be written towards.
    # To silence this warning, add the parameter 'overlaps="steps"' to the 'ProcessStepRun.run' relationship. (Background on this warning at: https://sqlalche.me/e/20/qzyx)
    # (This warning originated from the `configure_mappers()` process, which was invoked automatically in response to a user-initiated operation.)
    create_db_and_tables()

    delete_data()
    create_data()

if __name__ == '__main__':
    main()
