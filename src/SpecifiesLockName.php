<?php

namespace FrankDeJonge\LockedConsoleCommand;

use Symfony\Component\Console\Input\InputInterface;

interface SpecifiesLockName
{
    /**
     * Get the lock name.
     *
     * @return string
     */
    public function getLockName(InputInterface $input);
}