# Symfony Console Locked Commands

In some cases, you'll want only one process of a certain command
to be able to run at a time. The Command decorator supplied in this
package makes this possible by using the LockHandler class from
Symfony's Filesystem Component.

## Credits

The idea wasn't initially mine, I stole it from @seldaek.

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

$command = new LockedCommandDecorator($yourCommand, 'lock-name', '/lock/path'));

### Through interface implementations in the wrapper Command

* Implement `FrankDeJonge\LockedConsoleCommand\SpecifiesLockName` (`::getLockName()`)
* Implement `FrankDeJonge\LockedConsoleCommand\SpecifiesLockPath` (`::getLockPath()`)
