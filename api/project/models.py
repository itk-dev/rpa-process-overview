#!/usr/bin/env python3
import enum
from datetime import datetime
from typing import Any

from sqlalchemy import JSON
from sqlalchemy.orm import RelationshipProperty
from sqlmodel import Column, Field, Relationship, SQLModel


class StepRunStatus(str, enum.Enum):
    PENDING = 'PENDING'
    SUCCESS = 'SUCCESS'
    FAILED = 'FAILED'

# Process


class ProcessBase(SQLModel):
    name: str = Field(index=True)
    meta: dict[str, Any] = Field(sa_column=Column(JSON))


class Process(ProcessBase, table=True):
    __tablename__ = 'process'

    id: int | None = Field(default=None, primary_key=True)

    steps: list["ProcessStep"] = Relationship(back_populates="process", sa_relationship=RelationshipProperty(order_by="ProcessStep.index"))
    runs: list["ProcessRun"] = Relationship(back_populates="process")


class ProcessPublic(ProcessBase):
    id: int
    meta: dict[str, Any]
    steps: list["ProcessStepPublic"] = []

# Process step


class ProcessStepBase(SQLModel):
    index: int = Field()
    name: str = Field(index=True)

    process_id: int | None = Field(default=None, foreign_key="process.id")


class ProcessStep(ProcessStepBase, table=True):
    __tablename__ = 'process_step'

    id: int | None = Field(default=None, primary_key=True)

    process: Process | None = Relationship(back_populates="steps")


class ProcessStepPublic(ProcessStepBase):
    id: int

# Process run


class ProcessRunBase(SQLModel):
    meta: dict[str, Any] = Field(sa_column=Column(JSON))
    process_id: int | None = Field(default=None, foreign_key="process.id")


class ProcessRun(ProcessRunBase, table=True):
    __tablename__ = 'process_run'

    id: int | None = Field(default=None, primary_key=True)

    steps: list["ProcessStepRun"] = Relationship(back_populates="run",
                                                 sa_relationship=RelationshipProperty(order_by="ProcessStepRun.step_index"),
                                                 )
    process: Process | None = Relationship(back_populates="runs")


class ProcessRunPublic(ProcessRunBase):
    id: int
    meta: dict[str, Any]
    steps: list["ProcessStepRun"] = []

# Process step run


class ProcessStepRunBase(SQLModel):
    status: StepRunStatus
    started_at: datetime | None
    finished_at: datetime | None
    failure: dict[str, Any] | None = Field(sa_column=Column(JSON))

    run_id: int | None = Field(default=None, foreign_key="process_run.id")
    step_id: int | None = Field(default=None, foreign_key="process_step.id")
    step_index: int


class ProcessStepRunUpdate(SQLModel):
    status: StepRunStatus
    started_at: datetime
    finished_at: datetime | None = None
    failure: dict[str, Any] | None = None


class ProcessStepRun(ProcessStepRunBase, table=True):
    __tablename__ = 'process_step_run'

    id: int | None = Field(default=None, primary_key=True)

    run: ProcessRun | None = Relationship()
    step: ProcessStep | None = Relationship()

    def update(self, update: ProcessStepRunUpdate):
        self.status = update.status
        self.started_at = update.started_at
        self.finished_at = update.finished_at
        self.failure = update.failure if update.status == StepRunStatus.FAILED else None
