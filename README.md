# PhD

[![Build Status](https://travis-ci.org/marella/phd.svg?branch=master)](https://travis-ci.org/marella/phd)
[![Latest Stable Version](https://poser.pugx.org/marella/phd/v/stable)](https://packagist.org/packages/marella/phd) [![Total Downloads](https://poser.pugx.org/marella/phd/downloads)](https://packagist.org/packages/marella/phd) [![Latest Unstable Version](https://poser.pugx.org/marella/phd/v/unstable)](https://packagist.org/packages/marella/phd) [![License](https://poser.pugx.org/marella/phd/license)](https://packagist.org/packages/marella/phd)

PHP Database library. Copied from <a target="_blank" href="https://github.com/laravel/framework">Laravel framework</a>.

<!-- TOC depthFrom:2 depthTo:6 withLinks:1 updateOnSave:1 orderedList:0 -->

- [Installation](#installation)
	- [Requirements](#requirements)
- [Usage](#usage)
- [Configuration](#configuration)
	- [Read / Write Connections](#read-write-connections)
- [Running Raw SQL Queries](#running-raw-sql-queries)
	- [Running A Select Query](#running-a-select-query)
	- [Using Named Bindings](#using-named-bindings)
	- [Running An Insert Statement](#running-an-insert-statement)
	- [Running An Update Statement](#running-an-update-statement)
	- [Running A Delete Statement](#running-a-delete-statement)
	- [Running A General Statement](#running-a-general-statement)
- [Database Transactions](#database-transactions)
	- [Manually Using Transactions](#manually-using-transactions)
- [Using Multiple Database Connections](#using-multiple-database-connections)
- [Testing](#testing)
	- [Real Database Testing](#real-database-testing)
	- [Testing With DB Facade](#testing-with-db-facade)

<!-- /TOC -->

## Installation

```sh
composer require marella/phd
```

### Requirements
- PHP >= 5.5
- PDO PHP Extension

## Usage

Configuration and class API is similar to Laravel.

```php
<?php

require 'vendor/autoload.php';

$factory = new \PhD\ConnectionFactory();
$db = new \PhD\DatabaseManager($config, $factory);

$users = $db->select('select * from users where active = ?', [1]);
```

Or simply use the DB facade:

```php
<?php

require 'vendor/autoload.php';

use PhD\DB;
DB::init($config);

$users = DB::select('select * from users where active = ?', [1]);
```

Example config array can be found below.

Currently supported database systems:
- MySQL

Features NOT provided (currently)
- Query Builder
- Events

## Configuration

In config you may define all of your database connections, as well as specify which connection should be used by default.

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as an associative PHP array
    | however, you may desire to retrieve records in some
    | other format. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_ASSOC,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => 'mysql',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported is shown below to make development simple.
    |
    |
    | All database work is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'forge',
            'username' => 'forge',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

    ],
];
```

### Read / Write Connections

Sometimes you may wish to use one database connection for SELECT statements, and another for INSERT, UPDATE, and DELETE statements.

To see how read / write connections should be configured, let's look at this example:

```php
'mysql' => [
    'read' => [
        'host' => '192.168.1.1',
    ],
    'write' => [
        'host' => '196.168.1.2'
    ],
    'driver'    => 'mysql',
    'database'  => 'database',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
],
```

Note that two keys have been added to the configuration array: `read` and `write`. Both of these keys have array values containing a single key: `host`. The rest of the database options for the `read` and `write` connections will be merged from the main `mysql` array.

So, we only need to place items in the `read` and `write` arrays if we wish to override the values in the main array. So, in this case, `192.168.1.1` will be used as the "read" connection, while `192.168.1.2` will be used as the "write" connection. The database credentials, prefix, character set, and all other options in the main `mysql` array will be shared across both connections.

## Running Raw SQL Queries

Once you have configured your database connection, you may run queries using the `DB` facade. The `DB` facade provides methods for each type of query: `select`, `update`, `insert`, `delete`, and `statement`.

### Running A Select Query

To run a basic query, we can use the `select` method on the `DB` facade:

```php
$users = DB::select('select * from users where active = ?', [1]);
```

The first argument passed to the `select` method is the raw SQL query, while the second argument is any parameter bindings that need to be bound to the query. Typically, these are the values of the `where` clause constraints. Parameter binding provides protection against SQL injection.

The `select` method will always return an `array` of results.

```php
foreach ($users as $user) {
    echo $user['name'];
}
```

### Using Named Bindings

Instead of using `?` to represent your parameter bindings, you may execute a query using named bindings:

```php
$results = DB::select('select * from users where id = :id', ['id' => 1]);
```

### Running An Insert Statement

To execute an `insert` statement, you may use the `insert` method on the `DB` facade. Like `select`, this method takes the raw SQL query as its first argument, and bindings as the second argument:

```php
DB::insert('insert into users (id, name) values (?, ?)', [1, 'Dayle']);
```

### Running An Update Statement

The `update` method should be used to update existing records in the database. The number of rows affected by the statement will be returned by the method:

```php
$affected = DB::update('update users set votes = 100 where name = ?', ['John']);
```

### Running A Delete Statement

The `delete` method should be used to delete records from the database. Like `update`, the number of rows deleted will be returned:

```php
$deleted = DB::delete('delete from users');
```

### Running A General Statement

Some database statements should not return any value. For these types of operations, you may use the `statement` method on the `DB` facade:

```php
DB::statement('drop table users');
```

## Database Transactions

To run a set of operations within a database transaction, you may use the `transaction` method on the `DB` facade. If an exception is thrown within the transaction `Closure`, the transaction will automatically be rolled back. If the `Closure` executes successfully, the transaction will automatically be committed. You don't need to worry about manually rolling back or committing while using the `transaction` method:

```php
DB::transaction(function () {
    DB::update('update users set votes = 1');

    DB::delete('delete from posts');
});
```

### Manually Using Transactions

If you would like to begin a transaction manually and have complete control over rollbacks and commits, you may use the `beginTransaction` method on the `DB` facade:

```php
DB::beginTransaction();
```

You can rollback the transaction via the `rollBack` method:

```php
DB::rollBack();
```

Lastly, you can commit a transaction via the `commit` method:

```php
DB::commit();
```

## Using Multiple Database Connections

When using multiple connections, you may access each connection via the `connection` method on the `DB` facade. The `name` passed to the `connection` method should correspond to one of the connections listed in your `config/database.php` configuration file:

```php
$users = DB::connection('foo')->select(...);
```

You may also access the raw, underlying PDO instance using the `getPdo` method on a connection instance:

```php
$pdo = DB::connection()->getPdo();
```

## Testing

```sh
# cd to project's root directory
chmod +x phpunit.sh
./phpunit.sh
```

### Real Database Testing

Some of the tests run on a real database. To enable them, copy `config.example.php` to `config.php` in `tests` directory, fill in the database details and run `phpunit` again.

### Testing With DB Facade

`DB` class is a static proxy to a `DatabaseManager` instance. Use the `DB::setFacadeRoot()` method to pass a mock object. It accepts only a `DatabaseManager` instance so a stub has to be created.

```php
class DatabaseManagerStub extends DatabaseManager
{
    public function __construct()
    {
    }
}

...

// inside test method
$mock = $this->getMock('DatabaseManagerStub');
DB::setFacadeRoot($mock);
```

See the tests directory for more examples.
