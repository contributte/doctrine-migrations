# Migrations

## Content

- [Usage - how to register](#usage)
- [Extension - how to configure](#configuration)
- [Usage - list of available commands](#commands)

## Usage

- Use Symfony/Console integration [Contributte/Console](https://github.com/contributte/console)
- Register extension

```yaml
extensions:
    migrations: Nettrine\Migrations\DI\MigrationsExtension
```

## Configuration

- Default configuration

```yaml
migrations:
    table: doctrine_migrations # database table for applied migrations
    column: version # database column for applied migrations
    directory: %appDir%/../migrations # directory, where all migrations are stored
    namespace: Migrations # namespace of migration classes
    versionsOrganization: null # null, "year" or "year_and_month", organizes migrations to subdirectories
```

## Commands

[TODO]

