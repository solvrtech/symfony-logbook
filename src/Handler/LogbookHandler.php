<?php

namespace Solvrtech\Logbook\Handler;

use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Solvrtech\Logbook\Formatter\LogbookFormatter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class LogbookHandler extends AbstractProcessingHandler
{
    private ?string $url;
    private ?string $key;
    private ?string $minLevel;
    private ?string $appVersion;

    public function __construct(
        string $url,
        string $key,
        string $minLevel,
        string $appVersion
    ) {
        $this->url = $url;
        $this->key = $key;
        $this->minLevel = $minLevel;
        $this->appVersion = $appVersion;

        parent::__construct();
    }

    /**
     * @inheritDoc
     *
     * @throws Exception|TransportExceptionInterface
     */
    protected function write(LogRecord|array $record): void
    {
        $httpClient = HttpClient::create();
        if ($this->getMinLevel() <= $this->toIntLevel($record['level_name'])) {
            try {
                $httpClient->request(
                    'POST',
                    "{$this->getAPIUrl()}/api/log/save",
                    [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                            'x-lb-token' => $this->getAPIkey(),
                            'x-lb-version' => $this->appVersion,
                        ],
                        'body' => json_encode($record['formatted']),
                    ]
                );
            } catch (Exception $e) {
            }
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
     * Translate log level into int level
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
     * Get logbook API url.
     *
     * @return string
     *
     * @throws Exception
     */
    private function getAPIUrl(): string
    {
        if (null === $this->url) {
            throw new Exception('Logbook API url not found.');
        }

        return $this->url;
    }

    /**
     * Get logbook API key.
     *
     * @return string
     *
     * @throws Exception
     */
    private function getAPIkey(): string
    {
        if (null === $this->key) {
            throw new Exception('Logbook API key not found.');
        }

        return $this->key;
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LogbookFormatter();
    }
}
