<?php

namespace Solvrtech\Logbook\Check;

use Solvrtech\Logbook\Model\ConditionModel;

interface CheckInterface
{
    /**
     * Get the result of the health check.
     *
     * @return ConditionModel
     */
    public function result(): ConditionModel;
}
