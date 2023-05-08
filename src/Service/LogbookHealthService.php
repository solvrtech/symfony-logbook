<?php

namespace Solvrtech\Logbook\Service;

use DateTime;
use Solvrtech\Logbook\Check\CheckInterface;
use Solvrtech\Logbook\LogbookHealth;

class LogbookHealthService
{
    private LogbookHealth $health;
    private ?string $instanceId = null;

    public function __construct(
        LogbookHealth $health,
        string $instanceId = 'default'
    ) {
        $this->health = $health;
        $this->instanceId = $instanceId;
    }

    /**
     * Get all health check results.
     *
     * @return array
     */
    public function getResults(): array
    {
        $results = array_map(function (CheckInterface $check) {
            return $check->result();
        }, $this->health->getChecks());

        return [
            'datetime' => (new DateTime())->format('Y-m-d H:i:s'),
            'instanceId' => $this->instanceId,
            'checks' => $results,
        ];
    }
}
