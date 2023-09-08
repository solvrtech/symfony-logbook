<?php

namespace Solvrtech\Logbook\Transport\Redis;

use Solvrtech\Logbook\Transport\TransportFactoryInterface;
use Solvrtech\Logbook\Transport\TransportInterface;

class RedisTransportFactory implements TransportFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(string $dsn): TransportInterface
    {
        return new RedisTransport(Connection::fromDsn($dsn));
    }

    /**
     * {@inheritDoc}
     */
    public function supports(string $dsn): bool
    {
        return str_starts_with($dsn, 'redis://') || str_starts_with($dsn, 'rediss://');
    }
}