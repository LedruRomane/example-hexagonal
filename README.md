EXAMPLE HEXAGONAL PROJECT
=========

### This app is a API Symfony app containing:

- a database models & connection PostgreSQL + doctrine
- a GraphQL API for the React front client.

[-> Hexagonal documentation <-](./docs/hexagonal.md)

## Requirements

- Symfony CLI
- Docker

## Setup

- If you already have Symfony CLI locally, you're done!

### List all commands for Makefile (& URLs)

```shell
make
```
### Installation

```shell
make install
```

### Install database
> [!TIP]
> [Working with ULIDs & Database](./docs/ulid.md)
> 
> [Working with Fixtures](./docs/fixtures.md)
```shell
make db.install
make db.fixtures
 ```

## Linting

```shell
make lint
```

## Tests
> [!TIP]
> [How to do debug & test](./docs/tests.md)

```shell
make test
```

## Run the app
> [!WARNING]
> Don't forget to make install before and init db + fixtures.
### Serve symfony
```shell
make serve
```

### Stop symfony
```shell
make stop
```

## Going further

- [How to authenticate as a User for GraphiQL console](./docs/graphiql_auth.md)
- [Tests & debug](./docs/tests.md)
- [Fixtures & stories](./docs/fixtures.md)
- [Working with ULIDs](./docs/ulid.md)
- [GraphiQL: how to trigger queries and mutations](./docs/graphiql.md)
- [Usecase: how to trigger the forgot password](./docs/forgot_password.md)

## Urls

- [GraphiQL (QraphQL Web console)](http://127.0.0.1:63280/graphiql)
- [Mailer](http://127.0.0.1:62551)
- [Symfony profiler](http://127.0.0.1:63280/_profiler)

## Useful tools

- [PhpStorm GraphQL Extension](https://plugins.jetbrains.com/plugin/8097-graphql): provides GraphQL support in PhpStorm
  as well as syntax highlight, schema discovery and autocompletion.
- [PhpStorm UUID/ULID Generator Extension](https://plugins.jetbrains.com/plugin/8320-uuid-generator): provides UUID/ULID generation in PhpStorm.

---  
