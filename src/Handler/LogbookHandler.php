<?php

namespace Solvrtech\Logbook\Handler;

use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Solvrtech\Logbook\Formatter\LogbookFormatter;
use Solvrtech\Logbook\Transport\TransportInterface;

class LogbookHandler extends AbstractProcessingHandler
{
    private ?string $minLevel;
    private array $config;
    private TransportInterface $transport;

    public function __construct(
        string $minLevel,
        array $config,
        TransportInterface $transport
    ) {
        $this->minLevel = $minLevel;
        $this->config = $config;
        $this->transport = $transport;

        parent::__construct();
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function write(LogRecord|array $record): void
    {
        if ($this->getMinLevel() <= $this->toIntLevel($record['level_name'])) {
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'x-lb-token' => $this->config['apiKey'],
                'x-lb-version' => $this->config['appVersion'],
                'x-lb-instance-id' => $this->config['instanceId'],
                'url' => $this->config['apiUrl'],
            ];

            $this->transport->send(json_encode($record['formatted']), $headers);
        }
    }

    /**
     * Get the minimum log level allowed to be stored from environment.
     *
     * @return int
     */
    private function getMinLevel(): int
    {
        if (null !== $this->minLevel) {
            return $this->toIntLevel($this->minLevel);
        }

        return 0;
    }

    /**
     * Translates log level into int
     *
     * @param string $level
     *
     * @return int
     */
    private function toIntLevel(string $level): int
    {
        $intLevel = 0;

        try {
            $intLevel = match (strtolower($level)) {
                'debug' => 0,
                'info' => 1,
                'notice' => 2,
                'warning' => 3,
                'error' => 4,
                'critical' => 5,
                'alert' => 6,
                'emergency' => 7
            };
        } catch (Exception $e) {
        }

        return $intLevel;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LogbookFormatter();
    }
}
