<?php

namespace Solvrtech\Logbook\Transport\Sync;

use Exception;
use Solvrtech\Logbook\Exception\TransportException;
use Solvrtech\Logbook\Transport\TransportInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SyncTransport implements TransportInterface
{
    /**
     * {@inheritDoc}
     */
    public function send(string $body, array $headers): string
    {
        $httpClient = HttpClient::create();
        $apiUrl = $headers['url'];
        unset($headers['url']);

        try {
            $httpClient->request(
                'POST',
                "{$apiUrl}/api/log/save",
                [
                    'headers' => $headers,
                    'body' => $body,
                ]
            );
        } catch (Exception|TransportExceptionInterface $exception) {
            throw new TransportException('LogBook Error: '.$exception->getMessage(), 0, $exception);
        }

        return $body;
    }
}