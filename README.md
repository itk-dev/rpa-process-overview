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

### Icons

The icons are copied from [heroicons](https://heroicons.com).

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

[Symfony supports OpenID Connect](https://symfony.com/doc/current/security/access_token.html#using-openid-connect-oidc),
but our IdP does not play well with that. Therefore, we use our own battle-tested [OpenId Connect
Bundle](https://github.com/itk-dev/openid-connect-bundle) for OIDC login.

The bundle is configured with some environment variables:

``` dotenv
# .env.local
ADMIN_OIDC_ALLOW_HTTP=false
# Get these from your IdP provider
ADMIN_OIDC_METADATA_URL=https://…/.well-known/openid-configuration
ADMIN_OIDC_CLIENT_ID=…
ADMIN_OIDC_CLIENT_SECRET=…

ADMIN_OIDC_REDIRECT_URI=https://rpa-process-overview.example.com/

ADMIN_OIDC_ROLE_MAP='{
  "overview-manager": ["ROLE_OVERVIEW_MANAGER"]
}'
```

For local testing of OIDC login, we use [OpenID Provider Mock](https://github.com/geigerzaehler/oidc-provider-mock) (cf.
[`docker-compose.oidc.yml`](docker-compose.oidc.yml)) and the mock is running on
<https://idp.rpa-process-overview.local.itkdev.dk/>.

The following users are defined in the mock (cf. [`docker-compose.oidc.yml`](docker-compose.oidc.yml)):

| Username (sub)   | Roles            |
|------------------|------------------|
| overview-manager | overview-manager |
| user             | user             |

> [!TIP]
> Set `DOCKER_OIDC_DISABLE` to a non-empty value in `.env.local` to disable the OIDC service, e.g.
>
> ``` dotend
> # .env.local
> DOCKER_OIDC_DISABLE=true

## Release

We use a GitHub Actions workflow, [`.github/workflows/create-release.yml`](.github/workflows/create-release.yml), to
releases. The actual content of a release is built by [`bin/create-release`](bin/create-release).

To test building a release, run

``` shell
bin/create-release test
```
