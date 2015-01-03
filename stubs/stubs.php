<?php

use FrankDeJonge\LockedConsoleCommand\SpecifiesLockPath;
use Symfony\Component\Console\Command\Command;
use FrankDeJonge\LockedConsoleCommand\SpecifiesLockName;
use Symfony\Component\Console\Input\InputInterface;

class CommandThatSpecifiesTheLockName extends Command implements SpecifiesLockName
{
    public function getLockName(InputInterface $input)
    {
        return 'specified.lock.name';
    }
}

class CommandThatSpecifiesTheLockPath extends Command implements SpecifiesLockPath
{
    public function getLockPath(InputInterface $input)
    {
        return '/tmp/lock/path/';
    }
}