<?php

namespace Solvrtech\Logbook;

class LogbookHealth
{
    private array $checks;

    public function __construct(array $checks)
    {
        $this->checks = $checks;
    }

    /**
     * Returns all available health checks.
     *
     * @return array
     */
    public function getChecks(): array
    {
        return $this->checks;
    }
}
