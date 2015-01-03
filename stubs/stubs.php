<?php

use FrankDeJonge\LockedConsoleCommand\SpecifiesLockPath;
use Symfony\Component\Console\Command\Command;
use FrankDeJonge\LockedConsoleCommand\SpecifiesLockName;

class CommandThatSpecifiesTheLockName extends Command implements SpecifiesLockName
{
    /**
     * Get the lock name.
     *
     * @return string
     */
    public function getLockName()
    {
        return 'specified.lock.name';
    }
}

class CommandThatSpecifiesTheLockPath extends Command implements SpecifiesLockPath
{

    /**
     * Get the lock path.
     *
     * @return string
     */
    public function getLockPath()
    {
        return '/tmp/lock/path/';
    }
}