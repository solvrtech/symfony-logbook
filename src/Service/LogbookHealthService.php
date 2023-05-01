<?php

namespace Solvrtech\Symfony\Logbook\Service;

use Solvrtech\Symfony\Logbook\LogbookHealth;

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
