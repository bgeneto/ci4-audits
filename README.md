# Bgeneto\Audits
Lightweight audit logging for CodeIgniter 4

## Quick Start

1. Install with Composer: `> composer require bgeneto/audits`
2. Update the database: `> php spark migrate --all`
3. Set up your models:

```php
use Bgeneto\Audits\Traits\AuditsTrait;

class JobModel extends Model
{
	use AuditsTrait;
	protected $afterInsert = ['auditInsert'];
	protected $afterUpdate = ['auditUpdate'];
	protected $afterDelete = ['auditDelete'];
```

## Features

Provides ready-to-use object logging for CodeIgniter 4

## Installation

Install easily via Composer to take advantage of CodeIgniter 4's autoloading capabilities
and always be up-to-date:
```console
> composer require bgeneto/audits
```

Or, install manually by downloading the source files and adding the directory to
`app/Config/Autoload.php`.

Once the files are downloaded and included in the autoload, run any library migrations
to ensure the database is setup correctly:
```console
> php spark migrate --all
```

## Configuration (optional)

The library's default behavior can be altered by extending its config file. Copy
**examples/Audits.php** to **app/Config/Audits.php** and follow the instructions in the
comments. If no config file is found in **app/Config** the library will use its own.

## Usage

Once the library is included all the resources are ready to go and you just need to
specify which models and events to audit. Use AuditsTrait to add support to any models
you would like tracked:

```php
use Bgeneto\Audits\Traits\AuditsTrait;

class JobModel extends Model
{
	use AuditsTrait;
```

Then specify which events you want audited by assigning the corresponding audit methods
for those events:

```php
	protected $afterInsert = ['auditInsert'];
	protected $afterUpdate = ['auditUpdate'];
	protected $afterDelete = ['auditDelete'];
```

The Audits library will create basic logs of each event in the `audits` table, for example:

| id   | source | source_id | user_id | event  | summary               | created_at          |
| ---- | ------ | --------- | ------- | ------ | --------------------- | ------------------- |
| 10   | sites  | 27        | 9       | create | 2 fields: name, phone | 2024-04-05 15:58:40 |
| 11   | jobs   | 11        | 8       | update | 1 fields: description | 2024-04-05 16:01:35 |

