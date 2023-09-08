<?php

namespace Solvrtech\Logbook\Transport;

interface ConnectionInterface
{
    /**
     * This method allows you to store a log entry, including its body and headers, into the storage system
     * associated with this connection.
     *
     * @param string $body
     * @param array $headers
     *
     * @return string
     */
    public function add(string $body, array $headers): string;

    /**
     * This method allows you to fetch logs from the storage system associated with this connection.
     *
     * @return array|null
     */
    public function get(): ?array;

    /**
     * This method allows you to acknowledge that a list of logs, identified by their unique
     * identifiers, have been successfully consumed.
     *
     * @param array|null $ids
     */
    public function ack(?array $ids = null): void;
}