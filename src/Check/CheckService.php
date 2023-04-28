<?php

namespace Solvrtech\Logbook\Check;

use Solvrtech\Logbook\Model\ConditionModel;

abstract class CheckService implements CheckInterface
{
    /**
     * {@inheritDoc}
     */
    public function result(): ConditionModel
    {
        $condition = $this->run();
        $condition->setKey($this->getKey());

        return $condition;
    }

    /**
     * Runs all check
     *
     * @return ConditionModel
     */
    abstract public function run(): ConditionModel;

    /**
     * Get key of the check.
     *
     * @return string
     */
    abstract public function getKey(): string;
}