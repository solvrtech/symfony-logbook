<?php

namespace Solvrtech\Symfony\Logbook\Check;

use Solvrtech\Symfony\Logbook\Model\ConditionModel;

interface CheckInterface
{
    /**
     * Get the result of the health check.
     *
     * @return ConditionModel
     */
    public function result(): ConditionModel;
}
