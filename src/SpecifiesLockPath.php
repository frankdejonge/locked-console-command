<?php

namespace FrankDeJonge\LockedConsoleCommand;

use Symfony\Component\Console\Input\InputInterface;

interface SpecifiesLockPath
{
    /**
     * Get the lock path.
     *
     * @return string
     */
    public function getLockPath(InputInterface $input);
}