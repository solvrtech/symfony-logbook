<?php

namespace Solvrtech\Symfony\Logbook;

class LogbookHealth
{
    private array $checks;

    public function __construct(array $checks)
    {
        $this->checks = $checks;
    }

    /**
     * Get all available checks.
     *
     * @return array
     */
    public function getChecks(): array
    {
        return $this->checks;
    }
}
