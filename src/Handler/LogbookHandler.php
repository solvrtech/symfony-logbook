<?php

namespace Solvrtech\Symfony\Logbook\Handler;

use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Component\HttpClient\HttpClient;

class LogbookHandler extends AbstractProcessingHandler
{
    private ?string $url;
    private ?string $key;

    public function __construct(
        string $url,
        string $key,
        int|string|Level $level = Level::Debug,
        bool $bubble = true
    ) {
        $this->url = $url;
        $this->key = $key;

        parent::__construct($level, $bubble);
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function write(LogRecord $record): void
    {
        $httpClient = HttpClient::create();
        try {
            $httpClient->request(
                'POST',
                "{$this->getAPIUrl()}/api/log/save",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'token' => $this->getAPIkey(),
                    ],
                    'body' => $record['formatted'],
                ]
            );
        } catch (Exception $e) {
        }
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
        return new JsonFormatter();
    }
}
