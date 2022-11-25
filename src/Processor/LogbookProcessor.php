<?php

namespace Solvrtech\Symfony\Logbook\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class LogbookProcessor implements ProcessorInterface
{
    private array $serverData;

    private array $extraFields = [
        'url' => 'REQUEST_URI',
        'ip' => 'REMOTE_ADDR',
        'httpMethod' => 'REQUEST_METHOD',
        'server' => 'SERVER_NAME',
        'userAgent' => 'HTTP_USER_AGENT',
    ];

    public function __construct($serverData = null)
    {
        if (null === $serverData) {
            $this->serverData = &$_SERVER;
        } elseif (is_array($serverData) || $serverData instanceof \ArrayAccess) {
            $this->serverData = $serverData;
        } else {
            throw new \UnexpectedValueException('$serverData must be an array or object implementing ArrayAccess.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        // skip processing if for some reason request data
        if (!isset($this->serverData['REQUEST_URI'])) {
            return $record;
        }

        $record['extra'] = $this->appendExtraFields($record['extra']);

        return $record;
    }

    /**
     * Append extra fields to record array
     * 
     * @param  array $extra
     * 
     * @return array
     */
    private function appendExtraFields(array $extra): array
    {
        foreach ($this->extraFields as $key => $val) {
            $extra[$key] = $this->serverData[$val] ?? null;
        }

        return $extra;
    }
}
