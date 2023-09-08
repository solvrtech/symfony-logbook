<?php

namespace Solvrtech\Logbook\Transport;

use Exception;

abstract class AbstractAsyncTransport implements TransportInterface, AsyncTransportInterface
{
    public ConnectionInterface $connection;

    public function __construct(
        ConnectionInterface $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function send(string $body, array $headers): string
    {
        try {
            $this->connection->add($body, $headers);
        } catch (Exception $exception) {
        }

        return $body;
    }

    /**
     * {@inheritDoc}
     */
    public function get(): ?array
    {
        try {
            [$logs, $ids] = $this->connection->get();
        } catch (Exception $exception) {
            return null;
        }

        if (null === $logs) {
            return null;
        }

        $batch = [];

        foreach ($logs as $key => $log) {
            $batch['headers'] = $log['data']['headers'];
            $batch['logs'][] = [
                'id' => $log['id'],
                'log' => $log['data']['body'],
            ];
        }

        return 0 < count($batch) ? [$batch, $ids] : null;
    }
}