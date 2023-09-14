<?php

namespace Solvrtech\Logbook\Transport\Doctrine;

use Exception;
use Solvrtech\Logbook\Exception\TransportException;
use Solvrtech\Logbook\Transport\AbstractAsyncTransport;

class DoctrineTransport extends AbstractAsyncTransport
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
            $this->connection->ack();
        } catch (Exception $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }
}