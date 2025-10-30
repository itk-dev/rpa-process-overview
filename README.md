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

> [!NOTE]
> Running `task site:update` on [macOS (darwin)](https://en.wikipedia.org/wiki/Darwin_(operating_system)) will pull and
> patch the API Git submodule (cf. [API](#api)). See [`Taskfile.yml`](Taskfile.yml) for details.

Load fixtures with

``` shell
task app:fixtures:load
```

> [!TIP]
> Pro tip! Run
>
> ``` shell
> task fixtures:load --yes
> ```
>
> to load all fixtures in succession (including the fixtures mentioned below).

## Widgets

See [widgets/README.md](widgets/README.md).
<details>
<summary>Widgets development</summary>

If you don't have a crazy fast computer, you can try your luck with the widgets development setup outlined in this
section.

Reset data:

``` shell
task fixtures:load --yes
```

Start Vite dev server:

``` shell
task widgets:dev
```

Open a new terminal window and patch Symfony asset mapper to use a static filename and disable access control:

``` shell
patch --strip=1 < patches/widget-dev.patch
```

Build and watch for changes in the styles:

``` shell
# Force Tailwind to rebuild.
rm var/tailwind/*.css
task console -- tailwind:build --watch
```

Open <http://localhost:3000/> and enjoy. Any changes you make to the widget code should now be reflected (almost)
immediately (you may have to force reload if changing the CSS).

Remove the patch when you're done:

``` shell
patch --strip=1 --reverse < patches/widget-dev.patch
```

For convenience, you can do it all in one go:

``` shell name=widget-dev-all-in-one
rm var/tailwind/*.css
patch --strip=1 < patches/widget-dev.patch && \
task console -- tailwind:build --watch && \
patch --strip=1 --reverse < patches/widget-dev.patch
```

</details>

### Icons

The icons are copied from [heroicons](https://heroicons.com).

## API

For development, we run [AAK-MBU/Process_Dashboard_API](https://github.com/AAK-MBU/Process_Dashboard_API) locally. The
API is added as a [Git submodule](https://git-scm.com/book/en/v2/Git-Tools-Submodules) in the [api](./api) folder.

``` shell
task api:create:api-keys
```

Test access to the API:

``` shell
task api:test
task api:get API_PATH=/api/v1/auth/me

task api:get API_PATH='/api/v1/runs/?process_id=1'
task api:get API_PATH='/api/v1/runs/?process_id=1&meta_filter=name:Gregory%20Mendez'
```

See [`docker-compose.api.yml`](docker-compose.api.yml) for the docker compose setup for the API.

## Updating the API

Run

``` shell
task api:update
```

to update the API to the latest version (the [`main`
branch](https://github.com/AAK-MBU/Process_Dashboard_API/tree/main)).

### Loading data

``` shell
task api:script:run SCRIPT_PATH=«path to seed_data.py»
task api:script:run SCRIPT_PATH=«path to seed_data_aktindsigt.py»
```

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

| Username (sub)      | Roles               | What they can do                              |
|---------------------|---------------------|-----------------------------------------------|
| overview-editor     | overview-editor     | Create and edit overviews                     |
| overview-viewer     | overview-viewer     | View overviews                                |
| process-step-runner | process-step-runner | View overviews and rerun failed process steps |

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
