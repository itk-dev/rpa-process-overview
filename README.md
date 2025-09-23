# RPA Process Overview

``` shell
task help
```

## Production

``` shell
# .env.local
TASK_DOCKER_COMPOSE='itkdev-docker-compose'
TASK_COMPOSER_INSTALL_ARGS='--no-dev'
```

## API Mock

``` shell
docker compose up --build --detach --wait

curl "http://$(docker compose port api 8000)/openapi.json"
curl "http://$(docker compose port api 8000)/api/v1/process/"
curl "http://$(docker compose port api 8000)/api/v1/process/" --header 'x-api-key: a-not-so-secret-key'
```

Create some data:

``` shell
docker compose exec api uv run python -m src.api.create-data
curl "http://$(docker compose port api 8000)/api/v1/process/" --header 'x-api-key: a-not-so-secret-key'
```

See [api/README.md](api/README.md) for some more details.

## CORS

We use [NelmioCorsBundle](https://symfony.com/bundles/NelmioCorsBundle/current/index.html) for widget development.

``` shell
curl "http://$(task --silent compose -- port nginx 8080)/group/1/overview/1/data"
```

``` shell name=cors-test-widget-dev
curl -H "Origin: http://127.0.0.1:3000/ProcessOverview?page=3" \
    -H "Access-Control-Request-Method: GET" \
    -X OPTIONS --verbose \
    "http://$(task --silent compose -- port nginx 8080)/group/1/overview/1/data"
```

## Mock API

We use [faker](https://github.com/dotronglong/faker) to mock the RPA process API.

After changing mocks in `mocks/`, you must run `task compose -- restart faker` to reload the new data.

``` shell
curl "http://$(task --silent compose -- port faker 3030)/api/v1/process"
```
