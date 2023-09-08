<?php

namespace Solvrtech\Logbook\Transport\Redis;

use Solvrtech\Logbook\Transport\AbstractAsyncTransport;

class RedisTransport extends AbstractAsyncTransport
{
    public function __construct(
        Connection $connection
    ) {
        parent::__construct($connection);
    }

    /**
     * {@inheritDoc}
     */
    public function ack(?array $ids = null): void
    {
        try {
            $this->connection->ack($ids);
        } catch (\Exception $exception) {
        }
    }
}