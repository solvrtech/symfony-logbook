<?php

namespace Solvrtech\Symfony\Logbook\Check;

use Exception;
use Solvrtech\Symfony\Logbook\Exception\LogbookHealthException;
use Solvrtech\Symfony\Logbook\Model\ConditionModel;
use Symfony\Component\Process\Process;

class UsedDiskCheck extends CheckService
{
    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'used-disk';
    }

    /**
     * {@inheritDoc}
     */
    public function run(): ConditionModel
    {
        $condition = new ConditionModel();

        try {
            $diskSpace = self::getUsedDiskSpace();

            $condition->setStatus(ConditionModel::OK)
                ->setMeta([
                    'usedDiskSpace' => $diskSpace,
                    'unit' => '%',
                ]);
        } catch (Exception $exception) {
        }

        return $condition;
    }

    /**
     * Get used disk space on the system in percentage.
     *
     * @return int
     *
     * @throws LogbookHealthException
     */
    private function getUsedDiskSpace(): int
    {
        $process = Process::fromShellCommandline('df -P .');
        $process->run();
        $output = $process->getOutput();

        preg_match('/(\d*)%/', $output, $matches);

        if (null === $matches) {
            throw new LogbookHealthException();
        }

        return (int)$matches[0];
    }
}
