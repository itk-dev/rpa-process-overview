"""The models."""

import enum
from datetime import datetime
from typing import Any

from sqlalchemy import JSON
from sqlalchemy.orm import RelationshipProperty
from sqlmodel import Column, Field, Relationship, SQLModel

from .exception import UpdateError
from .mixins import TimestampsMixin


class StepRunStatus(str, enum.Enum):
    """Status values for a process step run."""

    PENDING = "PENDING"
    SUCCESS = "SUCCESS"
    FAILED = "FAILED"


# Process


class ProcessBase(SQLModel):
    """ProcessBase."""

    name: str = Field(index=True)
    meta: dict[str, Any] = Field(sa_column=Column(JSON))


class Process(ProcessBase, TimestampsMixin, table=True):
    """Process."""

    __tablename__ = "process"

    id: int | None = Field(default=None, primary_key=True)

    steps: list["ProcessStep"] = Relationship(
        back_populates="process",
        sa_relationship=RelationshipProperty(order_by="ProcessStep.index"),
    )
    runs: list["ProcessRun"] = Relationship(back_populates="process")


class ProcessPublic(ProcessBase):
    """ProcessPublic."""

    id: int
    meta: dict[str, Any]
    steps: list["ProcessStepPublic"] = []


# Process step


class ProcessStepBase(SQLModel):
    """ProcessStepBase."""

    index: int = Field()
    name: str = Field(index=True)

    process_id: int | None = Field(default=None, foreign_key="process.id")


class ProcessStep(ProcessStepBase, TimestampsMixin, table=True):
    """ProcessStep."""

    __tablename__ = "process_step"

    id: int | None = Field(default=None, primary_key=True)

    process: Process | None = Relationship(back_populates="steps")


class ProcessStepPublic(ProcessStepBase):
    """ProcessStepPublic."""

    id: int


# Process run


class ProcessRunBase(SQLModel):
    """ProcessRunBase."""

    meta: dict[str, Any] = Field(sa_column=Column(JSON))
    process_id: int | None = Field(default=None, foreign_key="process.id")


class ProcessRun(ProcessRunBase, TimestampsMixin, table=True):
    """ProcessRun."""

    __tablename__ = "process_run"

    id: int | None = Field(default=None, primary_key=True)

    steps: list["ProcessStepRun"] = Relationship(
        back_populates="run",
        sa_relationship=RelationshipProperty(order_by="ProcessStepRun.step_index"),
    )
    process: Process | None = Relationship(back_populates="runs")


class ProcessRunPublic(ProcessRunBase):
    """ProcessRunPublic."""

    id: int
    meta: dict[str, Any]
    steps: list["ProcessStepRun"] = []


# Process step run


class ProcessStepRunBase(SQLModel):
    """ProcessStepRunBase."""

    status: StepRunStatus
    started_at: datetime | None
    finished_at: datetime | None
    failure: dict[str, Any] | None = Field(sa_column=Column(JSON))

    run_id: int | None = Field(default=None, foreign_key="process_run.id")
    step_id: int | None = Field(default=None, foreign_key="process_step.id")
    step_index: int


class ProcessStepRunUpdate(SQLModel):
    """The input type for a process step run update."""

    status: StepRunStatus
    started_at: datetime
    finished_at: datetime | None = None
    failure: dict[str, Any] | None = None


class ProcessStepRun(ProcessStepRunBase, TimestampsMixin, table=True):
    """A process step run."""

    __tablename__ = "process_step_run"

    id: int | None = Field(default=None, primary_key=True)

    run: ProcessRun | None = Relationship(back_populates="steps")
    step: ProcessStep | None = Relationship()

    def apply_update(self, update: ProcessStepRunUpdate) -> "ProcessStepRun":
        """Apply an update."""
        if self.status == StepRunStatus.SUCCESS:
            raise UpdateError(detail="Cannot update successful item")

        self.status = update.status
        self.started_at = update.started_at
        self.finished_at = update.finished_at
        self.failure = update.failure if update.status == StepRunStatus.FAILED else None

        return self
