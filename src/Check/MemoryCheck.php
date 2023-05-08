<?php

namespace Solvrtech\Logbook\Check;

use Exception;
use Solvrtech\Logbook\Model\ConditionModel;

class MemoryCheck extends CheckService
{
    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'memory';
    }

    /**
     * {@inheritDoc}
     */
    public function run(): ConditionModel
    {
        $condition = new ConditionModel();

        try {
            $memoryUsage = self::getMemoryUsage();

            $condition->setStatus(ConditionModel::OK)
                ->setMeta([
                    'memoryUsage' => $memoryUsage,
                    'unit' => 'Mb',
                ]);
        } catch (Exception $exception) {
        }

        return $condition;
    }

    /**
     * Get memory usage in Megabyte.
     *
     * @return float|int
     */
    private function getMemoryUsage(): float|int
    {
        return round(memory_get_usage() / 1048576, 2);
    }
}
