# RPA Process Overview

``` shell
task help
```

## Production

First, add a little config to make our tasks use the right docker compose setup:

``` shell
# .env.local
TASK_DOCKER_COMPOSE='itkdev-docker-compose'
TASK_COMPOSER_INSTALL_ARGS='--no-dev'
```

Update the site by running:

``` shell
task site:update
```

## Development

Run

``` shell
task site:update
```

to get things started.

Load fixtures with

``` shell
task fixtures:load
```

## API Mock

We use a [FastAPI](https://fastapi.tiangolo.com) app to mock the RPA process overview API.

``` shell
curl "http://$(docker compose port api 8000)/openapi.json"
curl "http://$(docker compose port api 8000)/api/v1/process"
curl "http://$(docker compose port api 8000)/api/v1/process" --header 'x-api-key: a-not-so-secret-key'
```

Create some fixture data for the API:

``` shell
task api:fixtures:load
curl "http://$(docker compose port api 8000)/api/v1/process/" --header 'x-api-key: a-not-so-secret-key'
```

See [api/README.md](api/README.md) for some more details (and [`docker-compose.api.yml`](docker-compose.api.yml) for the
docker compose setup).

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

## User management

We have a number of commands for managing users. Run

``` shell
task console -- list app:user
```

to see the list of user related commands.
