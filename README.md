# PhD

[![Build Status](https://travis-ci.org/marella/phd.svg?branch=master)](https://travis-ci.org/marella/phd)
[![StyleCI](https://styleci.io/repos/51870528/shield?style=flat)](https://styleci.io/repos/51870528)
[![Latest Stable Version](https://poser.pugx.org/marella/phd/v/stable)](https://packagist.org/packages/marella/phd) [![Total Downloads](https://poser.pugx.org/marella/phd/downloads)](https://packagist.org/packages/marella/phd) [![Latest Unstable Version](https://poser.pugx.org/marella/phd/v/unstable)](https://packagist.org/packages/marella/phd) [![License](https://poser.pugx.org/marella/phd/license)](https://packagist.org/packages/marella/phd)

PHP Database library. Copied from <a target="_blank" href="https://github.com/laravel/framework">Laravel framework</a>.

```sh
composer require marella/phd
```

```php
<?php

require 'vendor/autoload.php';
$config = require 'config/database.php'; // load config array from a file

use PhD\DB;
DB::init($config);

$users = DB::select('select * from users where active = ?', [1]);
```

See the [wiki][wiki] for more details and documentation.

[wiki]: https://github.com/marella/phd/wiki
