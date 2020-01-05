# Nettrine Migrations

[Doctrine/Migrations](https://www.doctrine-project.org/projects/migrations.html) for Nette Framework.

## Content

- [Setup](#setup)
- [Relying](#relying)
- [Configuration](#configuration)
- [Usage](#usage)
- [3rd party](#3rd-party)
- [Examples](#examples)

## Setup

Install package

```bash
composer require nettrine/migrations
```

Configure extension

```yaml
extensions:
    nettrine.migrations: Nettrine\Migrations\DI\MigrationsExtension
```


## Relying

Take advantage of enpowering this package with 2 extra packages:

- `doctrine/orm`
- `symfony/console`


### `doctrine/orm`

This package relies on `doctrine/orm`, use prepared [nettrine/orm](https://github.com/nettrine/orm) integration.
Doctrine ORM depends on Doctrine DBAL, use prepared [nettrine/dbal](https://github.com/nettrine/dbal) integration

```bash
composer require nettrine/dbal
composer require nettrine/orm
```

Without these packages you can't process fixtures, because fixtures needs connection to database and information about entities. Don't forget to configure Doctrine DBAL & ORM properly with [console bridge](https://github.com/nettrine/orm/tree/master/.docs#console-bridge). Some commands need special treatment.


### `symfony/console`

This package relies on `symfony/console`, use prepared [contributte/console](https://github.com/contributte/console) integration.

```bash
composer require contributte/console
```

```yaml
extensions:
  console: Contributte\Console\DI\ConsoleExtension(%consoleMode%)
```


## Configuration

**Schema definition**

```yaml
nettrine.migrations:
  table: <string>
  column: <string>
  directory: <path>
  namespace: <string>
  versionsOrganization: <null|year|year_and_month>
  customTemplate: <null|path>
```

**Under the hood**

Minimal configuration:

```yaml
nettrine.migrations:
  directory: %appDir%/migrations
```


## Usage

Type `bin/console` in your terminal and there should be a `migrations` command group.

- `migrations:diff`
- `migrations:execute`
- `migrations:generate`
- `migrations:latest`
- `migrations:migrate`
- `migrations:status`
- `migrations:up-to-date`
- `migrations:version`

![Console Commands](https://raw.githubusercontent.com/nettrine/migrations/master/.docs/assets/console.png)

You gonna needed `migrations:diff` and `migrations:migrate` mostly.


### Migration

This is example of migration class.

You can count on [Nette Dependency Injection](https://doc.nette.org/en/3.0/dependency-injection).
Injecting into properties or via `inject<>` method is also supported.

```php
<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200000000001 extends AbstractMigration
{

  /**
   * @var MyCrypto
   * @inject
   */
  public $crypto;

  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE happy (id INT NOT NULL, coding INT NOT NULL, PRIMARY KEY(id))');
  }

}
```


## 3rd party

**kdyby/doctrine**

```yaml
decorator:
  Symfony\Component\Console\Command\Command:
    tags: [kdyby.console.command]
  Symfony\Component\Console\Helper\Helper:
    tags: [kdyby.console.helper]
```


## Examples

You can find more examples in [planette playground](https://github.com/planette/playground) repository.
