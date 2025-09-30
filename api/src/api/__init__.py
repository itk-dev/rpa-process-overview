"""The API."""
# https://sqlmodel.tiangolo.com/tutorial/fastapi/simple-hero-api/#sqlmodel-code-models-engine

# @todo Resolve this.
# ruff: noqa: FAST002 B008

from typing import Annotated, Any

from fastapi import Depends, FastAPI, HTTPException, Path, Query, Request, Response, Security
from fastapi_pagination import Page, add_pagination
from fastapi_pagination.ext.sqlmodel import paginate
from sqlmodel import Session, select

from .database import create_db_and_tables, engine
from .exception import HTTPNotFoundException, UpdateError
from .models import (
    Process,
    ProcessPublic,
    ProcessRun,
    ProcessRunPublic,
    ProcessStepRun,
    ProcessStepRunUpdate,
    StepRunStatus,
)
from .security import get_api_key, get_api_key_write

description = """
"""

app = FastAPI(
    title="RPA Process Overview",
    description=description,
)
add_pagination(app)


@app.on_event("startup")
def on_startup() -> None:
    """Start the show."""
    create_db_and_tables()


API_PATH_PREFIX = "/api/v1"


def get_session() -> Session:
    """Get session (database)."""
    with Session(engine) as session:
        yield session


description = """
Return only processes with these IDs, e.g `/process?ids[]=42&ids[]=87`.

Invalid (i.e. non-existing) IDs are silently ignored.
"""


@app.get(API_PATH_PREFIX + "/process")
def read_process_list(
    *,
    _: str = Security(get_api_key),
    request: Request,
    response: Response,
    session: Session = Depends(get_session),
    ids: Annotated[
        list[int],
        Query(
            alias="ids[]",
            description=description,
        ),
    ] = [],  # noqa: B006
    q: Annotated[str, Query(description="Search query")] = "",
) -> Page[ProcessPublic]:
    """Get process list.

    ``` shell
    /process
    /process?q=name
    /process?id=…&id=…
    ```
    """
    query = select(Process).order_by(Process.id)

    if len(q) > 0:
        query = query.filter(Process.search_index.like(f"%{q}%"))
    if ids is not None and len(ids) > 0:
        query = query.filter(Process.id.in_(ids))

    return _set_pagination_links(request, response, paginate(session, query))


@app.get(API_PATH_PREFIX + "/process/{process}")
def read_process(
    *,
    _: str = Security(get_api_key),
    session: Session = Depends(get_session),
    process_id: Annotated[int, Path(alias="process")],
) -> ProcessPublic:
    """Read process."""
    process = session.get(Process, process_id)
    if not process:
        raise HTTPNotFoundException(detail="Process not found")

    return process


description_status = (
    """Return only runs with these statusses, e.g `/process/87/run?status[]=FAILED&status[]=PENDING`."""
)


description_ids = """Return only runs with these IDs, e.g `/process?ids[]=42&ids[]=87`.

Invalid (i.e. non-existing) IDs are silently ignored.
"""


@app.get(API_PATH_PREFIX + "/process/{process}/run")
def read_process_run_list(  # noqa: PLR0913
    *,
    _: str = Security(get_api_key),
    request: Request,
    response: Response,
    session: Session = Depends(get_session),
    process_id: Annotated[int, Path(alias="process")],
    status: Annotated[
        list[StepRunStatus],
        Query(
            alias="status[]",
            description=description_status,
        ),
    ] = [],  # noqa: B006
    ids: Annotated[
        list[int],
        Query(
            alias="ids[]",
            description=description_ids,
        ),
    ] = [],  # noqa: B006
    q: str = "",
) -> Page[ProcessRunPublic]:
    """Get process list."""
    process = session.get(Process, process_id)
    if not process:
        raise HTTPNotFoundException(detail="Process not found")

    query = select(ProcessRun).filter(ProcessRun.process == process)

    if status is not None and len(status) > 0:
        query = query.filter(ProcessRun.status.in_(status))
    if q is not None:
        query = query.filter(ProcessRun.search_index.like(f"%{q}%"))
    if ids is not None and len(ids) > 0:
        query = query.filter(ProcessRun.id.in_(ids))

    return _set_pagination_links(request, response, paginate(session, query))


@app.get(API_PATH_PREFIX + "/process/{process_id}/run/{run_id}")
def read_process_run(
    *,
    _: str = Security(get_api_key),
    session: Session = Depends(get_session),
    process_id: int,
    run_id: int,
) -> ProcessRunPublic:
    """Get a process run."""
    process = session.get(Process, process_id)
    if not process:
        raise HTTPNotFoundException(detail="Process not found")

    run = session.get(ProcessRun, run_id)
    if not run:
        raise HTTPNotFoundException(detail="Run not found")

    return run


@app.post(API_PATH_PREFIX + "/process/{process}/run/{run}/retry")
def retry_process_run(
    *,
    _: str = Security(get_api_key_write),
    session: Session = Depends(get_session),
    process_id: Annotated[int, Path(alias="process")],
    run_id: Annotated[int, Path(alias="run")],
) -> dict[str, Any]:
    """Retry process run."""
    process = session.get(Process, process_id)
    if not process:
        raise HTTPNotFoundException(detail="Process not found")

    run = session.get(ProcessRun, run_id)
    if not run:
        raise HTTPNotFoundException(detail="Run not found")

    return {"ok": True}


@app.post(API_PATH_PREFIX + "/process/{process}/run/{run}/step/{step}")
def update_process_run(
    *,
    _: str = Security(get_api_key_write),
    update: ProcessStepRunUpdate,
    session: Session = Depends(get_session),
    process_id: Annotated[int, Path(alias="process")],
    run_id: Annotated[int, Path(alias="run")],
    step_index: Annotated[int, Path(alias="step")],
) -> ProcessRunPublic:
    """Update a process run."""
    process = session.get(Process, process_id)
    if not process:
        raise HTTPNotFoundException(detail="Process not found")

    run = session.get(ProcessRun, run_id)
    if not run:
        raise HTTPNotFoundException(detail="Run not found")

    try:
        step = process.steps[step_index]
    except IndexError as e:
        raise HTTPNotFoundException(detail="Step not found") from e

    item = session.query(ProcessStepRun).filter_by(run=run, step_index=step.index).one_or_none()

    if item is None:
        item = ProcessStepRun(
            process=process,
            run=run,
            step=step,
        )

    try:
        item.apply_update(update)
    except UpdateError as e:
        raise HTTPException(status_code=400, detail=f"Cannot update item: {e}") from e

    session.add(item)
    session.commit()

    return run


def _set_pagination_links(
    request: Request,
    response: Response,
    pagination: any,
) -> any:
    """Set pagination links on response.

    See https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Link for details.
    """
    links = []
    page = pagination.page

    links.append(f'<{request.url}>; rel="self"')
    if page > 1:
        links.append(f'<{request.url.include_query_params(page=page - 1)}>; rel="prev"')
    if page < pagination.pages:
        links.append(f'<{request.url.include_query_params(page=page + 1)}>; rel="next"')

    if len(links) > 0:
        response.headers["Link"] = ", ".join(links)

    return pagination
