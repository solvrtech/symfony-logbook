<?php

namespace Solvrtech\Logbook\Check;

use Exception;
use Solvrtech\Logbook\Exception\LogbookHealthException;
use Solvrtech\Logbook\Model\ConditionModel;

class CPULoadCheck extends CheckService
{
    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'cpu-load';
    }

    /**
     * {@inheritDoc}
     */
    public function run(): ConditionModel
    {
        $condition = new ConditionModel();

        try {
            $cpuLoad = self::getLoadAverage();

            $condition->setStatus(ConditionModel::OK)
                ->setMeta([
                    'cpuLoad' => $cpuLoad,
                ]);
        } catch (Exception $exception) {
        }

        return $condition;
    }

    /**
     * Get system load average.
     *
     * @return array
     *
     * @throws LogbookHealthException
     */
    private function getLoadAverage(): array
    {
        $cpuLoad = sys_getloadavg();

        if (!$cpuLoad) {
            throw new LogbookHealthException();
        }

        return [
            'lastMinute' => $cpuLoad[0],
            'last5Minutes' => $cpuLoad[1],
            'last15Minutes' => $cpuLoad[2],
        ];
    }
}
