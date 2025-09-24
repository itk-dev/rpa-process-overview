# https://sqlmodel.tiangolo.com/tutorial/fastapi/simple-hero-api/#sqlmodel-code-models-engine

from fastapi import FastAPI, HTTPException

from fastapi import Depends, Request, Query
from sqlmodel import Session, select, SQLModel
from typing import Generic, TypeVar

from .database import create_db_and_tables, engine

from .models import Process, ProcessPublic

ResourceType = TypeVar("ResourceType")

class ListResponse(SQLModel, Generic[ResourceType]):
    data: list[ResourceType]
    links: dict[str, str] = {}

class ProcessListResponse(ListResponse):
    data: list[ProcessPublic]

def get_session():
    with Session(engine) as session:
        yield session

app = FastAPI()

@app.on_event("startup")
def on_startup():
    create_db_and_tables()


@app.get("/process/", response_model=ProcessListResponse)
def read_process_list(request: Request, session: Session = Depends(get_session), page: int = 1, page_size: int = Query(default=100, le=100)):
    items = session.exec(
        select(Process)
            .order_by(Process.id)
            .offset((page-1)*page_size)
            # Fetch one more than needed to check if more exist
            .limit(page_size+1)
        ).all()

    has_more = False
    if len(items) > page_size:
        items = items[:-1]
        has_more = True

    url = request.url
    # https://jsonapi.org/format/#fetching-pagination
    links = {}
    if len(items) > 0:
        links["first"] = str(url.include_query_params(page=1))
        if page > 1:
            links["prev"] = str(url.include_query_params(page=page-1))
        if has_more:
            links["next"] = str(url.include_query_params(page=page+1))

    return ListResponse(data=items, links=links)

@app.get("/process/{process_id}", response_model=ProcessPublic)
# @app.get("/process/{process_id}") #, response_model=DANJAResource[ProcessPublic])
def read_process(*, session: Session = Depends(get_session), process_id: int):
    process = session.get(Process, process_id)
    if not process:
        raise HTTPException(status_code=404, detail="Process not found")
    return process
    # return DANJAResource.from_basemodel(process)

# @app.get("/process_step/", response_model=list[ProcessStepPublic])
# def read_process_steps(*, session: Session = Depends(get_session)):
#     items = session.exec(select(ProcessStep)).all()
#     return items

# @app.get("/process_step/{process_step_id}", response_model=ProcessStepPublic)
# def read_process_step(*, session: Session = Depends(get_session), process_step_id: int):
#     process = session.get(ProcessStep, process_step_id)
#     if not process:
#         raise HTTPException(status_code=404, detail="Process step not found")
#     return process


# @app.get("/hmm/", response_model=list[ProcessPublic])
# def read_hmm(
#     *,
#     session: Session = Depends(get_session),
#     offset: int = 0,
#     limit: int = Query(default=100, le=100),
# ):
#     processs = session.exec(select(Process).offset(offset).limit(limit)).all()
#     return processs

# @app.get("/hmm/{process_id}", response_model=ProcessPublic)
# def read_team(*, process_id: int, session: Session = Depends(get_session)):
#     process = session.get(Process, process_id)
#     if not process:
#         raise HTTPException(status_code=404, detail="Process not found")
#     return process
