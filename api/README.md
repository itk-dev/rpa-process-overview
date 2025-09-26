# API

> [!WARNING]
> Don't use this API mock for production!

## Database

``` mermaid
---
title: RPA Process Overview
---
classDiagram
    Process <|-- ProcessStep
    Process <|-- ProcessRun
    ProcessRun <|-- ProcessStepRun

    class Process {
        list[ProcessStep] steps
        list[ProcessRun] runs
    }

    class ProcessStep {
        Process process
        int index
        string name
    }

    class ProcessRun {
        Process process
        list[ProcessStepRun] steps
    }

    class ProcessStepRun {
        ProcessRun run
        string status
        datetime started_at
        datetime finished_at
        JSON failure
    }
```

``` text
GET /api/v1/process
  ?page=…
  ?q=…
GET /api/v1/process/{id}
# https://jsonapi.org/format/#fetching-relationships
GET /api/v1/process/{id}/relations/run
  ?page=…
  ?q=…
  ?id[]=…&id[]=…
(GET /api/v1/process/{id}/run/{id})
POST /api/v1/run/{id}/retry (/api/v1/process/{id}/run/{id}/retry)
(GET /api/v1/process/{id}/run/search)
```

<https://fastapi.tiangolo.com/virtual-environments/#create-a-virtual-environment>

``` shell
source .venv/bin/activate
pip install --requirement requirements.txt

pip install
```

``` shell name=update-run-step
curl --silent --verbose --location 'http://127.0.0.1:8000/api/v1/process/1/run/3/step/2' --header 'content-type: application/json' --data '
{
 "status":"FAILED",
 "started_at": "2025-09-25",
 "failure": {
  "code": 87,
  "failed_at": "2025-09-25"
 }
}
'

curl --silent --verbose --location 'http://127.0.0.1:8000/api/v1/process/1/run/3/step/0' --header 'content-type: application/json' --data '
{
 "status":"SUCCESS",
 "started_at": "2025-09-25"
}
'
```

## Security

Define API keys in `.env.local`, e.g.

``` dotenv
# .env.local
# Get a token from https://generate-random.org/api-token-generator or some such …
# Notice that the values must not be enclosed in single quotes and must not contain spaces!
API_KEYS_READ=["a-not-so-secret-key", "759568492f338454603821a04810eabf"]
API_KEYS_WRITE=["3825e7be2d1ca130063171d8362ad4996e3a0df1e9f6dd2a4dc6bebf38bfc205"]
```

Restart the API to load the updated config.

Use a key:

``` shell
curl http://127.0.0.1:8000/api/v1/process/ --header 'x-api-key: a-not-so-secret-key'
```

> [!TIP]
> During development you may want to effectively disable authorization, and this can be done by adding `null` as a valid
> API key:
>
> ``` dotenv
> # .env.local
> API_KEYS_READ=[null]
> API_KEYS_WRITE=[null]
> ```
>
> Don't do this in production!
