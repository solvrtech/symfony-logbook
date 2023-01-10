<?php

namespace Solvrtech\Symfony\Logbook\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;
use Solvrtech\Symfony\Logbook\Model\LogModel;
use Throwable;

class LogbookFormatter implements FormatterInterface
{
    private LogModel $logModel;

    public function __construct()
    {
        $this->logModel = new LogModel();
    }

    /**
     * @inheritDoc
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    /**
     * @inheritDoc
     */
    public function format(LogRecord $record)
    {
        $this->normalizeContext($record->context);

        return $this->logModel
            ->setMessage($record->message)
            ->setCode($record->level->value)
            ->setLevel($record->level->getName())
            ->setChannel($record->channel)
            ->setDatetime($record->datetime)
            ->setAdditional($record->extra['additional'])
            ->setClient($record->extra['client']);
    }

    /**
     *
     */
    public function normalizeContext(array $context): void
    {
        foreach ($context as $value) {
            if (is_array($value)) {
                self::normalizeContext($value);
            }

            if ($value instanceof Throwable) {
                $this->logModel->setFile(
                    "{$value->getFile()}:{$value->getLine()}"
                );

                $trace = [];
                foreach ($value->getTrace() as $val) {
                    if (isset($val['file'], $val['line'])) {
                        $trace[] = $val['file'].':'.$val['line'];
                    }
                }
                $this->logModel->setStackTrace($trace);
            }
        }
    }
}