#!/usr/bin/env python3
from typing import Any

from sqlmodel import Field, SQLModel, Relationship, Column
from sqlalchemy import JSON
from datetime import datetime

# Process

class ProcessBase(SQLModel):
    name: str = Field(index=True)
    meta: dict[str, Any] = Field(sa_column=Column(JSON))

class Process(ProcessBase, table=True):
    __tablename__ = 'process'

    id: int | None = Field(default=None, primary_key=True)

    steps: list["ProcessStep"] = Relationship(back_populates="process")
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

    steps: list["ProcessStepRun"] = Relationship(back_populates="run")
    process: Process | None = Relationship(back_populates="runs")

class ProcessRunPublic(ProcessRunBase):
    id: int
    meta: dict[str, Any]
    steps: list["ProcessStepRun"] = []

# Process step run

class ProcessStepRunBase(SQLModel):
    status: str
    started_at: datetime|None
    finished_at: datetime|None
    failure: dict[str, Any]|None = Field(sa_column=Column(JSON)) #, schema_extra={'examples': "A very nice Item"})

    run_id: int | None = Field(default=None, foreign_key="process_run.id")
    step_id: int | None = Field(default=None, foreign_key="process_step.id")

class ProcessStepRun(ProcessStepRunBase, table=True):
    __tablename__ = 'process_step_run'

    id: int | None = Field(default=None, primary_key=True)

    run: ProcessRun | None = Relationship()
    step: ProcessStep | None = Relationship()
