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
