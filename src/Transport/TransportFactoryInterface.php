<?php

namespace Solvrtech\Logbook\Transport;

interface TransportFactoryInterface
{
    /**
     * Create a new log transport instance based on the provided Data Source Name (DSN).
     *
     * @param string $dsn
     *
     * @return TransportInterface
     */
    public function create(string $dsn): TransportInterface;

    /**
     * Check if the given Data Source Name (DSN) is supported for creating a log transport.
     *
     * @param string $dsn
     *
     * @return bool
     */
    public function supports(string $dsn): bool;
}