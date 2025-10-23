"""Fixtures."""

from datetime import timedelta

from faker import Faker

from sqlmodel import Session, create_engine, delete
from app.db.database import create_db_and_tables, get_connection_url
from app.models import (
    Process,
    ProcessRun,
    ProcessStep,
    ProcessStepRun,
    StepRunStatus,
)


class Fixtures:
    """Load fixtures."""

    def delete_data(self) -> None:
        """Delete all data."""

        engine = create_engine(get_connection_url())
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

    def create_data(self, seed: int = 19750523) -> None:
        """Create data."""
        fake = Faker()
        fake.seed_instance(seed)

        engine = create_engine(get_connection_url())
        with Session(engine) as session:
            number_of_processes = fake.pyint(50, 200)
            for _ in range(number_of_processes):
                meta = {
                    "description": fake.sentence(5),
                    "run_metadata_schema": {
                        "cpr": "string",
                        "name": "string",
                        "branch": "string",
                    }
                }
                process = Process(
                    name=fake.sentence(4),
                    meta=meta,
                )
                session.add(process)

                print(f"process {process}")

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

                    print(f" step {step}")

                number_of_runs = fake.pyint(0, 100)
                for _ in range(number_of_runs):
                    meta = {
                        "cpr": str(fake.random_number(10, fix_len=True)),
                        "name": fake.name(),
                        "branch": fake.company(),
                    }

                    run = ProcessRun(
                        process=process,
                        meta=meta,
                        entity_id=meta["name"],
                        entity_name=meta["name"],
                    )
                    session.add(run)

                    print(f"  run {run}")

                    failed_step_index = fake.pyint(-1, number_of_steps + 1)
                    started_at = fake.past_datetime()
                    for index in range(number_of_steps):
                        status = StepRunStatus.SUCCESS if index < failed_step_index else StepRunStatus.PENDING
                        started_at = started_at + timedelta(seconds=fake.pyint(1, 1000))
                        finished_at = started_at + timedelta(seconds=fake.pyint(3, 42))
                        failure = None
                        if index == failed_step_index:
                            status = StepRunStatus.FAILED
                            occurred_at = (finished_at - timedelta(seconds=-fake.pyint(0, 3))).isoformat()
                            finished_at = None
                            failure = {
                                "code": fake.pyint(1, 7),
                                "message": fake.sentence(),
                                "retryable": fake.boolean(),
                                "occurred_at": occurred_at,
                            }
                        step_run = ProcessStepRun(
                            status=status,
                            started_at=started_at if status != StepRunStatus.PENDING else None,
                            finished_at=finished_at if status != StepRunStatus.PENDING else None,
                            process=process,
                            run=run,
                            step=steps[index],
                            # An event listener show set this.
                            step_index=index,
                            failure=failure,
                        )
                        session.add(step_run)

                        print(f"   step_run {step_run}")

                        # @todo Should we generate pending steps?
                        # if status == StepRunStatus.FAILED:
                        #     break    # noqa: ERA001

                    # An event listener should handle this.
                    run.update_status()

            session.commit()

    def load(self) -> None:
        """Load all fixtures."""

        create_db_and_tables()
        self.delete_data()
        self.create_data()


if __name__ == "__main__":
    Fixtures().load()
