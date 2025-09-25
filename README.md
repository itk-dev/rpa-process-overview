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
