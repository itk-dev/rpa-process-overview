#!/usr/bin/env python3

from sqlmodel import Field, SQLModel, Relationship

# Process

class ProcessBase(SQLModel):
    name: str = Field(index=True)

class Process(ProcessBase, table=True):
    id: int | None = Field(default=None, primary_key=True)

    steps: list["ProcessStep"] = Relationship(back_populates="process")

class ProcessPublic(ProcessBase):
    id: int
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
    # pass
