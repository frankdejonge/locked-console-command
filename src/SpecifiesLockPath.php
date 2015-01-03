<?php

namespace FrankDeJonge\LockedConsoleCommand;

interface SpecifiesLockPath
{
    /**
     * Get the lock path.
     *
     * @return string
     */
    public function getLockPath();
}