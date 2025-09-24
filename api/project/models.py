#!/usr/bin/env python3

from sqlmodel import Field, SQLModel, Relationship

# Process

class ProcessBase(SQLModel):
    name: str = Field(index=True)
    meta: str = Field()

class Process(ProcessBase, table=True):
    id: int | None = Field(default=None, primary_key=True)

    steps: list["ProcessStep"] = Relationship(back_populates="process")
    runs: list["ProcessRun"] = Relationship(back_populates="process")

class ProcessPublic(ProcessBase):
    id: int
    meta: str
    steps: list["ProcessStepPublic"] = []

# Process step

class ProcessStepBase(SQLModel):
    index: int = Field()
    name: str = Field(index=True)

    process_id: int | None = Field(default=None, foreign_key="process.id")

class ProcessStep(ProcessStepBase, table=True):
    id: int | None = Field(default=None, primary_key=True)

    process: Process | None = Relationship(back_populates="steps")

class ProcessStepPublic(ProcessStepBase):
    id: int

# Process run

class ProcessRunBase(SQLModel):
    meta: str = Field()
    steps: str = Field()
    process_id: int | None = Field(default=None, foreign_key="process.id")

class ProcessRun(ProcessRunBase, table=True):
    id: int | None = Field(default=None, primary_key=True)

    process: Process | None = Relationship(back_populates="runs")

class ProcessRunPublic(ProcessRunBase):
    id: int
    meta: str
    steps: str
