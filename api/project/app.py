# https://sqlmodel.tiangolo.com/tutorial/fastapi/simple-hero-api/#sqlmodel-code-models-engine

from fastapi import FastAPI, HTTPException

from fastapi import Depends, Request, Response
from sqlmodel import Session, select
from fastapi_pagination import add_pagination, Page
from fastapi_pagination.ext.sqlmodel import paginate

from .database import create_db_and_tables, engine

from .models import Process, ProcessPublic, ProcessRun, ProcessRunPublic, ProcessStepRun, ProcessStepRunUpdate

def get_session():
    with Session(engine) as session:
        yield session

description = """
"""

app = FastAPI(
    title="RPA Process Overview",
    description=description,
)
add_pagination(app)

@app.on_event("startup")
def on_startup():
    create_db_and_tables()

API_PATH_PREFIX = "/api/v1"

@app.get(API_PATH_PREFIX+"/process/")
def read_process_list(request: Request, response: Response, session: Session = Depends(get_session), q: str = None) -> Page[ProcessPublic]:
    query = (select(Process)
             .order_by(Process.id))

    if q is not None:
        query = query.filter(Process.name.like('%{}%'.format(q)))

    return _set_pagination_links(request, response, paginate(session, query))

@app.get(API_PATH_PREFIX+"/process/{process_id}")
def read_process(*, request: Request, session: Session = Depends(get_session), process_id: int) -> ProcessPublic:
    process = session.get(Process, process_id)
    if not process:
        raise HTTPException(status_code=404, detail="Process not found")

    return process

@app.get(API_PATH_PREFIX+"/process/{process_id}/run")
def read_process_run_list(*, request: Request, response: Response, session: Session = Depends(get_session), process_id: int, q: str = None) -> Page[ProcessRunPublic]:
    process = session.get(Process, process_id)
    if not process:
        raise HTTPException(status_code=404, detail="Process not found")

    query = (select(ProcessRun)
             .filter(ProcessRun.process==process))

    if q is not None:
        query = query.filter(ProcessRun.meta.like('%{}%'.format(q)))

    return _set_pagination_links(request, response, paginate(session, query))

@app.get(API_PATH_PREFIX+"/process/{process_id}/run/{run_id}")
def read_process_run(*, request: Request, response: Response, session: Session = Depends(get_session), process_id: int, run_id: int) -> ProcessRunPublic:
    process = session.get(Process, process_id)
    if not process:
        raise HTTPException(status_code=404, detail="Process not found")

    run = session.get(ProcessRun, run_id)
    if not run:
        raise HTTPException(status_code=404, detail="Run not found")

    return run

@app.post(API_PATH_PREFIX+"/process/{process_id}/run/{run_id}/retry")
def retry_process_run(*, request: Request, response: Response, session: Session = Depends(get_session), process_id: int, run_id: int):
    process = session.get(Process, process_id)
    if not process:
        raise HTTPException(status_code=404, detail="Process not found")

    run = session.get(ProcessRun, run_id)
    if not run:
        raise HTTPException(status_code=404, detail="Run not found")

    return {'ok': True}

@app.post(API_PATH_PREFIX+"/process/{process_id}/run/{run_id}/step/{step_index}")
def update_process_run(*, stuff: ProcessStepRunUpdate, session: Session = Depends(get_session), process_id: int, run_id: int, step_index: int) -> ProcessRunPublic:
    process = session.get(Process, process_id)
    if not process:
        raise HTTPException(status_code=404, detail="Process not found")

    run = session.get(ProcessRun, run_id)
    if not run:
        raise HTTPException(status_code=404, detail="Run not found")

    try:
        step = process.steps[step_index]
    except IndexError:
        raise HTTPException(status_code=404, detail="Step not found")

    item = session.query(ProcessStepRun).filter_by(run=run, step_index=step_index).one_or_none()
    # if item is not None and item.status == STATUS_SUCCESS:
    #     raise HTTPException(status_code=400, detail="Cannot update successful item")

    if item is None:
        item = ProcessStepRun(
            process=process,
            run=run,
            step=step,
            # @todo Set this when step is set.
            step_index=step.index,
        )
    item.update(stuff)

    session.add(item)
    session.commit()

    return run


def _set_pagination_links(request: Request, response: Response, pagination: any) -> any:
    # https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Link
    links = []
    page = pagination.page

    links.append('<{}>; rel="self"'.format(request.url))
    if page > 1:
        links.append('<{}>; rel="prev"'.format(request.url.include_query_params(page=page-1)))
    if page < pagination.pages:
        links.append('<{}>; rel="next"'.format(request.url.include_query_params(page=page+1)))

    if len(links) > 0:
        response.headers["Link"] = ', '.join(links)

    return pagination
