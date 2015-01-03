<?php

namespace FrankDeJonge\LockedConsoleCommand;

interface SpecifiesLockName
{
    /**
     * Get the lock name.
     *
     * @return string
     */
    public function getLockName();
}