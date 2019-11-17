# Nettrine Migrations

[Doctrine\Migrations](http://docs.doctrine-project.org/projects/doctrine-migrations/en/latest/) for Nette Framework.

## Content

- [Setup](#setup)
- [Configuration](#configuration)
- [Usage - available commands](#commands)

## Setup

Install package

```bash
composer require nettrine/migrations
```

Configure extension

```yaml
extensions:
    migrations: Nettrine\Migrations\DI\MigrationsExtension
    
migrations:
    directory: %appDir%/../migrations
```

This extension is highly dependent on `Symfony\Console`, it does not make sense to use it without `Console`. Take
a look at simple [Contributte/Console](https://github.com/contributte/console) integration and don't forget to also register `Nettrine\ORM\DI\OrmConsoleExtension`, otherwise migrations and fixtures won't work.

```
composer require contributte/console
```

```yaml
extensions:
    console: Contributte\Console\DI\ConsoleExtension
    orm.console: Nettrine\ORM\DI\OrmConsoleExtension
```

## Configuration

Default configuration should look like this:

```yaml
migrations:
    table: doctrine_migrations 
    column: version
    directory: %appDir%/../migrations
    namespace: Migrations
    versionsOrganization: null # null, year, year_and_month
    customTemplate: null # path to custom template
```

### Kdyby/Doctrine

If you want to use [Kdyby/Doctrine](https://github.com/Kdyby/Doctrine), please use this additional configuration: 

```yaml
decorator:
    Symfony\Component\Console\Command\Command:
        tags: [kdyby.console.command]
    Symfony\Component\Console\Helper\Helper:
        tags: [kdyby.console.helper]
```

## Commands

Type `bin/console` in your terminal and there should be a `migrations` command group.

`migrations:diff` command requires `EntityManagerHelper`. You should either register it yourself or setup `OrmConsoleExtension` from [nettrine/orm](https://github.com/nettrine/orm)

![commands](https://raw.githubusercontent.com/nettrine/migrations/master/.docs/assets/commands.png)

## Dependency Injection

It is possible to use either `@inject` annotation or `inject*()` methods on migration classes:

```php
/**
 * @var MyService
 * @inject
 */
public $myService;
```

```php
/**
 * @var MyService
 */
private $myService;

public function injectMyService(MyService $myService): void
{
    $this->myService = $myService;
}
```

Read more at official [Nette documentation](https://doc.nette.org/cs/2.4/di-usage).


