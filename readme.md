# Symfony (or Laravel) Console Locked Commands

[![Author](http://img.shields.io/badge/author-@frankdejonge-blue.svg?style=flat-square)](https://twitter.com/frankdejonge)
[![Build Status](https://img.shields.io/travis/frankdejonge/locked-console/command/master.svg?style=flat-square)](https://travis-ci.org/frankdejonge/locked-console/command)
[![Quality Score](https://img.shields.io/scrutinizer/g/frankdejonge/locked-console/command.svg?style=flat-square)](https://scrutinizer-ci.com/g/frankdejonge/locked-console/command)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/frankdejonge/locked-console/command.svg?style=flat-square)](https://packagist.org/packages/frankdejonge/locked-console/command)
[![Total Downloads](https://img.shields.io/packagist/dt/frankdejonge/locked-console/command.svg?style=flat-square)](https://packagist.org/packages/frankdejonge/locked-console/command)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/frankdejonge/locked-console/command.svg?style=flat-square)](https://scrutinizer-ci.com/g/frankdejonge/locked-console/command/code-structure)


In some cases, you'll want only one process of a certain command
to be able to run at a time. The Command decorator supplied in this
package makes this possible by using the LockHandler class from
Symfony's Filesystem Component.

## Credits

The idea wasn't initially mine, I stole it from @Seldaek.

# Installation

```
composer require frankdejonge/locked-console-command
```

# Usage

All you have to do, is wrap the command:

```php
<?php

use FrankDeJonge\LockedConsoleCommand\LockedCommandDecorator;
use Symfony\Component\Console\Application;

$application = new Application;
$app->add(new LockedCommandDecorator(new YourConsoleCommand()));
$app->run();
```

### Laravel Usage

```php
use FrankDeJonge\LockedConsoleCommand\LockedCommandDecorator;
Artisan::add(new LockedCommandDecorator(new SomeCommand()));
```

# How does the locking work?

The decorator uses a file lock (supplied by Symfony's LockHandler) to
ensure a lock is set before and released after executing the command.

If a lock is already set for a given task, an info message is shown and
the decorated command is prevented from running.

## Configuration

There are two configurable parts to influence locking.

1. The lock name
2. The lock path

### Trough __constructor argument injection

```php
$command = new LockedCommandDecorator($yourCommand, 'lock-name', '/lock/path'));
```

### Through interface implementations in the wrapper Command

* Implement `FrankDeJonge\LockedConsoleCommand\SpecifiesLockName` (`::getLockName()`)
* Implement `FrankDeJonge\LockedConsoleCommand\SpecifiesLockPath` (`::getLockPath()`)

The SpecifiesLockName interface is especially handy with dynamic lock names, for example:

```php

class SomeQueueWorker extends Command implements SpecifiesLockName
{
    public function getLockName(InputInterface $input)
    {
        return 'root:name:'.$input->getArgument('worker-id');
    }
}
```
