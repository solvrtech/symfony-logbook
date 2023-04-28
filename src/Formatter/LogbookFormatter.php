<?php

namespace Solvrtech\Logbook\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;
use Solvrtech\Logbook\Model\ClientModel;
use Solvrtech\Logbook\Model\LogModel;
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
    public function formatBatch(array $records): array
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    /**
     * @inheritDoc
     */
    public function format(LogRecord|array $record): LogModel
    {
        $this->normalizeContext($record['context']);
        $extra = $record['extra'];

        return $this->logModel
            ->setMessage($record['message'])
            ->setCode($record['level'])
            ->setLevel($record['level_name'])
            ->setChannel($record['channel'])
            ->setDatetime($record['datetime'])
            ->setAdditional(
                array_key_exists('additional', $extra) ?
                    $extra['additional'] :
                    []
            )
            ->setClient(
                array_key_exists('client', $extra) ?
                    $extra['client'] :
                    new ClientModel()
            );
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
                        $trace[] = $val['file'] . ':' . $val['line'];
                    }
                }
                $this->logModel->setStackTrace($trace);
            }
        }
    }
}
