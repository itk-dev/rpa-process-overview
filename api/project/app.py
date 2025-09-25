# https://sqlmodel.tiangolo.com/tutorial/fastapi/simple-hero-api/#sqlmodel-code-models-engine

from typing import Any

from fastapi import (Depends, FastAPI, HTTPException, Request, Response,
                     Security, status)
from fastapi.security import APIKeyHeader
from fastapi_pagination import Page, add_pagination
from fastapi_pagination.ext.sqlmodel import paginate
from sqlmodel import Session, select

from .database import create_db_and_tables, engine
from .models import (Process, ProcessPublic, ProcessRun, ProcessRunPublic,
                     ProcessStepRun, ProcessStepRunUpdate)


class HTTPNotFoundException(HTTPException):
    def __init__(self, detail: Any):
        super().__init__(status_code=status.HTTP_404_NOT_FOUND, detail=detail)


description = """
"""

app = FastAPI(
    title="RPA Process Overview",
    description=description,
)
add_pagination(app)

api_key_header = APIKeyHeader(name="x-api-key", auto_error=False)

API_KEYS_READ = [
    'read-only',
]

API_KEYS_WRITE = [
    'read-write',
]


def get_api_key(
    api_key_header: str = Security(api_key_header),
) -> str:
    """Retrieve and validate an API key from the query parameters or HTTP header.

    Args:
        api_key_header: The API key passed in the HTTP header.

    Returns:
        The validated API key.

    Raises:
        HTTPException: If the API key is invalid or missing.
    """
    if api_key_header in API_KEYS_READ:
        return api_key_header
    raise HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Invalid or missing API Key",
    )


def get_api_key_write(
    api_key_header: str = Security(api_key_header),
) -> str:
    """Retrieve and validate an API key from the query parameters or HTTP header.

    Args:
        api_key_header: The API key passed in the HTTP header.

    Returns:
        The validated API key.

    Raises:
        HTTPException: If the API key is invalid or missing.
    """
    if api_key_header in API_KEYS_WRITE:
        return api_key_header
    raise HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Invalid or missing API Key",
    )


@app.on_event("startup")
def on_startup():
    create_db_and_tables()


API_PATH_PREFIX = "/api/v1"


def get_session():
    with Session(engine) as session:
        yield session


@app.get(API_PATH_PREFIX+"/process/")
def read_process_list(
        *,
        api_key: str = Security(get_api_key),
        request: Request,
        response: Response,
        session: Session = Depends(get_session),
        q: str = None,
) -> Page[ProcessPublic]:
    query = (select(Process)
             .order_by(Process.id))

    if q is not None:
        query = query.filter(Process.name.like('%{}%'.format(q)))

    return _set_pagination_links(request, response, paginate(session, query))


@app.get(API_PATH_PREFIX+"/process/{process_id}")
def read_process(
        *,
        api_key: str = Security(get_api_key),
        request: Request,
        session: Session = Depends(get_session),
        process_id: int,
) -> ProcessPublic:
    process = session.get(Process, process_id)
    if not process:
        raise HTTPNotFoundException(detail="Process not found")

    return process


@app.get(API_PATH_PREFIX+"/process/{process_id}/run")
def read_process_run_list(
        *,
        api_key: str = Security(get_api_key),
        request: Request,
        response: Response,
        session: Session = Depends(get_session),
        process_id: int,
        q: str = None,
) -> Page[ProcessRunPublic]:
    process = session.get(Process, process_id)
    if not process:
        raise HTTPNotFoundException(detail="Process not found")

    query = (select(ProcessRun)
             .filter(ProcessRun.process == process))

    if q is not None:
        query = query.filter(ProcessRun.meta.like('%{}%'.format(q)))

    return _set_pagination_links(request, response, paginate(session, query))


@app.get(API_PATH_PREFIX+"/process/{process_id}/run/{run_id}")
def read_process_run(
        *,
        api_key: str = Security(get_api_key),
        request: Request,
        response: Response,
        session: Session = Depends(get_session),
        process_id: int,
        run_id: int,
) -> ProcessRunPublic:
    process = session.get(Process, process_id)
    if not process:
        raise HTTPNotFoundException(detail="Process not found")

    run = session.get(ProcessRun, run_id)
    if not run:
        raise HTTPNotFoundException(detail="Run not found")

    return run


@app.post(API_PATH_PREFIX+"/process/{process_id}/run/{run_id}/retry")
def retry_process_run(
        *,
        api_key: str = Security(get_api_key_write),
        request: Request,
        response: Response,
        session: Session = Depends(get_session),
        process_id: int,
        run_id: int
):
    process = session.get(Process, process_id)
    if not process:
        raise HTTPNotFoundException(detail="Process not found")

    run = session.get(ProcessRun, run_id)
    if not run:
        raise HTTPNotFoundException(detail="Run not found")

    return {'ok': True}


@app.post(API_PATH_PREFIX+"/process/{process_id}/run/{run_id}/step/{step_index}")
def update_process_run(
        *,
        api_key: str = Security(get_api_key_write),
        update: ProcessStepRunUpdate,
        session: Session = Depends(get_session),
        process_id: int,
        run_id: int,
        step_index: int,
) -> ProcessRunPublic:
    process = session.get(Process, process_id)
    if not process:
        raise HTTPNotFoundException(detail="Process not found")

    run = session.get(ProcessRun, run_id)
    if not run:
        raise HTTPNotFoundException(detail="Run not found")

    try:
        step = process.steps[step_index]
    except IndexError:
        raise HTTPNotFoundException(detail="Step not found")

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
    item.update(update)

    session.add(item)
    session.commit()

    return run


def _set_pagination_links(
        request: Request,
        response: Response,
        pagination: any,
) -> any:
    # https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Link
    links = []
    page = pagination.page

    links.append('<{}>; rel="self"'
                 .format(request.url))
    if page > 1:
        links.append('<{}>; rel="prev"'
                     .format(request.url.include_query_params(page=page-1)))
    if page < pagination.pages:
        links.append('<{}>; rel="next"'
                     .format(request.url.include_query_params(page=page+1)))

    if len(links) > 0:
        response.headers["Link"] = ', '.join(links)

    return pagination
