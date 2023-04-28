<?php

namespace Solvrtech\Logbook\Service;

use Solvrtech\Logbook\LogbookHealth;

class LogbookHealthService
{
    private LogbookHealth $health;

    public function __construct(LogbookHealth $health)
    {
        $this->health = $health;
    }

    public function getResults(): array
    {
        $checks = [];

        foreach ($this->health->getChecks() as $check) {
            $checks[] = $check->result();
        }

        return $checks;
    }
}