<?php

namespace Solvrtech\Logbook\Transport;

interface AsyncTransportInterface
{
    /**
     * Retrieve logs from the connection
     *
     * @return array|null
     */
    public function get(): ?array;

    /**
     * Acknowledge the consumption of logs with the specified identifiers.
     *
     * @param array|null $ids
     */
    public function ack(?array $ids = null): void;
}