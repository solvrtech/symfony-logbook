<?php

namespace Solvrtech\Logbook\Transport\Sync;

use Solvrtech\Logbook\Transport\TransportFactoryInterface;
use Solvrtech\Logbook\Transport\TransportInterface;

class SyncTransportFactory implements TransportFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(string $dsn): TransportInterface
    {
        return new SyncTransport();
    }

    /**
     * {@inheritDoc}
     */
    public function supports(string $dsn): bool
    {
        return str_starts_with($dsn, 'sync://');
    }
}