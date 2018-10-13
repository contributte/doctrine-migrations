# Migrations

## Content

- [Usage - how to register](#usage)
- [Extension - how to configure](#configuration)
- [Usage - list of available commands](#commands)

## Usage

At first you should register `MigrationsExtension` at your config file.

```yaml
extensions:
    migrations: Nettrine\Migrations\DI\MigrationsExtension
```

This extension is highly depending on `Symfony\Console`, it does not make sence to use it without `Console`. Take
a look at simple [Contributte/Console](https://github.com/contributte/console) integration.

```
composer require contributte/console
```

```yaml
extensions:
    console: Contributte\Console\DI\ConsoleExtension
```

## Configuration

Default configuration looks like this:

```yaml
migrations:
    table: doctrine_migrations 
    column: version
    directory: %appDir%/../migrations
    namespace: Migrations
    versionsOrganization: null # null, year, year_and_month
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


