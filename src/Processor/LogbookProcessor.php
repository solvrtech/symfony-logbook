<?php

namespace Solvrtech\Logbook\Processor;

use ArrayAccess;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Solvrtech\Logbook\Model\ClientModel;
use UnexpectedValueException;

class LogbookProcessor implements ProcessorInterface
{
    private const URL = "REQUEST_URI";
    private const IP = "REMOTE_ADDR";
    private const HTTP_METHOD = "REQUEST_METHOD";
    private const SERVER = "SERVER_NAME";
    private const USER_AGENT = "HTTP_USER_AGENT";

    private array $serverData;

    public function __construct($serverData = null)
    {
        if (null === $serverData) {
            $this->serverData = &$_SERVER;
        } elseif (is_array($serverData) || $serverData instanceof ArrayAccess) {
            $this->serverData = $serverData;
        } else {
            throw new UnexpectedValueException(
                '$serverData must be an array or object implementing ArrayAccess.'
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(LogRecord|array $record): LogRecord|array
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
     * @param array $extra
     *
     * @return array
     */
    private function appendExtraFields(array $extra): array
    {
        return [
            'additional' => $extra,
            'client' => (new ClientModel())
                ->setUrl($this->serverData[self::URL])
                ->setServer($this->serverData[self::SERVER])
                ->setHttpMethod($this->serverData[self::HTTP_METHOD])
                ->setIp($this->serverData[self::IP])
                ->setUserAgent($this->serverData[self::USER_AGENT]),
        ];
    }
}
