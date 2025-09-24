# https://sqlmodel.tiangolo.com/tutorial/fastapi/simple-hero-api/#sqlmodel-code-models-engine

from fastapi import FastAPI, HTTPException

from fastapi import Depends, Request, Query
from sqlmodel import Session, select, SQLModel
from typing import Generic, TypeVar

from .database import create_db_and_tables, engine

from .models import Process, ProcessPublic, ProcessRun, ProcessRunPublic

ResourceType = TypeVar("ResourceType")

class ListResponse(SQLModel, Generic[ResourceType]):
    links: dict[str, str] = {}
    data: list[ResourceType]

class ResourceResponse(SQLModel):
    links: dict[str, str] = {}
    data: SQLModel

class ProcessListResponse(ListResponse):
    data: list[ProcessPublic]

class ProcessResponse(ResourceResponse):
    data: ProcessPublic

class ProcessRunListResponse(ListResponse):
    data: list[ProcessRunPublic]

def get_session():
    with Session(engine) as session:
        yield session

app = FastAPI()

@app.on_event("startup")
def on_startup():
    create_db_and_tables()

API_PATH_PREFIX = "/api/v1"

@app.get(API_PATH_PREFIX+"/process/", response_model=ProcessListResponse)
def read_process_list(request: Request, session: Session = Depends(get_session), page: int = 1, page_size: int = Query(default=100, le=100), q: str = None):
    query = (select(Process)
             .order_by(Process.id)
             .offset((page-1)*page_size)
             # Fetch one more than needed to check if more exist
             .limit(page_size+1))

    if q is not None:
        query = query.filter(Process.name.like('%{}%'.format(q)))

    items = session.exec(query).all()

    has_more = False
    if len(items) > page_size:
        items = items[:-1]
        has_more = True

    url = request.url
    # https://jsonapi.org/format/#fetching-pagination
    links = {
        "self": str(url),
    }
    if len(items) > 0:
        links["first"] = str(url.include_query_params(page=1))
        if page > 1:
            links["prev"] = str(url.include_query_params(page=page-1))
        if has_more:
            links["next"] = str(url.include_query_params(page=page+1))

    return ListResponse(data=items, links=links)

@app.get(API_PATH_PREFIX+"/process/{process_id}", response_model=ProcessResponse)
def read_process(*, request: Request, session: Session = Depends(get_session), process_id: int):
    process = session.get(Process, process_id)
    if not process:
        raise HTTPException(status_code=404, detail="Process not found")

    url = request.url
    # https://jsonapi.org/format/#fetching-pagination
    links = {
        "self": str(url),
        "run": str(url)+"/run",
    }

    return ProcessResponse(data=process, links=links)

@app.get(API_PATH_PREFIX+"/process/{process_id}/run", response_model=ProcessRunListResponse)
def read_process_run(*, request: Request, session: Session = Depends(get_session), process_id: int, page: int = 1, page_size: int = Query(default=10, le=100), q: str = None):
    process = session.get(Process, process_id)
    if not process:
        raise HTTPException(status_code=404, detail="Process not found")

    query = (select(ProcessRun)
             .filter(ProcessRun.process==process)
             .order_by(ProcessRun.id)
             .offset((page-1)*page_size)
             # Fetch one more than needed to check if more exist
             .limit(page_size+1))

    if q is not None:
        query = query.filter(ProcessRun.meta.like('%{}%'.format(q)))

    items = session.exec(query).all()

    has_more = False
    if len(items) > page_size:
        items = items[:-1]
        has_more = True

    url = request.url
    # https://jsonapi.org/format/#fetching-pagination
    links = {
        "self": str(url),
    }
    if len(items) > 0:
        links["first"] = str(url.include_query_params(page=1))
        if page > 1:
            links["prev"] = str(url.include_query_params(page=page-1))
        if has_more:
            links["next"] = str(url.include_query_params(page=page+1))

    return ProcessRunListResponse(data=items, links=links)
