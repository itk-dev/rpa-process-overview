# API

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

``` shell
pip uninstall --requirement requirements.txt --requirement requirements-dev.txt --yes
pip install --requirement requirements.txt
pip freeze > requirements.txt

pip uninstall --requirement requirements.txt --requirement requirements-dev.txt --yes
pip install --requirement requirements-dev.txt
pip freeze > requirements-dev.txt
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

Defined API keys in `.env.local`, e.g.

``` dotenv
# .env.local
# Get a token from https://generate-random.org/api-token-generator or some such …
# Notice that the values must not be enclose in single quotes!
API_KEYS_READ=["a-not-so-secret-key", "759568492f338454603821a04810eabf"]
API_KEYS_WRITE=["3825e7be2d1ca130063171d8362ad4996e3a0df1e9f6dd2a4dc6bebf38bfc205"]
```

Restart the API to load the updated config.

Use a key:

``` shell
curl http://127.0.0.1:8000/api/v1/process/ --header 'x-api-key: a-not-so-secret-key'
```
